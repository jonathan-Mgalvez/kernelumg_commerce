<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $mes = Quote::where('status', '!=', 'borrador')->where('created_at', '>=', now()->startOfMonth());
        $ventasMes = Order::where('status', '!=', 'cancelado')->where('created_at', '>=', now()->startOfMonth());

        return view('admin.dashboard', [
            'stats' => [
                'pedidos_mes' => (clone $ventasMes)->count(),
                'ventas_mes' => (clone $ventasMes)->sum('total'),
                'pedidos_pendientes' => Order::where('status', 'pendiente')->count(),
                'ofertas' => Offer::vigente()->count(),
                'productos' => Product::count(),
                'stock_bajo' => Product::whereColumn('stock', '<=', 'min_stock')->count(),
                'cotizaciones_mes' => (clone $mes)->count(),
                'monto_mes' => (clone $mes)->sum('total'),
                'pendientes' => Quote::where('status', 'enviada')->count(),
                'mensajes' => ContactMessage::where('read', false)->count(),
                'clientes' => User::where('role', User::ROLE_CLIENTE)->count(),
            ],
            'ultimas' => Quote::with('user')->where('status', '!=', 'borrador')->latest()->take(5)->get(),
            'pedidos' => Order::with('user')->latest()->take(5)->get(),
            'alertas' => Product::whereColumn('stock', '<=', 'min_stock')->orderBy('stock')->take(5)->get(),
        ]);
    }

    /** "Lotes y Almacén": inventario con stock actual vs mínimo. */
    public function inventory(Request $request)
    {
        $products = Product::with('category')
            ->when($request->query('estado') === 'bajo', fn ($q) => $q->whereColumn('stock', '<=', 'min_stock'))
            ->when(trim((string) $request->query('q')), fn ($q, $s) => $q->where(
                fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%")
            ))
            ->orderBy('sku')->paginate(15)->withQueryString();

        return view('admin.inventory', [
            'products' => $products,
            'valor' => Product::selectRaw('SUM(stock * price) as v')->value('v') ?? 0,
        ]);
    }
}
