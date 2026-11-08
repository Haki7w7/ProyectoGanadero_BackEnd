<?php

namespace Database\Factories;

use App\Models\Ronda;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ronda>
 */
class RondaFactory extends Factory
{
    protected $model = Ronda::class;

    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'fecha_inicio'  => now()->subHours(2),
            'fecha_fin'     => null,
            'estado'        => 'en_progreso',
            'observaciones' => 'Ronda de patrullaje y revisión de cercas y pastos.',
        ];
    }

    public function completada(): self
    {
        return $this->state(fn (array $attributes) => [
            'estado'    => 'completada',
            'fecha_fin' => now(),
        ]);
    }
}

