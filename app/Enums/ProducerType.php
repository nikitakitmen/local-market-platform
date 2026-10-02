<?php

namespace App\Enums;

/**
 * Правовая форма производителя.
 */
enum ProducerType: string
{
    case SelfEmployed = 'self_employed';
    case Individual = 'individual';
    case Organization = 'organization';

    public function label(): string
    {
        return match ($this) {
            self::SelfEmployed => 'Самозанятый',
            self::Individual => 'ИП',
            self::Organization => 'Организация',
        };
    }
}
