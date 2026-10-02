<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\SiteNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Смена статусов заказа: Новый → Принят → Выполнен, либо Отменён.
 */
class OrderService
{
    /** Производитель (или оператор) принимает заказ в работу. */
    public function accept(Order $order): void
    {
        if (! $order->canBeAccepted()) {
            throw ValidationException::withMessages(['order' => 'Принять можно только новый заказ.']);
        }

        $order->update(['status' => OrderStatus::Accepted, 'accepted_at' => now()]);

        $text = $order->isDelivery()
            ? 'Производитель «'.$order->producer->name.'» собирает заказ, скоро его заберёт курьер.'
            : 'Производитель «'.$order->producer->name.'» подготовит заказ к самовывозу.';

        $order->buyer->notify(new SiteNotification(
            'Заказ '.$order->number.' принят',
            $text,
            route('account.orders.show', $order),
            'bi-check2-circle'
        ));
    }

    /** Заказ выполнен: выдан покупателю (самовывоз) или доставлен курьером. */
    public function complete(Order $order): void
    {
        if ($order->status !== OrderStatus::Accepted) {
            throw ValidationException::withMessages(['order' => 'Выполнить можно только принятый заказ.']);
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => OrderStatus::Completed,
                'completed_at' => now(),
                // При получении заказ считается оплаченным (наличные получены при вручении)
                'payment_status' => PaymentStatus::Paid,
                'paid_at' => $order->paid_at ?? now(),
            ]);

            // Если заказ завершает оператор, доставка тоже отмечается выполненной
            if ($order->delivery && $order->delivery->status !== DeliveryStatus::Delivered) {
                $order->delivery->update(['status' => DeliveryStatus::Delivered, 'delivered_at' => now()]);
            }

            // Счётчик продаж используется для сортировки «Популярные»
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::withTrashed()->whereKey($item->product_id)->increment('sales_count', $item->quantity);
                }
            }
        });

        $order->buyer->notify(new SiteNotification(
            'Заказ '.$order->number.' выполнен',
            'Спасибо за покупку! Оцените товары — ваш отзыв поможет другим покупателям.',
            route('account.orders.show', $order),
            'bi-bag-check'
        ));

        $order->producer->user->notify(new SiteNotification(
            'Заказ '.$order->number.' выполнен',
            'Заказ на сумму '.money($order->total).' успешно завершён.',
            route('producer.orders.show', $order),
            'bi-bag-check'
        ));
    }

    /** Отмена заказа покупателем, производителем или оператором. */
    public function cancel(Order $order, User $cancelledBy, ?string $reason = null): void
    {
        if (! $order->canBeCancelled()) {
            throw ValidationException::withMessages(['order' => 'Этот заказ уже нельзя отменить.']);
        }

        $courier = $order->delivery?->courier;

        DB::transaction(function () use ($order, $reason) {
            // У отменённого заказа нет доставки — курьер больше не увидит его в списке
            $order->delivery?->delete();

            $order->update([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);
        });

        $text = 'Заказ '.$order->number.' отменён'.($reason ? ': '.$reason : '.');

        if ($cancelledBy->id === $order->user_id) {
            $order->producer->user->notify(new SiteNotification('Покупатель отменил заказ', $text, route('producer.orders.show', $order), 'bi-x-circle'));
        } else {
            $order->buyer->notify(new SiteNotification('Заказ отменён', $text, route('account.orders.show', $order), 'bi-x-circle'));
        }

        $courier?->notify(new SiteNotification('Доставка отменена', $text, route('courier.my'), 'bi-x-circle'));
    }
}
