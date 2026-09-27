<?php

namespace App\Policies;

use App\Models\Pesaje;
use App\Models\User;

class PesajePolicy
{
    /**
     * Todos los roles autenticados pueden ver el listado de pesajes.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario', 'operario']);
    }

    /**
     * Todos los roles autenticados pueden ver el detalle de un pesaje.
     */
    public function view(User $user, Pesaje $pesaje): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario', 'operario']);
    }

    /**
     * Operario y admin pueden registrar pesajes.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operario']);
    }

    /**
     * Solo admin puede actualizar pesajes.
     */
    public function update(User $user, Pesaje $pesaje): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Solo admin puede eliminar pesajes.
     */
    public function delete(User $user, Pesaje $pesaje): bool
    {
        return $user->hasRole('admin');
    }
}
