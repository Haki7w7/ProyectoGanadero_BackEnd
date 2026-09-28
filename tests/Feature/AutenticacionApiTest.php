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
        $user = User::factory()->create();
        
        // 1. Crear un token real de Sanctum para el usuario
        $token = $user->createToken('test-token')->plainTextToken;

        // 2. Realizar la petición de logout autenticado con la cabecera Bearer
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
                               ->postJson("/api/v1/auth/logout");

        $logoutResponse->assertOk();

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