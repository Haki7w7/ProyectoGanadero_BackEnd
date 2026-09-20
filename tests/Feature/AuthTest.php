<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Comprueba que el Login es PÚBLICO y entrega el token
     */
    public function test_login_es_publico_y_retorna_token_con_credenciales_validas(): void
    {
        // Creamos un usuario de prueba
        $usuario = User::factory()->create([
            'email'    => 'admin@ganado.test',
            'password' => bcrypt('password123'),
        ]);

        // Hacemos la petición SIN ningún token previo (abierta)
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'admin@ganado.test',
            'password' => 'password123',
        ]);

        // Verifica que responde 200 y que viene el campo 'token'
        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }

    /**
     * 2. Comprueba que una ruta PRIVADA rechaza a quien no lleve token
     */
    public function test_ruta_privada_rechaza_peticion_sin_token(): void
    {
        // Intentamos entrar a /api/user sin enviar cabecera Authorization
        $response = $this->getJson('/api/user');

        // Debe rechazar con 401 Unauthorized
        $response->assertStatus(401);
    }

    /**
     * 3. Comprueba que la ruta PRIVADA deja pasar cuando sí llevas el token
     */
    public function test_ruta_privada_permite_acceso_con_bearer_token(): void
    {
        $usuario = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        // Paso A: Obtenemos el token desde el login público
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email'    => $usuario->email,
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        // Paso B: Usamos ese token en la ruta privada
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->getJson('/api/user');

        // Ahora sí responde 200 OK con los datos del usuario
        $response->assertStatus(200)
                 ->assertJsonPath('email', $usuario->email);
    }
}
