<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>{{ $order->code }}</title></head>
<body style="margin:0;background:#f4f3f0;font-family:Arial,Helvetica,sans-serif;color:#1b1c1a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 12px;">
<tr><td align="center">
    <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border:1px solid #d8d6cf;">
        <tr><td style="background:#142c3a;color:#ffffff;padding:16px 24px;font-size:18px;font-weight:bold;letter-spacing:1px;">FERROTEK</td></tr>
        <tr><td style="padding:24px;">
            <p style="margin:0 0 4px;font-size:12px;text-transform:uppercase;color:#5a6065;">Pedido {{ $order->code }}</p>
            @if ($tipo === 'confirmado')
                <h1 style="margin:0 0 12px;font-size:22px;color:#142c3a;">¡Gracias, {{ $order->user->name }}! Recibimos tu pedido.</h1>
                <p style="margin:0 0 16px;font-size:14px;line-height:20px;">Forma de pago: <strong>{{ $order->payment_label }}</strong>. Te avisaremos por este medio cada vez que cambie el estado del pedido.</p>
            @else
                <h1 style="margin:0 0 12px;font-size:22px;color:#142c3a;">Tu pedido ahora está: {{ $order->status_label }}</h1>
                <p style="margin:0 0 16px;font-size:14px;line-height:20px;">Puedes ver el detalle y el seguimiento en la sección "Mis pedidos" de tu cuenta.</p>
            @endif

            <table role="presentation" width="100%" cellpadding="6" cellspacing="0" style="font-size:13px;border-collapse:collapse;">
                <tr style="background:#eae8e1;"><th align="left">Producto</th><th align="right">Cant.</th><th align="right">Importe</th></tr>
                @foreach ($order->items as $item)
                    <tr style="border-bottom:1px solid #eae8e1;">
                        <td>{{ $item->product->name }}<br><span style="color:#5a6065;">{{ $item->product->sku }}</span></td>
                        <td align="right">{{ $item->quantity }}</td>
                        <td align="right">Q{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr><td colspan="2" align="right">Flete</td><td align="right">{{ $order->shipping > 0 ? 'Q'.number_format($order->shipping, 2) : 'Bonificado' }}</td></tr>
                <tr><td colspan="2" align="right">IVA</td><td align="right">Q{{ number_format($order->tax, 2) }}</td></tr>
                <tr><td colspan="2" align="right"><strong>Total</strong></td><td align="right"><strong>Q{{ number_format($order->total, 2) }}</strong></td></tr>
            </table>

            <p style="margin:16px 0 0;font-size:13px;color:#5a6065;">Entrega en: {{ $order->shipping_address }}</p>
            <p style="margin:24px 0 0;"><a href="{{ route('orders.show', $order) }}" style="display:inline-block;background:#2b4251;color:#ffffff;text-decoration:none;padding:10px 18px;font-size:14px;">Ver mi pedido</a></p>
        </td></tr>
        <tr><td style="padding:12px 24px;background:#f4f3f0;font-size:11px;color:#5a6065;">{{ config('empresa.contacto.direccion') }} · {{ config('empresa.contacto.telefono') }}</td></tr>
    </table>
</td></tr>
</table>
</body>
</html>
