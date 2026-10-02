<?php

namespace App\Enums;

/**
 * Статус доставки курьером.
 */
enum DeliveryStatus: string
{
    case Waiting = 'waiting';
    case Assigned = 'assigned';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => 'Ожидает курьера',
            self::Assigned => 'Курьер назначен',
            self::InTransit => 'В пути',
            self::Delivered => 'Доставлено',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Waiting => 'warning',
            self::Assigned => 'info',
            self::InTransit => 'primary',
            self::Delivered => 'success',
        };
    }
}
