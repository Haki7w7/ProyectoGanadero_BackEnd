<?php

namespace App\Policies;

use App\Models\Potrero;
use App\Models\User;

class PotreroPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario', 'operario']);
    }

    public function view(User $user, Potrero $potrero): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario', 'operario']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Potrero $potrero): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Potrero $potrero): bool
    {
        return $user->hasRole('admin');
    }
}
