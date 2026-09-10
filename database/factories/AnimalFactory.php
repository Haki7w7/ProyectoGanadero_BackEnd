<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AnimalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'numero_arete'     => 'CR-' . $this->faker->unique()->numberBetween(2000, 9999) . '-LIB',
            'sexo'             => $this->faker->randomElement(['Macho', 'Hembra']),
            'fecha_nacimiento' => $this->faker->date('Y-m-d', '-1 years'),
            'estado'           => $this->faker->randomElement(['Activo', 'Vendido', 'Enfermo']),
            'potrero_id'       => 1, // Se sobrescribe desde el AnimalSeeder
            'raza_id'          => 1, // Se sobrescribe desde el AnimalSeeder
        ];
    }
}