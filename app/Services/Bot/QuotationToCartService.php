<?php

namespace App\Services\Bot;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class QuotationToCartService
{
    public function convert(int $quotationId, string $sessionToken, ?int $userId = null): array
    {
        return DB::transaction(function () use ($quotationId, $sessionToken, $userId) {
            $quotation = Quotation::with('details.product')
                ->where('id', $quotationId)
                ->where('session_token', $sessionToken)
                ->first();

            if (!$quotation) {
                throw new HttpException(404, 'La cotización referenciada no fue localizada.');
            }

            if ($quotation->expires_at < now()) {
                throw new HttpException(410, 'La vigencia tarifaria de la cotización ha caducado.');
            }

            if ($userId) {
                $cart = Cart::firstOrCreate(['user_id' => $userId]);
            } else {
                $cart = Cart::firstOrCreate(['session_token' => $sessionToken]);
            }

            $warnings = [];

            foreach ($quotation->details as $detail) {
                $product = Product::lockForUpdate()->find($detail->product_id);

                if (!$product || !$product->is_active || $product->stock <= 0) {
                    $warnings[] = [
                        'product_id' => $detail->product_id,
                        'sku' => $detail->product ? $detail->product->sku : 'N/A',
                        'name' => $detail->product ? $detail->product->name : 'No disponible',
                        'requested_quantity' => $detail->quantity,
                        'adjusted_quantity' => 0,
                        'reason' => 'Artículo actualmente agotado en inventario.'
                    ];
                    continue;
                }

                $availableQuantity = min($detail->quantity, $product->stock);

                if ($availableQuantity < $detail->quantity) {
                    $warnings[] = [
                        'product_id' => $product->id,
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'requested_quantity' => $detail->quantity,
                        'adjusted_quantity' => $availableQuantity,
                        'reason' => "Existencias parciales: se asignaron {$availableQuantity} unidades."
                    ];
                }

                $existingCartItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $product->id)
                    ->first();

                if ($existingCartItem) {
                    $newQty = min($existingCartItem->quantity + $availableQuantity, $product->stock);
                    $existingCartItem->update(['quantity' => $newQty]);
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'quantity' => $availableQuantity
                    ]);
                }
            }

            $quotation->update(['is_converted' => true]);

            // Recargar ítems con producto para computar el subtotal real
            $cart->load('items.product');
            $totalItems = (int) $cart->items->sum('quantity');
            $cartSubtotal = (float) $cart->items->sum(function ($item) {
                return ($item->product ? (float) $item->product->price : 0.0) * $item->quantity;
            });

            return [
                'cart_id' => $cart->id,
                'total_cart_items' => $totalItems,
                'cart_subtotal' => round($cartSubtotal, 2),
                'warnings' => $warnings,
                'checkout_url' => url('/checkout')
            ];
        });
    }
}