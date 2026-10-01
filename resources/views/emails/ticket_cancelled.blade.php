<!DOCTYPE html>
<html lang="es">
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #ddd; border-radius: 6px;">
        <h2 style="color: #b02a37;">Solicitud cancelada</h2>
        <p>Hola <strong>{{ $ticket->requester->user_name }}</strong>,</p>
        <p>Tu solicitud <strong>#{{ $ticket->ticket_number }}</strong>, con el título <em>{{ $ticket->title }}</em>, fue cancelada.</p>
        <div style="background: #f8d7da; padding: 14px; border-left: 4px solid #b02a37; margin: 18px 0;">
            <strong>Motivo de cancelación:</strong>
            <p style="margin-bottom: 0;">{{ $reason }}</p>
        </div>
        <p>Si consideras que la solicitud debe gestionarse nuevamente, puedes crear una nueva solicitud con la información corregida.</p>
    </div>
</body>
</html>
