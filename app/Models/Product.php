<?php

namespace App\Models;

use App\Enums\ProducerStatus;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    /** Варианты сортировки каталога. */
    public const SORTS = [
        'popular' => 'Популярные',
        'new' => 'Новые',
        'cheap' => 'Сначала дешевле',
        'expensive' => 'Сначала дороже',
        'rating' => 'Высокий рейтинг',
    ];

    protected $fillable = [
        'producer_id',
        'category_id',
        'subcategory_id',
        'name',
        'slug',
        'description',
        'price',
        'unit',
        'image',
        'in_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'in_stock' => 'boolean',
            'is_active' => 'boolean',
            'rating' => 'float',
        ];
    }

    // ---------------------------------------------------------------------
    // Связи
    // ---------------------------------------------------------------------

    public function producer(): BelongsTo
    {
        return $this->belongsTo(Producer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('price');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    // ---------------------------------------------------------------------
    // Scopes каталога
    // ---------------------------------------------------------------------

    /** Товары, которые видят покупатели: не скрыты и принадлежат подтверждённому производителю. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('products.is_active', true)
            ->whereHas('producer', fn (Builder $q) => $q->where('status', ProducerStatus::Approved));
    }

    /** Простой поиск по названию, описанию и названию производителя (обычный LIKE в MySQL). */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes(trim($term), '%_\\').'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('products.name', 'like', $like)
                ->orWhere('products.description', 'like', $like)
                ->orWhereHas('producer', fn (Builder $p) => $p->where('name', 'like', $like));
        });
    }

    /**
     * Фильтры каталога. Ключи: q, category_id, subcategory_id, price_min, price_max,
     * producer_id, city_id, rating, in_stock.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, fn (Builder $q, $term) => $q->search($term))
            ->when($filters['category_id'] ?? null, fn (Builder $q, $id) => $q->where('category_id', $id))
            ->when($filters['subcategory_id'] ?? null, fn (Builder $q, $id) => $q->where('subcategory_id', $id))
            ->when($filters['price_min'] ?? null, fn (Builder $q, $min) => $q->where('price', '>=', $min))
            ->when($filters['price_max'] ?? null, fn (Builder $q, $max) => $q->where('price', '<=', $max))
            ->when($filters['producer_id'] ?? null, fn (Builder $q, $id) => $q->where('producer_id', $id))
            ->when($filters['city_id'] ?? null, fn (Builder $q, $id) => $q->whereHas('producer', fn (Builder $p) => $p->where('city_id', $id)))
            ->when($filters['rating'] ?? null, fn (Builder $q, $rating) => $q->where('rating', '>=', $rating))
            ->when($filters['in_stock'] ?? null, fn (Builder $q) => $q->where('in_stock', true));
    }

    public function scopeSort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'new' => $query->latest('products.created_at'),
            'cheap' => $query->orderBy('price'),
            'expensive' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('rating')->orderByDesc('reviews_count'),
            default => $query->orderByDesc('sales_count')->orderByDesc('rating'),
        };
    }

    // ---------------------------------------------------------------------
    // Вспомогательные методы
    // ---------------------------------------------------------------------

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? Storage::disk('public')->url($this->image)
            : asset('images/placeholder-product.svg');
    }

    public function hasVariants(): bool
    {
        return $this->variants->isNotEmpty();
    }

    /** Можно ли сейчас купить товар. */
    public function isAvailable(): bool
    {
        if (! $this->in_stock || ! $this->is_active) {
            return false;
        }

        // Если есть варианты — хотя бы один должен быть в наличии
        return ! $this->hasVariants() || $this->variants->contains('in_stock', true);
    }

    /** Вариант по умолчанию: первый вариант в наличии. */
    public function defaultVariant(): ?ProductVariant
    {
        return $this->variants->firstWhere('in_stock', true) ?? $this->variants->first();
    }

    /** Цена товара = минимальная цена варианта (для фильтрации и сортировки по цене). */
    public function syncPriceFromVariants(): void
    {
        $min = $this->variants()->min('price');

        if ($min !== null) {
            $this->forceFill(['price' => $min])->saveQuietly();
        }
    }

    /** Пересчитать рейтинг по видимым отзывам. */
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
