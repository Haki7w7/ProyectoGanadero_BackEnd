<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Potrero extends Model
{
    use HasFactory;

    protected $table = 'potreros';
    protected $primaryKey = 'potrero_id';

    protected $fillable = [
        'nombre',
        'hectareas_de_extension',
        'capacidad_maxima',
        'estado_pasto',
    ];

    protected $casts = [
        'hectareas_de_extension' => 'float',
        'capacidad_maxima'        => 'integer',
    ];

    public function animales(): HasMany
    {
        return $this->hasMany(Animal::class, 'potrero_id');
    }

    /**
     * Scope para filtrar potreros que no han alcanzado su capacidad máxima.
     */
    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->whereRaw(
            '(SELECT COUNT(*) FROM animales WHERE animales.potrero_id = potreros.potrero_id) < potreros.capacidad_maxima'
        );
    }
}