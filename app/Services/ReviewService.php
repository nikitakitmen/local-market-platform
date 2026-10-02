<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Notifications\SiteNotification;
use Illuminate\Validation\ValidationException;

/**
 * Отзывы: проверка права на отзыв, создание, модерация и пересчёт рейтингов.
 */
class ReviewService
{
    /** Последний выполненный заказ пользователя с этим товаром. */
    public function completedOrderFor(User $user, Product $product): ?Order
    {
        return $user->orders()
            ->completed()
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->latest('completed_at')
            ->first();
    }

    public function hasReviewed(User $user, Product $product): bool
    {
        return Review::where('user_id', $user->id)->where('product_id', $product->id)->exists();
    }

    /** Оставлять отзыв можно только после выполненного заказа и только один раз на товар. */
    public function canReview(User $user, Product $product): bool
    {
        return ! $this->hasReviewed($user, $product) && $this->completedOrderFor($user, $product) !== null;
    }

    public function create(User $user, Product $product, int $rating, string $text): Review
    {
        if ($this->hasReviewed($user, $product)) {
            throw ValidationException::withMessages(['text' => 'Вы уже оставили отзыв на этот товар.']);
        }

        $order = $this->completedOrderFor($user, $product);

        if (! $order) {
            throw ValidationException::withMessages(['text' => 'Отзыв можно оставить только после выполненного заказа с этим товаром.']);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'producer_id' => $product->producer_id,
            'order_id' => $order->id,
            'rating' => $rating,
            'text' => $text,
        ]);

        $this->refreshRatings($review);

        $product->producer->user->notify(new SiteNotification(
            'Новый отзыв: '.$rating.' из 5',
            $user->name.' оставил(а) отзыв на «'.$product->name.'».',
            route('producer.reviews.index'),
            'bi-star'
        ));

        return $review;
    }

    public function setHidden(Review $review, bool $hidden): void
    {
        $review->update(['is_hidden' => $hidden]);
        $this->refreshRatings($review);
    }

    public function delete(Review $review): void
    {
        $review->delete();
        $this->refreshRatings($review);
    }

    /** Рейтинг товара и производителя хранится в их таблицах и пересчитывается при изменении отзывов. */
    public function refreshRatings(Review $review): void
    {
        $review->product?->recalculateRating();
        $review->producer?->recalculateRating();
    }
}
