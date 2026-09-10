<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Raza extends Model
{
    use HasFactory;

    protected $table = 'razas';
    protected $primaryKey = 'raza_id';

    protected $fillable = ['nombre'];

    public function animales(): HasMany
    {
        return $this->hasMany(Animal::class, 'raza_id', 'raza_id');
    }
}
