<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    
    protected $table = 'usuarios'; 

    protected $fillable = [
        'nombre',      
        'correo',      
        'contraseña',  
        'rol',        
    ];

    protected $hidden = [
        'contraseña',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'contraseña'        => 'hashed', 
        ];
    }
}