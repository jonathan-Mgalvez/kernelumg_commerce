{{-- Totales del carrito. Requiere $subtotal, $discount, $shipping, $tax, $total. --}}
<div class="card p-4 space-y-1.5 text-body-md">
    <h2 class="text-headline-sm text-primary mb-2">Resumen de compra</h2>
    <div class="flex justify-between"><span class="text-on-surface-variant">Subtotal:</span><span class="num">Q{{ number_format($subtotal, 2) }}</span></div>
    @if ($discount > 0)
        <div class="flex justify-between"><span class="text-on-surface-variant">Ahorro por ofertas:</span><span class="num text-danger">−Q{{ number_format($discount, 2) }}</span></div>
    @endif
    <div class="flex justify-between"><span class="text-on-surface-variant">Flete:</span>
        <span class="num text-secondary">{{ $shipping > 0 ? 'Q'.number_format($shipping, 2) : 'Bonificado' }}</span></div>
    <div class="flex justify-between"><span class="text-on-surface-variant">IVA ({{ config('empresa.iva') * 100 }}%):</span><span class="num">Q{{ number_format($tax, 2) }}</span></div>
    <div class="flex items-end justify-between pt-3 mt-2 border-t border-line">
        <p class="text-label-md uppercase text-on-surface">Total a pagar</p>
        <p class="text-[1.75rem] font-semibold text-primary num">Q{{ number_format($total, 2) }}</p>
    </div>
    @if ($shipping > 0)
        <p class="pt-1 text-body-sm text-muted">Te faltan Q{{ number_format(config('empresa.envio_gratis_desde') - ($subtotal - $discount), 2) }} para flete bonificado.</p>
    @endif
</div>
