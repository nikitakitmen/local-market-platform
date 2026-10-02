<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Торговая точка производителя.
 */
class ProducerLocation extends Model
{
    protected $fillable = ['name', 'address', 'working_hours', 'phone', 'is_pickup_point'];

    protected function casts(): array
    {
        return [
            'is_pickup_point' => 'boolean',
        ];
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(Producer::class);
    }

    public function scopePickupPoints(Builder $query): Builder
    {
        return $query->where('is_pickup_point', true);
    }
}
