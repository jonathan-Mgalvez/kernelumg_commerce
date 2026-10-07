<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class InventoryLockService
{
    /**
     * Bloquea pesimistamente los registros de productos y valida suficiencia de stock.
     *
     * @param Collection<int, CartItem> $cartItems
     * @return array<int, Product>
     * @throws ValidationException
     */
    public function lockAndValidate(Collection $cartItems): array
    {
        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => ['El carrito de compras está vacío.']
            ]);
        }

        $lockedProducts = [];

        foreach ($cartItems as $item) {
            // Ejecución de SELECT ... FOR UPDATE en MySQL InnoDB
            $product = Product::where('id', $item->product_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw ValidationException::withMessages([
                    'stock' => ["El artículo '{$item->product->name}' ya no está disponible en catálogo."]
                ]);
            }

            if ($product->stock < $item->quantity) {
                throw ValidationException::withMessages([
                    'stock' => [
                        "Inventario insuficiente para '{$product->name}'. Solicitadas: {$item->quantity}, Disponibles: {$product->stock}."
                    ]
                ]);
            }

            $lockedProducts[$product->id] = $product;
        }

        return $lockedProducts;
    }

    /**
     * Aplica el decremento definitivo de existencias sobre los modelos ya bloqueados.
     *
     * @param Collection<int, CartItem> $cartItems
     * @param array<int, Product> $lockedProducts
     */
    public function deductStock(Collection $cartItems, array $lockedProducts): void
    {
        foreach ($cartItems as $item) {
            if (isset($lockedProducts[$item->product_id])) {
                $product = $lockedProducts[$item->product_id];
                $product->stock -= $item->quantity;
                $product->save();
            }
        }
    }
}