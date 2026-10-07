<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Mensaje de Contacto</title>
</head>
<body style="font-family: Arial, sans-serif; font-size: 13px; color: #222222; line-height: 1.5; padding: 20px;">
    <h2 style="font-size: 16px; border-bottom: 2px solid #1a2a3a; padding-bottom: 6px; text-transform: uppercase;">
        Nuevo Mensaje Recibido desde el Formulario Web
    </h2>
    <table style="width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 13px;">
        <tr>
            <td style="width: 140px; font-weight: bold; padding: 6px 0;">Remitente:</td>
            <td style="padding: 6px 0;">{{ $contactMessage->name }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 6px 0;">Correo Electrónico:</td>
            <td style="padding: 6px 0;">{{ $contactMessage->email }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 6px 0;">Asunto:</td>
            <td style="padding: 6px 0;">{{ $contactMessage->subject }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 6px 0;">Fecha y Hora:</td>
            <td style="padding: 6px 0;">{{ $contactMessage->created_at->format('d/m/Y H:i:s') }}</td>
        </tr>
    </table>
    <div style="margin-top: 20px; border: 1px solid #cccccc; padding: 12px; background-color: #f8f9fa;">
        <strong>Mensaje:</strong>
        <p style="margin-top: 8px; white-space: pre-line;">{{ $contactMessage->message }}</p>
    </div>
    <p style="margin-top: 25px; font-size: 11px; color: #777777;">
        Este es un mensaje automático generado por KernelUMG Commerce.
    </p>
</body>
</html>