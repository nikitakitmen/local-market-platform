<?php

namespace App\Enums;

/**
 * Способ оплаты. Реальные платёжные системы не подключены —
 * для карты и СБП используется демонстрационный сценарий.
 */
enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case Sbp = 'sbp';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Наличными при получении',
            self::Card => 'Банковская карта',
            self::Sbp => 'СБП',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cash => 'bi-cash-coin',
            self::Card => 'bi-credit-card',
            self::Sbp => 'bi-qr-code',
        };
    }

    /** Оплата онлайн (демо-страница оплаты). */
    public function isOnline(): bool
    {
        return $this !== self::Cash;
    }
}
