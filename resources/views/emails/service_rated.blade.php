<!DOCTYPE html>
<html>
<head>
    <title>Nueva Calificación de Servicio</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #e9ecef; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; }
        .content { padding: 20px; }
        .rating { color: #ffc107; font-size: 1.5em; }
        .footer { margin-top: 20px; font-size: 0.8em; text-align: center; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nueva Calificación Recibida</h2>
        </div>
        <div class="content">
            <p>Se ha recibido una nueva calificación para el ticket <strong>#{{ $ticket->ticket_number }}</strong>.</p>
            
            <p><strong>Solicitante:</strong> {{ $ticket->requester->user_name }}<br>
            <strong>Ticket:</strong> {{ $ticket->title }}</p>
            
            <p><strong>Calificación:</strong><br>
            <span class="rating">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $ticket->rating) ★ @else ☆ @endif
                @endfor
            </span> ({{ $ticket->rating }}/5)
            </p>
            
            <p><strong>Opinión / Feedback:</strong><br>
            <em>"{{ $ticket->feedback ?? 'Sin comentarios adicionales.' }}"</em></p>
            
            <p><a href="{{ route('admin.tickets.show', $ticket->ticket_id) }}">Ver detalles en el Panel Administrativo</a></p>
        </div>
        <div class="footer">
            <p>Sistema A-DDIE - Notificación Automática</p>
        </div>
    </div>
</body>
</html>
