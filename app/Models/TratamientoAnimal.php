<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TratamientoAnimal extends Model
{
   use HasFactory;

    protected $table = 'tratamientos';
    protected $primaryKey = 'tratamiento_id';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
    ];

    public function animales(): BelongsToMany
    {
        return $this->belongsToMany(Animal::class, 'tratamiento_animal', 'tratamiento_id', 'id_animal')
                    ->withPivot(['tratamiento_animal_id', 'fecha_aplicacion', 'dosis_ml', 'observaciones'])
                    ->withTimestamps();
    }
}
