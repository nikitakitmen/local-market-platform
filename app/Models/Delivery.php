<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Доставка заказа курьером.
 */
class Delivery extends Model
{
    protected $fillable = ['order_id', 'courier_id', 'status', 'assigned_at', 'picked_up_at', 'delivered_at'];

    protected function casts(): array
    {
        return [
            'status' => DeliveryStatus::class,
            'assigned_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Доставки, которые курьер может взять: заказ принят производителем, курьер ещё не назначен.
     * Если у курьера указан город — показываем только заказы производителей из этого города.
     */
    public function scopeAvailableFor(Builder $query, User $courier): Builder
    {
        return $query->where('status', DeliveryStatus::Waiting)
            ->whereNull('courier_id')
            ->whereHas('order', function (Builder $order) use ($courier) {
                $order->where('status', OrderStatus::Accepted);

                if ($courier->city_id) {
                    $order->whereHas('producer', fn (Builder $p) => $p->where('city_id', $courier->city_id));
                }
            });
    }
}
