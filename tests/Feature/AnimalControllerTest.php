<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Potrero;
use App\Models\Raza;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnimalControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        \Laravel\Sanctum\Sanctum::actingAs($admin, ['*']);
    }

    public function test_index_responde_200_y_aplica_tope_de_paginacion(): void
    {
        Animal::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/animales?per_page=500');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
                'meta'  => ['current_page', 'last_page', 'per_page', 'total'],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);

        $this->assertLessThanOrEqual(100, $response->json('meta.per_page'));
    }

    public function test_index_filtra_por_search_en_numero_de_arete(): void
    {
        Animal::factory()->create(['numero_arete' => 'GG-1234']);
        Animal::factory()->create(['numero_arete' => 'GG-1299']);
        Animal::factory()->create(['numero_arete' => 'CR-5678']);

        $response = $this->getJson('/api/v1/animales?search=gg-12');

        $response->assertStatus(200);
        $this->assertSame(2, $response->json('meta.total'));
        $this->assertEqualsCanonicalizing(
            ['GG-1234', 'GG-1299'],
            collect($response->json('data'))->pluck('numero_arete')->all()
        );

        $this->getJson('/api/v1/animales?search=%25')->assertJsonPath('meta.total', 0);
    }

    public function test_store_valida_campos_obligatorios_y_responde_422(): void
    {
        $response = $this->postJson('/api/v1/animales', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['numero_arete', 'raza_id', 'potrero_id', 'sexo']);
    }

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
            ->assertJsonPath('data.numero_arete', 'ARETE-TEST-100')
            ->assertJsonPath('data.codigo_qr', 'VACA-ARETE-TEST-100')
            ->assertHeader('Location', '/api/v1/animales/' . $response->json('data.id_animal'));

        $this->assertDatabaseHas('animales', [
            'numero_arete' => 'ARETE-TEST-100',
            'codigo_qr'    => 'VACA-ARETE-TEST-100',
        ]);
    }

    public function test_store_en_potrero_lleno_devuelve_409_conflict(): void
    {
        $raza = Raza::factory()->create();
        $potrero = Potrero::factory()->create(['capacidad_maxima' => 2]);
        Animal::factory()->create(['potrero_id' => $potrero->potrero_id, 'estado' => 'Activo']);
        Animal::factory()->create(['potrero_id' => $potrero->potrero_id, 'estado' => 'Enfermo']);

        $response = $this->postJson('/api/v1/animales', [
            'numero_arete'     => 'ARETE-LLENO-409',
            'raza_id'          => $raza->raza_id,
            'potrero_id'       => $potrero->potrero_id,
            'sexo'             => 'Hembra',
            'fecha_nacimiento' => '2024-02-10',
            'estado'           => 'Activo',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', 'Regla de Negocio');

        $this->assertDatabaseMissing('animales', ['numero_arete' => 'ARETE-LLENO-409']);
    }

    public function test_reasignar_animal_a_potrero_lleno_devuelve_409_conflict(): void
    {
        $potreroOrigen = Potrero::factory()->create(['capacidad_maxima' => 5]);
        $potreroLleno = Potrero::factory()->create(['capacidad_maxima' => 1]);

        $animalAMover = Animal::factory()->create([
            'potrero_id' => $potreroOrigen->potrero_id,
            'estado'     => 'Activo',
        ]);
        Animal::factory()->create([
            'potrero_id' => $potreroLleno->potrero_id,
            'estado'     => 'Activo',
        ]);

        $response = $this->putJson('/api/v1/animales/' . $animalAMover->id_animal, [
            'potrero_id' => $potreroLleno->potrero_id,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', 'Regla de Negocio');

        $this->assertDatabaseHas('animales', [
            'id_animal'  => $animalAMover->id_animal,
            'potrero_id' => $potreroOrigen->potrero_id,
        ]);
    }

    public function test_animal_vendido_no_consume_cupo_y_permite_ingresar_otro_animal(): void
    {
        $raza = Raza::factory()->create();
        $potrero = Potrero::factory()->create(['capacidad_maxima' => 1]);
        Animal::factory()->create([
            'potrero_id' => $potrero->potrero_id,
            'estado'     => 'Vendido',
        ]);

        $response = $this->postJson('/api/v1/animales', [
            'numero_arete'     => 'ARETE-CUPO-LIBRE',
            'raza_id'          => $raza->raza_id,
            'potrero_id'       => $potrero->potrero_id,
            'sexo'             => 'Macho',
            'fecha_nacimiento' => '2024-03-20',
            'estado'           => 'Activo',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.numero_arete', 'ARETE-CUPO-LIBRE');

        $responseVendido = $this->postJson('/api/v1/animales', [
            'numero_arete'     => 'ARETE-YA-VENDIDO',
            'raza_id'          => $raza->raza_id,
            'potrero_id'       => $potrero->potrero_id,
            'sexo'             => 'Hembra',
            'fecha_nacimiento' => '2024-03-20',
            'estado'           => 'Vendido',
        ]);

        $responseVendido->assertStatus(201)
            ->assertJsonPath('data.estado', 'Vendido');
    }

    public function test_reactivar_animal_vendido_a_activo_en_potrero_lleno_devuelve_409_conflict(): void
    {
        $potrero = Potrero::factory()->create(['capacidad_maxima' => 1]);
        Animal::factory()->create([
            'potrero_id' => $potrero->potrero_id,
            'estado'     => 'Activo',
        ]);
        $animalVendido = Animal::factory()->create([
            'potrero_id' => $potrero->potrero_id,
            'estado'     => 'Vendido',
        ]);

        $response = $this->putJson('/api/v1/animales/' . $animalVendido->id_animal, [
            'estado' => 'Activo',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', 'Regla de Negocio');

        $this->assertDatabaseHas('animales', [
            'id_animal' => $animalVendido->id_animal,
            'estado'    => 'Vendido',
        ]);
    }

    public function test_columna_foto_no_existe_y_respuestas_de_animal_resource_no_incluyen_foto(): void
    {
        $this->assertFalse(Schema::hasColumn('animales', 'foto'));

        $animal = Animal::factory()->create([
            'codigo_qr' => 'VACA-SIN-FOTO-001',
        ]);

        $indexResponse = $this->getJson('/api/v1/animales');
        $indexResponse->assertOk();
        $this->assertNotEmpty($indexResponse->json('data'));
        foreach ($indexResponse->json('data') as $item) {
            $this->assertArrayNotHasKey('foto', $item);
        }

        $showResponse = $this->getJson('/api/v1/animales/' . $animal->id_animal);
        $showResponse->assertOk();
        $this->assertArrayNotHasKey('foto', $showResponse->json('data'));

        $qrResponse = $this->getJson('/api/v1/animales/qr/' . $animal->codigo_qr);
        $qrResponse->assertOk();
        $this->assertArrayNotHasKey('foto', $qrResponse->json('data'));
    }
}