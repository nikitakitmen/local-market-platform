<?php

namespace App\Models;

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Заказ одному производителю.
 */
class Order extends Model
{
    protected $fillable = [
        'checkout_id',
        'user_id',
        'producer_id',
        'promo_code_id',
        'pickup_location_id',
        'status',
        'delivery_method',
        'payment_method',
        'payment_status',
        'recipient_name',
        'recipient_phone',
        'address',
        'delivery_distance_km',
        'comment',
        'subtotal',
        'discount',
        'delivery_cost',
        'total',
        'commission_percent',
        'commission_amount',
        'cancel_reason',
        'paid_at',
        'accepted_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'delivery_method' => DeliveryMethod::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'delivery_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'commission_percent' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // ---------------------------------------------------------------------
    // Связи
    // ---------------------------------------------------------------------

    /** Покупатель. */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(Producer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(ProducerLocation::class, 'pickup_location_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // ---------------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------------

    public function scopeStatus(Builder $query, OrderStatus|string|null $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Completed);
    }

    /**
     * Проблемные заказы, которым нужна помощь оператора:
     * — новый заказ, который производитель не принял больше суток;
     * — принятый заказ с доставкой, который больше 3 часов ждёт курьера.
     */
    public function scopeProblem(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where(fn (Builder $new) => $new
                ->where('status', OrderStatus::New)
                ->where('created_at', '<', now()->subDay()))
                ->orWhere(fn (Builder $waiting) => $waiting
                    ->where('status', OrderStatus::Accepted)
                    ->where('accepted_at', '<', now()->subHours(3))
                    ->whereHas('delivery', fn (Builder $d) => $d->where('status', DeliveryStatus::Waiting)));
        });
    }

    /** Заказ требует внимания оператора (см. scopeProblem). */
    public function isProblem(): bool
    {
        if ($this->status === OrderStatus::New) {
            return $this->created_at->lt(now()->subDay());
        }

        return $this->status === OrderStatus::Accepted
            && $this->accepted_at?->lt(now()->subHours(3))
            && $this->delivery?->status === DeliveryStatus::Waiting;
    }

    /** Поиск по номеру заказа («LM-000012» или «12»). */
    public function scopeNumber(Builder $query, ?string $number): Builder
    {
        $id = (int) preg_replace('/\D/', '', (string) $number);

        return $number ? $query->whereKey($id) : $query;
    }

    // ---------------------------------------------------------------------
    // Вспомогательные методы
    // ---------------------------------------------------------------------

    /** Номер заказа для отображения: LM-000123. */
    public function getNumberAttribute(): string
    {
        return 'LM-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function isDelivery(): bool
    {
        return $this->delivery_method === DeliveryMethod::Delivery;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    /** Нужна ли онлайн-оплата (карта/СБП ещё не оплачены). */
    public function awaitsOnlinePayment(): bool
    {
        return $this->payment_method->isOnline()
            && ! $this->isPaid()
            && ! $this->status->isFinal();
    }

    public function canBeCancelledByBuyer(): bool
    {
        return $this->status === OrderStatus::New;
    }

    public function canBeAccepted(): bool
    {
        return $this->status === OrderStatus::New;
    }

    /** Производитель отмечает выполнение только для самовывоза — доставку завершает курьер. */
    public function canBeCompletedByProducer(): bool
    {
        return $this->status === OrderStatus::Accepted && ! $this->isDelivery();
    }

    /** Отменить можно, пока курьер не забрал заказ. */
    public function canBeCancelled(): bool
    {
        if ($this->status->isFinal()) {
            return false;
        }

        return ! in_array($this->delivery?->status, [DeliveryStatus::InTransit, DeliveryStatus::Delivered], true);
    }

    /** Отзыв можно оставить только по выполненному заказу. */
    public function canBeReviewed(): bool
    {
        return $this->status === OrderStatus::Completed;
    }
}
