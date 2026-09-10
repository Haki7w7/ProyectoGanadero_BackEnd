<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';
    protected $primaryKey = 'categoria_id';

    protected $fillable = ['nombre', 'tipo'];

    public function insumos(): HasMany
    {
        return $this->hasMany(Insumo::class, 'categoria_id', 'categoria_id');
    }
}
