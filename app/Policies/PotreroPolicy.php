<?php

namespace App\Policies;

use App\Models\Potrero;
use App\Models\User;

class PotreroPolicy
{
    /**
     * Todos los roles autenticados pueden ver el listado de potreros.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario', 'operario']);
    }

    /**
     * Todos los roles autenticados pueden ver el detalle de un potrero.
     */
    public function view(User $user, Potrero $potrero): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario', 'operario']);
    }

    /**
     * Solo admin puede crear potreros.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Solo admin puede actualizar potreros.
     */
    public function update(User $user, Potrero $potrero): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Solo admin puede eliminar potreros.
     */
    public function delete(User $user, Potrero $potrero): bool
    {
        return $user->hasRole('admin');
    }
}
