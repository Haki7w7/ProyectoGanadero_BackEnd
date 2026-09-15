<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Potrero;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PotreroControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_potreros_responde_200_con_paginacion(): void
    {
        Potrero::factory()->count(10)->create();

        $response = $this->getJson('/api/v1/potreros?per_page=5&sort_by=capacidad_maxima&order=desc');

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

        $this->assertEquals(5, $response->json('data.per_page'));
    }

    public function test_store_potrero_crea_con_codigo_201(): void
    {
        $payload = [
            'nombre'                 => 'Potrero Pruebas Automatizadas',
            'hectareas_de_extension' => 25.50,
            'capacidad_maxima'       => 35,
            'estado_pasto'           => 'Excelente',
        ];

        $response = $this->postJson('/api/v1/potreros', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.nombre', 'Potrero Pruebas Automatizadas');

        $this->assertDatabaseHas('potreros', ['nombre' => 'Potrero Pruebas Automatizadas']);
    }

    public function test_destroy_potrero_falla_si_tiene_animales_asignados(): void
    {
        $potrero = Potrero::factory()->create();
        Animal::factory()->create(['potrero_id' => $potrero->potrero_id]);

        $response = $this->deleteJson('/api/v1/potreros/' . $potrero->potrero_id);

        $response->assertStatus(422);
        $this->assertDatabaseHas('potreros', ['potrero_id' => $potrero->potrero_id]);
    }
}