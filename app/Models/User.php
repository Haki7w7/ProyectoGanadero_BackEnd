<?php

namespace App\Models;
use App\Enum\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }


    // Helpers de Rol de Uusuarios 

    public function isadmin(): bool{
        return $this->role === UserRole::ADMIN;
    } 

    public function isVeterinario(): bool{
        return $this->role === UserRole::VETERINARIO;
    }

    public function isOperario(): bool{
        return $this->role === UserRole::OPERARIO;
    }
}