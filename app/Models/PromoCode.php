<?php

namespace App\Models;

use App\Enums\PromoCodeType;
use Illuminate\Database\Eloquent\Model;

/**
 * Промокод платформы: процент или фиксированная скидка.
 */
class PromoCode extends Model
{
    protected $fillable = ['code', 'type', 'value', 'min_order_amount', 'start_date', 'end_date', 'is_active'];

    protected function casts(): array
    {
        return [
            'type' => PromoCodeType::class,
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /** Код хранится в верхнем регистре. */
    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = mb_strtoupper(trim($value));
    }

    public function isCurrentlyActive(): bool
    {
        $today = today();

        return $this->is_active
            && ($this->start_date === null || $this->start_date->lte($today))
            && ($this->end_date === null || $this->end_date->gte($today));
    }

    /** Причина, по которой промокод нельзя применить к сумме, или null, если можно. */
    public function errorFor(float $amount): ?string
    {
        if (! $this->isCurrentlyActive()) {
            return 'Срок действия промокода истёк или он отключён.';
        }

        if ($this->min_order_amount && $amount < (float) $this->min_order_amount) {
            return 'Промокод действует при сумме товаров от '.money($this->min_order_amount).'.';
        }

        return null;
    }

    /** Размер скидки для суммы товаров. */
    public function discountFor(float $amount): float
    {
        $discount = $this->type === PromoCodeType::Percent
            ? $amount * (float) $this->value / 100
            : (float) $this->value;

        return round(min($discount, $amount), 2);
    }

    public function getLabelAttribute(): string
    {
        return $this->type === PromoCodeType::Percent
            ? '−'.rtrim(rtrim(number_format((float) $this->value, 2, '.', ''), '0'), '.').'%'
            : '−'.money($this->value);
    }
}
