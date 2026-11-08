<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Potrero;
use App\Models\Raza;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnimalQrTest extends TestCase
{
    use RefreshDatabase;

    private User $operario;
    private Animal $animal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operario = User::factory()->create(['role' => 'operario']);
        $raza = Raza::factory()->create(['nombre' => 'Brahman']);
        $potrero = Potrero::factory()->create([
            'nombre'    => 'Potrero Norte',
            'codigo_qr' => 'POTRERO-NORTE',
        ]);

        $this->animal = Animal::factory()->create([
            'numero_arete' => 'CR-9999-LIB',
            'codigo_qr'    => 'VACA-TEST-001',
            'foto'         => 'https://images.example.com/vaca.jpg',
            'raza_id'      => $raza->raza_id,
            'potrero_id'   => $potrero->potrero_id,
        ]);
    }

    public function test_consultar_animal_por_codigo_qr_retorna_200_y_ficha_completa(): void
    {
        Sanctum::actingAs($this->operario, ['*']);

        $response = $this->getJson('/api/v1/animales/qr/VACA-TEST-001');

        $response->assertStatus(200)
            ->assertJsonPath('data.numero_arete', 'CR-9999-LIB')
            ->assertJsonPath('data.codigo_qr', 'VACA-TEST-001')
            ->assertJsonPath('data.foto', 'https://images.example.com/vaca.jpg')
            ->assertJsonPath('data.raza.nombre', 'Brahman')
            ->assertJsonPath('data.potrero.nombre', 'Potrero Norte')
            ->assertJsonPath('data.potrero.codigo_qr', 'POTRERO-NORTE');
    }

    public function test_consultar_animal_por_codigo_qr_inexistente_retorna_404(): void
    {
        Sanctum::actingAs($this->operario, ['*']);

        $response = $this->getJson('/api/v1/animales/qr/VACA-NO-EXISTE');

        $response->assertStatus(404)
            ->assertJsonPath('error', 'No encontrado');
    }

    public function test_consultar_animal_por_codigo_qr_sin_autenticacion_retorna_401(): void
    {
        $response = $this->getJson('/api/v1/animales/qr/VACA-TEST-001');

        $response->assertStatus(401)
            ->assertJsonPath('error', 'No autenticado');
    }
}

