<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insumo extends Model
{
    use HasFactory;

    protected $table = 'insumos';
    protected $primaryKey = 'insumo_id';

    protected $fillable = [
        'nombre',
        'categoria_id',
        'precio',
        'unidad_medida_id',
    ];

    protected $casts = [
        'precio' => 'float',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }
}