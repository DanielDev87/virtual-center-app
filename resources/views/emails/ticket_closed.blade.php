<!DOCTYPE html>
<html>
<head>
    <title>Servicio Finalizado</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #f8f9fa; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; }
        .header-logo { display: block; margin: 0 auto 8px; height: 56px; width: auto; }
        .content { padding: 20px; }
        .solution-box { background-color: #e8f5e9; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0; border-radius: 3px; }
        .button { display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; }
        .footer { margin-top: 20px; font-size: 0.8em; text-align: center; color: #777; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('img/logomsula.png');
        $logoSrc = (isset($message) && file_exists($logoPath))
            ? $message->embed($logoPath)
            : asset('img/logomsula.png');
    @endphp
    <div class="container">
        <div class="header">
            <img src="{{ $logoSrc }}" alt="Mesa de Servicio" class="header-logo">
            <h2>¡Tu solicitud ha sido completada!</h2>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $ticket->requester->user_name }}</strong>,</p>
            <p>Nos complace informarte que tu ticket <strong>#{{ $ticket->ticket_number }}</strong> con el título "<em>{{ $ticket->title }}</em>" ha sido finalizado.</p>
            
            @php
                $closureProgress = $ticket->progress()
                    ->whereIn('status_update', ['service_closed', 'service_closed_admin', 'service_closed_area_admin', 'operario_completion_approved'])
                    ->latest()
                    ->first();
            @endphp

            @if($closureProgress && $closureProgress->progress_description)
            <div class="solution-box">
                <h4 style="margin-top: 0; color: #2e7d32;">Respuesta del Servicio:</h4>
                <p>{{ preg_replace('/^(Cierre del servicio: |Cierre administrativo del servicio: |Cierre por admin de área: |Auditoría aprobada por admin de área: )/', '', $closureProgress->progress_description) }}</p>
            </div>
            @endif

            @if($ticket->resource_link)
            <p><strong>Recurso Entregado:</strong><br>
            <a href="{{ $ticket->resource_link }}" style="color: #0066cc;">{{ $ticket->resource_link }}</a></p>
            @endif

            <p>Tu opinión es muy importante para nosotros. Por favor, tómate un momento para calificar el servicio recibido.</p>
            
            <p style="text-align: center;">
                <a href="{{ route('service-management.track', ['ticket_number' => $ticket->ticket_number]) }}" class="button">Ver Ticket y Calificar</a>
            </p>
        </div>
        <div class="footer">
            <p>Sistema A-DDIE - Gestión de Servicios Educativos</p>
        </div>
    </div>
</body>
</html>
