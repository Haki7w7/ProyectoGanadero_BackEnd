<?php

namespace App\Policies;

use App\Models\Ronda;
use App\Models\User;

class RondaPolicy
{
    /**
     * Determina si el usuario puede listar rondas.
     * Admin y Operario tienen acceso (el servicio segmenta según rol).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operario']);
    }

    /**
     * Determina si el usuario puede ver el detalle de una ronda.
     * Un operario solo puede ver sus propias rondas; el admin puede ver cualquiera.
     */
    public function view(User $user, Ronda $ronda): bool
    {
        return $user->isAdmin() || $user->id === $ronda->user_id;
    }

    /**
     * Determina si el usuario puede iniciar una nueva ronda.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operario']);
    }

    /**
     * Determina si el usuario puede registrar puntos o finalizar la ronda.
     * Solo el operario propietario de la ronda o un admin pueden modificarla.
     */
    public function update(User $user, Ronda $ronda): bool
    {
        return $user->isAdmin() || $user->id === $ronda->user_id;
    }

    /**
     * Solo el administrador puede eliminar rondas.
     */
    public function delete(User $user, Ronda $ronda): bool
    {
        return $user->isAdmin();
    }
}

