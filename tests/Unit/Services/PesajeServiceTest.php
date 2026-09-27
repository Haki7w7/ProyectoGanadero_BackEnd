<?php

namespace Tests\Unit\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\User;
use App\Services\PesajeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas unitarias de PesajeService.
 *
 * - Camino feliz: registro de pesaje para un animal existente.
 * - Recurso inexistente: consultar un pesaje que no existe responde 404.
 */
class PesajeServiceTest extends TestCase
{
    use RefreshDatabase;

    private PesajeService $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->servicio = app(PesajeService::class);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    /** Camino feliz: se registra un pesaje ligado al animal existente. */
    public function test_registra_pesaje_para_animal_existente(): void
    {
        $animal = Animal::factory()->create();

        $pesaje = $this->servicio->crearPesaje([
            'id_animal'     => $animal->id_animal,
            'peso_kg'       => 432.75,
            'fecha_pesaje'  => '2026-09-20 08:30:00',
            'observaciones' => 'Pesaje de control mensual.',
        ]);

        $this->assertInstanceOf(Pesaje::class, $pesaje);
        $this->assertSame($animal->id_animal, (int) $pesaje->id_animal);
        $this->assertSame(432.75, $pesaje->peso_kg);
        $this->assertSame(1, $animal->pesajes()->count());
    }

    /** Un pesaje inexistente se reporta como 404 (y no como 409). */
    public function test_pesaje_inexistente_lanza_excepcion_404(): void
    {
        try {
            $this->servicio->obtenerPorId(999999);
            $this->fail('Se esperaba ReglaNegocioException por pesaje inexistente.');
        } catch (ReglaNegocioException $e) {
            $this->assertSame('El pesaje solicitado no existe.', $e->getMessage());
            $this->assertSame(ReglaNegocioException::HTTP_NOT_FOUND, $e->estadoHttp());
        }
    }
}
