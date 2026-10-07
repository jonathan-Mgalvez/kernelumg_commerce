<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Obtiene o instancia el carrito correspondiente al actor actual.
     */
    public function getActiveCart(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }

        $sessionToken = session()->get('cart_token');

        if (!$sessionToken) {
            $sessionToken = Str::random(40);
            session()->put('cart_token', $sessionToken);
        }

        return Cart::firstOrCreate(['session_token' => $sessionToken]);
    }

    /**
     * Agrega un producto a la cesta o incrementa su cantidad.
     */
    public function addItem(int $productId, int $quantity = 1): CartItem
    {
        $cart = $this->getActiveCart();
        $product = Product::where('id', $productId)->where('is_active', true)->firstOrFail();

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $cartItem->quantity + $quantity
            ]);

            return $cartItem;
        }

        return CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => $quantity
        ]);
    }

    /**
     * Actualiza la cantidad de un ítem existente.
     */
    public function updateItemQuantity(int $itemId, int $quantity): bool
    {
        $cart = $this->getActiveCart();
        $cartItem = CartItem::where('id', $itemId)
            ->where('cart_id', $cart->id)
            ->firstOrFail();

        if ($quantity <= 0) {
            return $cartItem->delete();
        }

        return $cartItem->update(['quantity' => $quantity]);
    }

    /**
     * Elimina un ítem específico del carrito activo.
     */
    public function removeItem(int $itemId): bool
    {
        $cart = $this->getActiveCart();

        return (bool) CartItem::where('id', $itemId)
            ->where('cart_id', $cart->id)
            ->delete();
    }

    /**
     * Obtiene el desglose consolidado de la cesta con precios calculados.
     */
    public function getCartSummary(): array
    {
        $cart = $this->getActiveCart();
        $items = $cart->items()->with(['product.activeOffer'])->get();

        $subtotal = 0.00;
        $totalItems = 0;
        $formattedItems = [];

        foreach ($items as $item) {
            $product = $item->product;

            if (!$product || !$product->is_active) {
                continue;
            }

            $unitPrice = (float) $product->price;
            $discountRate = 0.00;

            if ($product->activeOffer) {
                $discountRate = (float) $product->activeOffer->discount_rate;
                $unitPrice = round($unitPrice * ((100 - $discountRate) / 100), 2);
            }

            $lineTotal = round($unitPrice * $item->quantity, 2);
            $subtotal += $lineTotal;
            $totalItems += $item->quantity;

            $formattedItems[] = [
                'id' => $item->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'slug' => $product->slug,
                'image_url' => $product->image_url,
                'regular_price' => (float) $product->price,
                'effective_price' => $unitPrice,
                'discount_rate' => $discountRate,
                'quantity' => $item->quantity,
                'stock' => $product->stock,
                'line_total' => $lineTotal,
            ];
        }

        $tax = round($subtotal * 0.12, 2);
        $total = round($subtotal + $tax, 2);

        return [
            'cart_id' => $cart->id,
            'items' => $formattedItems,
            'total_items' => $totalItems,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    /**
     * Vacía por completo la cesta activa.
     */
    public function clearCart(): void
    {
        $cart = $this->getActiveCart();
        $cart->items()->delete();
    }
}