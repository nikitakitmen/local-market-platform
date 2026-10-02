<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /** Заказ видят: покупатель, производитель-продавец, сотрудники и назначенный курьер. */
    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id
            || $this->manage($user, $order)
            || $user->isStaff()
            || ($user->isCourier() && $order->delivery?->courier_id === $user->id);
    }

    /** Покупатель может отменить свой новый заказ. */
    public function cancel(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    /** Обрабатывать заказ (принять, выполнить, отменить) может производитель-продавец. */
    public function manage(User $user, Order $order): bool
    {
        return $user->isProducer() && $user->producer?->id === $order->producer_id;
    }
}
