<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function aplicaciones(): HasMany
    {
        return $this->hasMany(TratamientoAnimal::class, 'tratamiento_id', 'tratamiento_id');
    }

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