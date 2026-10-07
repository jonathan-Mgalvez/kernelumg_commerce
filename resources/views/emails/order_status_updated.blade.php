<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualización de Pedido</title>
</head>
<body style="font-family: Arial, sans-serif; font-size: 13px; color: #222222; line-height: 1.5; padding: 20px;">
    <h2 style="font-size: 16px; border-bottom: 2px solid #1a2a3a; padding-bottom: 6px; text-transform: uppercase;">
        Actualización de Estado de su Pedido
    </h2>
    <p>Estimado/a cliente,</p>
    <p>Le notificamos que el estado de su orden <strong>#{{ $order->tracking_code }}</strong> ha cambiado a: <strong>{{ $order->status }}</strong>.</p>
    <p style="margin-top: 20px; font-size: 11px; color: #777777;">
        KernelUMG Commerce - Notificaciones automáticas.
    </p>
</body>
</html>