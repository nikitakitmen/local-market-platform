<?php

namespace App\Models;

use App\Enums\ProducerStatus;
use App\Enums\ProducerType;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Producer extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'user_id',
        'city_id',
        'name',
        'slug',
        'type',
        'inn',
        'description',
        'phone',
        'email',
        'address',
        'logo',
        'status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProducerType::class,
            'status' => ProducerStatus::class,
            'rating' => 'float',
            'approved_at' => 'datetime',
        ];
    }

    // ---------------------------------------------------------------------
    // Связи
    // ---------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ProducerLocation::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ProducerApplication::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    // ---------------------------------------------------------------------
    // Scopes и вспомогательные методы
    // ---------------------------------------------------------------------

    /** Только подтверждённые администратором производители. */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ProducerStatus::Approved);
    }

    public function isApproved(): bool
    {
        return $this->status === ProducerStatus::Approved;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    /** Пересчитать рейтинг по видимым отзывам на товары производителя. */
    public function recalculateRating(): void
    {
        $stats = $this->reviews()->where('is_hidden', false)
            ->selectRaw('COUNT(*) as total, AVG(rating) as average')
            ->first();

        $this->forceFill([
            'rating' => round((float) $stats->average, 2),
            'reviews_count' => (int) $stats->total,
        ])->saveQuietly();
    }
}
