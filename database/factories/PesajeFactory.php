<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Pesaje;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pesaje>
 */
class PesajeFactory extends Factory
{
protected $model = Pesaje::class;

    public function definition(): array
    {
        return [
            'id_animal'     => Animal::factory(),
            'peso_kg'       => $this->faker->randomFloat(2, 150.0, 650.0),
            'fecha_pesaje'  => $this->faker->dateTimeBetween('-6 months', 'now'),
            'observaciones' => $this->faker->optional(0.7)->sentence(),
        ];
    }
}
