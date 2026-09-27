<?php

namespace Tests\Unit\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Potrero;
use App\Services\AnimalService;
use App\Services\PotreroService;
use Illuminate\Auth\Access\Response as RespuestaGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Tests\TestCase;

/**
 * Pruebas unitarias de PotreroService.
 *
 * - RN-02: no se elimina un potrero con animales asignados (con dobles de prueba).
 * - Caso límite 2: paginación extrema (per_page=-5 -> 1, per_page=500 -> 100).
 */
class PotreroServiceTest extends TestCase
{
    use RefreshDatabase;

    /** RN-02 con dobles: el potrero (mock) tiene animales, delete() nunca se llama. */
    public function test_rn02_no_elimina_potrero_con_animales_asignados(): void
    {
        Gate::shouldReceive('authorize')->andReturn(RespuestaGate::allow());

        $potrero = Mockery::mock(Potrero::class);
        $potrero->shouldReceive('animales->exists')->once()->andReturn(true);
        $potrero->shouldNotReceive('delete');

        $servicio = Mockery::mock(PotreroService::class)->makePartial();
        $servicio->shouldReceive('obtenerPorId')->with(3)->once()->andReturn($potrero);

        try {
            $servicio->eliminarPotrero(3);
            $this->fail('Se esperaba ReglaNegocioException (RN-02).');
        } catch (ReglaNegocioException $e) {
            $this->assertSame('No se puede eliminar el potrero porque tiene animales asignados actualmente.', $e->getMessage());
            $this->assertSame(ReglaNegocioException::HTTP_CONFLICT, $e->estadoHttp());
        }
    }

    /**
     * Caso límite 2: valores extremos de per_page se acotan al rango [1, 100]
     * en lugar de provocar un error 500.
     */
    public function test_caso_limite_paginacion_extrema_se_acota_entre_1_y_100(): void
    {
        Potrero::factory()->count(3)->create();

        $potreros = app(PotreroService::class);
        $animales = app(AnimalService::class);

        // per_page negativo -> 1 (subsanación Lab 4/5 a cargo de la Persona 3).
        $this->assertSame(1, $potreros->listarPotreros(['per_page' => -5])->perPage());
        $this->assertSame(1, $animales->listarAnimales(['per_page' => -5])->perPage());

        // per_page excesivo -> 100.
        $this->assertSame(100, $potreros->listarPotreros(['per_page' => 500])->perPage());
        $this->assertSame(100, $animales->listarAnimales(['per_page' => 500])->perPage());
    }
}
