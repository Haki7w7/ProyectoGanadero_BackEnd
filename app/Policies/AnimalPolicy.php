<?php

namespace App\Policies;

use App\Models\Animal;
use App\Models\User;

class AnimalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario', 'operario']);
    }

    public function view(User $user, Animal $animal): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario', 'operario']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Animal $animal): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Animal $animal): bool
    {
        return $user->hasRole('admin');
    }
}
