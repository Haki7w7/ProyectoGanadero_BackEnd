<?php

namespace Database\Factories;

use App\Models\Raza;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Raza>
 */
class RazaFactory extends Factory
{
 protected $model = Raza::class;

    public function definition(): array
    {
        $razas = [
            'Brahman',
            'Nelore',
            'Angus',
            'Holstein',
            'Gyr',
            'Simmental',
            'Charolais',
            'Brangus',
            'Sardo Negro',
            'Guzerá',
        ];

        return [
            'nombre' => $this->faker->unique()->randomElement($razas),
        ];
    }
}
