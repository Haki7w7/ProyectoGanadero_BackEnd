<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Tratamiento extends pivot
{
    use HasFactory;

    protected $table = 'tratamiento_animal';
    protected $primaryKey = 'tratamiento_animal_id';

    protected $fillable = [
        'id_animal',
        'tratamiento_id',
        'fecha_aplicacion',
        'dosis_ml',
        'observaciones',
    ];

    protected $casts = [
        'fecha_aplicacion' => 'datetime',
        'dosis_ml' => 'float',
    ];
}
