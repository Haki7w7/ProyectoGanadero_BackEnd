<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Pruebas de registro protegido por rol admin, listado de usuarios y
 * asignación de roles mediante Spatie sin columna física users.role.
 */
class RegisterSinSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_columna_fisica_role_no_existe_en_tabla_users(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'role'));
    }

    public function test_register_sin_autenticacion_responde_401(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Nuevo Operario',
            'email' => 'nuevo@guateganado.cr',
            'password' => 'Xq7#vLm2!pRt9@wZ4',
            'password_confirmation' => 'Xq7#vLm2!pRt9@wZ4',
            'role' => 'operario',
        ])->assertStatus(401);
    }

    public function test_register_como_veterinario_u_operario_responde_403(): void
    {
        foreach (['veterinario', 'operario'] as $rolNoAutorizado) {
            $usuario = User::factory()->create(['role' => $rolNoAutorizado]);
            Sanctum::actingAs($usuario, ['*']);

            $this->postJson('/api/v1/auth/register', [
                'name' => 'Intento No Autorizado',
                'email' => "intento_{$rolNoAutorizado}@guateganado.cr",
                'password' => 'Xq7#vLm2!pRt9@wZ4',
                'password_confirmation' => 'Xq7#vLm2!pRt9@wZ4',
                'role' => 'operario',
            ])
                ->assertStatus(403)
                ->assertJsonPath('error', 'No autorizado')
                ->assertJsonPath('mensaje', 'Solo un administrador puede registrar nuevos usuarios.');
        }
    }

    public function test_register_como_admin_no_devuelve_500_sin_seeder_no_retorna_token_y_conserva_sesion_admin(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Principal',
            'email' => 'admin@guateganado.cr',
            'role' => 'admin',
        ]);
        $tokenAdmin = $admin->createToken('admin-token', ['*'])->plainTextToken;

        // El rol 'operario' aún no existe en la BD antes del registro.
        $this->assertDatabaseMissing('roles', ['name' => 'operario']);

        $r = $this->withHeader('Authorization', "Bearer {$tokenAdmin}")
            ->postJson('/api/v1/auth/register', [
                'name' => 'Nuevo Operario',
                'email' => 'Nuevo@GuateGanado.cr',
                'password' => 'Xq7#vLm2!pRt9@wZ4',
                'password_confirmation' => 'Xq7#vLm2!pRt9@wZ4',
                'role' => 'operario',
            ]);

        $r->assertStatus(201)
            ->assertJsonStructure(['message', 'usuario' => ['id', 'name', 'email', 'role']])
            ->assertJsonMissing(['token'])
            ->assertJsonPath('usuario.email', 'nuevo@guateganado.cr')
            ->assertJsonPath('usuario.role', 'operario');

        $this->assertArrayNotHasKey('token', $r->json());

        $u = User::where('email', 'nuevo@guateganado.cr')->firstOrFail();
        $this->assertSame('operario', $u->getRoleNames()->first());

        // El usuario recién creado no debe tener tokens emitidos por el registro.
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $u->id,
        ]);

        // El token del admin sigue funcionando en GET /api/user.
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$tokenAdmin}")
            ->getJson('/api/user')
            ->assertStatus(200)
            ->assertJsonPath('email', 'admin@guateganado.cr')
            ->assertJsonPath('role', 'admin');
    }

    public function test_register_con_cada_rol_aceptado_responde_201(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin, ['*']);

        foreach (['admin', 'veterinario', 'operario'] as $rol) {
            $this->postJson('/api/v1/auth/register', [
                'name' => 'Usuario '.$rol,
                'email' => $rol.'@guateganado.cr',
                'password' => 'Xq7#vLm2!pRt9@wZ4',
                'password_confirmation' => 'Xq7#vLm2!pRt9@wZ4',
                'role' => $rol,
            ])
                ->assertStatus(201)
                ->assertJsonMissingPath('token')
                ->assertJsonPath('usuario.role', $rol);
        }
    }

    public function test_register_rechaza_un_rol_inexistente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin, ['*']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Intruso',
            'email' => 'intruso@guateganado.cr',
            'password' => 'Xq7#vLm2!pRt9@wZ4',
            'password_confirmation' => 'Xq7#vLm2!pRt9@wZ4',
            'role' => 'superadmin',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role']);

        $this->assertDatabaseMissing('users', ['email' => 'intruso@guateganado.cr']);
    }

    public function test_las_abilities_del_token_se_persisten_segun_rol_al_iniciar_sesion(): void
    {
        $this->registrarComoAdmin('Operario Test', 'o1@guateganado.cr', 'operario');
        $this->registrarComoAdmin('Admin Test', 'adm@guateganado.cr', 'admin');

        $this->postJson('/api/v1/auth/login', [
            'email' => 'o1@guateganado.cr',
            'password' => 'Xq7#vLm2!pRt9@wZ4',
        ])->assertStatus(200);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'adm@guateganado.cr',
            'password' => 'Xq7#vLm2!pRt9@wZ4',
        ])->assertStatus(200);

        $operario = $this->abilitiesDe('o1@guateganado.cr');
        $this->assertStringContainsString('animales:read', $operario);
        $this->assertStringContainsString('pesajes:create', $operario);
        $this->assertStringNotContainsString('*', $operario);

        $this->assertStringContainsString('*', $this->abilitiesDe('adm@guateganado.cr'));
    }

    public function test_listado_de_usuarios_requiere_autenticacion_y_rol_admin(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Uno',
            'email' => 'admin1@guateganado.cr',
            'role' => 'admin',
        ]);
        $vet = User::factory()->create([
            'name' => 'Vet Dos',
            'email' => 'vet2@guateganado.cr',
            'role' => 'veterinario',
        ]);
        $op = User::factory()->create([
            'name' => 'Op Tres',
            'email' => 'op3@guateganado.cr',
            'role' => 'operario',
        ]);

        // 1. Sin token -> 401
        $this->getJson('/api/v1/users')->assertStatus(401);

        // 2. Como veterinario -> 403
        Sanctum::actingAs($vet, ['*']);
        $this->getJson('/api/v1/users')->assertStatus(403);

        // 3. Como operario -> 403
        Sanctum::actingAs($op, ['*']);
        $this->getJson('/api/v1/users')->assertStatus(403);

        // 4. Como admin -> 200 con lista ordenada por id ascendente y roles
        Sanctum::actingAs($admin, ['*']);
        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', $admin->id)
            ->assertJsonPath('data.0.role', 'admin')
            ->assertJsonPath('data.1.id', $vet->id)
            ->assertJsonPath('data.1.role', 'veterinario')
            ->assertJsonPath('data.2.id', $op->id)
            ->assertJsonPath('data.2.role', 'operario');
    }

    private function registrarComoAdmin(string $nombre, string $email, string $rol): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'root-admin@guateganado.cr'],
            [
                'name' => 'Root Admin',
                'password' => bcrypt('Xq7#vLm2!pRt9@wZ4'),
                'role' => 'admin',
            ]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole(\Spatie\Permission\Models\Role::findOrCreate('admin', 'web'));
        }

        Sanctum::actingAs($admin, ['*']);

        $this->postJson('/api/v1/auth/register', [
            'name' => $nombre,
            'email' => $email,
            'password' => 'Xq7#vLm2!pRt9@wZ4',
            'password_confirmation' => 'Xq7#vLm2!pRt9@wZ4',
            'role' => $rol,
        ])->assertStatus(201);
    }

    /** Columna `abilities` del último token emitido al usuario indicado. */
    private function abilitiesDe(string $email): string
    {
        return (string) DB::table('personal_access_tokens')
            ->where('tokenable_id', User::where('email', $email)->value('id'))
            ->latest('id')
            ->value('abilities');
    }
}
