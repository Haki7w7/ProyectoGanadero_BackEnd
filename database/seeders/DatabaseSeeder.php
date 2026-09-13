<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\Potrero;
use App\Models\Raza;
use App\Models\Tratamiento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crea Usuario Administrador de Prueba
        User::factory()->create([
            'name'     => 'Aarón Rodríguez',
            'email'    => 'aaron@guateganado.cr',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        // 2. Genera Razas y Potreros
        $razas    = Raza::factory()->count(10)->create();
        $potreros = Potrero::factory()->count(10)->create();

        // 3. Crea Catálogo de Tratamientos
        $tratamientos = collect([
            Tratamiento::create([
                'nombre'      => 'Vacuna Contra Aftosa',
                'descripcion' => 'Vacunación semestral obligatoria',
                'tipo'        => 'Vacuna',
            ]),
            Tratamiento::create([
                'nombre'      => 'Desparasitante Interno Ivermectina',
                'descripcion' => 'Control de parásitos gastrointestinales',
                'tipo'        => 'Desparasitante',
            ]),
            Tratamiento::create([
                'nombre'      => 'Complejo Vitamínico B12',
                'descripcion' => 'Suplemento nutricional de engorde',
                'tipo'        => 'Vitamina',
            ]),
        ]);

        // 4. Crea 20 Animales reutilizando las Razas y Potreros recién creados
        $animales = Animal::factory()
            ->count(20)
            ->recycle($razas)
            ->recycle($potreros)
            ->create();

        // Recargar desde BD para obtener los id_animal reales
        $animales = Animal::all();

        // 5. Asigna historial de Pesajes y Tratamientos a cada Animal
        foreach ($animales as $animal) {
            // Historial de pesajes (2 a 4 por animal)
            Pesaje::factory()->count(rand(2, 4))->create([
                'id_animal' => $animal->id_animal,
            ]);

            // Asociación con tabla intermedia tratamiento_animal (1 a 2 tratamientos)
            $tratamientosAleatorios = $tratamientos->random(rand(1, 2));
            foreach ($tratamientosAleatorios as $tratamiento) {
                $animal->tratamientos()->attach($tratamiento->tratamiento_id, [
                    'fecha_aplicacion' => now()->subDays(rand(1, 90)),
                    'dosis_ml'         => rand(5, 20),
                    'observaciones'    => 'Aplicación de rutina registrada mediante Seeder.',
                ]);
            }
        }
    }
}