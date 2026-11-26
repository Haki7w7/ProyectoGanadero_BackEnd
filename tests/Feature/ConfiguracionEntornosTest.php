<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class ConfiguracionEntornosTest extends TestCase
{
    use RefreshDatabase;

    private function limpiarVariablesSeeder(): void
    {
        $repo = Env::getRepository();
        foreach (['SEED_ADMIN_EMAIL', 'SEED_ADMIN_NAME', 'SEED_ADMIN_PASSWORD', 'SEED_VET_PASSWORD', 'SEED_OPERARIO_PASSWORD'] as $clave) {
            $repo->clear($clave);
            putenv($clave);
            unset($_ENV[$clave], $_SERVER[$clave]);
        }
    }

    public function test_database_seeder_en_produccion_falla_sin_seed_admin_password(): void
    {
        $entornoOriginal = $this->app['env'];
        $this->limpiarVariablesSeeder();

        try {
            $this->app['env'] = 'production';

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('SEED_ADMIN_PASSWORD');

            (new DatabaseSeeder())->run();
        } finally {
            $this->app['env'] = $entornoOriginal;
            $this->limpiarVariablesSeeder();
        }
    }

    public function test_database_seeder_en_produccion_solo_inicializa_admin_desde_entorno_sin_cuentas_demo(): void
    {
        $entornoOriginal = $this->app['env'];
        $claveSegura = 'Pr0d#Adm1n$2026!Segura';
        $repo = Env::getRepository();

        $repo->set('SEED_ADMIN_EMAIL', 'admin.prod@guateganado.cr');
        $repo->set('SEED_ADMIN_NAME', 'Admin Produccion');
        $repo->set('SEED_ADMIN_PASSWORD', $claveSegura);

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
            $this->limpiarVariablesSeeder();
        }
    }

    public function test_database_seeder_en_desarrollo_genera_claves_dinamicas_sin_credenciales_fijas(): void
    {
        $this->limpiarVariablesSeeder();

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
