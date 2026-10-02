<?php

namespace App\Policies;

use App\Models\ProducerLocation;
use App\Models\User;

class ProducerLocationPolicy
{
    public function update(User $user, ProducerLocation $location): bool
    {
        return $user->producer?->id === $location->producer_id;
    }

    public function delete(User $user, ProducerLocation $location): bool
    {
        return $this->update($user, $location);
    }
}
