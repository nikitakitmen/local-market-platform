<?php

namespace Database\Seeders;

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Producer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\DeliveryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Демо-заказы: история выполненных заказов за последний месяц (для аналитики и отзывов)
 * и несколько заказов в разных статусах, чтобы показать работу всех ролей.
 */
class OrderSeeder extends Seeder
{
    private float $commission;

    private DeliveryService $deliveryService;

    public function run(): void
    {
        mt_srand(2026); // одинаковые демо-данные при каждом запуске

        $this->commission = Setting::number('platform_commission_percent');
        $this->deliveryService = app(DeliveryService::class);

        $producers = Producer::approved()->with(['products.variants', 'locations'])->get()->keyBy(fn ($p) => Str::before($p->email, '@'));
        $buyers = User::where('role', UserRole::Buyer)->with('addresses.city')->get()->keyBy(fn ($u) => Str::before($u->email, '@'));
        $courier = User::where('email', 'courier@localmarket.test')->first();
        $courierNn = User::where('email', 'courier2@localmarket.test')->first();

        // 1. История выполненных заказов за 30 дней
        $kazanBuyers = ['buyer', 'maria', 'dmitry', 'igor'];
        $kazanProducers = ['farm', 'bakery', 'coffee', 'flowers'];

        for ($i = 0; $i < 64; $i++) {
            $isNn = $i % 7 === 6;
            $producer = $producers[$isNn ? 'craft' : $kazanProducers[$i % 4]];
            $buyer = $buyers[$isNn ? 'elena' : $kazanBuyers[mt_rand(0, 3)]];
            $createdAt = now()->subDays(32 - intdiv($i, 2))->setTime(mt_rand(9, 19), mt_rand(0, 59));

            $this->makeOrder($buyer, $producer, OrderStatus::Completed, $createdAt, [
                'courier' => $isNn ? $courierNn : $courier,
            ]);
        }

        // Два отменённых заказа в истории
        $this->makeOrder($buyers['dmitry'], $producers['flowers'], OrderStatus::Cancelled, now()->subDays(12), [
            'cancel_reason' => 'Покупатель передумал',
        ]);
        $this->makeOrder($buyers['buyer'], $producers['coffee'], OrderStatus::Cancelled, now()->subDays(6), [
            'cancel_reason' => 'Нет нужного помола, предложили замену',
            'method' => DeliveryMethod::Pickup,
        ]);

        // 2. Текущие заказы в разных статусах
        // Принят, ждёт курьера — появится в «Доступных доставках» курьера
        $this->makeOrder($buyers['buyer'], $producers['bakery'], OrderStatus::Accepted, now()->subMinutes(50), [
            'method' => DeliveryMethod::Delivery, 'payment' => PaymentMethod::Card,
        ]);
        $this->makeOrder($buyers['maria'], $producers['coffee'], OrderStatus::Accepted, now()->subMinutes(35), [
            'method' => DeliveryMethod::Delivery, 'payment' => PaymentMethod::Cash,
        ]);
        $this->makeOrder($buyers['dmitry'], $producers['farm'], OrderStatus::Accepted, now()->subHours(5), [
            'method' => DeliveryMethod::Delivery, 'payment' => PaymentMethod::Sbp,
        ]);

        // Курьер уже везёт заказ
        $this->makeOrder($buyers['buyer'], $producers['farm'], OrderStatus::Accepted, now()->subHours(2), [
            'method' => DeliveryMethod::Delivery, 'payment' => PaymentMethod::Cash,
            'courier' => $courier, 'delivery_status' => DeliveryStatus::InTransit,
        ]);
        // Курьер назначен, но ещё не забрал заказ
        $this->makeOrder($buyers['igor'], $producers['flowers'], OrderStatus::Accepted, now()->subHours(1), [
            'method' => DeliveryMethod::Delivery, 'payment' => PaymentMethod::Card,
            'courier' => $courier, 'delivery_status' => DeliveryStatus::Assigned,
        ]);

        // Новые заказы — производитель должен их принять
        $this->makeOrder($buyers['buyer'], $producers['farm'], OrderStatus::New, now()->subMinutes(15), [
            'method' => DeliveryMethod::Delivery, 'payment' => PaymentMethod::Sbp,
        ]);
        $this->makeOrder($buyers['maria'], $producers['bakery'], OrderStatus::New, now()->subMinutes(40), [
            'method' => DeliveryMethod::Pickup, 'payment' => PaymentMethod::Cash,
        ]);
        // «Проблемный» заказ: производитель не принял его больше суток
        $this->makeOrder($buyers['dmitry'], $producers['coffee'], OrderStatus::New, now()->subDays(2), [
            'method' => DeliveryMethod::Delivery, 'payment' => PaymentMethod::Cash,
        ]);
        // Самовывоз, принят
        $this->makeOrder($buyers['elena'], $producers['craft'], OrderStatus::Accepted, now()->subHours(3), [
            'method' => DeliveryMethod::Pickup, 'payment' => PaymentMethod::Card,
        ]);

        $this->updateSalesCounters();
    }

    private function makeOrder(User $buyer, Producer $producer, OrderStatus $status, Carbon $createdAt, array $options = []): Order
    {
        $method = $options['method'] ?? (mt_rand(0, 3) > 0 ? DeliveryMethod::Delivery : DeliveryMethod::Pickup);
        $payment = $options['payment'] ?? [PaymentMethod::Cash, PaymentMethod::Card, PaymentMethod::Sbp][mt_rand(0, 2)];

        // Позиции заказа: 1–3 случайных товара в наличии
        $products = $producer->products->where('in_stock', true)->shuffle()->take(mt_rand(1, 3))->values();
        $items = [];

        foreach ($products as $product) {
            $variant = $product->variants->where('in_stock', true)->shuffle()->first();
            $price = (float) ($variant?->price ?? $product->price);
            $items[] = ['product' => $product, 'variant' => $variant, 'price' => $price, 'quantity' => mt_rand(1, 2)];
        }

        // Добираем до минимальной суммы заказа, увеличивая количество первой позиции
        $minOrder = Setting::number('min_order_amount');
        $subtotalValue = array_sum(array_map(fn ($item) => $item['price'] * $item['quantity'], $items));

        while ($subtotalValue < $minOrder) {
            $items[0]['quantity']++;
            $subtotalValue += $items[0]['price'];
        }

        $subtotalValue = round($subtotalValue, 2);
        $address = $buyer->addresses->first();
        $location = $producer->locations->where('is_pickup_point', true)->first();
        $isDelivery = $method === DeliveryMethod::Delivery;
        $deliveryCost = $isDelivery ? $this->deliveryService->calculateCost($subtotalValue, $address->distance_km) : 0;

        $isFinal = in_array($status, [OrderStatus::Completed, OrderStatus::Cancelled], true);
        $acceptedAt = $status !== OrderStatus::New && $status !== OrderStatus::Cancelled ? $createdAt->copy()->addMinutes(mt_rand(5, 30)) : null;
        $completedAt = $status === OrderStatus::Completed ? $createdAt->copy()->addMinutes(mt_rand(90, 240)) : null;
        $paidOnline = $payment->isOnline() && $status !== OrderStatus::Cancelled;

        $order = Order::forceCreate([
            'checkout_id' => (string) Str::uuid(),
            'user_id' => $buyer->id,
            'producer_id' => $producer->id,
            'pickup_location_id' => $isDelivery ? null : $location?->id,
            'status' => $status,
            'delivery_method' => $method,
            'payment_method' => $payment,
            'payment_status' => ($status === OrderStatus::Completed || $paidOnline) ? PaymentStatus::Paid : PaymentStatus::Unpaid,
            'recipient_name' => $buyer->name,
            'recipient_phone' => $buyer->phone,
            'address' => $isDelivery ? $address->full_address : $location->name.', '.$location->address,
            'delivery_distance_km' => $isDelivery ? $address->distance_km : null,
            'comment' => mt_rand(0, 3) === 0 ? 'Позвоните, пожалуйста, за 10 минут до приезда.' : null,
            'subtotal' => $subtotalValue,
            'discount' => 0,
            'delivery_cost' => $deliveryCost,
            'total' => $subtotalValue + $deliveryCost,
            'commission_percent' => $this->commission,
            'commission_amount' => round($subtotalValue * $this->commission / 100, 2),
            'cancel_reason' => $options['cancel_reason'] ?? null,
            'paid_at' => $paidOnline ? $createdAt->copy()->addMinutes(2) : $completedAt,
            'accepted_at' => $acceptedAt,
            'completed_at' => $completedAt,
            'cancelled_at' => $status === OrderStatus::Cancelled ? $createdAt->copy()->addHours(1) : null,
            'created_at' => $createdAt,
            'updated_at' => $completedAt ?? $acceptedAt ?? $createdAt,
        ]);

        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item['product']->id,
                'product_variant_id' => $item['variant']?->id,
                'product_name' => $item['product']->name,
                'variant_name' => $item['variant']?->name,
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'total' => $item['price'] * $item['quantity'],
            ]);
        }

        // Доставка (у отменённого заказа доставки нет)
        if ($isDelivery && $status !== OrderStatus::Cancelled) {
            $deliveryStatus = match (true) {
                $status === OrderStatus::Completed => DeliveryStatus::Delivered,
                isset($options['delivery_status']) => $options['delivery_status'],
                default => DeliveryStatus::Waiting,
            };
            $hasCourier = $deliveryStatus !== DeliveryStatus::Waiting;

            $order->delivery()->forceCreate([
                'courier_id' => $hasCourier ? ($options['courier'] ?? null)?->id : null,
                'status' => $deliveryStatus,
                'assigned_at' => $hasCourier ? $acceptedAt?->copy()->addMinutes(10) : null,
                'picked_up_at' => in_array($deliveryStatus, [DeliveryStatus::InTransit, DeliveryStatus::Delivered], true) ? $acceptedAt?->copy()->addMinutes(40) : null,
                'delivered_at' => $completedAt,
                'created_at' => $createdAt,
                'updated_at' => $completedAt ?? $createdAt,
            ]);
        }

        return $order;
    }

    /** Счётчик продаж = количество проданных единиц в выполненных заказах. */
    private function updateSalesCounters(): void
    {
        $sold = Order::completed()->with('items')->get()->flatMap->items
            ->groupBy('product_id')
            ->map(fn ($items) => $items->sum('quantity'));

        foreach ($sold as $productId => $quantity) {
            Product::whereKey($productId)->update(['sales_count' => $quantity]);
        }
    }
}
