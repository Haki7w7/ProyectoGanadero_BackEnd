<?php

namespace Database\Factories;

use App\Models\Potrero;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Potrero>
 */
class PotreroFactory extends Factory
{
protected $model = Potrero::class;

    public function definition(): array
    {
        $nombres = [
            'Potrero El Alto', 'Potrero La Bajada', 'Potrero El Zapote',
            'Potrero La Ceiba', 'Potrero El Arrayán', 'Potrero Los Bueyes',
            'Potrero La Quebrada', 'Potrero San José', 'Potrero El Guanacaste',
            'Potrero Las Lagunas', 'Potrero El Roble', 'Potrero La Esperanza',
            'Potrero El Naranjo', 'Potrero Los Laureles', 'Potrero El Cedro',
            'Potrero La Palma', 'Potrero El Mango', 'Potrero San Pedro',
            'Potrero La Colina', 'Potrero El Sauce', 'Potrero Las Acacias',
            'Potrero El Paraíso', 'Potrero La Pradera', 'Potrero El Encino',
            'Potrero Los Almendros', 'Potrero La Sabana', 'Potrero El Jícaro',
            'Potrero San Martín', 'Potrero Las Flores', 'Potrero El Coyol',
        ];

        return [
            'nombre'                 => $this->faker->unique()->randomElement($nombres),
            'hectareas_de_extension' => $this->faker->randomFloat(2, 5.0, 50.0),
            'capacidad_maxima'       => $this->faker->numberBetween(10, 50),
            'estado_pasto'           => $this->faker->randomElement(['Excelente', 'Bueno', 'Regular', 'En Descanso']),
        ];
    }
}
