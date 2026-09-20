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
                'data',
                'meta'  => ['current_page', 'last_page', 'per_page', 'total'],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);

        $this->assertEquals(5, $response->json('meta.per_page'));
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

        // Lab 5: las reglas de negocio responden 409 Conflict.
        $response->assertStatus(409)
            ->assertJsonPath('error', 'Regla de Negocio');
        $this->assertDatabaseHas('potreros', ['potrero_id' => $potrero->potrero_id]);
    }
}