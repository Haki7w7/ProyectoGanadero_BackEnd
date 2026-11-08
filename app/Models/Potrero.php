<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Potrero extends Model
{
    use HasFactory;

    protected $table = 'potreros';
    protected $primaryKey = 'potrero_id';

    protected $fillable = [
        'nombre',
        'codigo_qr',
        'hectareas_de_extension',
        'capacidad_maxima',
        'estado_pasto',
    ];

    protected $casts = [
        'hectareas_de_extension' => 'float',
        'capacidad_maxima'        => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Potrero $potrero) {
            if (empty($potrero->codigo_qr)) {
                $slug = Str::slug($potrero->nombre ?? 'p');
                $potrero->codigo_qr = 'POTRERO-' . strtoupper($slug ?: Str::random(6));
            }
        });
    }

    public function animales(): HasMany
    {
        return $this->hasMany(Animal::class, 'potrero_id');
    }

    public function puntosRonda(): HasMany
    {
        return $this->hasMany(PuntoRonda::class, 'potrero_id', 'potrero_id');
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