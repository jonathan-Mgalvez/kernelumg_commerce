<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Carrito de compras del cliente (uno por usuario, guardado en BD).
 * El precio se toma siempre del producto al momento: precio de lista con la oferta vigente,
 * más flete e IVA con las mismas reglas del cotizador (config/empresa.php).
 */
class CarritoService
{
    public function carrito(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    /** Agrega sin pasar del stock. Devuelve un aviso si se tuvo que ajustar la cantidad. */
    public function agregar(User $user, Product $product, int $cantidad = 1): ?string
    {
        if (! $product->active || $product->stock <= 0) {
            throw ValidationException::withMessages(['quantity' => "{$product->name} no está disponible por ahora."]);
        }

        $item = $this->carrito($user)->items()->firstOrNew(['product_id' => $product->id]);
        $actual = $item->exists ? $item->quantity : 0;
        $nueva = min($actual + max(1, $cantidad), $product->stock);

        if ($nueva === $actual) {
            throw ValidationException::withMessages(['quantity' => "Ya tienes en el carrito todo el stock disponible de {$product->name} ({$product->stock})."]);
        }

        $item->quantity = $nueva;
        $item->save();
        $item->cart->touch();

        return $nueva - $actual < $cantidad ? "Solo hay {$product->stock} en stock; se ajustó la cantidad." : null;
    }

    /** Cambia la cantidad (0 = quitar). Devuelve un aviso si se limitó al stock. */
    public function cambiarCantidad(CartItem $item, int $cantidad): ?string
    {
        if ($cantidad <= 0) {
            $item->delete();

            return null;
        }

        $stock = $item->product->stock;
        $item->update(['quantity' => min($cantidad, $stock)]);

        return $cantidad > $stock ? "Solo hay {$stock} en stock de {$item->product->name}; se ajustó la cantidad." : null;
    }

    public function quitar(CartItem $item): void
    {
        $item->delete();
    }

    /** Número de líneas en el carrito (para el contador del menú). */
    public function lineas(User $user): int
    {
        return CartItem::whereHas('cart', fn ($q) => $q->where('user_id', $user->id))->count();
    }

    /** Líneas con precios y totales, listo para la vista. */
    public function resumen(User $user): array
    {
        $items = $this->carrito($user)->items()->with('product.activeOffer', 'product.category')->oldest()->get()
            ->filter(fn (CartItem $i) => $i->product?->active)->values();

        return ['items' => $items] + $this->calcular($items);
    }

    /**
     * Calcula líneas y totales. Cada elemento debe tener ->product (con activeOffer cargado) y ->quantity.
     * La oferta es el descuento de la línea; el flete se bonifica desde el monto de config('empresa').
     */
    public function calcular(Collection $items): array
    {
        $subtotal = 0;
        $descuento = 0;
        $lineas = [];

        foreach ($items as $item) {
            $precio = (float) $item->product->price;
            $rate = (float) ($item->product->activeOffer?->discount_rate ?? 0);
            $bruto = round($precio * $item->quantity, 2);
            $desc = round($bruto * $rate / 100, 2);

            $lineas[$item->product_id] = [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $precio,
                'discount_rate' => $rate,
                'amount' => $bruto - $desc,
            ];
            $subtotal += $bruto;
            $descuento += $desc;
        }

        $neto = $subtotal - $descuento;
        $envio = ($neto == 0 || $neto >= config('empresa.envio_gratis_desde')) ? 0 : (float) config('empresa.envio_costo');
        $iva = round(($neto + $envio) * config('empresa.iva'), 2);

        return [
            'lineas' => $lineas,
            'subtotal' => round($subtotal, 2),
            'discount' => round($descuento, 2),
            'shipping' => $envio,
            'tax' => $iva,
            'total' => round($neto + $envio + $iva, 2),
        ];
    }
}
