<?php

namespace App\Enums;

/**
 * Статус проверки производителя (и его заявки).
 */
enum ProducerStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'На проверке',
            self::Approved => 'Подтверждён',
            self::Rejected => 'Отклонён',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
