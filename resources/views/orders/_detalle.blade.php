{{-- Líneas y totales de un pedido. Requiere $order con items.product. --}}
<div class="card">
    <div class="flex items-start justify-between gap-3 p-4">
        <div>
            <p class="overline">{{ $order->quote ? 'Desde cotización '.$order->quote->code : 'Compra desde carrito' }}</p>
            <h2 class="text-headline-md text-primary">Detalle del pedido</h2>
        </div>
        <span class="px-2 py-1 rounded bg-primary-fixed text-primary-container text-label-md">{{ $order->code }}</span>
    </div>
    <table class="table-ft">
        <thead><tr><th>Producto</th><th class="!text-right">Cant.</th><th class="!text-right">Precio</th><th class="!text-right">Importe (Q)</th></tr></thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>
                        <p class="font-semibold">{{ $item->product->name }}</p>
                        <p class="text-body-sm text-muted">{{ $item->product->sku }}</p>
                    </td>
                    <td class="num">{{ number_format($item->quantity) }}</td>
                    <td class="num">Q{{ number_format($item->unit_price, 2) }}</td>
                    <td class="num">
                        <p class="font-semibold">Q{{ number_format($item->amount, 2) }}</p>
                        @if ($item->discount_rate > 0)
                            <p class="text-body-sm text-secondary">−{{ rtrim(rtrim($item->discount_rate, '0'), '.') }}% {{ $order->quote_id ? 'Vol' : 'Oferta' }}</p>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="m-4 p-4 bg-surface-container-low rounded space-y-1.5 text-body-md">
        <div class="flex justify-between"><span class="text-on-surface-variant">Subtotal:</span><span class="num">Q{{ number_format($order->subtotal, 2) }}</span></div>
        <div class="flex justify-between"><span class="text-on-surface-variant">Descuentos:</span><span class="num text-secondary">−Q{{ number_format($order->discount, 2) }}</span></div>
        <div class="flex justify-between"><span class="text-on-surface-variant">Flete:</span><span class="num">{{ $order->shipping > 0 ? 'Q'.number_format($order->shipping, 2) : 'Bonificado' }}</span></div>
        <div class="flex justify-between"><span class="text-on-surface-variant">IVA:</span><span class="num">Q{{ number_format($order->tax, 2) }}</span></div>
        <div class="flex items-end justify-between pt-3 mt-2 border-t border-line">
            <p class="text-label-md uppercase text-on-surface">Total</p>
            <p class="text-[1.75rem] font-semibold text-primary num">Q{{ number_format($order->total, 2) }}</p>
        </div>
    </div>
</div>
