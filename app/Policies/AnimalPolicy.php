<?php

namespace App\Policies;

use App\Models\Animal;
use App\Models\User;

class AnimalPolicy
{
    /**
     * Todos los roles autenticados pueden ver el listado de animales.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario', 'operario']);
    }

    /**
     * Todos los roles autenticados pueden ver el detalle de un animal.
     */
    public function view(User $user, Animal $animal): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario', 'operario']);
    }

    /**
     * Solo admin puede crear animales.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Solo admin puede actualizar animales.
     */
    public function update(User $user, Animal $animal): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Solo admin puede eliminar animales.
     */
    public function delete(User $user, Animal $animal): bool
    {
        return $user->hasRole('admin');
    }
}
