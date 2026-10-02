<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\SiteNotification;
use Illuminate\Validation\ValidationException;

/**
 * Доставка: расчёт стоимости и действия курьера.
 */
class DeliveryService
{
    public function __construct(private OrderService $orders) {}

    /**
     * Стоимость доставки = базовая стоимость + стоимость за км × расстояние.
     * Если сумма товаров заказа не меньше порога — доставка бесплатная.
     * Все значения задаются в настройках сайта.
     */
    public function calculateCost(float $orderAmount, int $distanceKm): float
    {
        $freeFrom = Setting::number('delivery_free_from');

        if ($freeFrom > 0 && $orderAmount >= $freeFrom) {
            return 0.0;
        }

        return round(Setting::number('delivery_base_cost') + Setting::number('delivery_cost_per_km') * $distanceKm, 2);
    }

    /**
     * Курьер берёт заказ. Обновление атомарное: если два курьера нажмут кнопку одновременно,
     * заказ закрепится только за одним из них.
     */
    public function take(Delivery $delivery, User $courier): void
    {
        $updated = Delivery::query()
            ->availableFor($courier)
            ->whereKey($delivery->id)
            ->update([
                'courier_id' => $courier->id,
                'status' => DeliveryStatus::Assigned->value,
                'assigned_at' => now(),
            ]);

        if (! $updated) {
            throw ValidationException::withMessages(['delivery' => 'Этот заказ уже взял другой курьер или он больше недоступен.']);
        }

        $this->notifyBuyer($delivery->fresh(), 'Курьер назначен', 'Курьер '.$courier->name.' скоро заберёт ваш заказ у производителя.', 'bi-person-check');
    }

    /** Курьер забрал заказ у производителя и везёт покупателю. */
    public function startTransit(Delivery $delivery): void
    {
        if ($delivery->status !== DeliveryStatus::Assigned) {
            throw ValidationException::withMessages(['delivery' => 'Сначала заказ должен быть закреплён за курьером.']);
        }

        $delivery->update(['status' => DeliveryStatus::InTransit, 'picked_up_at' => now()]);

        $this->notifyBuyer($delivery, 'Заказ в пути', 'Курьер забрал заказ и уже едет к вам.', 'bi-truck');
    }

    /** Заказ доставлен — заказ автоматически становится выполненным. */
    public function markDelivered(Delivery $delivery): void
    {
        if ($delivery->status !== DeliveryStatus::InTransit) {
            throw ValidationException::withMessages(['delivery' => 'Отметить доставку можно только для заказа в пути.']);
        }

        $delivery->update(['status' => DeliveryStatus::Delivered, 'delivered_at' => now()]);

        $this->orders->complete($delivery->order);
    }

    /** Оператор вручную назначает курьера. */
    public function assign(Delivery $delivery, User $courier): void
    {
        if ($delivery->status !== DeliveryStatus::Waiting) {
            throw ValidationException::withMessages(['delivery' => 'Курьера можно назначить только доставке, ожидающей курьера.']);
        }

        $delivery->update([
            'courier_id' => $courier->id,
            'status' => DeliveryStatus::Assigned,
            'assigned_at' => now(),
        ]);

        $courier->notify(new SiteNotification(
            'Вам назначена доставка',
            'Оператор закрепил за вами заказ '.$delivery->order->number.'.',
            route('courier.my'),
            'bi-box-seam'
        ));
    }

    /** Оператор снимает курьера — доставка возвращается в список доступных. */
    public function unassign(Delivery $delivery): void
    {
        if ($delivery->status !== DeliveryStatus::Assigned) {
            throw ValidationException::withMessages(['delivery' => 'Снять курьера можно только до того, как он забрал заказ.']);
        }

        $delivery->courier?->notify(new SiteNotification(
            'Доставка снята',
            'Оператор снял с вас заказ '.$delivery->order->number.'.',
            route('courier.my'),
            'bi-x-circle'
        ));

        $delivery->update(['courier_id' => null, 'status' => DeliveryStatus::Waiting, 'assigned_at' => null]);
    }

    private function notifyBuyer(Delivery $delivery, string $title, string $text, string $icon): void
    {
        $order = $delivery->order;

        $order->buyer->notify(new SiteNotification(
            $title.' — '.$order->number,
            $text,
            route('account.orders.show', $order),
            $icon
        ));
    }
}
