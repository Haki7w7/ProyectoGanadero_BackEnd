<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_es_publico_y_retorna_token_con_credenciales_validas(): void
    {
        $usuario = User::factory()->create([
            'email'    => 'admin@ganado.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'admin@ganado.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }

    public function test_ruta_privada_rechaza_peticion_sin_token(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_ruta_privada_permite_acceso_con_bearer_token(): void
    {
        $usuario = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email'    => $usuario->email,
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->getJson('/api/user');

        $response->assertStatus(200)
                 ->assertJsonPath('email', $usuario->email);
    }
}
