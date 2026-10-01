<!DOCTYPE html>
<html lang="es">
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #ddd; border-radius: 6px;">
        <h2 style="color: #b02a37;">Actualización de ticket pendiente</h2>
        <p>El ticket <strong>#{{ $ticket->ticket_number }}</strong>, con el título <em>{{ $ticket->title }}</em>, superó el tiempo objetivo de respuesta.</p>
        <p>El equipo responsable ya fue notificado para revisar la solicitud y actualizar su estado.</p>
        <p>Este aviso no significa que el ticket haya sido cancelado o cerrado. Puedes consultar su estado desde el sistema de soporte.</p>
    </div>
</body>
</html>
