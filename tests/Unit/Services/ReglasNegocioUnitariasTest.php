<?php

namespace Tests\Unit\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Animal;
use App\Models\Categoria;
use App\Models\Raza;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Services\AnimalService;
use App\Services\CategoriaService;
use App\Services\RazaService;
use App\Services\UnidadMedidaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response as RespuestaGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Tests\TestCase;

/**
 * Reglas de negocio de catálogos (RN-04, RN-05, RN-07) con dobles de prueba
 * y demostración de la Capa 2 de autorización (Gate dentro del servicio).
 */
class ReglasNegocioUnitariasTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifica que el servicio lance la ReglaNegocioException esperada
     * (409 Conflict) con el mensaje indicado.
     */
    private function assertReglaNegocio(callable $accion, string $mensaje): void
    {
        try {
            $accion();
            $this->fail("Se esperaba ReglaNegocioException: {$mensaje}");
        } catch (ReglaNegocioException $e) {
            $this->assertSame($mensaje, $e->getMessage());
            $this->assertSame(ReglaNegocioException::HTTP_CONFLICT, $e->estadoHttp());
        }
    }

    /** RN-04: no se elimina una categoría con insumos asociados. */
    public function test_rn04_no_elimina_categoria_con_insumos(): void
    {
        Gate::shouldReceive('authorize')->andReturn(RespuestaGate::allow());

        $categoria = Mockery::mock(Categoria::class);
        $categoria->shouldReceive('insumos->exists')->once()->andReturn(true);
        $categoria->shouldNotReceive('delete');

        $servicio = Mockery::mock(CategoriaService::class)->makePartial();
        $servicio->shouldReceive('obtenerPorId')->with(1)->once()->andReturn($categoria);

        $this->assertReglaNegocio(
            fn () => $servicio->eliminarCategoria(1),
            'No se puede eliminar la categoría porque tiene insumos asociados.'
        );
    }

    /** RN-05: no se elimina una raza asignada a animales. */
    public function test_rn05_no_elimina_raza_con_animales(): void
    {
        Gate::shouldReceive('authorize')->andReturn(RespuestaGate::allow());

        $raza = Mockery::mock(Raza::class);
        $raza->shouldReceive('animales->exists')->once()->andReturn(true);
        $raza->shouldNotReceive('delete');

        $servicio = Mockery::mock(RazaService::class)->makePartial();
        $servicio->shouldReceive('obtenerPorId')->with(2)->once()->andReturn($raza);

        $this->assertReglaNegocio(
            fn () => $servicio->eliminarRaza(2),
            'No se puede eliminar la raza porque tiene animales asociados.'
        );
    }

    /** RN-07: no se elimina una unidad de medida en uso por insumos. */
    public function test_rn07_no_elimina_unidad_de_medida_en_uso(): void
    {
        Gate::shouldReceive('authorize')->andReturn(RespuestaGate::allow());

        $unidad = Mockery::mock(UnidadMedida::class);
        $unidad->shouldReceive('insumos->exists')->once()->andReturn(true);
        $unidad->shouldNotReceive('delete');

        $servicio = Mockery::mock(UnidadMedidaService::class)->makePartial();
        $servicio->shouldReceive('obtenerPorId')->with(5)->once()->andReturn($unidad);

        $this->assertReglaNegocio(
            fn () => $servicio->eliminarUnidadMedida(5),
            'No se puede eliminar la unidad de medida porque tiene insumos asociados.'
        );
    }

    /**
     * Capa 2 (demostración): un operario llama DIRECTAMENTE al servicio, sin
     * pasar por la ruta HTTP ni el controlador, y el servicio lo rechaza.
     *
     * Requiere que AnimalService::eliminarAnimal() invoque
     * Gate::authorize('delete', $animal) y que AnimalPolicy::delete() solo
     * permita al admin (tareas de la Persona 2).
     */
    public function test_capa2_servicio_rechaza_eliminacion_de_operario_sin_pasar_por_http(): void
    {
        $operario = User::factory()->create(['role' => 'operario']);
        $animal = Animal::factory()->create();

        $this->actingAs($operario);

        try {
            app(AnimalService::class)->eliminarAnimal($animal->id_animal);
            $this->fail('El servicio debió lanzar AuthorizationException para un operario.');
        } catch (AuthorizationException $e) {
            // Esperado: la Capa 2 bloqueó la operación.
        }

        // La base de datos no se modificó.
        $this->assertDatabaseHas('animales', ['id_animal' => $animal->id_animal]);
    }

    /**
     * Capa 2 con doble de prueba: el Gate (mock) debe ser consultado con la
     * habilidad 'delete' y el animal concreto; si deniega, el servicio propaga
     * la AuthorizationException y no elimina nada.
     */
    public function test_capa2_servicio_consulta_el_gate_con_la_habilidad_delete(): void
    {
        $animal = Animal::factory()->create();

        Gate::shouldReceive('authorize')
            ->once()
            ->with('delete', Mockery::on(fn ($modelo) => $modelo instanceof Animal
                && (int) $modelo->id_animal === (int) $animal->id_animal))
            ->andThrow(new AuthorizationException('No tiene permisos para realizar esta acción.'));

        $this->expectException(AuthorizationException::class);

        app(AnimalService::class)->eliminarAnimal($animal->id_animal);
    }
}
