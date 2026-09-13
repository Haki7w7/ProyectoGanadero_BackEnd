<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TratamientoAnimal extends Pivot
{
    use HasFactory;

    protected $table = 'tratamiento_animal';
    protected $primaryKey = 'tratamiento_animal_id';
    public $incrementing = true;

    protected $fillable = [
        'id_animal',
        'tratamiento_id',
        'fecha_aplicacion',
        'dosis_ml',
        'observaciones',
    ];

    protected $casts = [
        'fecha_aplicacion' => 'datetime',
        'dosis_ml'         => 'float',
    ];
}