<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'unidades_medida';
    protected $primaryKey = 'unidad_medida_id';

    protected $fillable = ['nombre', 'apertura'];

    public function insumos(): HasMany
    {
        return $this->hasMany(Insumo::class, 'unidad_medida_id', 'unidad_medida_id');
    }
}
