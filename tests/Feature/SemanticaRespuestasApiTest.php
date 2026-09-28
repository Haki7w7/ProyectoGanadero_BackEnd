<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase; // 1. Importar el trait

class SemanticaRespuestasApiTest extends TestCase
{
    use RefreshDatabase; // 2. Incluir el trait dentro de la clase para resetear/migrar la BD

    /** Test 3: Estructura uniforme de respuesta */
    public function test_get_animales_returns_canonical_structure()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/animales");

        $response->assertOk()
                 ->assertJsonStructure([
                     'message',
                     'data',
                     'meta',
                     'links'
                 ]);
    }

    /** Test 4: Semántica de creación con Location */
    public function test_post_animales_returns_created_with_location()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = ['nombre' => 'Tigre', 'edad' => 3];

        $response = $this->postJson("/api/v1/animales", $payload);

        $response->assertCreated()
                 ->assertHeader('Location');
    }
}