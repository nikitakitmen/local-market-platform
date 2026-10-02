<?php

namespace App\Enums;

/**
 * Роли пользователей платформы.
 */
enum UserRole: string
{
    case Buyer = 'buyer';
    case Producer = 'producer';
    case Courier = 'courier';
    case Operator = 'operator';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Buyer => 'Покупатель',
            self::Producer => 'Производитель',
            self::Courier => 'Курьер',
            self::Operator => 'Оператор',
            self::Admin => 'Администратор',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Buyer => 'secondary',
            self::Producer => 'primary',
            self::Courier => 'info',
            self::Operator => 'warning',
            self::Admin => 'dark',
        };
    }

    /**
     * Роли, которые администратор может назначить вручную.
     * Роль «Производитель» выдаётся только через одобрение заявки.
     */
    public static function assignable(): array
    {
        return [self::Buyer, self::Courier, self::Operator, self::Admin];
    }
}
