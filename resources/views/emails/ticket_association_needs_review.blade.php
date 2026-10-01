<!DOCTYPE html>
<html lang="es">
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #ddd; border-radius: 6px;">
        <h2 style="color: #b02a37;">Revisa tu solicitud de asociación</h2>
        <p>Hola,</p>
        <p>Tu solicitud para asociar tickets al ticket principal <strong>#{{ $parentTicket->ticket_number }}</strong> ({{ $parentTicket->title }}) no pudo procesarse.</p>
        <div style="background: #f8d7da; padding: 14px; border-left: 4px solid #b02a37; margin: 18px 0;">
            <strong>Motivo:</strong>
            <p style="margin-bottom: 0;">{{ $reason }}</p>
        </div>
        <p>Por favor revisa los tickets seleccionados y envía nuevamente la solicitud si corresponde.</p>
    </div>
</body>
</html>
