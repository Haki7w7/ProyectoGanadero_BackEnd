<?php

namespace App\Models;

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

    public function raza(): BelongsTo
    {
        return $this->belongsTo(Raza::class, 'raza_id', 'raza_id');
    }

    public function potrero(): BelongsTo
    {
        return $this->belongsTo(Potrero::class, 'potrero_id', 'potrero_id');
    }

    public function pesajes(): HasMany
    {
        return $this->hasMany(Pesaje::class, 'id_animal', 'id_animal');
    }

    public function tratamientos(): BelongsToMany
    {
        return $this->belongsToMany(Tratamiento::class, 'tratamiento_animal', 'id_animal', 'tratamiento_id')
                    ->withPivot(['tratamiento_animal_id', 'fecha_aplicacion', 'dosis_ml', 'observaciones'])
                    ->withTimestamps();
    }
}
