<?php

namespace Tests\Unit;

use App\Exceptions\ReglaNegocioException;
use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\Potrero;
use App\Models\Raza;
use App\Services\AnimalService;
use App\Services\PotreroService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReglasNegocioTest extends TestCase
{
    use RefreshDatabase;

    private AnimalService $animalService;
    private PotreroService $potreroService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(\App\Models\User::factory()->create(['role' => 'admin']));
        $this->animalService = app(AnimalService::class);
        $this->potreroService = app(PotreroService::class);
    }

    public function test_regla_1_no_permite_eliminar_animal_con_historial_de_pesajes(): void
    {
        $animal = Animal::factory()->create();
        Pesaje::factory()->create(['id_animal' => $animal->id_animal]);

        $this->expectException(ReglaNegocioException::class);
        $this->expectExceptionMessage('No se puede eliminar el animal porque tiene pesajes registrados en su historial.');

        $this->animalService->eliminarAnimal($animal->id_animal);

        $this->assertDatabaseHas('animales', ['id_animal' => $animal->id_animal]);
    }

    public function test_regla_1_permite_eliminar_animal_sin_pesajes(): void
    {
        $animal = Animal::factory()->create();

        $resultado = $this->animalService->eliminarAnimal($animal->id_animal);

        $this->assertTrue($resultado);
        $this->assertDatabaseMissing('animales', ['id_animal' => $animal->id_animal]);
    }

    public function test_regla_2_no_permite_eliminar_potrero_con_animales_asignados(): void
    {
        $potrero = Potrero::factory()->create();
        Animal::factory()->create(['potrero_id' => $potrero->potrero_id]);

        $this->expectException(ReglaNegocioException::class);
        $this->expectExceptionMessage('No se puede eliminar el potrero porque tiene animales asignados actualmente.');

        $this->potreroService->eliminarPotrero($potrero->potrero_id);

        $this->assertDatabaseHas('potreros', ['potrero_id' => $potrero->potrero_id]);
    }

    public function test_regla_2_permite_eliminar_potrero_sin_animales(): void
    {
        $potrero = Potrero::factory()->create();

        $resultado = $this->potreroService->eliminarPotrero($potrero->potrero_id);

        $this->assertTrue($resultado);
        $this->assertDatabaseMissing('potreros', ['potrero_id' => $potrero->potrero_id]);
    }

    public function test_rollback_transaccional_revierte_animal_si_pesaje_falla(): void
    {
        $raza = Raza::factory()->create();
        $potrero = Potrero::factory()->create();

        $datosAnimal = [
            'numero_arete'     => 'ROLLBACK-999',
            'raza_id'          => $raza->raza_id,
            'potrero_id'       => $potrero->potrero_id,
            'sexo'             => 'Macho',
            'fecha_nacimiento' => '2023-01-01',
            'estado'           => 'Activo',
        ];

        $datosPesajeErroneos = [
            'peso_kg'      => 'PESO_INVALIDO_TEXTO',
            'fecha_pesaje' => null,
        ];

        try {
            $this->animalService->registrarAnimalConPesaje($datosAnimal, $datosPesajeErroneos);
            $this->fail('La transacción debió fallar.');
        } catch (\Throwable $e) {
        }

        $this->assertDatabaseMissing('animales', [
            'numero_arete' => 'ROLLBACK-999',
        ]);
    }
}