<?php

namespace App\Models;

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

    public function animales(): HasMany
    {
        return $this->hasMany(Animal::class, 'potrero_id', 'potrero_id');
    }
}
