<?php

namespace App\Policies;

use App\Models\TratamientoAnimal;
use App\Models\User;

class TratamientoAnimalPolicy
{
    /**
     * Admin y veterinario pueden ver el listado de aplicaciones de tratamiento.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario']);
    }

    /**
     * Admin y veterinario pueden ver el detalle de una aplicación de tratamiento.
     */
    public function view(User $user, TratamientoAnimal $tratamientoAnimal): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario']);
    }

    /**
     * Veterinario y admin pueden registrar aplicaciones de tratamiento.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario']);
    }

    /**
     * Veterinario y admin pueden actualizar aplicaciones de tratamiento.
     */
    public function update(User $user, TratamientoAnimal $tratamientoAnimal): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario']);
    }

    /**
     * Solo admin puede eliminar aplicaciones de tratamiento.
     */
    public function delete(User $user, TratamientoAnimal $tratamientoAnimal): bool
    {
        return $user->hasRole('admin');
    }
}
