<?php

namespace App\Enums;

/**
 * Статус заказа.
 */
enum OrderStatus: string
{
    case New = 'new';
    case Accepted = 'accepted';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Новый',
            self::Accepted => 'Принят',
            self::Completed => 'Выполнен',
            self::Cancelled => 'Отменён',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Accepted => 'primary',
            self::Completed => 'success',
            self::Cancelled => 'secondary',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }
}
