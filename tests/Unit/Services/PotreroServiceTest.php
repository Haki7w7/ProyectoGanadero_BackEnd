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

class PotreroServiceTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_caso_limite_paginacion_extrema_se_acota_entre_1_y_100(): void
    {
        Potrero::factory()->count(3)->create();

        $potreros = app(PotreroService::class);
        $animales = app(AnimalService::class);

        $this->assertSame(1, $potreros->listarPotreros(['per_page' => -5])->perPage());
        $this->assertSame(1, $animales->listarAnimales(['per_page' => -5])->perPage());

        $this->assertSame(100, $potreros->listarPotreros(['per_page' => 500])->perPage());
        $this->assertSame(100, $animales->listarAnimales(['per_page' => 500])->perPage());
    }
}
