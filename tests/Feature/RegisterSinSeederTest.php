<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Fase 0: el registro público debe funcionar sin depender del seeder.
 *
 * Regresión de un 500 (RoleDoesNotExist) que se producía cuando la base de
 * datos aún no tenía creados los roles de Spatie.
 */
class RegisterSinSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_publico_no_devuelve_500_sin_seeder(): void
    {
        // BD limpia: no se ha corrido DatabaseSeeder, luego no existen los roles.
        $this->assertDatabaseCount('roles', 0);

        $r = $this->postJson('/api/v1/auth/register', [
            'name' => 'Nuevo Operario',
            'email' => 'nuevo@guateganado.cr',
            'password' => 'Xq7#vLm2!pRt9@wZ4',
            'password_confirmation' => 'Xq7#vLm2!pRt9@wZ4',
            'role' => 'operario',
        ]);

        $r->assertStatus(201)->assertJsonStructure(['message', 'token', 'usuario']);
        $this->assertNotNull($r->json('token'));

        $u = User::where('email', 'nuevo@guateganado.cr')->first();
        $this->assertSame('operario', $u->getRoleNames()->first());
    }

    public function test_register_con_cada_rol_aceptado_responde_201(): void
    {
        foreach (['admin', 'veterinario', 'operario'] as $rol) {
            $this->postJson('/api/v1/auth/register', [
                'name' => 'Usuario '.$rol,
                'email' => $rol.'@guateganado.cr',
                'password' => 'Xq7#vLm2!pRt9@wZ4',
                'password_confirmation' => 'Xq7#vLm2!pRt9@wZ4',
                'role' => $rol,
            ])->assertStatus(201);
        }
    }

    public function test_register_rechaza_un_rol_inexistente(): void
    {
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

    public function test_las_abilities_del_token_se_persisten_segun_rol(): void
    {
        $this->registrar('Operario Test', 'o1@guateganado.cr', 'operario');
        $this->registrar('Admin Test', 'adm@guateganado.cr', 'admin');

        $operario = $this->abilitiesDe('o1@guateganado.cr');
        $this->assertStringContainsString('animales:read', $operario);
        $this->assertStringContainsString('pesajes:create', $operario);
        $this->assertStringNotContainsString('*', $operario);

        $this->assertStringContainsString('*', $this->abilitiesDe('adm@guateganado.cr'));
    }

    /**
     * Login y register deben emitir el mismo conjunto de abilities para un rol
     * dado (App\Support\Abilities es la única fuente de verdad).
     */
    public function test_login_y_register_emiten_las_mismas_abilities(): void
    {
        $this->registrar('Operario Login', 'ol@guateganado.cr', 'operario');

        $abilitiesDelRegistro = $this->abilitiesDelToken(
            $this->ultimoTokenDe('ol@guateganado.cr')
        );

        $tokenDelLogin = $this->postJson('/api/v1/auth/login', [
            'email' => 'ol@guateganado.cr',
            'password' => 'Xq7#vLm2!pRt9@wZ4',
        ])->json('token');

        $this->assertSame(
            $abilitiesDelRegistro,
            $this->abilitiesDelToken($tokenDelLogin)
        );
    }

    private function registrar(string $nombre, string $email, string $rol): void
    {
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

    /** Columna `abilities` del token indicado en texto plano. */
    private function abilitiesDelToken(string $plainTextToken): string
    {
        return (string) DB::table('personal_access_tokens')
            ->where('token', hash('sha256', $plainTextToken))
            ->value('abilities');
    }

    /**
     * Reconstruye el texto plano del token emitido en el último login/registro.
     * Sanctum solo guarda el hash, pero el id forma parte del texto plano
     * ("<id>|<hash>"), así que se puede recomponer a partir de la fila.
     */
    private function ultimoTokenDe(string $email): string
    {
        $fila = DB::table('personal_access_tokens')
            ->where('tokenable_id', User::where('email', $email)->value('id'))
            ->latest('id')
            ->first();

        return $fila->id.'|'.$fila->token;
    }
}
