<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pesaje extends Model
{
    use HasFactory;

    protected $table = 'pesajes';
    protected $primaryKey = 'pesaje_id';

    protected $fillable = [
        'id_animal',
        'peso_kg',
        'fecha_pesaje',
        'observaciones',
    ];

    protected $casts = [
        'peso_kg' => 'float',
        'fecha_pesaje' => 'datetime',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'id_animal', 'id_animal');
    }
}
