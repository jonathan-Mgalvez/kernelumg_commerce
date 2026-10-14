{{-- Panel "Resumen de Cotización" (columna derecha del diseño de Stitch). Requiere $quote y $editable. --}}
<div class="card">
    <div class="flex items-start justify-between gap-3 p-4">
        <div>
            <p class="overline">Documento {{ $editable ? 'activo' : '' }}</p>
            <h2 class="text-headline-md text-primary">Resumen de Cotización</h2>
        </div>
        <span class="px-2 py-1 rounded bg-primary-fixed text-primary-container text-label-md">{{ $quote->code }}</span>
    </div>

    <table class="table-ft">
        <thead>
            <tr><th>Partida y detalle</th><th class="!text-right">Importe (Q)</th></tr>
        </thead>
        <tbody>
            @forelse ($quote->items as $item)
                <tr>
                    <td>
                        <p class="font-semibold text-on-surface">{{ $item->quantity }}x {{ $item->product->name }}</p>
                        <p class="text-body-sm text-muted">{{ $item->product->sku }} · Q{{ number_format($item->unit_price, 2) }} / {{ $item->product->unit }}</p>
                        @if ($editable)
                            <div class="mt-1.5 flex items-center gap-2">
                                <form method="POST" action="{{ route('quote.item.update', $item) }}" class="flex items-center gap-1">
                                    @csrf @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="100000"
                                           class="input h-7 w-20 text-body-sm" aria-label="Cantidad" onchange="this.form.submit()">
                                </form>
                                <form method="POST" action="{{ route('quote.item.remove', $item) }}">
                                    @csrf @method('DELETE')
                                    <button class="text-label-sm uppercase text-danger hover:underline">Quitar</button>
                                </form>
                            </div>
                        @endif
                    </td>
                    <td class="num align-top">
                        <p class="font-semibold">Q{{ number_format($item->amount, 2) }}</p>
                        <p class="text-body-sm {{ $item->discount_rate > 0 ? 'text-secondary' : 'text-muted' }}">
                            {{ $item->discount_rate > 0 ? '−'.rtrim(rtrim($item->discount_rate, '0'), '.').'% Vol' : 'Std Net' }}
                        </p>
                    </td>
                </tr>
            @empty
                <tr><td colspan="2" class="py-8 text-center text-muted">
                    <span class="material-symbols-outlined text-[36px] block">shopping_cart</span>
                    Aún no hay productos. Agrégalos desde la búsqueda o el catálogo.
                </td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="m-4 p-4 bg-surface-container-low rounded space-y-1.5 text-body-md">
        <div class="flex justify-between"><span class="text-on-surface-variant">Subtotal regular:</span><span class="num">Q{{ number_format($quote->subtotal, 2) }}</span></div>
        <div class="flex justify-between"><span class="text-on-surface-variant">Descuento por volumen:</span><span class="num text-secondary">−Q{{ number_format($quote->discount, 2) }}</span></div>
        <div class="flex justify-between"><span class="text-on-surface-variant">Flete a pie de obra:</span>
            <span class="num text-secondary">{{ $quote->shipping > 0 ? 'Q'.number_format($quote->shipping, 2) : ($quote->items->isEmpty() ? '—' : 'Bonificado (100%)') }}</span></div>
        <div class="flex justify-between"><span class="text-on-surface-variant">IVA ({{ config('empresa.iva') * 100 }}%):</span><span class="num">Q{{ number_format($quote->tax, 2) }}</span></div>
        <div class="flex items-end justify-between pt-3 mt-2 border-t border-line">
            <div>
                <p class="text-label-md uppercase text-on-surface">Total cotizado</p>
                <p class="text-body-sm text-muted">Moneda nacional (GTQ - Q)</p>
            </div>
            <p class="text-[1.75rem] sm:text-[2rem] font-semibold text-primary num">Q{{ number_format($quote->total, 2) }}</p>
        </div>
    </div>

    @if ($quote->items->isNotEmpty() && $quote->subtotal - $quote->discount < config('empresa.envio_gratis_desde'))
        <p class="mx-4 -mt-2 mb-3 text-body-sm text-muted">
            Te faltan Q{{ number_format(config('empresa.envio_gratis_desde') - ($quote->subtotal - $quote->discount), 2) }} para obtener flete bonificado.
        </p>
    @endif
</div>
