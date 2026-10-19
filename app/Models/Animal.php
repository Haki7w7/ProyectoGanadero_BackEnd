<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Animal extends Model
{
    use HasFactory;

    protected $table = 'animales';
    protected $primaryKey = 'id_animal';

    protected $fillable = [
        'numero_arete',
        'raza_id',
        'sexo',
        'fecha_nacimiento',
        'estado',
        'potrero_id',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    // Relaciones
    public function raza(): BelongsTo
    {
        return $this->belongsTo(Raza::class, 'raza_id');
    }

    public function potrero(): BelongsTo
    {
        return $this->belongsTo(Potrero::class, 'potrero_id');
    }

    public function pesajes(): HasMany
    {
        return $this->hasMany(Pesaje::class, 'id_animal', 'id_animal');
    }

   public function tratamientos(): BelongsToMany
{
    return $this->belongsToMany(
        Tratamiento::class,
        'tratamiento_animal',
        'id_animal',
        'tratamiento_id'
    )
    ->using(TratamientoAnimal::class)
    ->withPivot(['tratamiento_animal_id', 'fecha_aplicacion', 'dosis_ml', 'observaciones'])
    ->withTimestamps();
}

    // Scopes Reutilizables
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 'Activo');
    }

    public function scopePorPotrero(Builder $query, $potreroId): Builder
    {
        return $query->where('potrero_id', $potreroId);
    }

    public function scopePorRaza(Builder $query, $razaId): Builder
    {
        return $query->where('raza_id', $razaId);
    }

    public function scopePorEstado(Builder $query, string $estado): Builder
    {
        return $query->where('estado', $estado);
    }

    public function scopePorSexo(Builder $query, string $sexo): Builder
    {
        return $query->where('sexo', $sexo);
    }

    /**
     * Búsqueda parcial por número de arete (?search=GG-12).
     * Se escapan los comodines % y _ (con "!" como carácter de escape, válido en
     * MySQL, SQLite y PostgreSQL) para que el texto se busque literalmente.
     */
    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        $termino = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], trim($termino));

        return $query->whereRaw('numero_arete LIKE ? ESCAPE ?', ["%{$termino}%", '!']);
    }

    public function scopeConRelaciones(Builder $query): Builder
    {
        return $query->with(['raza', 'potrero', 'pesajes']);
    }
}