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

class AnimalServiceTest extends TestCase
{
    use RefreshDatabase;

    private AnimalService $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->servicio = app(AnimalService::class);

        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

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

    public function test_crea_animal_cuando_el_potrero_tiene_cupo(): void
    {
        $potrero = Potrero::factory()->create(['capacidad_maxima' => 5]);

        $animal = $this->servicio->crearAnimal($this->datosAnimal($potrero, 'ARETE-OK-001'));

        $this->assertInstanceOf(Animal::class, $animal);
        $this->assertSame($potrero->potrero_id, (int) $animal->potrero_id);
        $this->assertDatabaseHas('animales', ['numero_arete' => 'ARETE-OK-001']);
    }

    public function test_rn03_rechaza_animal_en_potrero_con_capacidad_llena(): void
    {
        $potrero = Potrero::factory()->create(['capacidad_maxima' => 2]);
        Animal::factory()->count(2)->create([
            'potrero_id' => $potrero->potrero_id,
            'estado'     => 'Activo',
        ]);

        try {
            $this->servicio->crearAnimal($this->datosAnimal($potrero, 'ARETE-LLENO'));
            $this->fail('Se esperaba ReglaNegocioException por potrero lleno.');
        } catch (ReglaNegocioException $e) {
            $this->assertSame('El potrero seleccionado ya alcanzó su capacidad máxima.', $e->getMessage());
            $this->assertSame(ReglaNegocioException::HTTP_CONFLICT, $e->estadoHttp());
        }

        $this->assertDatabaseMissing('animales', ['numero_arete' => 'ARETE-LLENO']);
    }

    public function test_caso_limite_potrero_admite_el_animal_n_y_rechaza_el_n_mas_1(): void
    {
        $capacidad = 3;
        $potrero = Potrero::factory()->create(['capacidad_maxima' => $capacidad]);
        Animal::factory()->count($capacidad - 1)->create([
            'potrero_id' => $potrero->potrero_id,
            'estado'     => 'Activo',
        ]);

        $this->servicio->crearAnimal($this->datosAnimal($potrero, 'ARETE-N'));
        $this->assertSame($capacidad, $potrero->animales()->count());

        $this->expectException(ReglaNegocioException::class);
        $this->expectExceptionMessage('El potrero seleccionado ya alcanzó su capacidad máxima.');

        $this->servicio->crearAnimal($this->datosAnimal($potrero, 'ARETE-N-MAS-1'));
    }

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
