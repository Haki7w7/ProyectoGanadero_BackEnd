<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PuntoRonda extends Model
{
    use HasFactory;

    protected $table = 'puntos_ronda';

    protected $fillable = [
        'ronda_id',
        'potrero_id',
        'latitud',
        'longitud',
        'estado_potrero',
        'observacion',
        'registrado_en',
    ];

    protected $casts = [
        'latitud'       => 'float',
        'longitud'      => 'float',
        'registrado_en' => 'datetime',
    ];

    public function ronda(): BelongsTo
    {
        return $this->belongsTo(Ronda::class, 'ronda_id', 'id');
    }

    public function potrero(): BelongsTo
    {
        return $this->belongsTo(Potrero::class, 'potrero_id', 'potrero_id');
    }
}

