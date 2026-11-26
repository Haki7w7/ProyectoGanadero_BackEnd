<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Potrero;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PotreroControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        \Laravel\Sanctum\Sanctum::actingAs($admin, ['*']);
    }

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
            ->assertJsonPath('data.nombre', 'Potrero Pruebas Automatizadas')
            ->assertJsonPath('data.codigo_qr', 'POTRERO-POTRERO-PRUEBAS-AUTOMATIZADAS');

        $this->assertDatabaseHas('potreros', [
            'nombre'    => 'Potrero Pruebas Automatizadas',
            'codigo_qr' => 'POTRERO-POTRERO-PRUEBAS-AUTOMATIZADAS',
        ]);
    }

    public function test_destroy_potrero_falla_si_tiene_animales_asignados(): void
    {
        $potrero = Potrero::factory()->create();
        Animal::factory()->create(['potrero_id' => $potrero->potrero_id]);

        $response = $this->deleteJson('/api/v1/potreros/' . $potrero->potrero_id);

        $response->assertStatus(409)
            ->assertJsonPath('error', 'Regla de Negocio');
        $this->assertDatabaseHas('potreros', ['potrero_id' => $potrero->potrero_id]);
    }

    public function test_reducir_capacidad_maxima_por_debajo_de_ocupacion_actual_devuelve_409_conflict(): void
    {
        $potrero = Potrero::factory()->create(['capacidad_maxima' => 5]);
        Animal::factory()->create(['potrero_id' => $potrero->potrero_id, 'estado' => 'Activo']);
        Animal::factory()->create(['potrero_id' => $potrero->potrero_id, 'estado' => 'Enfermo']);
        Animal::factory()->create(['potrero_id' => $potrero->potrero_id, 'estado' => 'Vendido']);

        $response = $this->putJson('/api/v1/potreros/' . $potrero->potrero_id, [
            'capacidad_maxima' => 1,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', 'Regla de Negocio');

        $this->assertDatabaseHas('potreros', [
            'potrero_id'       => $potrero->potrero_id,
            'capacidad_maxima' => 5,
        ]);

        $this->putJson('/api/v1/potreros/' . $potrero->potrero_id, [
            'capacidad_maxima' => 2,
        ])
            ->assertOk()
            ->assertJsonPath('data.capacidad_maxima', 2)
            ->assertJsonPath('data.ocupacion_actual', 2)
            ->assertJsonPath('data.espacios_disponibles', 0);
    }

    public function test_index_y_show_exponen_ocupacion_actual_y_espacios_disponibles_exactos(): void
    {
        $potrero = Potrero::factory()->create(['capacidad_maxima' => 5]);
        Animal::factory()->count(2)->create(['potrero_id' => $potrero->potrero_id, 'estado' => 'Activo']);
        Animal::factory()->create(['potrero_id' => $potrero->potrero_id, 'estado' => 'Enfermo']);
        Animal::factory()->count(2)->create(['potrero_id' => $potrero->potrero_id, 'estado' => 'Vendido']);

        $indexResponse = $this->getJson('/api/v1/potreros');
        $indexResponse->assertOk();

        $item = collect($indexResponse->json('data'))
            ->firstWhere('potrero_id', $potrero->potrero_id);

        $this->assertNotNull($item);
        $this->assertSame(3, $item['ocupacion_actual']);
        $this->assertSame(2, $item['espacios_disponibles']);

        $this->getJson('/api/v1/potreros/' . $potrero->potrero_id)
            ->assertOk()
            ->assertJsonPath('data.ocupacion_actual', 3)
            ->assertJsonPath('data.espacios_disponibles', 2);

        $potreroConSoloVendidos = Potrero::factory()->create(['capacidad_maxima' => 1]);
        Animal::factory()->create(['potrero_id' => $potreroConSoloVendidos->potrero_id, 'estado' => 'Vendido']);

        $this->assertTrue(
            Potrero::disponibles()->where('potrero_id', $potreroConSoloVendidos->potrero_id)->exists()
        );
    }
}