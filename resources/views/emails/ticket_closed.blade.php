<!DOCTYPE html>
<html>
<head>
    <title>Servicio Finalizado</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #f8f9fa; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; }
        .content { padding: 20px; }
        .button { display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; }
        .footer { margin-top: 20px; font-size: 0.8em; text-align: center; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>¡Tu solicitud ha sido completada!</h2>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $ticket->requester->user_name }}</strong>,</p>
            <p>Nos complace informarte que tu ticket <strong>#{{ $ticket->ticket_number }}</strong> con el título "<em>{{ $ticket->title }}</em>" ha sido finalizado.</p>
            
            <p><strong>Recurso Generado:</strong><br>
            <a href="{{ $ticket->resource_link }}">{{ $ticket->resource_link }}</a></p>

            <p>Tu opinión es muy importante para nosotros. Por favor, tómate un momento para calificar el servicio recibido.</p>
            
            <p style="text-align: center;">
                <a href="{{ route('service-management.show', $ticket->ticket_id) }}" class="button">Ver Ticket y Calificar</a>
            </p>
        </div>
        <div class="footer">
            <p>Sistema A-DDIE - Gestión de Servicios Educativos</p>
        </div>
    </div>
</body>
</html>
