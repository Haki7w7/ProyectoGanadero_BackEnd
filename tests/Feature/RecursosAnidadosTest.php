<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\Tratamiento;
use App\Models\TratamientoAnimal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Lab 5 - Persona 2: API Resources y rutas anidadas del módulo
 * Producción y Sanidad (Animales, Pesajes, Tratamientos).
 */
class RecursosAnidadosTest extends TestCase
{
    use RefreshDatabase;

    private function crearTratamiento(array $atributos = []): Tratamiento
    {
        return Tratamiento::create(array_merge([
            'nombre'      => 'Vacuna Aftosa',
            'descripcion' => 'Dosis anual',
            'tipo'        => 'Vacuna',
        ], $atributos));
    }

    private function aplicar(Animal $animal, Tratamiento $tratamiento, array $extra = []): TratamientoAnimal
    {
        return TratamientoAnimal::create(array_merge([
            'id_animal'        => $animal->id_animal,
            'tratamiento_id'   => $tratamiento->tratamiento_id,
            'fecha_aplicacion' => '2026-08-01 08:00:00',
            'dosis_ml'         => 5.5,
            'observaciones'    => 'Sin novedades',
        ], $extra));
    }

    // ---------------------------------------------------------------
    // GET /api/v1/animales/{animal}/pesajes
    // ---------------------------------------------------------------

    public function test_lista_solo_los_pesajes_del_animal_con_paginacion_uniforme(): void
    {
        $animal = Animal::factory()->create();
        $otro   = Animal::factory()->create();
        Pesaje::factory()->count(3)->create(['id_animal' => $animal->id_animal]);
        Pesaje::factory()->count(2)->create(['id_animal' => $otro->id_animal]);

        // Aunque se intente filtrar por otro animal en la query, manda el de la URL.
        $response = $this->getJson("/api/v1/animales/{$animal->id_animal}/pesajes?per_page=2&id_animal={$otro->id_animal}");

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [['pesaje_id', 'id_animal', 'peso_kg', 'fecha_pesaje', 'observaciones', 'animal', 'created_at', 'updated_at']],
                'meta'  => ['current_page', 'last_page', 'per_page', 'total'],
                'links' => ['first', 'last', 'prev', 'next'],
            ])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2);

        foreach ($response->json('data') as $pesaje) {
            $this->assertSame($animal->id_animal, $pesaje['id_animal']);
        }

        $this->assertStringContainsString('per_page=2', $response->json('links.next'));
    }

    public function test_pesajes_de_un_animal_inexistente_responde_404(): void
    {
        $this->getJson('/api/v1/animales/999999/pesajes')
            ->assertStatus(404)
            ->assertExactJson([
                'error'   => 'No encontrado',
                'mensaje' => 'El animal solicitado no existe.',
            ]);
    }

    public function test_animal_no_numerico_en_ruta_anidada_responde_404(): void
    {
        $this->getJson('/api/v1/animales/abc/pesajes')
            ->assertStatus(404)
            ->assertJsonPath('error', 'No encontrado');
    }

    // ---------------------------------------------------------------
    // POST /api/v1/animales/{animal}/pesajes
    // ---------------------------------------------------------------

    public function test_registra_un_pesaje_para_el_animal_de_la_url(): void
    {
        $animal = Animal::factory()->create();
        $otro   = Animal::factory()->create();

        // Se intenta colar otro id_animal en el cuerpo: debe ignorarse.
        $response = $this->postJson("/api/v1/animales/{$animal->id_animal}/pesajes", [
            'id_animal'     => $otro->id_animal,
            'peso_kg'       => 412.5,
            'fecha_pesaje'  => '2026-09-01 10:00:00',
            'observaciones' => 'Control mensual',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Pesaje registrado correctamente.')
            ->assertJsonPath('data.id_animal', $animal->id_animal)
            ->assertJsonPath('data.peso_kg', 412.5)
            ->assertHeader('Location', '/api/v1/pesajes/' . $response->json('data.pesaje_id'));

        $this->assertDatabaseHas('pesajes', ['id_animal' => $animal->id_animal, 'peso_kg' => 412.5]);
        $this->assertDatabaseMissing('pesajes', ['id_animal' => $otro->id_animal]);
    }

    public function test_registrar_pesaje_valida_los_campos_del_cuerpo(): void
    {
        $animal = Animal::factory()->create();

        $this->postJson("/api/v1/animales/{$animal->id_animal}/pesajes", [])
            ->assertStatus(422)
            ->assertJsonPath('error', 'Validación')
            ->assertJsonValidationErrors(['peso_kg', 'fecha_pesaje'])
            ->assertJsonMissingValidationErrors('id_animal');
    }

    public function test_registrar_pesaje_en_animal_inexistente_responde_404_y_no_422(): void
    {
        $this->postJson('/api/v1/animales/999999/pesajes', [])
            ->assertStatus(404)
            ->assertJsonPath('mensaje', 'El animal solicitado no existe.');

        $this->postJson('/api/v1/animales/999999/pesajes', [
            'peso_kg'      => 300,
            'fecha_pesaje' => '2026-09-01 10:00:00',
        ])->assertStatus(404);
    }

    // ---------------------------------------------------------------
    // GET /api/v1/animales/{animal}/tratamientos  (historial sanitario)
    // ---------------------------------------------------------------

    public function test_historial_sanitario_lista_las_aplicaciones_del_animal(): void
    {
        $animal = Animal::factory()->create();
        $otro   = Animal::factory()->create();
        $vacuna = $this->crearTratamiento();

        $this->aplicar($animal, $vacuna);
        $this->aplicar($animal, $vacuna, ['fecha_aplicacion' => '2026-09-01 08:00:00']);
        $this->aplicar($otro, $vacuna);

        $response = $this->getJson("/api/v1/animales/{$animal->id_animal}/tratamientos");

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [[
                    'tratamiento_animal_id', 'id_animal', 'tratamiento_id',
                    'fecha_aplicacion', 'dosis_ml', 'observaciones',
                    'tratamiento' => ['tratamiento_id', 'nombre', 'descripcion', 'tipo'],
                ]],
                'meta'  => ['current_page', 'last_page', 'per_page', 'total'],
                'links' => ['first', 'last', 'prev', 'next'],
            ])
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.tratamiento.nombre', 'Vacuna Aftosa')
            ->assertJsonPath('data.0.dosis_ml', 5.5);

        foreach ($response->json('data') as $registro) {
            $this->assertSame($animal->id_animal, $registro['id_animal']);
        }
    }

    public function test_historial_sanitario_de_un_animal_inexistente_responde_404(): void
    {
        $this->getJson('/api/v1/animales/999999/tratamientos')
            ->assertStatus(404)
            ->assertJsonPath('error', 'No encontrado');
    }

    // ---------------------------------------------------------------
    // API Resources: nombres de campo estables y relaciones condicionales
    // ---------------------------------------------------------------

    public function test_detalle_de_animal_expone_relaciones_con_nombres_estables(): void
    {
        $animal = Animal::factory()->create(['fecha_nacimiento' => '2023-05-15']);
        Pesaje::factory()->create(['id_animal' => $animal->id_animal, 'peso_kg' => 350.25]);
        $this->aplicar($animal, $this->crearTratamiento());

        $response = $this->getJson("/api/v1/animales/{$animal->id_animal}");

        $response->assertOk()
            ->assertJsonPath('data.id_animal', $animal->id_animal)
            ->assertJsonPath('data.numero_arete', $animal->numero_arete)
            ->assertJsonPath('data.fecha_nacimiento', '2023-05-15')
            ->assertJsonPath('data.raza.raza_id', $animal->raza_id)
            ->assertJsonPath('data.potrero.potrero_id', $animal->potrero_id)
            ->assertJsonPath('data.pesajes.0.peso_kg', 350.25)
            ->assertJsonPath('data.tratamientos.0.nombre', 'Vacuna Aftosa')
            ->assertJsonPath('data.tratamientos.0.aplicacion.dosis_ml', 5.5)
            ->assertJsonPath('data.tratamientos.0.aplicacion.observaciones', 'Sin novedades');
    }

    public function test_listado_de_animales_usa_resource_sin_relaciones_pesadas(): void
    {
        Animal::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/animales');

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [['id_animal', 'numero_arete', 'sexo', 'fecha_nacimiento', 'estado', 'raza_id', 'potrero_id', 'raza', 'potrero', 'created_at', 'updated_at']],
                'meta'  => ['current_page', 'last_page', 'per_page', 'total'],
                'links' => ['first', 'last', 'prev', 'next'],
            ])
            ->assertJsonMissingPath('data.0.pesajes')
            ->assertJsonMissingPath('data.0.tratamientos');
    }

    public function test_detalle_de_pesaje_incluye_resumen_del_animal(): void
    {
        $pesaje = Pesaje::factory()->create();

        $this->getJson("/api/v1/pesajes/{$pesaje->pesaje_id}")
            ->assertOk()
            ->assertJsonPath('data.pesaje_id', $pesaje->pesaje_id)
            ->assertJsonPath('data.animal.id_animal', $pesaje->id_animal)
            ->assertJsonStructure(['data' => ['animal' => ['id_animal', 'numero_arete']]]);
    }

    public function test_id_no_numerico_en_rutas_planas_responde_404_en_json(): void
    {
        foreach (['animales', 'pesajes', 'tratamientos', 'tratamientos-aplicaciones', 'razas', 'potreros'] as $recurso) {
            $this->getJson("/api/v1/{$recurso}/abc")
                ->assertStatus(404)
                ->assertJsonPath('error', 'No encontrado');
        }
    }

    // ---------------------------------------------------------------
    // 201 + Location y 204 en los controladores refactorizados
    // ---------------------------------------------------------------

    public function test_crear_pesaje_plano_responde_201_con_location(): void
    {
        $animal = Animal::factory()->create();

        $response = $this->postJson('/api/v1/pesajes', [
            'id_animal'    => $animal->id_animal,
            'peso_kg'      => 280,
            'fecha_pesaje' => '2026-09-05 09:00:00',
        ]);

        $response->assertStatus(201)
            ->assertHeader('Location', '/api/v1/pesajes/' . $response->json('data.pesaje_id'));
    }

    public function test_crear_y_eliminar_tratamiento_responden_201_y_204(): void
    {
        $crear = $this->postJson('/api/v1/tratamientos', [
            'nombre' => 'Desparasitante',
            'tipo'   => 'Desparasitación',
        ]);

        $id = $crear->json('data.tratamiento_id');
        $crear->assertStatus(201)
            ->assertHeader('Location', "/api/v1/tratamientos/{$id}")
            ->assertJsonPath('data.nombre', 'Desparasitante');

        $eliminar = $this->deleteJson("/api/v1/tratamientos/{$id}");
        $eliminar->assertNoContent();
        $this->assertSame('', $eliminar->getContent());
    }

    public function test_no_se_puede_eliminar_un_tratamiento_con_aplicaciones(): void
    {
        $animal      = Animal::factory()->create();
        $tratamiento = $this->crearTratamiento();
        $this->aplicar($animal, $tratamiento);

        $this->deleteJson("/api/v1/tratamientos/{$tratamiento->tratamiento_id}")
            ->assertStatus(409)
            ->assertJsonPath('error', 'Regla de Negocio');
    }

    public function test_crear_y_eliminar_aplicacion_de_tratamiento_responden_201_y_204(): void
    {
        $animal      = Animal::factory()->create();
        $tratamiento = $this->crearTratamiento();

        $crear = $this->postJson('/api/v1/tratamientos-aplicaciones', [
            'id_animal'        => $animal->id_animal,
            'tratamiento_id'   => $tratamiento->tratamiento_id,
            'fecha_aplicacion' => '2026-09-10 07:30:00',
            'dosis_ml'         => 3.25,
        ]);

        $id = $crear->json('data.tratamiento_animal_id');
        $crear->assertStatus(201)
            ->assertHeader('Location', "/api/v1/tratamientos-aplicaciones/{$id}")
            ->assertJsonPath('data.dosis_ml', 3.25);

        $this->deleteJson("/api/v1/tratamientos-aplicaciones/{$id}")->assertNoContent();
        $this->assertDatabaseMissing('tratamiento_animal', ['tratamiento_animal_id' => $id]);
    }
}
