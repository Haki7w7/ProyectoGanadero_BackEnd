<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Potrero;
use App\Models\Raza;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnimalControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifica que el index responde 200 y aplica el tope máximo de paginación.
     */
    public function test_index_responde_200_y_aplica_tope_de_paginacion(): void
    {
        Animal::factory()->count(20)->create();

        // Se solicita per_page=500, pero el backend debe acotarlo a 50
        $response = $this->getJson('/api/v1/animales?per_page=500');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'current_page',
                    'data',
                    'per_page',
                    'total',
                ],
            ]);

        $this->assertLessThanOrEqual(50, $response->json('data.per_page'));
    }

    /**
     * Verifica que la validación declarativa rechaza datos inválidos con 422.
     */
    public function test_store_valida_campos_obligatorios_y_responde_422(): void
    {
        $response = $this->postJson('/api/v1/animales', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['numero_arete', 'raza_id', 'potrero_id', 'sexo']);
    }

    /**
     * Verifica la creación exitosa retornando 201 Created.
     */
    public function test_store_crea_animal_correctamente_con_codigo_201(): void
    {
        $raza = Raza::factory()->create();
        $potrero = Potrero::factory()->create();

        $payload = [
            'numero_arete'     => 'ARETE-TEST-100',
            'raza_id'          => $raza->raza_id,
            'potrero_id'       => $potrero->potrero_id,
            'sexo'             => 'Macho',
            'fecha_nacimiento' => '2023-05-15',
            'estado'           => 'Activo',
        ];

        $response = $this->postJson('/api/v1/animales', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Animal creado correctamente.')
            ->assertJsonPath('data.numero_arete', 'ARETE-TEST-100');

        $this->assertDatabaseHas('animales', ['numero_arete' => 'ARETE-TEST-100']);
    }
}