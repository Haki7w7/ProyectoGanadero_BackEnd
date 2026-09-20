<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Potrero;
use App\Models\Raza;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Lab 5 - Persona 1: semántica HTTP y manejo centralizado de excepciones.
 */
class ManejoErroresHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_recurso_inexistente_responde_404_en_json(): void
    {
        $this->getJson('/api/v1/animales/999999')
            ->assertStatus(404)
            ->assertExactJson([
                'error'   => 'No encontrado',
                'mensaje' => 'El animal solicitado no existe.',
            ]);
    }

    public function test_ruta_inexistente_responde_404_en_json(): void
    {
        $this->getJson('/api/v1/no-existe')
            ->assertStatus(404)
            ->assertJsonPath('error', 'No encontrado')
            ->assertJsonPath('mensaje', 'La ruta solicitada no existe.');
    }

    public function test_model_not_found_exception_responde_404_en_json(): void
    {
        Route::get('/api/v1/_prueba/modelo', function () {
            throw (new ModelNotFoundException())->setModel(Animal::class, [1]);
        });

        $this->getJson('/api/v1/_prueba/modelo')
            ->assertStatus(404)
            ->assertExactJson([
                'error'   => 'No encontrado',
                'mensaje' => 'El recurso solicitado no existe.',
            ]);
    }

    public function test_validacion_responde_422_con_detalle_por_campo(): void
    {
        $this->postJson('/api/v1/animales', [])
            ->assertStatus(422)
            ->assertJsonPath('error', 'Validación')
            ->assertJsonValidationErrors(['numero_arete', 'raza_id', 'potrero_id', 'sexo']);
    }

    public function test_regla_de_negocio_responde_409_conflict(): void
    {
        $potrero = Potrero::factory()->create();
        Animal::factory()->create(['potrero_id' => $potrero->potrero_id]);

        $this->deleteJson('/api/v1/potreros/' . $potrero->potrero_id)
            ->assertStatus(409)
            ->assertJsonPath('error', 'Regla de Negocio');
    }

    public function test_error_inesperado_responde_500_sin_stack_trace(): void
    {
        // Aun con APP_DEBUG=true no debe filtrarse ningún detalle interno.
        config(['app.debug' => true]);

        Route::get('/api/v1/_prueba/boom', function () {
            throw new \RuntimeException('secreto interno de la base de datos');
        });

        $response = $this->getJson('/api/v1/_prueba/boom');

        $response->assertStatus(500)
            ->assertExactJson([
                'error'   => 'Error interno',
                'mensaje' => 'Ocurrió un error inesperado. Intente nuevamente más tarde.',
            ]);

        $this->assertStringNotContainsString('secreto interno', $response->getContent());
        $this->assertStringNotContainsString('RuntimeException', $response->getContent());
        $response->assertJsonMissingPath('trace')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('file');
    }

    public function test_metodo_no_permitido_responde_405_en_json(): void
    {
        $this->postJson('/api/v1/animales/1')
            ->assertStatus(405)
            ->assertJsonPath('error', 'Solicitud inválida');
    }

    public function test_creacion_responde_201_con_cabecera_location(): void
    {
        $raza = Raza::factory()->create();
        $potrero = Potrero::factory()->create();

        $response = $this->postJson('/api/v1/animales', [
            'numero_arete'     => 'ARETE-LOC-001',
            'raza_id'          => $raza->raza_id,
            'potrero_id'       => $potrero->potrero_id,
            'sexo'             => 'Macho',
            'fecha_nacimiento' => '2023-05-15',
            'estado'           => 'Activo',
        ]);

        $response->assertStatus(201);

        $id = $response->json('data.id_animal');
        $this->assertNotNull($id);
        $response->assertHeader('Location', '/api/v1/animales/' . $id);
    }

    public function test_eliminacion_exitosa_responde_204_sin_cuerpo(): void
    {
        $animal = Animal::factory()->create();

        $response = $this->deleteJson('/api/v1/animales/' . $animal->id_animal);

        $response->assertNoContent();
        $this->assertSame('', $response->getContent());
        $this->assertDatabaseMissing('animales', ['id_animal' => $animal->id_animal]);
    }

    public function test_paginacion_tiene_estructura_uniforme_y_conserva_filtros_en_links(): void
    {
        Animal::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/animales?per_page=2&sort_by=numero_arete');

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data',
                'meta'  => ['current_page', 'last_page', 'per_page', 'total'],
                'links' => ['first', 'last', 'prev', 'next'],
            ])
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonCount(2, 'data');

        $this->assertNull($response->json('links.prev'));
        $this->assertStringContainsString('per_page=2', $response->json('links.next'));
        $this->assertStringContainsString('sort_by=numero_arete', $response->json('links.next'));
    }
}
