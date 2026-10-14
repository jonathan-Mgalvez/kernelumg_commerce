<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Quote;
use App\Services\PedidoService;
use Illuminate\Http\Request;

/** "Mis pedidos" del cliente: historial, seguimiento, cancelar y comprar una cotización aprobada. */
class OrderController extends Controller
{
    public function __construct(private PedidoService $pedidos) {}

    public function index(Request $request)
    {
        $orders = $request->user()->orders()->withCount('items')
            ->when($request->query('estado'), fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(10)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        $this->autorizar($request, $order);

        return view('orders.show', ['order' => $order->load('items.product', 'quote')]);
    }

    /** El cliente solo puede cancelar mientras el pedido está pendiente. */
    public function cancel(Request $request, Order $order)
    {
        $this->autorizar($request, $order);
        abort_unless($order->status === 'pendiente', 403, 'Solo puedes cancelar pedidos pendientes.');

        $this->pedidos->cambiarEstado($order, 'cancelado');

        return back()->with('status', "Pedido {$order->code} cancelado. El stock fue liberado.");
    }

    public function fromQuote(Request $request, Quote $quote)
    {
        abort_unless($quote->user_id === $request->user()->id, 403);
        $data = $request->validate(CheckoutController::reglas());

        $order = $this->pedidos->desdeCotizacion($quote, $data);

        return redirect()->route('checkout.success', $order);
    }

    private function autorizar(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->canManageInventory(), 403);
    }
}
