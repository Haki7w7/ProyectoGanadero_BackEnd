<?php

namespace Database\Factories;

use App\Models\Potrero;
use App\Models\PuntoRonda;
use App\Models\Ronda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PuntoRonda>
 */
class PuntoRondaFactory extends Factory
{
    protected $model = PuntoRonda::class;

    public function definition(): array
    {
        return [
            'ronda_id'       => Ronda::factory(),
            'potrero_id'     => Potrero::factory(),
            'latitud'        => $this->faker->latitude(10.0, 10.9),
            'longitud'       => $this->faker->longitude(-85.0, -84.0),
            'estado_potrero' => $this->faker->randomElement(['bueno', 'regular', 'requiere_atencion']),
            'observacion'    => $this->faker->optional()->sentence(),
            'registrado_en'  => now(),
        ];
    }
}

