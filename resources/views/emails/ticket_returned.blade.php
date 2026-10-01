<!DOCTYPE html>
<html lang="es">
<body>
    <h2>Ticket devuelto</h2>
    <p>Hola {{ $assigner->user_name }},</p>
    <p>El Operario devolvió el ticket <strong>#{{ $ticket->ticket_number }}</strong> a la lista de tickets no asignados.</p>
    <p><strong>Título:</strong> {{ $ticket->title }}</p>
    <p><strong>Motivo:</strong> {{ $reason }}</p>
    <p>Ingresa al sistema para revisar y asignar nuevamente el ticket.</p>
</body>
</html>