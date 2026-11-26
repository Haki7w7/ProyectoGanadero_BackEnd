<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class ConfiguracionEntornosTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_en_produccion_falla_sin_seed_admin_password(): void
    {
        $entornoOriginal = $this->app['env'];
        putenv('SEED_ADMIN_PASSWORD');
        unset($_ENV['SEED_ADMIN_PASSWORD'], $_SERVER['SEED_ADMIN_PASSWORD']);

        try {
            $this->app['env'] = 'production';

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('SEED_ADMIN_PASSWORD');

            (new DatabaseSeeder())->run();
        } finally {
            $this->app['env'] = $entornoOriginal;
        }
    }

    public function test_database_seeder_en_produccion_solo_inicializa_admin_desde_entorno_sin_cuentas_demo(): void
    {
        $entornoOriginal = $this->app['env'];
        $claveSegura = 'Pr0d#Adm1n$2026!Segura';

        putenv("SEED_ADMIN_EMAIL=admin.prod@guateganado.cr");
        putenv("SEED_ADMIN_NAME=Admin Produccion");
        putenv("SEED_ADMIN_PASSWORD={$claveSegura}");
        $_ENV['SEED_ADMIN_EMAIL'] = 'admin.prod@guateganado.cr';
        $_ENV['SEED_ADMIN_NAME'] = 'Admin Produccion';
        $_ENV['SEED_ADMIN_PASSWORD'] = $claveSegura;

        try {
            $this->app['env'] = 'production';

            (new DatabaseSeeder())->run();

            $this->assertSame(1, User::count());
            $this->assertSame(0, Animal::count());
            $this->assertDatabaseMissing('users', ['email' => 'maria@guateganado.cr']);
            $this->assertDatabaseMissing('users', ['email' => 'juan@guateganado.cr']);

            $admin = User::where('email', 'admin.prod@guateganado.cr')->firstOrFail();
            $this->assertTrue($admin->hasRole('admin'));
            $this->assertTrue(Hash::check($claveSegura, $admin->password));
        } finally {
            $this->app['env'] = $entornoOriginal;
            putenv('SEED_ADMIN_EMAIL');
            putenv('SEED_ADMIN_NAME');
            putenv('SEED_ADMIN_PASSWORD');
            unset(
                $_ENV['SEED_ADMIN_EMAIL'],
                $_ENV['SEED_ADMIN_NAME'],
                $_ENV['SEED_ADMIN_PASSWORD'],
                $_SERVER['SEED_ADMIN_EMAIL'],
                $_SERVER['SEED_ADMIN_NAME'],
                $_SERVER['SEED_ADMIN_PASSWORD']
            );
        }
    }

    public function test_database_seeder_en_desarrollo_genera_claves_dinamicas_sin_credenciales_fijas(): void
    {
        putenv('SEED_ADMIN_PASSWORD');
        putenv('SEED_VET_PASSWORD');
        putenv('SEED_OPERARIO_PASSWORD');
        unset(
            $_ENV['SEED_ADMIN_PASSWORD'],
            $_ENV['SEED_VET_PASSWORD'],
            $_ENV['SEED_OPERARIO_PASSWORD'],
            $_SERVER['SEED_ADMIN_PASSWORD'],
            $_SERVER['SEED_VET_PASSWORD'],
            $_SERVER['SEED_OPERARIO_PASSWORD']
        );

        (new DatabaseSeeder())->run();

        $this->assertSame(3, User::count());

        $admin = User::where('email', 'aaron@guateganado.cr')->firstOrFail();
        $vet = User::where('email', 'maria@guateganado.cr')->firstOrFail();
        $operario = User::where('email', 'juan@guateganado.cr')->firstOrFail();

        $this->assertNotEmpty($admin->password);
        $this->assertNotSame($admin->password, $vet->password);
        $this->assertNotSame($vet->password, $operario->password);
    }
}
