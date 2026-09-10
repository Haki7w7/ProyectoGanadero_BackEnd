<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Potrero;
use App\Models\Raza;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Usuario Administrador de Prueba
        User::factory()->create([
            'name' => 'Aarón Rodríguez',
            'email' => 'aaron@guateganado.cr',
            'password' => Hash::make('password123'),
        ]);

        // 2. Crear Raza y Potrero Base
        $raza = Raza::create(['nombre' => 'Brahman']);
        
        $potrero = Potrero::create([
            'nombre' => 'Potrero El Norte',
            'hectareas_de_extension' => 50.0,
            'capacidad_maxima' => 40,
            'estado_pasto' => 'excelente',
        ]);

        // 3. Crear Datos Semilla Oficiales del Contrato Comparativo (Hito 0)
        Animal::create([
            'numero_arete' => 'CR-1020-LIB',
            'raza_id' => $raza->raza_id,
            'sexo' => 'Hembra',
            'fecha_nacimiento' => '2023-05-10',
            'estado' => 'Activo',
            'potrero_id' => $potrero->potrero_id,
        ]);

        Animal::create([
            'numero_arete' => 'CR-1021-LIB',
            'raza_id' => $raza->raza_id,
            'sexo' => 'Macho',
            'fecha_nacimiento' => '2023-08-15',
            'estado' => 'Activo',
            'potrero_id' => $potrero->potrero_id,
        ]);

        // 4. Llamar al AnimalSeeder para generar registros adicionales
        $this->call([
            AnimalSeeder::class,
        ]);
    }
}