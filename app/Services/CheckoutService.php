<?php

namespace App\Services;

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\SiteNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Оформление заказа.
 *
 * Корзина может содержать товары разных производителей. При оформлении она делится
 * на отдельные заказы — по одному на каждого производителя. Все заказы одного оформления
 * получают общий checkout_id (нужен для страницы оплаты и страницы «Спасибо за заказ»).
 */
class CheckoutService
{
    public function __construct(
        private CartService $cart,
        private DeliveryService $delivery,
    ) {}

    /**
     * @param  array  $data  delivery_method, payment_method, recipient_name, recipient_phone, comment, pickup_locations[producer_id]
     * @param  Address|null  $address  адрес доставки (для способа «доставка»)
     * @return Collection<int, Order>
     */
    public function placeOrder(User $user, array $data, ?Address $address = null): Collection
    {
        $summary = $this->cart->summary($user);
        $this->ensureCartCanBeOrdered($summary);

        $method = DeliveryMethod::from($data['delivery_method']);
        $groups = $summary['groups']->values();

        // Для каждого производителя определяем адрес получения
        $pickupLocations = [];

        foreach ($groups as $group) {
            $producer = $group['producer'];

            if ($method === DeliveryMethod::Delivery) {
                if (! $address) {
                    throw ValidationException::withMessages(['address_id' => 'Укажите адрес доставки.']);
                }

                // Доставка локальная: курьеры работают в пределах города производителя
                if ($producer->city_id !== $address->city_id) {
                    throw ValidationException::withMessages([
                        'address_id' => 'Производитель «'.$producer->name.'» доставляет только по городу '
                            .$producer->city->name.'. Выберите самовывоз или адрес в этом городе.',
                    ]);
                }
            } else {
                $locationId = $data['pickup_locations'][$producer->id] ?? null;
                $location = $producer->locations->where('is_pickup_point', true)->firstWhere('id', (int) $locationId);

                if (! $location) {
                    throw ValidationException::withMessages([
                        'pickup_locations.'.$producer->id => 'Выберите пункт самовывоза для «'.$producer->name.'».',
                    ]);
                }

                $pickupLocations[$producer->id] = $location;
            }
        }

        $promo = $summary['discount'] > 0 ? $summary['promo'] : null;
        $commissionPercent = Setting::number('platform_commission_percent');
        $checkoutId = (string) Str::uuid();

        $orders = DB::transaction(function () use ($user, $data, $address, $method, $groups, $pickupLocations, $summary, $promo, $commissionPercent, $checkoutId) {
            $orders = collect();
            $discountLeft = $summary['discount'];

            foreach ($groups as $index => $group) {
                $producer = $group['producer'];
                $subtotal = $group['subtotal'];

                // Скидка по промокоду делится между заказами пропорционально их сумме,
                // последний заказ получает остаток, чтобы не потерять копейки при округлении.
                $discount = $index === $groups->count() - 1
                    ? $discountLeft
                    : round($summary['discount'] * $subtotal / $summary['subtotal'], 2);
                $discountLeft = round($discountLeft - $discount, 2);

                $goodsTotal = round($subtotal - $discount, 2);
                $deliveryCost = $method === DeliveryMethod::Delivery
                    ? $this->delivery->calculateCost($subtotal, $address->distance_km)
                    : 0.0;

                $location = $pickupLocations[$producer->id] ?? null;

                $order = Order::create([
                    'checkout_id' => $checkoutId,
                    'user_id' => $user->id,
                    'producer_id' => $producer->id,
                    'promo_code_id' => $promo?->id,
                    'pickup_location_id' => $location?->id,
                    'status' => OrderStatus::New,
                    'delivery_method' => $method,
                    'payment_method' => $data['payment_method'],
                    'payment_status' => PaymentStatus::Unpaid,
                    'recipient_name' => $data['recipient_name'],
                    'recipient_phone' => $data['recipient_phone'],
                    'address' => $location ? $location->name.', '.$location->address : $address->full_address,
                    'delivery_distance_km' => $method === DeliveryMethod::Delivery ? $address->distance_km : null,
                    'comment' => $data['comment'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'delivery_cost' => $deliveryCost,
                    'total' => round($goodsTotal + $deliveryCost, 2),
                    'commission_percent' => $commissionPercent,
                    'commission_amount' => round($goodsTotal * $commissionPercent / 100, 2),
                ]);

                /** @var CartItem $item */
                foreach ($group['items'] as $item) {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'product_name' => $item->product->name,
                        'variant_name' => $item->variant?->name,
                        'price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'total' => $item->total,
                    ]);
                }

                if ($method === DeliveryMethod::Delivery) {
                    $order->delivery()->create(['status' => DeliveryStatus::Waiting]);
                }

                $orders->push($order);
            }

            $promo?->increment('used_count');
            $this->cart->clear($summary['cart']);

            return $orders;
        });

        $this->notify($user, $orders);

        return $orders;
    }

    /** Проверки корзины перед оформлением. */
    private function ensureCartCanBeOrdered(array $summary): void
    {
        if ($summary['groups']->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Корзина пуста.']);
        }

        if ($summary['has_unavailable']) {
            throw ValidationException::withMessages(['cart' => 'В корзине есть товары, которых нет в наличии. Удалите их, чтобы продолжить.']);
        }

        foreach ($summary['groups'] as $group) {
            if ($group['missing'] > 0) {
                throw ValidationException::withMessages([
                    'cart' => 'Минимальная сумма заказа — '.money($summary['min_order']).'. У производителя «'
                        .$group['producer']->name.'» не хватает '.money($group['missing']).'.',
                ]);
            }
        }
    }

    private function notify(User $user, Collection $orders): void
    {
        foreach ($orders as $order) {
            $order->producer->user->notify(new SiteNotification(
                'Новый заказ '.$order->number,
                'Покупатель '.$user->name.' оформил заказ на сумму '.money($order->total).'.',
                route('producer.orders.show', $order),
                'bi-bag-plus'
            ));
        }

        $user->notify(new SiteNotification(
            $orders->count() > 1 ? 'Оформлено заказов: '.$orders->count() : 'Заказ '.$orders->first()->number.' оформлен',
            $orders->count() > 1
                ? 'Корзина разделена на заказы по производителям: '.$orders->map->number->implode(', ').'.'
                : 'Производитель получил заказ и скоро его подтвердит.',
            route('account.orders.index'),
            'bi-bag-check'
        ));
    }
}
