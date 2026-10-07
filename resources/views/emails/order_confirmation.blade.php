<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Pedido</title>
</head>
<body style="font-family: Arial, sans-serif; font-size: 13px; color: #222222; line-height: 1.5; padding: 20px;">
    <h2 style="font-size: 16px; border-bottom: 2px solid #1a2a3a; padding-bottom: 6px; text-transform: uppercase;">
        Confirmación de Pedido - KernelUMG Commerce
    </h2>
    <p>Estimado/a <strong>{{ $order->user->name ?? 'Cliente' }}</strong>,</p>
    <p>Su compra ha sido procesada de manera exitosa en nuestro sistema. A continuación se detalla la información consolidada de su transacción:</p>
    <div style="margin: 15px 0; padding: 10px; border: 1px solid #cccccc; background-color: #f8f9fa;">
        <div><strong>Código de Seguimiento (Tracking):</strong> {{ $order->tracking_code }}</div>
        <div><strong>Estado Actual:</strong> {{ $order->status }}</div>
        <div><strong>Dirección de Envío:</strong> {{ $order->shipping_address }}</div>
        <div><strong>Método de Pago:</strong> {{ $order->payment_method }}</div>
    </div>
    <h3 style="font-size: 14px; text-transform: uppercase; margin-top: 20px; border-bottom: 1px solid #cccccc; padding-bottom: 4px;">
        Desglose de Productos
    </h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px;">
        <thead>
            <tr style="background-color: #f8f9fa; border-bottom: 1px solid #333333;">
                <th style="border: 1px solid #cccccc; padding: 6px; text-align: left;">Ítem</th>
                <th style="border: 1px solid #cccccc; padding: 6px; text-align: right;">Cantidad</th>
                <th style="border: 1px solid #cccccc; padding: 6px; text-align: right;">Precio Unitario</th>
                <th style="border: 1px solid #cccccc; padding: 6px; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->details as $detail)
                <tr>
                    <td style="border: 1px solid #cccccc; padding: 6px;">{{ $detail->product->name }} (SKU: {{ $detail->product->sku }})</td>
                    <td style="border: 1px solid #cccccc; padding: 6px; text-align: right;">{{ $detail->quantity }}</td>
                    <td style="border: 1px solid #cccccc; padding: 6px; text-align: right;">Q {{ number_format($detail->unit_price, 2) }}</td>
                    <td style="border: 1px solid #cccccc; padding: 6px; text-align: right;">Q {{ number_format($detail->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="border: 1px solid #cccccc; padding: 6px; text-align: right; font-weight: bold;">Subtotal Neto:</td>
                <td style="border: 1px solid #cccccc; padding: 6px; text-align: right;">Q {{ number_format($order->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" style="border: 1px solid #cccccc; padding: 6px; text-align: right; font-weight: bold;">Impuesto (IVA 12%):</td>
                <td style="border: 1px solid #cccccc; padding: 6px; text-align: right;">Q {{ number_format($order->tax, 2) }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td colspan="3" style="border: 1px solid #cccccc; padding: 6px; text-align: right; font-size: 13px;">Total Cancelado:</td>
                <td style="border: 1px solid #cccccc; padding: 6px; text-align: right; font-size: 13px;">Q {{ number_format($order->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    <p style="margin-top: 25px; font-size: 11px; color: #777777;">
        Puede consultar el avance de su pedido ingresando a la plataforma con su cuenta de cliente en la sección de seguimiento.
    </p>
</body>
</html>