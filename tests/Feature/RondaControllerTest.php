<?php

namespace Tests\Feature;

use App\Models\Potrero;
use App\Models\PuntoRonda;
use App\Models\Ronda;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RondaControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operario1;
    private User $operario2;
    private Potrero $potreroNorte;
    private Potrero $potreroSur;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->operario1 = User::factory()->create(['name' => 'Operario Uno', 'role' => 'operario']);
        $this->operario2 = User::factory()->create(['name' => 'Operario Dos', 'role' => 'operario']);

        $this->potreroNorte = Potrero::factory()->create([
            'nombre'    => 'Potrero Norte',
            'codigo_qr' => 'POTRERO-NORTE',
        ]);

        $this->potreroSur = Potrero::factory()->create([
            'nombre'    => 'Potrero Sur',
            'codigo_qr' => 'POTRERO-SUR',
        ]);
    }

    public function test_operario_solo_ve_sus_propias_rondas(): void
    {
        // Rondas de operario 1
        Ronda::factory()->create(['user_id' => $this->operario1->id]);
        Ronda::factory()->create(['user_id' => $this->operario1->id]);

        // Ronda de operario 2
        Ronda::factory()->create(['user_id' => $this->operario2->id]);

        Sanctum::actingAs($this->operario1, ['*']);

        $response = $this->getJson('/api/v1/rondas');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 2);

        foreach ($response->json('data') as $ronda) {
            $this->assertEquals($this->operario1->id, $ronda['user_id']);
        }
    }

    public function test_admin_ve_el_historial_global_de_todos_los_operarios(): void
    {
        Ronda::factory()->create(['user_id' => $this->operario1->id]);
        Ronda::factory()->create(['user_id' => $this->operario2->id]);

        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson('/api/v1/rondas');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'operador' => ['id', 'name', 'email'],
                        'estado',
                        'puntos',
                    ],
                ],
            ]);
    }

    public function test_operario_puede_iniciar_ronda_y_se_asigna_su_user_id(): void
    {
        Sanctum::actingAs($this->operario1, ['*']);

        $response = $this->postJson('/api/v1/rondas', [
            'observaciones' => 'Iniciando patrullaje de prueba.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.user_id', $this->operario1->id)
            ->assertJsonPath('data.estado', 'en_progreso')
            ->assertJsonPath('data.observaciones', 'Iniciando patrullaje de prueba.');

        $this->assertDatabaseHas('rondas', [
            'user_id' => $this->operario1->id,
            'estado'  => 'en_progreso',
        ]);
    }

    public function test_operario_no_puede_iniciar_segunda_ronda_si_ya_tiene_una_en_progreso(): void
    {
        Ronda::factory()->create([
            'user_id' => $this->operario1->id,
            'estado'  => 'en_progreso',
        ]);

        Sanctum::actingAs($this->operario1, ['*']);

        $response = $this->postJson('/api/v1/rondas');

        $response->assertStatus(409)
            ->assertJsonPath('error', 'Regla de Negocio');
    }

    public function test_operario_puede_ver_detalle_de_su_ronda_pero_no_la_de_otro(): void
    {
        $ronda1 = Ronda::factory()->create(['user_id' => $this->operario1->id]);
        $ronda2 = Ronda::factory()->create(['user_id' => $this->operario2->id]);

        Sanctum::actingAs($this->operario1, ['*']);

        // Ver su propia ronda -> 200 OK
        $response1 = $this->getJson("/api/v1/rondas/{$ronda1->id}");
        $response1->assertStatus(200)
            ->assertJsonPath('data.id', $ronda1->id);

        // Intentar ver ronda de otro operario -> 403 Forbidden
        $response2 = $this->getJson("/api/v1/rondas/{$ronda2->id}");
        $response2->assertStatus(403)
            ->assertJsonPath('error', 'Acceso denegado');
    }

    public function test_admin_puede_ver_detalle_de_cualquier_ronda(): void
    {
        $ronda = Ronda::factory()->create(['user_id' => $this->operario1->id]);

        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson("/api/v1/rondas/{$ronda->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $ronda->id);
    }

    public function test_registrar_punto_de_control_con_codigo_qr_de_potrero(): void
    {
        $ronda = Ronda::factory()->create([
            'user_id' => $this->operario1->id,
            'estado'  => 'en_progreso',
        ]);

        Sanctum::actingAs($this->operario1, ['*']);

        $payload = [
            'codigo_qr'      => 'POTRERO-NORTE',
            'latitud'        => 10.45678901,
            'longitud'       => -84.12345678,
            'estado_potrero' => 'bueno',
            'observacion'    => 'Pasto verde, cerca en buen estado.',
        ];

        $response = $this->postJson("/api/v1/rondas/{$ronda->id}/puntos", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.ronda_id', $ronda->id)
            ->assertJsonPath('data.potrero.codigo_qr', 'POTRERO-NORTE')
            ->assertJsonPath('data.estado_potrero', 'bueno');

        $this->assertDatabaseHas('puntos_ronda', [
            'ronda_id'   => $ronda->id,
            'potrero_id' => $this->potreroNorte->potrero_id,
        ]);
    }

    public function test_registrar_punto_falla_si_el_potrero_no_existe(): void
    {
        $ronda = Ronda::factory()->create([
            'user_id' => $this->operario1->id,
            'estado'  => 'en_progreso',
        ]);

        Sanctum::actingAs($this->operario1, ['*']);

        $response = $this->postJson("/api/v1/rondas/{$ronda->id}/puntos", [
            'codigo_qr'      => 'POTRERO-INEXISTENTE',
            'latitud'        => 10.45678901,
            'longitud'       => -84.12345678,
            'estado_potrero' => 'bueno',
        ]);

        // Falla validación 422 (exists)
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['codigo_qr']);
    }

    public function test_registrar_punto_falla_si_la_ronda_esta_completada(): void
    {
        $ronda = Ronda::factory()->completada()->create([
            'user_id' => $this->operario1->id,
        ]);

        Sanctum::actingAs($this->operario1, ['*']);

        $response = $this->postJson("/api/v1/rondas/{$ronda->id}/puntos", [
            'codigo_qr'      => 'POTRERO-NORTE',
            'latitud'        => 10.45678901,
            'longitud'       => -84.12345678,
            'estado_potrero' => 'bueno',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', 'Regla de Negocio');
    }

    public function test_registrar_punto_falla_si_el_potrero_se_repite_en_la_misma_ronda(): void
    {
        $ronda = Ronda::factory()->create([
            'user_id' => $this->operario1->id,
            'estado'  => 'en_progreso',
        ]);

        // Registrar primer punto en Potrero Norte
        PuntoRonda::factory()->create([
            'ronda_id'   => $ronda->id,
            'potrero_id' => $this->potreroNorte->potrero_id,
        ]);

        Sanctum::actingAs($this->operario1, ['*']);

        // Intentar registrar de nuevo Potrero Norte
        $response = $this->postJson("/api/v1/rondas/{$ronda->id}/puntos", [
            'codigo_qr'      => 'POTRERO-NORTE',
            'latitud'        => 10.45900000,
            'longitud'       => -84.12500000,
            'estado_potrero' => 'regular',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', 'Regla de Negocio')
            ->assertJsonPath('mensaje', 'El potrero ya ha sido registrado en esta ronda de inspección.');
    }

    public function test_finalizar_ronda_cambia_estado_a_completada_y_asigna_fecha_fin(): void
    {
        $ronda = Ronda::factory()->create([
            'user_id' => $this->operario1->id,
            'estado'  => 'en_progreso',
        ]);

        Sanctum::actingAs($this->operario1, ['*']);

        $response = $this->patchJson("/api/v1/rondas/{$ronda->id}/finalizar", [
            'observaciones' => 'Ronda finalizada sin novedades mayores.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.estado', 'completada')
            ->assertJsonPath('data.observaciones', 'Ronda finalizada sin novedades mayores.');

        $this->assertNotNull($response->json('data.fecha_fin'));

        $this->assertDatabaseHas('rondas', [
            'id'     => $ronda->id,
            'estado' => 'completada',
        ]);
    }

    public function test_finalizar_ronda_falla_si_ya_estaba_completada(): void
    {
        $ronda = Ronda::factory()->completada()->create([
            'user_id' => $this->operario1->id,
        ]);

        Sanctum::actingAs($this->operario1, ['*']);

        $response = $this->patchJson("/api/v1/rondas/{$ronda->id}/finalizar");

        $response->assertStatus(409)
            ->assertJsonPath('error', 'Regla de Negocio');
    }
}
