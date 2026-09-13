<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tratamiento extends Model
{
    use HasFactory;

    protected $table = 'tratamientos';
    protected $primaryKey = 'tratamiento_id';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
    ];

    /**
     * Relación muchos a muchos con el modelo Animal.
     */
    public function animales(): BelongsToMany
    {
        return $this->belongsToMany(
            Animal::class,
            'tratamiento_animal',
            'tratamiento_id',
            'id_animal'
        )
        ->using(TratamientoAnimal::class)
        ->withPivot([
            'id_animal',
            'tratamiento_id',
            'fecha_aplicacion',
            'dosis_ml',
            'observaciones',
        ]);
    }
}