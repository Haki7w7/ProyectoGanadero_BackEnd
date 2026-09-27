<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\Potrero;
use App\Models\Raza;
use App\Models\Tratamiento;
use App\Models\TratamientoAnimal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AutorizacionRolesApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $veterinario;
    private User $operario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin       = User::factory()->create(['role' => 'admin']);
        $this->veterinario = User::factory()->create(['role' => 'veterinario']);
        $this->operario    = User::factory()->create(['role' => 'operario']);
    }

    // ==========================================
    // ANIMALES: Solo Admin puede crear/eliminar
    // ==========================================

    public function test_admin_puede_eliminar_animal_y_responde_204(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $animal = Animal::factory()->create();

        $response = $this->deleteJson("/api/v1/animales/{$animal->id_animal}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('animales', ['id_animal' => $animal->id_animal]);
    }

    public function test_operario_no_puede_eliminar_animal_y_responde_403(): void
    {
        Sanctum::actingAs($this->operario, ['*']);

        $animal = Animal::factory()->create();

        $response = $this->deleteJson("/api/v1/animales/{$animal->id_animal}");

        $response->assertStatus(403)
            ->assertExactJson([
                'error'   => 'Acceso denegado',
                'mensaje' => 'No tiene permisos para realizar esta acción.',
            ]);

        $this->assertDatabaseHas('animales', ['id_animal' => $animal->id_animal]);
    }

    public function test_veterinario_no_puede_crear_animal_y_responde_403(): void
    {
        Sanctum::actingAs($this->veterinario, ['*']);

        $potrero = Potrero::factory()->create();
        $raza    = Raza::factory()->create();

        $response = $this->postJson('/api/v1/animales', [
            'numero_arete'     => 'VET-TEST-01',
            'raza_id'          => $raza->raza_id,
            'potrero_id'       => $potrero->potrero_id,
            'sexo'             => 'Hembra',
            'fecha_nacimiento' => '2024-01-01',
            'estado'           => 'Activo',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('error', 'Acceso denegado');
    }

    // ==========================================
    // PESAJES: Operario y Admin pueden crear; solo Admin elimina
    // ==========================================

    public function test_operario_puede_registrar_pesaje_y_responde_201(): void
    {
        Sanctum::actingAs($this->operario, ['*']);

        $animal = Animal::factory()->create();

        $response = $this->postJson('/api/v1/pesajes', [
            'id_animal'     => $animal->id_animal,
            'peso_kg'       => 350.50,
            'fecha_pesaje'  => '2026-03-01',
            'observaciones' => 'Pesaje de control',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Pesaje registrado correctamente.');
    }

    public function test_operario_no_puede_eliminar_pesaje_y_responde_403(): void
    {
        Sanctum::actingAs($this->operario, ['*']);

        $pesaje = Pesaje::factory()->create();

        $response = $this->deleteJson("/api/v1/pesajes/{$pesaje->pesaje_id}");

        $response->assertStatus(403)
            ->assertJsonPath('error', 'Acceso denegado');

        $this->assertDatabaseHas('pesajes', ['pesaje_id' => $pesaje->pesaje_id]);
    }

    public function test_admin_puede_eliminar_pesaje_y_responde_204(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $pesaje = Pesaje::factory()->create();

        $response = $this->deleteJson("/api/v1/pesajes/{$pesaje->pesaje_id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('pesajes', ['pesaje_id' => $pesaje->pesaje_id]);
    }

    // ==========================================
    // TRATAMIENTOS: Veterinario y Admin pueden registrar; Operario denegado
    // ==========================================

    public function test_veterinario_puede_registrar_aplicacion_tratamiento_y_responde_201(): void
    {
        Sanctum::actingAs($this->veterinario, ['*']);

        $animal      = Animal::factory()->create();
        $tratamiento = Tratamiento::create([
            'nombre'      => 'Vacuna Carbunco',
            'descripcion' => 'Inmunización anual',
            'tipo'        => 'Vacuna',
        ]);

        $response = $this->postJson('/api/v1/tratamientos-aplicaciones', [
            'id_animal'        => $animal->id_animal,
            'tratamiento_id'   => $tratamiento->tratamiento_id,
            'fecha_aplicacion' => '2026-03-15 09:00:00',
            'dosis_ml'         => 10.0,
            'observaciones'    => 'Aplicado en pata trasera',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Aplicación de tratamiento registrada correctamente.');
    }

    public function test_operario_no_puede_registrar_aplicacion_tratamiento_y_responde_403(): void
    {
        Sanctum::actingAs($this->operario, ['*']);

        $animal      = Animal::factory()->create();
        $tratamiento = Tratamiento::create([
            'nombre'      => 'Antibiótico Penicilina',
            'descripcion' => 'Dosis de choque',
            'tipo'        => 'Antibiótico',
        ]);

        $response = $this->postJson('/api/v1/tratamientos-aplicaciones', [
            'id_animal'        => $animal->id_animal,
            'tratamiento_id'   => $tratamiento->tratamiento_id,
            'fecha_aplicacion' => '2026-03-15 09:00:00',
            'dosis_ml'         => 15.0,
        ]);

        $response->assertStatus(403)
            ->assertExactJson([
                'error'   => 'Acceso denegado',
                'mensaje' => 'No tiene permisos para realizar esta acción.',
            ]);
    }

    // ==========================================
    // POTREROS: Solo Admin crea/modifica/elimina; todos leen
    // ==========================================

    public function test_todos_los_roles_pueden_listar_potreros_200(): void
    {
        Potrero::factory()->count(2)->create();

        foreach ([$this->admin, $this->veterinario, $this->operario] as $usuario) {
            Sanctum::actingAs($usuario, ['*']);
            $this->getJson('/api/v1/potreros')->assertStatus(200);
        }
    }

    public function test_operario_no_puede_crear_potrero_y_responde_403(): void
    {
        Sanctum::actingAs($this->operario, ['*']);

        $response = $this->postJson('/api/v1/potreros', [
            'nombre'                 => 'Potrero Ilícito',
            'hectareas_de_extension' => 10,
            'capacidad_maxima'       => 15,
            'estado_pasto'           => 'Bueno',
        ]);

        $response->assertStatus(403)
            ->assertExactJson([
                'error'   => 'Acceso denegado',
                'mensaje' => 'No tiene permisos para realizar esta acción.',
            ]);
    }
}
