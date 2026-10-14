<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Lógica del cotizador. Toda operación sobre una cotización pasa por aquí,
 * para que en el futuro un asistente con IA pueda reutilizar los mismos métodos
 * (agregarProducto, cambiarCantidad, quitarProducto...) a partir de texto libre.
 */
class CotizacionService
{
    /** Devuelve la cotización en borrador del usuario, o la crea. */
    public function borradorActivo(User $user): Quote
    {
        $quote = $user->quotes()->where('status', 'borrador')->latest()->first();

        return $quote ?? $user->quotes()->create([
            'code' => $this->nuevoCodigo(),
            'status' => 'borrador',
            'valid_until' => now()->addDays(config('empresa.vigencia_dias')),
        ]);
    }

    public function agregarProducto(Quote $quote, Product $product, int $cantidad = 1): Quote
    {
        $item = $quote->items()->firstOrNew(['product_id' => $product->id]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + max(1, $cantidad);
        $item->unit_price = $product->price;
        $item->amount = 0;
        $item->save();

        return $this->recalcular($quote);
    }

    public function cambiarCantidad(Quote $quote, int $itemId, int $cantidad): Quote
    {
        $item = $quote->items()->findOrFail($itemId);
        $cantidad <= 0 ? $item->delete() : $item->update(['quantity' => $cantidad]);

        return $this->recalcular($quote);
    }

    public function quitarProducto(Quote $quote, int $itemId): Quote
    {
        $quote->items()->whereKey($itemId)->delete();

        return $this->recalcular($quote);
    }

    /** % de descuento por volumen para una cantidad. */
    public function descuentoPorVolumen(int $cantidad): float
    {
        foreach (config('empresa.descuentos_volumen') as $minimo => $porcentaje) {
            if ($cantidad >= $minimo) {
                return (float) $porcentaje;
            }
        }

        return 0.0;
    }

    /** Recalcula líneas y totales: subtotal, descuento por volumen, flete e IVA. */
    public function recalcular(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote) {
            $subtotal = 0;
            $descuento = 0;

            foreach ($quote->items()->get() as $item) {
                $bruto = $item->quantity * $item->unit_price;
                $rate = $this->descuentoPorVolumen($item->quantity);
                $desc = round($bruto * $rate / 100, 2);

                $item->update(['discount_rate' => $rate, 'amount' => $bruto - $desc]);

                $subtotal += $bruto;
                $descuento += $desc;
            }

            $neto = $subtotal - $descuento;
            $envio = ($neto == 0 || $neto >= config('empresa.envio_gratis_desde')) ? 0 : config('empresa.envio_costo');
            $iva = round(($neto + $envio) * config('empresa.iva'), 2);

            $quote->update([
                'subtotal' => $subtotal,
                'discount' => $descuento,
                'shipping' => $envio,
                'tax' => $iva,
                'total' => $neto + $envio + $iva,
            ]);

            return $quote->fresh('items.product');
        });
    }

    /** Pasa el borrador a "enviada" para que el administrador la revise. */
    public function enviar(Quote $quote, ?string $direccion = null, ?string $notas = null): Quote
    {
        $quote->update([
            'status' => 'enviada',
            'delivery_address' => $direccion,
            'notes' => $notas,
            'valid_until' => now()->addDays(config('empresa.vigencia_dias')),
        ]);

        return $quote;
    }

    /** Texto plano del resumen, para compartir por WhatsApp. */
    public function resumenTexto(Quote $quote): string
    {
        $lineas = ["*Cotización {$quote->code} - FerroTek*"];
        foreach ($quote->items as $item) {
            $lineas[] = "• {$item->quantity}x {$item->product->name}: Q".number_format($item->amount, 2);
        }
        $lineas[] = 'Total: Q'.number_format($quote->total, 2).' (IVA incluido)';

        return implode("\n", $lineas);
    }

    /** Estado de la cotización en un arreglo simple (lo usa el asistente con IA). */
    public function resumen(Quote $quote): array
    {
        $quote->loadMissing('items.product');

        return [
            'codigo' => $quote->code,
            'partidas' => $quote->items->map(fn ($item) => [
                'sku' => $item->product->sku,
                'producto' => $item->product->name,
                'cantidad' => $item->quantity,
                'precio_unitario' => (float) $item->unit_price,
                'descuento_volumen_pct' => (float) $item->discount_rate,
                'importe' => (float) $item->amount,
            ])->values()->all(),
            'subtotal' => (float) $quote->subtotal,
            'descuento' => (float) $quote->discount,
            'flete' => (float) $quote->shipping,
            'iva' => (float) $quote->tax,
            'total' => (float) $quote->total,
            'moneda' => 'GTQ',
        ];
    }

    private function nuevoCodigo(): string
    {
        $anio = now()->year;
        $ultimo = Quote::where('code', 'like', "COT-$anio-%")->max('code');
        $n = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;

        return sprintf('COT-%d-%04d', $anio, $n);
    }
}
