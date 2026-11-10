<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\Potrero;
use App\Models\PuntoRonda;
use App\Models\Raza;
use App\Models\Ronda;
use App\Models\Tratamiento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::truncate();
        User::truncate();
        Permission::truncate();

        // 1. Crear los 3 roles requeridos por el Lab 6
        $roleAdmin       = Role::create(['name' => 'admin']);
        $roleVeterinario = Role::create(['name' => 'veterinario']);
        $roleOperario    = Role::create(['name' => 'operario']);

        // 2. Crear usuarios de prueba (uno por rol)
        $admin = User::factory()->create([
            'name'     => 'Aarón Rodríguez',
            'email'    => 'aaron@guateganado.cr',
            'password' => Hash::make('Admin@1234!'),
        ]);

        $veterinario = User::factory()->create([
            'name'     => 'María López',
            'email'    => 'maria@guateganado.cr',
            'password' => Hash::make('Vet@12345!'),
        ]);

        $operario = User::factory()->create([
            'name'     => 'Juan Villareal',
            'email'    => 'Juan@guateganado.cr',
            'password' => Hash::make('Op@123456!'),
        ]);

        // Asignar roles con Spatie (NO con columna 'role' ni Enum)
        $admin->assignRole($roleAdmin);
        $veterinario->assignRole($roleVeterinario);
        $operario->assignRole($roleOperario);

        // 3. Genera Razas y Potreros con Códigos QR reconocidos
        $razas    = Raza::factory()->count(10)->create();
        
        $potreros = collect([
            Potrero::create([
                'nombre'                 => 'Potrero Norte',
                'codigo_qr'              => 'POTRERO-NORTE',
                'hectareas_de_extension' => 25.5,
                'capacidad_maxima'       => 40,
                'estado_pasto'           => 'Excelente',
            ]),
            Potrero::create([
                'nombre'                 => 'Potrero Sur',
                'codigo_qr'              => 'POTRERO-SUR',
                'hectareas_de_extension' => 30.0,
                'capacidad_maxima'       => 50,
                'estado_pasto'           => 'Bueno',
            ]),
            Potrero::create([
                'nombre'                 => 'Potrero Este',
                'codigo_qr'              => 'POTRERO-ESTE',
                'hectareas_de_extension' => 18.0,
                'capacidad_maxima'       => 25,
                'estado_pasto'           => 'Regular',
            ]),
            Potrero::create([
                'nombre'                 => 'Potrero Oeste',
                'codigo_qr'              => 'POTRERO-OESTE',
                'hectareas_de_extension' => 22.0,
                'capacidad_maxima'       => 35,
                'estado_pasto'           => 'Excelente',
            ]),
            Potrero::create([
                'nombre'                 => 'Potrero Central',
                'codigo_qr'              => 'POTRERO-CENTRAL',
                'hectareas_de_extension' => 15.0,
                'capacidad_maxima'       => 20,
                'estado_pasto'           => 'Bueno',
            ]),
        ]);

        // Potreros adicionales generados por factory
        $potrerosExtras = Potrero::factory()->count(5)->create();
        $todosPotreros = $potreros->concat($potrerosExtras);

        // 4. Crea Catálogo de Tratamientos
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

        // 5. Crea 20 Animales (los primeros 5 con QRs estándar VACA-001 a VACA-005)
        for ($i = 1; $i <= 5; $i++) {
            $num = str_pad($i, 3, '0', STR_PAD_LEFT);
            Animal::create([
                'numero_arete'     => "CR-{$num}-LIB",
                'codigo_qr'        => "VACA-{$num}",
                'foto'             => 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=600&q=80',
                'raza_id'          => $razas->random()->raza_id,
                'potrero_id'       => $todosPotreros->random()->potrero_id,
                'sexo'             => $i % 2 === 0 ? 'Macho' : 'Hembra',
                'fecha_nacimiento' => now()->subMonths(12 + $i * 4)->format('Y-m-d'),
                'estado'           => 'Activo',
            ]);
        }

        $animalesExtras = Animal::factory()
            ->count(15)
            ->recycle($razas)
            ->recycle($todosPotreros)
            ->create();

        // Recargar todos los animales
        $animales = Animal::all();

        // 6. Asigna historial de Pesajes y Tratamientos a cada Animal
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

        // 7. Genera Rondas de Inspección de Prueba para Operario y Admin
        $rondaCompletada = Ronda::create([
            'user_id'       => $operario->id,
            'fecha_inicio'  => now()->subDays(1)->setTime(8, 0, 0),
            'fecha_fin'     => now()->subDays(1)->setTime(10, 30, 0),
            'estado'        => 'completada',
            'observaciones' => 'Ronda matutina de inspección perimetral. Cerca norte reforzada.',
        ]);

        PuntoRonda::create([
            'ronda_id'       => $rondaCompletada->id,
            'potrero_id'     => $potreros[0]->potrero_id, // POTRERO-NORTE
            'latitud'        => 10.45678901,
            'longitud'       => -84.12345678,
            'estado_potrero' => 'bueno',
            'observacion'    => 'Pasto en óptimas condiciones, agua limpia.',
            'registrado_en'  => now()->subDays(1)->setTime(8, 25, 0),
        ]);

        PuntoRonda::create([
            'ronda_id'       => $rondaCompletada->id,
            'potrero_id'     => $potreros[1]->potrero_id, // POTRERO-SUR
            'latitud'        => 10.45123456,
            'longitud'       => -84.12876543,
            'estado_potrero' => 'regular',
            'observacion'    => 'Se observa bebedero con ligera fuga.',
            'registrado_en'  => now()->subDays(1)->setTime(9, 10, 0),
        ]);

        // Ronda activa para probar la aplicación móvil inmediatamente
        $rondaActiva = Ronda::create([
            'user_id'       => $operario->id,
            'fecha_inicio'  => now()->subHours(1),
            'fecha_fin'     => null,
            'estado'        => 'en_progreso',
            'observaciones' => 'Ronda en curso iniciada por Juan Villareal.',
        ]);

        PuntoRonda::create([
            'ronda_id'       => $rondaActiva->id,
            'potrero_id'     => $potreros[0]->potrero_id, // POTRERO-NORTE
            'latitud'        => 10.45781200,
            'longitud'       => -84.12210000,
            'estado_potrero' => 'bueno',
            'observacion'    => 'Punto inicial de la ronda actual.',
            'registrado_en'  => now()->subMinutes(45),
        ]);
    }
}