<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Права на управление товарами в кабинете производителя.
 */
class ProductPolicy
{
    /** Публиковать товары может только производитель, подтверждённый администратором. */
    public function create(User $user): bool
    {
        return $user->isProducer() && (bool) $user->producer?->isApproved();
    }

    /** Редактировать и удалять товар может только его владелец. */
    public function update(User $user, Product $product): bool
    {
        return $user->isProducer() && $user->producer?->id === $product->producer_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}
