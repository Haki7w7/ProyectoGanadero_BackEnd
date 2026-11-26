<?php

namespace App\Policies;

use App\Models\Ronda;
use App\Models\User;

class RondaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operario']);
    }

    public function view(User $user, Ronda $ronda): bool
    {
        return $user->isAdmin() || $user->id === $ronda->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operario']);
    }

    public function update(User $user, Ronda $ronda): bool
    {
        return $user->isAdmin() || $user->id === $ronda->user_id;
    }

    public function delete(User $user, Ronda $ronda): bool
    {
        return $user->isAdmin();
    }
}

