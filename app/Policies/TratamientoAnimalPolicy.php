<?php

namespace App\Policies;

use App\Models\TratamientoAnimal;
use App\Models\User;

class TratamientoAnimalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario']);
    }

    public function view(User $user, TratamientoAnimal $tratamientoAnimal): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario']);
    }

    public function update(User $user, TratamientoAnimal $tratamientoAnimal): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario']);
    }

    public function delete(User $user, TratamientoAnimal $tratamientoAnimal): bool
    {
        return $user->hasRole('admin');
    }
}
