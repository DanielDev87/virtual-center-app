<!DOCTYPE html>
<html>
<head>
    <title>Ticket asignado</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #f8f9fa; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; }
        .content { padding: 20px; }
        .button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white !important; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 0.8em; text-align: center; color: #777; }
        .details-box { background-color: #f1f3f5; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Se ha asignado un ticket</h2>
        </div>
        <div class="content">
            <p>Hola,</p>
            <p>Se te ha asignado un nuevo ticket para su atención.</p>

            <div class="details-box">
                <p><strong>Número de Ticket:</strong> {{ $assignment->ticket->ticket_number }}</p>
                <p><strong>Título:</strong> {{ $assignment->ticket->title }}</p>
                <p><strong>Tipo:</strong> {{ $assignment->ticket->requestType->type_name ?? 'N/A' }}</p>
                <p><strong>Estado:</strong> {{ $assignment->ticket->status }}</p>
            </div>

            <p>Por favor revisa el sistema para continuar con la gestión del caso.</p>
            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ route('contributors.tickets.show', $assignment->ticket->ticket_id) }}" class="button">Ver ticket</a>
            </p>
        </div>
        <div class="footer">
            <p>Sistema A-DDIE - Gestión de Servicios Educativos</p>
        </div>
    </div>
</body>
</html>
