@php
    $priorityNames = [1 => 'Baja', 2 => 'Media', 3 => 'Alta', 4 => 'Urgente'];
    $action = ((int) $ticket->status === 1)
        ? 'Asigna el ticket a un responsable y registra una primera respuesta al solicitante.'
        : 'Revisa el avance con el responsable y registra una actualización antes del límite.';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #1f2937;">
    <h2 style="color: #b45309;">Ticket próximo a vencer su SLA</h2>
    <p>Este ticket alcanzó el umbral de aviso configurado ({{ $thresholdPercent }} % del tiempo de respuesta restante).</p>
    <table cellpadding="6" style="border-collapse: collapse;">
        <tr><td><strong>Ticket</strong></td><td>#{{ $ticket->ticket_number }} - {{ $ticket->title }}</td></tr>
        <tr><td><strong>Tópico</strong></td><td>{{ optional($ticket->requestType)->type_name ?? 'Sin tópico' }}</td></tr>
        <tr><td><strong>Prioridad</strong></td><td>{{ $priorityNames[(int) $ticket->priority] ?? '—' }}</td></tr>
        <tr><td><strong>Tiempo restante</strong></td><td>{{ $info['remaining_text'] }}</td></tr>
    </table>
    <p><strong>Acción recomendada:</strong> {{ $action }}</p>
    <p style="font-size: 12px; color: #6b7280;">Ingresa al sistema para gestionar el ticket. Este aviso se envía una sola vez por umbral.</p>
</body>
</html>