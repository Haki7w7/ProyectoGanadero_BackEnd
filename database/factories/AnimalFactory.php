<?php

namespace Database\Factories;

use App\Models\Potrero;
use App\Models\Raza;
use App\Models\Animal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Animal>
 */


class AnimalFactory extends Factory
{
    
protected $model = Animal::class;

    public function definition(): array
    {
        return [
            'numero_arete'     => 'ART-' . $this->faker->unique()->numerify('######'),
            'codigo_qr'        => 'VACA-' . $this->faker->unique()->numerify('#####'),
            'foto'             => 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=600&q=80',
            'raza_id'          => Raza::factory(),
            'potrero_id'       => Potrero::factory(),
            'sexo'             => $this->faker->randomElement(['Macho', 'Hembra']),
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-5 years', '-6 months')->format('Y-m-d'),
            'estado'           => $this->faker->randomElement(['Activo', 'Enfermo', 'Vendido']),
        ];
    }
}