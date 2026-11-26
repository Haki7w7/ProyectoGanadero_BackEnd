<?php

namespace Tests\Unit\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Animal;
use App\Models\Tratamiento;
use App\Models\TratamientoAnimal;
use App\Models\User;
use App\Services\TratamientoAnimalService;
use App\Services\TratamientoService;
use Illuminate\Auth\Access\Response as RespuestaGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Tests\TestCase;

class TratamientoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_aplicacion_de_tratamiento_valida(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'veterinario']));

        $animal = Animal::factory()->create();
        $tratamiento = Tratamiento::create([
            'nombre'      => 'Vacuna Contra Aftosa',
            'descripcion' => 'Vacunación semestral obligatoria',
            'tipo'        => 'Vacuna',
        ]);

        $aplicacion = app(TratamientoAnimalService::class)->crearTratamientoAnimal([
            'id_animal'        => $animal->id_animal,
            'tratamiento_id'   => $tratamiento->tratamiento_id,
            'fecha_aplicacion' => '2026-09-25 10:00:00',
            'dosis_ml'         => 5.5,
            'observaciones'    => 'Aplicación de rutina.',
        ]);

        $this->assertInstanceOf(TratamientoAnimal::class, $aplicacion);
        $this->assertSame(5.5, $aplicacion->dosis_ml);
        $this->assertDatabaseHas('tratamiento_animal', [
            'id_animal'      => $animal->id_animal,
            'tratamiento_id' => $tratamiento->tratamiento_id,
        ]);
    }

    public function test_rn06_no_elimina_tratamiento_ya_aplicado(): void
    {
        Gate::shouldReceive('authorize')->andReturn(RespuestaGate::allow());

        $tratamiento = Mockery::mock(Tratamiento::class);
        $tratamiento->shouldReceive('aplicaciones->exists')->once()->andReturn(true);
        $tratamiento->shouldNotReceive('delete');

        $servicio = Mockery::mock(TratamientoService::class)->makePartial();
        $servicio->shouldReceive('obtenerPorId')->with(4)->once()->andReturn($tratamiento);

        try {
            $servicio->eliminarTratamiento(4);
            $this->fail('Se esperaba ReglaNegocioException (RN-06).');
        } catch (ReglaNegocioException $e) {
            $this->assertSame('No se puede eliminar el tratamiento porque tiene aplicaciones registradas.', $e->getMessage());
            $this->assertSame(ReglaNegocioException::HTTP_CONFLICT, $e->estadoHttp());
        }
    }
}
