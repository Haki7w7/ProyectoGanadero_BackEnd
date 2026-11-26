<?php

namespace App\Policies;

use App\Models\Pesaje;
use App\Models\User;

class PesajePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario', 'operario']);
    }

    public function view(User $user, Pesaje $pesaje): bool
    {
        return $user->hasAnyRole(['admin', 'veterinario', 'operario']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operario']);
    }

    public function update(User $user, Pesaje $pesaje): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Pesaje $pesaje): bool
    {
        return $user->hasRole('admin');
    }
}
