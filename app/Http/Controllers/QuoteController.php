<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Services\AsistenteCotizacion;
use App\Services\AsistenteException;
use App\Services\CotizacionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function __construct(private CotizacionService $cotizador) {}

    /** Pantalla del cotizador (basada en "Asistente de Cotización" de Stitch). */
    public function index(Request $request)
    {
        $quote = $this->cotizador->borradorActivo($request->user())->load('items.product');

        $products = Product::active()->with('category')
            ->when(trim((string) $request->query('q')), fn ($query, $q) => $query->where(
                fn ($w) => $w->where('name', 'like', "%$q%")->orWhere('sku', 'like', "%$q%")
            ))
            ->orderBy('name')->paginate(8)->withQueryString();

        return view('quotes.builder', [
            'quote' => $quote,
            'products' => $products,
            'whatsapp' => $this->whatsappUrl($quote),
            'chat' => $request->session()->get("asistente.{$quote->id}.mensajes", []),
            // El asistente con IA es solo para clientes (no para admin ni inventario)
            'asistenteActivo' => $request->user()->role === User::ROLE_CLIENTE && app(AsistenteCotizacion::class)->configurado(),
        ]);
    }

    /** Chat con el asistente de IA: Gemini modifica la cotización usando herramientas. */
    public function assistant(Request $request, AsistenteCotizacion $asistente)
    {
        $data = $request->validate(['mensaje' => ['required', 'string', 'max:1000']]);
        $quote = $this->cotizador->borradorActivo($request->user());
        $clave = "asistente.{$quote->id}";

        try {
            $resultado = $asistente->responder($quote, $data['mensaje'], $request->session()->get("{$clave}.historial", []));
        } catch (AsistenteException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }

        $mensajes = $request->session()->get("{$clave}.mensajes", []);
        $mensajes[] = ['rol' => 'cliente', 'texto' => $data['mensaje'], 'hora' => now()->format('H:i')];
        $mensajes[] = ['rol' => 'asistente', 'texto' => $resultado['mensaje'], 'hora' => now()->format('H:i')];
        $request->session()->put($clave, [
            'historial' => $resultado['historial'],
            'mensajes' => array_slice($mensajes, -30),
        ]);

        $quote = $quote->fresh('items.product');

        return response()->json([
            'mensaje' => $resultado['mensaje'],
            'hora' => now()->format('H:i'),
            'acciones' => $resultado['acciones'],
            'cotizacion' => $this->cotizador->resumen($quote),
            'resumen_html' => view('quotes._summary', ['quote' => $quote, 'editable' => true])->render(),
        ]);
    }

    public function resetAssistant(Request $request)
    {
        $quote = $this->cotizador->borradorActivo($request->user());
        $request->session()->forget("asistente.{$quote->id}");

        return back();
    }

    public function add(Request $request, Product $product)
    {
        $data = $request->validate(['quantity' => ['nullable', 'integer', 'min:1', 'max:100000']]);
        $quote = $this->cotizador->borradorActivo($request->user());
        $this->cotizador->agregarProducto($quote, $product, $data['quantity'] ?? 1);

        return back()->with('status', "Se agregó \"{$product->name}\" a tu cotización.");
    }

    public function update(Request $request, QuoteItem $item)
    {
        $quote = $this->borradorDelItem($request, $item);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:100000']]);
        $this->cotizador->cambiarCantidad($quote, $item->id, $data['quantity']);

        return back();
    }

    public function remove(Request $request, QuoteItem $item)
    {
        $quote = $this->borradorDelItem($request, $item);
        $this->cotizador->quitarProducto($quote, $item->id);

        return back()->with('status', 'Producto quitado de la cotización.');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'delivery_address' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $quote = $this->cotizador->borradorActivo($request->user());
        if ($quote->items()->doesntExist()) {
            return back()->withErrors(['delivery_address' => 'Agrega al menos un producto antes de enviar la cotización.']);
        }

        $this->cotizador->enviar($quote, $data['delivery_address'], $data['notes'] ?? null);

        return redirect()->route('quote.show', $quote)->with('status', "Cotización {$quote->code} enviada. Un asesor la revisará pronto.");
    }

    public function history(Request $request)
    {
        $quotes = $request->user()->quotes()->withCount('items')
            ->where('status', '!=', 'borrador')
            ->when($request->query('estado'), fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(10)->withQueryString();

        return view('quotes.history', compact('quotes'));
    }

    public function show(Request $request, Quote $quote)
    {
        $this->autorizar($request, $quote);

        return view('quotes.show', [
            'quote' => $quote->load('items.product', 'user'),
            'whatsapp' => $this->whatsappUrl($quote),
        ]);
    }

    public function pdf(Request $request, Quote $quote)
    {
        $this->autorizar($request, $quote);

        return Pdf::loadView('quotes.pdf', ['quote' => $quote->load('items.product', 'user')])
            ->download("{$quote->code}.pdf");
    }

    private function autorizar(Request $request, Quote $quote): void
    {
        abort_unless($quote->user_id === $request->user()->id || $request->user()->canManageInventory(), 403);
    }

    /** Solo se pueden modificar líneas del borrador propio. */
    private function borradorDelItem(Request $request, QuoteItem $item): Quote
    {
        $quote = $item->quote;
        abort_unless($quote->user_id === $request->user()->id && $quote->status === 'borrador', 403);

        return $quote;
    }

    private function whatsappUrl(Quote $quote): string
    {
        return 'https://wa.me/'.config('empresa.contacto.whatsapp').'?text='.rawurlencode($this->cotizador->resumenTexto($quote->loadMissing('items.product')));
    }
}
