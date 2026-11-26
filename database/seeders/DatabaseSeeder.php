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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        if (app()->environment('production')) {
            $adminPassword = env('SEED_ADMIN_PASSWORD');

            if (! is_string($adminPassword) || trim($adminPassword) === '') {
                throw new RuntimeException(
                    'En producción debe definir SEED_ADMIN_PASSWORD en las variables de entorno para inicializar la cuenta administradora.'
                );
            }

            $roleAdmin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'veterinario', 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'operario', 'guard_name' => 'web']);

            $adminEmail = Str::lower(trim((string) env('SEED_ADMIN_EMAIL', 'aaron@guateganado.cr')));
            $adminName = trim((string) env('SEED_ADMIN_NAME', 'Aarón Rodríguez'));

            $admin = User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name'     => $adminName,
                    'password' => Hash::make($adminPassword),
                ]
            );

            $admin->syncRoles([$roleAdmin]);

            return;
        }

        Schema::disableForeignKeyConstraints();
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        Role::truncate();
        User::truncate();
        Permission::truncate();
        Schema::enableForeignKeyConstraints();

        $roleAdmin       = Role::create(['name' => 'admin']);
        $roleVeterinario = Role::create(['name' => 'veterinario']);
        $roleOperario    = Role::create(['name' => 'operario']);

        $claveAdmin = (string) (env('SEED_ADMIN_PASSWORD') ?: Str::password(16));
        $claveVet = (string) (env('SEED_VET_PASSWORD') ?: Str::password(16));
        $claveOperario = (string) (env('SEED_OPERARIO_PASSWORD') ?: Str::password(16));

        $admin = User::factory()->create([
            'name'     => (string) env('SEED_ADMIN_NAME', 'Aarón Rodríguez'),
            'email'    => Str::lower((string) env('SEED_ADMIN_EMAIL', 'aaron@guateganado.cr')),
            'password' => Hash::make($claveAdmin),
        ]);

        $veterinario = User::factory()->create([
            'name'     => 'María López',
            'email'    => 'maria@guateganado.cr',
            'password' => Hash::make($claveVet),
        ]);

        $operario = User::factory()->create([
            'name'     => 'Juan Villareal',
            'email'    => 'juan@guateganado.cr',
            'password' => Hash::make($claveOperario),
        ]);

        $admin->assignRole($roleAdmin);
        $veterinario->assignRole($roleVeterinario);
        $operario->assignRole($roleOperario);

        if ($this->command !== null) {
            $this->command->table(
                ['Rol', 'Email', 'Contraseña local (efímera o desde .env)'],
                [
                    ['admin', $admin->email, $claveAdmin],
                    ['veterinario', $veterinario->email, $claveVet],
                    ['operario', $operario->email, $claveOperario],
                ]
            );
        }

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

        $potrerosExtras = Potrero::factory()->count(5)->create();
        $todosPotreros = $potreros->concat($potrerosExtras);

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

        for ($i = 1; $i <= 5; $i++) {
            $num = str_pad($i, 3, '0', STR_PAD_LEFT);
            Animal::create([
                'numero_arete'     => "CR-{$num}-LIB",
                'codigo_qr'        => "VACA-{$num}",
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

        $animales = Animal::all();

        foreach ($animales as $animal) {
            Pesaje::factory()->count(rand(2, 4))->create([
                'id_animal' => $animal->id_animal,
            ]);

            $tratamientosAleatorios = $tratamientos->random(rand(1, 2));
            foreach ($tratamientosAleatorios as $tratamiento) {
                $animal->tratamientos()->attach($tratamiento->tratamiento_id, [
                    'fecha_aplicacion' => now()->subDays(rand(1, 90)),
                    'dosis_ml'         => rand(5, 20),
                    'observaciones'    => 'Aplicación de rutina registrada mediante Seeder.',
                ]);
            }
        }

        $rondaCompletada = Ronda::create([
            'user_id'       => $operario->id,
            'fecha_inicio'  => now()->subDays(1)->setTime(8, 0, 0),
            'fecha_fin'     => now()->subDays(1)->setTime(10, 30, 0),
            'estado'        => 'completada',
            'observaciones' => 'Ronda matutina de inspección perimetral. Cerca norte reforzada.',
        ]);

        PuntoRonda::create([
            'ronda_id'       => $rondaCompletada->id,
            'potrero_id'     => $potreros[0]->potrero_id,
            'latitud'        => 10.45678901,
            'longitud'       => -84.12345678,
            'estado_potrero' => 'bueno',
            'observacion'    => 'Pasto en óptimas condiciones, agua limpia.',
            'registrado_en'  => now()->subDays(1)->setTime(8, 25, 0),
        ]);

        PuntoRonda::create([
            'ronda_id'       => $rondaCompletada->id,
            'potrero_id'     => $potreros[1]->potrero_id,
            'latitud'        => 10.45123456,
            'longitud'       => -84.12876543,
            'estado_potrero' => 'regular',
            'observacion'    => 'Se observa bebedero con ligera fuga.',
            'registrado_en'  => now()->subDays(1)->setTime(9, 10, 0),
        ]);

        $rondaActiva = Ronda::create([
            'user_id'       => $operario->id,
            'fecha_inicio'  => now()->subHours(1),
            'fecha_fin'     => null,
            'estado'        => 'en_progreso',
            'observaciones' => 'Ronda en curso iniciada por Juan Villareal.',
        ]);

        PuntoRonda::create([
            'ronda_id'       => $rondaActiva->id,
            'potrero_id'     => $potreros[0]->potrero_id,
            'latitud'        => 10.45781200,
            'longitud'       => -84.12210000,
            'estado_potrero' => 'bueno',
            'observacion'    => 'Punto inicial de la ronda actual.',
            'registrado_en'  => now()->subMinutes(45),
        ]);
    }
}