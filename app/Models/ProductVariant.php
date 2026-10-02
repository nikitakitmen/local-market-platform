<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Простой вариант товара: «250 г», «500 г», «Большой букет».
 */
class ProductVariant extends Model
{
    protected $fillable = ['name', 'price', 'in_stock', 'sort_order'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'in_stock' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
