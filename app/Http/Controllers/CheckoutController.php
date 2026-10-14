<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CarritoService;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function index(Request $request, CarritoService $carrito)
    {
        $resumen = $carrito->resumen($request->user());
        if ($resumen['items']->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Tu carrito está vacío.']);
        }

        return view('checkout.index', $resumen + [
            'direccion' => $request->user()->orders()->latest()->value('shipping_address'),
        ]);
    }

    public function store(Request $request, PedidoService $pedidos)
    {
        $order = $pedidos->desdeCarrito($request->user(), $request->validate(self::reglas()));

        return redirect()->route('checkout.success', $order);
    }

    public function success(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return view('checkout.success', ['order' => $order->load('items.product')]);
    }

    /** Reglas compartidas con "Convertir cotización en pedido". */
    public static function reglas(): array
    {
        return [
            'payment_method' => ['required', Rule::in(array_keys(Order::PAGOS))],
            'shipping_address' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
