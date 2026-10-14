<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Services\CarritoService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CarritoService $carrito) {}

    public function index(Request $request)
    {
        return view('cart.index', $this->carrito->resumen($request->user()));
    }

    public function add(Request $request, Product $product)
    {
        $data = $request->validate(['quantity' => ['nullable', 'integer', 'min:1', 'max:100000']]);
        $aviso = $this->carrito->agregar($request->user(), $product, $data['quantity'] ?? 1);

        return back()->with('status', $aviso ?? "Se agregó \"{$product->name}\" al carrito.");
    }

    public function update(Request $request, CartItem $item)
    {
        $this->autorizar($request, $item);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:100000']]);
        $aviso = $this->carrito->cambiarCantidad($item, $data['quantity']);

        return back()->with($aviso ? ['status' => $aviso] : []);
    }

    public function remove(Request $request, CartItem $item)
    {
        $this->autorizar($request, $item);
        $this->carrito->quitar($item);

        return back()->with('status', 'Producto quitado del carrito.');
    }

    private function autorizar(Request $request, CartItem $item): void
    {
        abort_unless($item->cart->user_id === $request->user()->id, 403);
    }
}
