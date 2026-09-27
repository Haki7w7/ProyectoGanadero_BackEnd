<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    private ?string $rolePendingAssignment = null;

    public function setRoleAttribute(?string $value): void
    {
        $this->rolePendingAssignment = $value;
    }

    public function getRoleAttribute(): ?string
    {
        return $this->getRoleNames()->first();
    }

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if ($user->rolePendingAssignment !== null) {
                \Spatie\Permission\Models\Role::firstOrCreate([
                    'name'       => $user->rolePendingAssignment,
                    'guard_name' => 'web',
                ]);
                $user->syncRoles([$user->rolePendingAssignment]);
                $user->rolePendingAssignment = null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // Helpers de Rol — usan Spatie (hasRole) en lugar del Enum eliminado

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isVeterinario(): bool
    {
        return $this->hasRole('veterinario');
    }

    public function isOperario(): bool
    {
        return $this->hasRole('operario');
    }
}