<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutenticacionApiTest extends TestCase
{
    use RefreshDatabase;

    /** Test 5: Revocación de token en logout */
    public function test_logout_revokes_token()
    {
        $user = User::factory()->create(['role' => 'admin']);

        // 1. Crear un token real de Sanctum para el usuario
        $token = $user->createToken('test-token')->plainTextToken;

        // 2. Realizar la petición de logout autenticado con la cabecera Bearer
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
                               ->postJson("/api/v1/auth/logout");

        $logoutResponse->assertOk();

        // El token quedó eliminado de la base de datos.
        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Dentro de una misma prueba Laravel conserva en memoria al usuario ya
        // autenticado; se "olvida" para que la siguiente petición vuelva a
        // validar el token (como ocurriría en una petición HTTP real).
        $this->app['auth']->forgetGuards();

        // 3. Intentar acceder a un recurso protegido con el mismo token ya revocado
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson("/api/v1/animales");

        // Ahora responderá correctamente 401 Unauthorized
        $response->assertUnauthorized();
    }

    /** Test 6: Protección contra fuerza bruta */
    public function test_login_brute_force_protection()
    {
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson("/api/v1/auth/login", [
                'email' => 'fake@example.com',
                'password' => 'wrongpass'
            ]);
        }

        $response->assertStatus(429); // Too Many Requests
    }
}
