<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('user')->withCount('items')
            ->when($request->query('estado'), fn ($q, $s) => $q->where('status', $s))
            ->when(trim((string) $request->query('q')), fn ($q, $s) => $q->where(
                fn ($w) => $w->where('code', 'like', "%$s%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$s%")->orWhere('company', 'like', "%$s%"))
            ))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', ['order' => $order->load('items.product', 'user', 'quote')]);
    }

    public function update(Request $request, Order $order, PedidoService $pedidos)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Order::STATUSES))]]);
        $pedidos->cambiarEstado($order, $data['status']);

        return back()->with('status', "Pedido {$order->code}: {$order->fresh()->status_label}. Se notificó al cliente por correo.");
    }
}
