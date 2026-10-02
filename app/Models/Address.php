<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Адрес доставки покупателя.
 */
class Address extends Model
{
    use HasFactory;

    /**
     * Зоны удалённости от центра города. Без карт и геокодинга точное расстояние неизвестно,
     * поэтому покупатель выбирает примерную зону, а её расстояние идёт в формулу стоимости доставки.
     */
    public const DISTANCE_ZONES = [
        3 => 'Центр города (до 3 км)',
        7 => 'В пределах города (3–7 км)',
        12 => 'Окраина города (7–12 км)',
        20 => 'Пригород (12–20 км)',
        30 => 'Область (20–30 км)',
    ];

    protected $fillable = [
        'city_id',
        'title',
        'street',
        'apartment',
        'entrance',
        'floor',
        'distance_km',
        'comment',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** Полный адрес одной строкой: «г. Казань, ул. Баумана, 12, кв. 5». */
    public function getFullAddressAttribute(): string
    {
        $parts = ['г. '.$this->city?->name, $this->street];

        if ($this->apartment) {
            $parts[] = 'кв. '.$this->apartment;
        }

        if ($this->entrance) {
            $parts[] = 'подъезд '.$this->entrance;
        }

        if ($this->floor) {
            $parts[] = 'этаж '.$this->floor;
        }

        return implode(', ', array_filter($parts));
    }

    public function getDistanceLabelAttribute(): string
    {
        return self::DISTANCE_ZONES[$this->distance_km] ?? $this->distance_km.' км';
    }
}
