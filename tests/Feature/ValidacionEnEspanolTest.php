<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Fase 0: el locale de la API debe ser español.
 *
 * Regresión del defecto por el que la API respondía
 * "The numero arete field must be a string." (APP_LOCALE=en dejaba inerte
 * lang/es/validation.php) y por el que los nombres de campo se mostraban
 * crudos ("El campo numero_arete ...").
 */
class ValidacionEnEspanolTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($this->admin, ['*']);
    }

    public function test_el_locale_de_la_api_es_espanol(): void
    {
        $this->assertSame('es', app()->getLocale());
    }

    public function test_los_nombres_de_atributo_se_muestran_trducidos(): void
    {
        $r = $this->postJson('/api/v1/animales', ['numero_arete' => ['array']]);

        $msg = $r->json('errors.numero_arete.0');

        $this->assertStringContainsString('número de arete', $msg);
        $this->assertStringNotContainsString('numero_arete', $msg);
    }

    #[DataProvider('mensajesDebenEstarEnEspanol')]
    public function test_las_reglas_primitivas_responden_en_espanol(string $url, array $payload, string $campo): void
    {
        $r = $this->postJson($url, $payload);

        $msg = $r->json('errors.'.$campo.'.0');

        $this->assertIsString($msg, "Se esperaba mensaje de validación en: {$campo}");
        $this->assertStringNotContainsStringIgnoringCase('The ', $msg);
        $this->assertStringNotContainsStringIgnoringCase('field must be', $msg);
    }

    public static function mensajesDebenEstarEnEspanol(): array
    {
        return [
            'string' => ['/api/v1/animales', ['numero_arete' => ['a']], 'numero_arete'],
            'integer' => ['/api/v1/animales', ['numero_arete' => 'A', 'raza_id' => 'x', 'potrero_id' => 1, 'sexo' => 'Macho'], 'raza_id'],
            'numeric' => ['/api/v1/potreros', ['nombre' => 'P', 'hectareas_de_extension' => 'x', 'capacidad_maxima' => 1], 'hectareas_de_extension'],
            'in' => ['/api/v1/animales', ['numero_arete' => 'B', 'raza_id' => 1, 'potrero_id' => 1, 'sexo' => 'Alien'], 'sexo'],
            'max' => ['/api/v1/animales', ['numero_arete' => str_repeat('A', 60), 'raza_id' => 1, 'potrero_id' => 1, 'sexo' => 'Macho'], 'numero_arete'],
            'required' => ['/api/v1/animales', [], 'numero_arete'],
        ];
    }

    public function test_los_errores_que_no_son_de_validacion_si_estan_en_espanol(): void
    {
        $this->getJson('/api/v1/animales/999999')
            ->assertStatus(404)
            ->assertJsonPath('error', 'No encontrado')
            ->assertJsonPath('mensaje', 'El animal solicitado no existe.');

        $this->getJson('/api/v1/no-existe')
            ->assertStatus(404)
            ->assertJsonPath('mensaje', 'La ruta solicitada no existe.');
    }

    public function test_el_401_responde_en_espanol_sin_token(): void
    {
        // Se cierra la sesión simulada en setUp() para probar sin credenciales.
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/animales')
            ->assertStatus(401)
            ->assertJsonPath('error', 'No autenticado')
            ->assertJsonPath('mensaje', 'Debe autenticarse para acceder a este recurso.');
    }

    public function test_el_cuerpo_json_invalido_responde_400_en_espanol(): void
    {
        $r = $this->call('POST', '/api/v1/animales', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], '{"numero_arete": "X", "sexo":}');

        $r->assertStatus(400)
            ->assertJsonPath('error', 'Solicitud inválida')
            ->assertJsonPath('mensaje', 'El cuerpo JSON enviado no es válido.');
    }
}
