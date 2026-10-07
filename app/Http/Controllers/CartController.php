<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(): View
    {
        $cartSummary = $this->cartService->getCartSummary();
        return view('cart.index', compact('cartSummary'));
    }

    public function add(Request $request, int $productId): RedirectResponse
    {
        $quantity = (int) $request->input('quantity', 1);
        $this->cartService->addItem($productId, max(1, $quantity));
        return redirect()->back()->with('success', 'Producto agregado al carrito exitosamente.');
    }

    public function update(Request $request, int $itemId): RedirectResponse
    {
        $quantity = (int) $request->input('quantity', 1);
        $this->cartService->updateItemQuantity($itemId, $quantity);
        return redirect()->route('cart.index')->with('success', 'Cantidad actualizada correctamente.');
    }

    public function remove(int $itemId): RedirectResponse
    {
        $this->cartService->removeItem($itemId);
        return redirect()->route('cart.index')->with('success', 'Artículo removido del carrito.');
    }
}