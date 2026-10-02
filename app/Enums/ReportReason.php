<?php

namespace App\Enums;

/**
 * Причина жалобы на отзыв.
 */
enum ReportReason: string
{
    case Spam = 'spam';
    case Offensive = 'offensive';
    case Fake = 'fake';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Spam => 'Спам или реклама',
            self::Offensive => 'Оскорбления',
            self::Fake => 'Недостоверная информация',
            self::Other => 'Другое',
        };
    }
}
