<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutProcessRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderProcessingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected OrderProcessingService $orderProcessingService;

    public function __construct(
        CartService $cartService,
        OrderProcessingService $orderProcessingService
    ) {
        $this->cartService = $cartService;
        $this->orderProcessingService = $orderProcessingService;
    }

    public function index(): View|RedirectResponse
    {
        $cartSummary = $this->cartService->getCartSummary();

        if (empty($cartSummary['items'])) {
            return redirect()->route('cart.index')->with('error', 'Su carrito de compras está vacío.');
        }

        $user = Auth::user();

        return view('checkout.index', compact('cartSummary', 'user'));
    }

    public function process(CheckoutProcessRequest $request): RedirectResponse
    {
        $order = $this->orderProcessingService->processOrder(
            (int) Auth::id(),
            $request->validated()
        );

        return redirect()->route('checkout.success', ['tracking' => $order->tracking_code])
            ->with('success', 'Su compra ha sido registrada satisfactoriamente.');
    }

    public function success(string $tracking): View
    {
        $order = Order::where('tracking_code', $tracking)
            ->where('user_id', Auth::id())
            ->with(['details.product'])
            ->firstOrFail();

        return view('checkout.success', compact('order'));
    }
}