<?php

namespace Tests\Unit\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Animal;
use App\Models\Potrero;
use App\Models\Raza;
use App\Models\User;
use App\Services\AnimalService;
use Illuminate\Auth\Access\Response as RespuestaGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Tests\TestCase;

/**
 * Pruebas unitarias de AnimalService.
 *
 * - Camino feliz: creación de animal con cupo en el potrero.
 * - RN-01: no se elimina un animal con historial de pesajes (con dobles de prueba).
 * - RN-03: no se asigna un animal a un potrero lleno.
 * - Caso límite 1: el potrero admite el animal N y rechaza el N+1.
 */
class AnimalServiceTest extends TestCase
{
    use RefreshDatabase;

    private AnimalService $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->servicio = app(AnimalService::class);

        // Admin autenticado: si la Capa 2 (Gate dentro del servicio) está activa,
        // las operaciones del camino feliz siguen permitidas.
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    /** Datos válidos de un animal para el potrero indicado. */
    private function datosAnimal(Potrero $potrero, string $arete): array
    {
        return [
            'numero_arete'     => $arete,
            'raza_id'          => Raza::factory()->create()->raza_id,
            'potrero_id'       => $potrero->potrero_id,
            'sexo'             => 'Hembra',
            'fecha_nacimiento' => '2024-01-15',
            'estado'           => 'Activo',
        ];
    }

    /** Camino feliz: el potrero tiene cupo y el animal se crea. */
    public function test_crea_animal_cuando_el_potrero_tiene_cupo(): void
    {
        $potrero = Potrero::factory()->create(['capacidad_maxima' => 5]);

        $animal = $this->servicio->crearAnimal($this->datosAnimal($potrero, 'ARETE-OK-001'));

        $this->assertInstanceOf(Animal::class, $animal);
        $this->assertSame($potrero->potrero_id, (int) $animal->potrero_id);
        $this->assertDatabaseHas('animales', ['numero_arete' => 'ARETE-OK-001']);
    }

    /** RN-03: potrero con capacidad llena rechaza un animal nuevo. */
    public function test_rn03_rechaza_animal_en_potrero_con_capacidad_llena(): void
    {
        $potrero = Potrero::factory()->create(['capacidad_maxima' => 2]);
        Animal::factory()->count(2)->create(['potrero_id' => $potrero->potrero_id]);

        try {
            $this->servicio->crearAnimal($this->datosAnimal($potrero, 'ARETE-LLENO'));
            $this->fail('Se esperaba ReglaNegocioException por potrero lleno.');
        } catch (ReglaNegocioException $e) {
            $this->assertSame('El potrero seleccionado ya alcanzó su capacidad máxima.', $e->getMessage());
            $this->assertSame(ReglaNegocioException::HTTP_CONFLICT, $e->estadoHttp());
        }

        $this->assertDatabaseMissing('animales', ['numero_arete' => 'ARETE-LLENO']);
    }

    /** Caso límite 1: capacidad exacta. Se admite el animal N y se rechaza el N+1. */
    public function test_caso_limite_potrero_admite_el_animal_n_y_rechaza_el_n_mas_1(): void
    {
        $capacidad = 3;
        $potrero = Potrero::factory()->create(['capacidad_maxima' => $capacidad]);
        Animal::factory()->count($capacidad - 1)->create(['potrero_id' => $potrero->potrero_id]);

        // Animal N (ocupa el último cupo): se admite.
        $this->servicio->crearAnimal($this->datosAnimal($potrero, 'ARETE-N'));
        $this->assertSame($capacidad, $potrero->animales()->count());

        // Animal N+1: se rechaza.
        $this->expectException(ReglaNegocioException::class);
        $this->expectExceptionMessage('El potrero seleccionado ya alcanzó su capacidad máxima.');

        $this->servicio->crearAnimal($this->datosAnimal($potrero, 'ARETE-N-MAS-1'));
    }

    /**
     * RN-01 con dobles de prueba: el animal (mock) tiene pesajes, por lo que el
     * servicio lanza la excepción y NUNCA llama a delete().
     */
    public function test_rn01_no_elimina_animal_con_historial_de_pesajes(): void
    {
        Gate::shouldReceive('authorize')->andReturn(RespuestaGate::allow());

        $animal = Mockery::mock(Animal::class);
        $animal->shouldReceive('pesajes->exists')->once()->andReturn(true);
        $animal->shouldNotReceive('delete');

        $servicio = Mockery::mock(AnimalService::class)->makePartial();
        $servicio->shouldReceive('obtenerPorId')->with(10)->once()->andReturn($animal);

        $this->expectException(ReglaNegocioException::class);
        $this->expectExceptionMessage('No se puede eliminar el animal porque tiene pesajes registrados en su historial.');

        $servicio->eliminarAnimal(10);
    }

    /** RN-01 (contraparte) con dobles: sin pesajes, delete() se invoca exactamente una vez. */
    public function test_rn01_elimina_animal_sin_pesajes(): void
    {
        Gate::shouldReceive('authorize')->andReturn(RespuestaGate::allow());

        $animal = Mockery::mock(Animal::class);
        $animal->shouldReceive('pesajes->exists')->once()->andReturn(false);
        $animal->shouldReceive('delete')->once()->andReturn(true);

        $servicio = Mockery::mock(AnimalService::class)->makePartial();
        $servicio->shouldReceive('obtenerPorId')->with(11)->once()->andReturn($animal);

        $this->assertTrue($servicio->eliminarAnimal(11));
    }
}
