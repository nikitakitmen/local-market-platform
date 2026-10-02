<?php

namespace App\Enums;

/**
 * Способ получения заказа.
 */
enum DeliveryMethod: string
{
    case Delivery = 'delivery';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::Delivery => 'Доставка курьером',
            self::Pickup => 'Самовывоз',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Delivery => 'bi-truck',
            self::Pickup => 'bi-shop',
        };
    }
}
