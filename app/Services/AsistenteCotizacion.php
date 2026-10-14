<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Quote;
use Illuminate\Support\Facades\Http;

/**
 * Asistente de cotización con Google Gemini y Function Calling.
 *
 * El cliente escribe en lenguaje natural ("500 varillas de 1/2 y 30 cajas de E6011") y
 * Gemini usa herramientas que buscan en el catálogo real (MySQL) y modifican la cotización
 * en borrador mediante CotizacionService. Los precios y totales siempre los calcula el
 * servidor, nunca el modelo.
 */
class AsistenteCotizacion
{
    private const MAX_VUELTAS = 6;
    private const REINTENTOS = 2;
    private const MAX_HISTORIAL = 40;

    /** Palabras que usan los clientes y cómo se llaman los productos en el catálogo. */
    private const SINONIMOS = [
        'varilla' => ['corrugado'], 'varillas' => ['corrugado'], 'hierro' => ['acero'], 'fierro' => ['acero'],
        'electrodos' => ['electrodo'], 'discos' => ['disco'], 'tornillos' => ['tornillo'], 'tubos' => ['tubo'],
        'cascos' => ['casco'], 'guantes' => ['guante'], 'lentes' => ['lente'], 'cable' => ['thhn'],
        'flipon' => ['breaker'], 'flipones' => ['breaker'], 'pulidora' => ['amoladora'], 'esmeril' => ['amoladora'],
        'lamina' => ['lámina'], 'laminas' => ['lámina'], 'seguridad' => ['casco', 'guante', 'lente', 'arnés'],
    ];

    public function __construct(private CotizacionService $cotizador) {}

    public function configurado(): bool
    {
        return filled(config('services.gemini.key'));
    }

    /**
     * Procesa un mensaje del cliente sobre su cotización.
     *
     * @param  array  $historial  Contenidos previos de la conversación (formato de la API de Gemini).
     * @return array{mensaje: string, historial: array, acciones: array, modelo: string}
     */
    public function responder(Quote $quote, string $mensaje, array $historial = []): array
    {
        if (! $this->configurado()) {
            throw new AsistenteException('El asistente con IA no está configurado (falta GEMINI_API_KEY en .env).', 500);
        }

        $contents = [...$historial, ['role' => 'user', 'parts' => [['text' => $mensaje]]]];
        $acciones = [];
        $modelo = config('services.gemini.model');
        $texto = null;

        for ($vuelta = 0; $vuelta < self::MAX_VUELTAS; $vuelta++) {
            $payload = [
                'systemInstruction' => ['parts' => [['text' => $this->instrucciones($quote)]]],
                'contents' => $contents,
                'tools' => [['functionDeclarations' => $this->declaraciones()]],
            ];

            try {
                $respuesta = $this->generar($modelo, $payload);
            } catch (AsistenteException $e) {
                // Si el modelo principal está saturado o sin cuota desde el inicio, se usa el de respaldo
                // para toda la conversación (no se mezclan modelos a mitad del ciclo).
                $respaldo = config('services.gemini.fallback_model');
                if ($vuelta > 0 || ! $respaldo || ! in_array($e->getCode(), [429, 503], true)) {
                    throw $e;
                }
                $modelo = $respaldo;
                $respuesta = $this->generar($modelo, $payload);
            }

            $contenido = $respuesta['candidates'][0]['content'] ?? null;
            if (! $contenido || empty($contenido['parts'])) {
                throw new AsistenteException('Gemini no devolvió una respuesta. Intenta reformular tu mensaje.', 502);
            }

            $llamadas = array_values(array_filter(array_column($contenido['parts'], 'functionCall')));

            // Se reenvía el contenido del modelo tal cual (conserva las "thought signatures").
            $contents[] = $this->normalizarContenido($contenido);

            if (empty($llamadas)) {
                $texto = trim(collect($contenido['parts'])->pluck('text')->filter()->implode("\n"));
                break;
            }

            $partes = [];
            foreach ($llamadas as $llamada) {
                $args = $llamada['args'] ?? [];
                $resultado = $this->ejecutar($quote, $llamada['name'], $args);
                $acciones[] = ['herramienta' => $llamada['name'], 'args' => $args];
                $partes[] = ['functionResponse' => array_filter([
                    'id' => $llamada['id'] ?? null,
                    'name' => $llamada['name'],
                    'response' => ['resultado' => $resultado],
                ], fn ($v) => $v !== null)];
            }
            $contents[] = ['role' => 'user', 'parts' => $partes];
        }

        $texto = $texto ?: 'Listo, revisa el resumen de tu cotización a la derecha.';

        return [
            'mensaje' => $texto,
            'historial' => array_slice($contents, -self::MAX_HISTORIAL),
            'acciones' => $acciones,
            'modelo' => $modelo,
        ];
    }

    /** Declaraciones de las herramientas (Function Calling). */
    public function declaraciones(): array
    {
        return [
            [
                'name' => 'buscar_productos',
                'description' => 'Busca productos activos del catálogo de FerroTek por nombre, SKU, norma o categoría. '
                    .'Devuelve SKU, nombre, norma, precio unitario en quetzales (sin IVA), unidad y stock.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'consulta' => ['type' => 'STRING', 'description' => 'Palabras clave, ej. "varilla 1/2", "E6011", "disco corte".'],
                    ],
                    'required' => ['consulta'],
                ],
            ],
            [
                'name' => 'agregar_producto',
                'description' => 'Agrega un producto a la cotización del cliente (o suma a la cantidad si ya está).',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'sku' => ['type' => 'STRING', 'description' => 'SKU exacto obtenido de buscar_productos, ej. "FT-ST-4410".'],
                        'cantidad' => ['type' => 'INTEGER', 'description' => 'Cantidad a agregar (mínimo 1).'],
                    ],
                    'required' => ['sku', 'cantidad'],
                ],
            ],
            [
                'name' => 'cambiar_cantidad',
                'description' => 'Fija la cantidad total de un producto que ya está en la cotización. Con cantidad 0 lo quita.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'sku' => ['type' => 'STRING'],
                        'cantidad' => ['type' => 'INTEGER', 'description' => 'Nueva cantidad total (0 para quitar).'],
                    ],
                    'required' => ['sku', 'cantidad'],
                ],
            ],
            [
                'name' => 'ver_cotizacion',
                'description' => 'Devuelve las partidas y totales actuales de la cotización (subtotal, descuento por volumen, flete, IVA y total).',
                'parameters' => ['type' => 'OBJECT', 'properties' => new \stdClass],
            ],
        ];
    }

    /** Ejecuta la herramienta pedida por el modelo y devuelve un resultado serializable. */
    public function ejecutar(Quote $quote, string $nombre, array $args): array
    {
        switch ($nombre) {
            case 'buscar_productos':
                $productos = $this->buscar((string) ($args['consulta'] ?? ''));

                return [
                    'cantidad' => $productos->count(),
                    'productos' => $productos->map(fn (Product $p) => array_filter([
                        'sku' => $p->sku,
                        'nombre' => $p->name,
                        'norma' => $p->standard,
                        'precio_unitario' => (float) $p->price,
                        'unidad' => $p->unit,
                        'stock' => $p->stock,
                        'oferta_pct' => $p->activeOffer ? (float) $p->activeOffer->discount_rate : null,
                        'precio_oferta_carrito' => $p->activeOffer ? $p->final_price : null,
                        'oferta_hasta' => $p->activeOffer?->ends_at->format('d/m/Y'),
                    ], fn ($v) => $v !== null))->values()->all(),
                ];

            case 'agregar_producto':
                $producto = Product::active()->where('sku', $args['sku'] ?? '')->first();
                if (! $producto) {
                    return ['error' => "No existe un producto activo con SKU \"".($args['sku'] ?? '')."\". Usa buscar_productos."];
                }
                $cantidad = max(1, (int) ($args['cantidad'] ?? 1));
                $enCotizacion = (int) $quote->items()->where('product_id', $producto->id)->value('quantity');
                if ($producto->stock <= 0) {
                    return ['error' => "{$producto->name} está agotado."];
                }
                $aviso = null;
                if ($enCotizacion + $cantidad > $producto->stock) {
                    $cantidad = max(0, $producto->stock - $enCotizacion);
                    $aviso = "Solo hay {$producto->stock} en stock; se ajustó la cantidad.";
                    if ($cantidad === 0) {
                        return ['error' => "Ya tienes todo el stock disponible ({$producto->stock}) de {$producto->name} en la cotización."];
                    }
                }
                $this->cotizador->agregarProducto($quote, $producto, $cantidad);

                return array_filter(['ok' => true, 'agregado' => "{$cantidad}x {$producto->name}", 'aviso' => $aviso,
                    'cotizacion' => $this->cotizador->resumen($quote->fresh())]);

            case 'cambiar_cantidad':
                $item = $quote->items()->whereHas('product', fn ($q) => $q->where('sku', $args['sku'] ?? ''))->with('product')->first();
                if (! $item) {
                    return ['error' => 'Ese producto no está en la cotización.'];
                }
                $cantidad = max(0, (int) ($args['cantidad'] ?? 0));
                $aviso = null;
                if ($cantidad > $item->product->stock) {
                    $cantidad = $item->product->stock;
                    $aviso = "Solo hay {$item->product->stock} en stock; se ajustó la cantidad.";
                }
                $this->cotizador->cambiarCantidad($quote, $item->id, $cantidad);

                return array_filter(['ok' => true, 'aviso' => $aviso, 'cotizacion' => $this->cotizador->resumen($quote->fresh())]);

            case 'ver_cotizacion':
                return $this->cotizador->resumen($quote->fresh());

            default:
                return ['error' => "Herramienta desconocida: {$nombre}"];
        }
    }

    /** Búsqueda tolerante: cada palabra debe aparecer en nombre, SKU, norma o categoría. */
    private function buscar(string $consulta)
    {
        $palabras = collect(preg_split('/\s+/', trim($consulta)))->filter(fn ($p) => mb_strlen($p) >= 2)->take(5);

        $query = Product::active()->with('category', 'activeOffer');
        foreach ($palabras as $palabra) {
            $terminos = [$palabra, ...(self::SINONIMOS[mb_strtolower($palabra)] ?? [])];
            $query->where(function ($w) use ($terminos) {
                foreach ($terminos as $t) {
                    $w->orWhere('name', 'like', "%{$t}%")
                        ->orWhere('sku', 'like', "%{$t}%")
                        ->orWhere('standard', 'like', "%{$t}%")
                        ->orWhere('subtitle', 'like', "%{$t}%")
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$t}%"));
                }
            });
        }

        $resultados = $query->orderByDesc('stock')->take(8)->get();

        // Si con todas las palabras no hay resultados, se intenta con cualquiera de ellas.
        if ($resultados->isEmpty() && $palabras->count() > 1) {
            $resultados = Product::active()->with('activeOffer')->where(function ($w) use ($palabras) {
                foreach ($palabras as $palabra) {
                    $w->orWhere('name', 'like', "%{$palabra}%")->orWhere('sku', 'like', "%{$palabra}%");
                }
            })->orderByDesc('stock')->take(8)->get();
        }

        return $resultados;
    }

    private function instrucciones(Quote $quote): string
    {
        $descuentos = collect(config('empresa.descuentos_volumen'))
            ->map(fn ($pct, $min) => "desde {$min} unidades: {$pct}%")->implode(', ');

        return <<<TXT
        Eres el asistente de cotización de FerroTek, una ferretería industrial en Guatemala. Atiendes a la cotización {$quote->code}.
        Reglas:
        - NUNCA inventes productos, SKUs ni precios. Usa siempre buscar_productos antes de agregar algo.
        - Para agregar usa agregar_producto con el SKU exacto. Si hay varias opciones parecidas y no es obvio cuál quiere el cliente, pregúntale.
        - Si el cliente no dice cantidad, asume 1. "Varilla" se refiere a "Barras de Acero Corrugado".
        - Si algo no existe o no hay stock suficiente, díselo claramente.
        - Si un producto trae oferta_pct, menciona la oferta: ese precio aplica al comprarlo directo desde el catálogo o el carrito
          (precio_oferta_carrito, válido hasta oferta_hasta); en la cotización se aplica el descuento por volumen.
        - Los precios son en quetzales (Q) sin IVA. El sistema aplica automáticamente descuento por volumen por línea ({$descuentos}),
          flete gratis desde Q{$this->q(config('empresa.envio_gratis_desde'))} (si no, Q{$this->q(config('empresa.envio_costo'))}) e IVA 12%.
        - Después de modificar la cotización, responde con un resumen breve: qué agregaste o cambiaste y el total actual (usa los datos que devuelven las herramientas).
        - Responde en español, de forma breve y profesional. No uses tablas ni markdown complejo; usa viñetas simples si hace falta.
        TXT;
    }

    private function q(float $n): string
    {
        return number_format($n, 2);
    }

    /** POST a Gemini con reintentos ante saturación (503) o límite por minuto (429). */
    private function generar(string $modelo, array $payload): array
    {
        $url = config('services.gemini.url')."/{$modelo}:generateContent";

        for ($intento = 0; ; $intento++) {
            $respuesta = Http::withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->acceptJson()->timeout(60)->post($url, $payload);

            if ($respuesta->successful()) {
                return $respuesta->json();
            }

            $status = $respuesta->status();
            $detalle = (string) $respuesta->json('error.message', $respuesta->body());
            $porDia = str_contains($detalle, 'PerDay') || str_contains(json_encode($respuesta->json('error.details') ?? []), 'PerDay');

            if (in_array($status, [429, 503], true) && ! $porDia && $intento < self::REINTENTOS) {
                usleep((int) (1.5 * ($intento + 1) * 1_000_000));
                continue;
            }

            throw match (true) {
                $status === 429 && $porDia => new AsistenteException('Se agotó la cuota gratuita diaria de Gemini. Intenta mañana o cambia GEMINI_MODEL en .env.', 429),
                in_array($status, [429, 503], true) => new AsistenteException('El asistente está saturado en este momento. Intenta de nuevo en un minuto.', $status),
                in_array($status, [401, 403], true) => new AsistenteException('La API key de Gemini no tiene acceso. Revisa GEMINI_API_KEY en .env.', 500),
                default => new AsistenteException("Error al comunicarse con Gemini ({$status}): {$detalle}", 502),
            };
        }
    }

    /**
     * PHP convierte "{}" en [] al decodificar JSON; la API exige objetos en "args" y "response".
     */
    private function normalizarContenido(array $contenido): array
    {
        $contenido['role'] = $contenido['role'] ?? 'model';
        foreach ($contenido['parts'] as $i => $parte) {
            if (isset($parte['functionCall']) && empty($parte['functionCall']['args'])) {
                $contenido['parts'][$i]['functionCall']['args'] = new \stdClass;
            }
        }

        return $contenido;
    }
}
