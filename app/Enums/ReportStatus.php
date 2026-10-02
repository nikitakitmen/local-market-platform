<?php

namespace App\Enums;

/**
 * Статус жалобы на отзыв.
 */
enum ReportStatus: string
{
    case Pending = 'pending';
    case Resolved = 'resolved';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Новая',
            self::Resolved => 'Отзыв скрыт',
            self::Dismissed => 'Отклонена',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Resolved => 'success',
            self::Dismissed => 'secondary',
        };
    }
}
