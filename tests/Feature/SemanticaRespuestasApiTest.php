<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Potrero;
use App\Models\Raza;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase; // 1. Importar el trait

class SemanticaRespuestasApiTest extends TestCase
{
    use RefreshDatabase; // 2. Incluir el trait dentro de la clase para resetear/migrar la BD

    /** Test 3: Estructura uniforme de respuesta */
    public function test_get_animales_returns_canonical_structure()
    {
        // El usuario necesita un rol válido: AnimalPolicy::viewAny exige admin, veterinario u operario.
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user, ['*']);

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
        // Solo el admin puede crear animales (AnimalPolicy::create).
        $user = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user, ['*']);

        $raza    = Raza::factory()->create();
        $potrero = Potrero::factory()->create(['capacidad_maxima' => 10]);

        // Payload válido según StoreAnimalRequest.
        $payload = [
            'numero_arete'     => 'ARETE-LOC-001',
            'raza_id'          => $raza->raza_id,
            'sexo'             => 'Macho',
            'fecha_nacimiento' => '2024-03-10',
            'estado'           => 'Activo',
            'potrero_id'       => $potrero->potrero_id,
        ];

        $response = $this->postJson("/api/v1/animales", $payload);

        $response->assertCreated()
                 ->assertHeader('Location', '/api/v1/animales/' . $response->json('data.id_animal'));
    }
}
