<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Pruebas del asistente con IA usando respuestas simuladas de Gemini (no consumen la API real). */
class AsistenteCotizacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config([
            'services.gemini.key' => 'clave-de-prueba',
            'services.gemini.model' => 'modelo-principal',
            'services.gemini.fallback_model' => 'modelo-respaldo',
        ]);
        Http::preventStrayRequests();
    }

    private static function llamada(string $nombre, array $args): array
    {
        return ['candidates' => [['content' => ['role' => 'model', 'parts' => [['functionCall' => ['name' => $nombre, 'args' => $args]]]]]]];
    }

    private static function texto(string $texto): array
    {
        return ['candidates' => [['content' => ['role' => 'model', 'parts' => [['text' => $texto]]]]]];
    }

    public function test_el_asistente_busca_y_agrega_productos_a_la_cotizacion(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
            ->push(self::llamada('buscar_productos', ['consulta' => 'varilla 1/2']))
            ->push(self::llamada('agregar_producto', ['sku' => 'FT-ST-4410', 'cantidad' => 500]))
            ->push(self::texto('Agregué 500 varillas de 1/2". Total actualizado.')),
        ]);

        $user = User::where('email', 'cliente@ferrotek.test')->first();
        $res = $this->actingAs($user)->postJson('/cotizador/asistente', ['mensaje' => 'Necesito 500 varillas de 1/2']);

        $res->assertOk()
            ->assertJsonPath('mensaje', 'Agregué 500 varillas de 1/2". Total actualizado.')
            ->assertJsonPath('cotizacion.partidas.0.sku', 'FT-ST-4410')
            ->assertJsonPath('cotizacion.partidas.0.cantidad', 500)
            ->assertJsonPath('cotizacion.partidas.0.descuento_volumen_pct', 8.5);

        // 500 x 115.85 = 57,925.00 − 8.5% (4,923.63) = 53,001.37 + IVA 12% (6,360.16) = 59,361.53
        $this->assertEquals(59361.53, $res->json('cotizacion.total'));
        $this->assertSame(['buscar_productos', 'agregar_producto'], array_column($res->json('acciones'), 'herramienta'));

        // Se devolvió a Gemini el resultado real de la búsqueda
        Http::assertSent(function ($request) {
            $ultimo = collect($request['contents'])->last();

            return str_contains($request->url(), 'modelo-principal')
                && ($ultimo['parts'][0]['functionResponse']['name'] ?? null) === 'buscar_productos'
                && $ultimo['parts'][0]['functionResponse']['response']['resultado']['productos'][0]['sku'] === 'FT-ST-4410';
        });

        // El chat queda guardado en sesión y se muestra al volver al cotizador
        $this->get('/cotizador')->assertOk()->assertSee('500 varillas de 1', false);
    }

    public function test_no_agrega_sku_inventado_ni_mas_que_el_stock(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
            ->push(self::llamada('agregar_producto', ['sku' => 'NO-EXISTE', 'cantidad' => 1]))
            ->push(self::llamada('agregar_producto', ['sku' => 'FT-PW-7019', 'cantidad' => 999])) // stock 58
            ->push(self::texto('Listo.')),
        ]);

        $res = $this->actingAs(User::where('email', 'cliente@ferrotek.test')->first())
            ->postJson('/cotizador/asistente', ['mensaje' => 'taladros']);

        $res->assertOk();
        $this->assertCount(1, $res->json('cotizacion.partidas'));
        $this->assertSame(58, $res->json('cotizacion.partidas.0.cantidad'));
    }

    public function test_si_el_modelo_principal_esta_saturado_usa_el_de_respaldo(): void
    {
        Http::fake([
            '*/modelo-principal:generateContent' => Http::response(['error' => ['code' => 503, 'message' => 'high demand']], 503),
            '*/modelo-respaldo:generateContent' => Http::response(self::texto('Hola, ¿qué necesitas?')),
        ]);

        $this->actingAs(User::where('email', 'cliente@ferrotek.test')->first())
            ->postJson('/cotizador/asistente', ['mensaje' => 'hola'])
            ->assertOk()
            ->assertJsonPath('mensaje', 'Hola, ¿qué necesitas?');
    }

    public function test_cuota_diaria_agotada_devuelve_mensaje_claro(): void
    {
        Http::fake(['*' => Http::response(['error' => ['code' => 429, 'message' => 'Quota exceeded GenerateRequestsPerDayPerProjectPerModel-FreeTier']], 429)]);

        $this->actingAs(User::where('email', 'cliente@ferrotek.test')->first())
            ->postJson('/cotizador/asistente', ['mensaje' => 'hola'])
            ->assertStatus(429)
            ->assertJsonPath('error', 'Se agotó la cuota gratuita diaria de Gemini. Intenta mañana o cambia GEMINI_MODEL en .env.');
    }

    public function test_sin_api_key_el_chat_no_aparece(): void
    {
        config(['services.gemini.key' => null]);

        $this->actingAs(User::where('email', 'cliente@ferrotek.test')->first())
            ->get('/cotizador')->assertOk()->assertDontSee('FerroTek AI Assistant');
    }

    public function test_visitante_no_puede_usar_el_asistente(): void
    {
        $this->postJson('/cotizador/asistente', ['mensaje' => 'hola'])->assertUnauthorized();
    }
}
