<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['product_id', 'product_variant_id', 'quantity'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /** Цена за единицу: цена варианта или цена товара. */
    public function getUnitPriceAttribute(): float
    {
        return (float) ($this->variant?->price ?? $this->product->price);
    }

    public function getTotalAttribute(): float
    {
        return round($this->unit_price * $this->quantity, 2);
    }

    /** Позицию можно купить: товар виден, в наличии, вариант в наличии. */
    public function isAvailable(): bool
    {
        if (! $this->product || ! $this->product->is_active || ! $this->product->in_stock) {
            return false;
        }

        if (! $this->product->producer?->isApproved()) {
            return false;
        }

        return $this->variant === null || $this->variant->in_stock;
    }
}
