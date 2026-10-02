<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /** Удалить отзыв может автор или администратор. */
    public function delete(User $user, Review $review): bool
    {
        return $review->user_id === $user->id || $user->isAdmin();
    }

    /** Ответить на отзыв может производитель, чей товар оценили. */
    public function reply(User $user, Review $review): bool
    {
        return $user->isProducer() && $user->producer?->id === $review->producer_id;
    }

    /** Пожаловаться можно на чужой отзыв. */
    public function report(User $user, Review $review): bool
    {
        return $review->user_id !== $user->id;
    }
}
