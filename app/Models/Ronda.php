<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ronda extends Model
{
    use HasFactory;

    protected $table = 'rondas';

    protected $fillable = [
        'user_id',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function puntosRonda(): HasMany
    {
        return $this->hasMany(PuntoRonda::class, 'ronda_id', 'id');
    }

    public function scopeEnProgreso(Builder $query): Builder
    {
        return $query->where('estado', 'en_progreso');
    }

    public function scopeCompletadas(Builder $query): Builder
    {
        return $query->where('estado', 'completada');
    }

    public function scopePorUsuario(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}

