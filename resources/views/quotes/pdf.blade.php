<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $quote->code }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #1b1c1a; margin: 0; }
        .header { border-bottom: 2px solid #2b4251; padding-bottom: 10px; margin-bottom: 16px; }
        .brand { font-size: 20px; font-weight: bold; color: #142c3a; letter-spacing: 2px; }
        .muted { color: #5a6065; }
        .code { float: right; text-align: right; }
        .code b { font-size: 16px; color: #2b4251; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #eae8e1; color: #5a6065; text-transform: uppercase; font-size: 9px; text-align: left; padding: 6px; border-bottom: 1px solid #d8d6cf; }
        td { padding: 6px; border-bottom: 1px solid #eae8e1; }
        .r { text-align: right; }
        .totals { width: 45%; margin-left: 55%; margin-top: 14px; }
        .totals td { border: none; padding: 3px 6px; }
        .grand td { border-top: 2px solid #2b4251; font-size: 14px; font-weight: bold; color: #142c3a; padding-top: 8px; }
        .box { background: #f4f3f0; padding: 8px 10px; margin-bottom: 14px; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 9px; color: #5a6065; border-top: 1px solid #d8d6cf; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="code">
            <b>{{ $quote->code }}</b><br>
            <span class="muted">Fecha: {{ $quote->created_at->format('d/m/Y') }}</span><br>
            <span class="muted">Válida hasta: {{ $quote->valid_until?->format('d/m/Y') }}</span><br>
            <span class="muted">Estado: {{ $quote->status_label }}</span>
        </div>
        <div class="brand">FERROTEK</div>
        <div class="muted">{{ config('empresa.eslogan') }}</div>
        <div class="muted">{{ config('empresa.contacto.direccion') }} · {{ config('empresa.contacto.telefono') }}</div>
    </div>

    <div class="box">
        <b>Cliente:</b> {{ $quote->user->name }}{{ $quote->user->company ? ' — '.$quote->user->company : '' }}<br>
        <b>Correo:</b> {{ $quote->user->email }}{{ $quote->user->phone ? ' · Tel. '.$quote->user->phone : '' }}<br>
        <b>Despacho:</b> {{ $quote->delivery_address ?? 'Por definir' }}
        @if ($quote->notes)<br><b>Notas:</b> {{ $quote->notes }}@endif
    </div>

    <table>
        <thead>
            <tr><th>SKU</th><th>Descripción</th><th class="r">Cant.</th><th class="r">P. unitario</th><th class="r">Desc.</th><th class="r">Importe</th></tr>
        </thead>
        <tbody>
            @foreach ($quote->items as $item)
                <tr>
                    <td>{{ $item->product->sku }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="r">{{ number_format($item->quantity) }}</td>
                    <td class="r">Q{{ number_format($item->unit_price, 2) }}</td>
                    <td class="r">{{ $item->discount_rate > 0 ? $item->discount_rate.'%' : '—' }}</td>
                    <td class="r">Q{{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal regular</td><td class="r">Q{{ number_format($quote->subtotal, 2) }}</td></tr>
        <tr><td>Descuento por volumen</td><td class="r">-Q{{ number_format($quote->discount, 2) }}</td></tr>
        <tr><td>Flete a pie de obra</td><td class="r">{{ $quote->shipping > 0 ? 'Q'.number_format($quote->shipping, 2) : 'Bonificado' }}</td></tr>
        <tr><td>IVA ({{ config('empresa.iva') * 100 }}%)</td><td class="r">Q{{ number_format($quote->tax, 2) }}</td></tr>
        <tr class="grand"><td>TOTAL (GTQ)</td><td class="r">Q{{ number_format($quote->total, 2) }}</td></tr>
    </table>

    <div class="footer">
        Precios sujetos a disponibilidad de inventario y vigentes hasta la fecha indicada. FerroTek — empresa ficticia, proyecto académico de Desarrollo Web.
    </div>
</body>
</html>
