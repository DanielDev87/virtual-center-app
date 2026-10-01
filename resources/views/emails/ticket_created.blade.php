<!DOCTYPE html>
<html>
<head>
    <title>Solicitud Recibida</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #f8f9fa; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; }
        .header-logo { display: block; margin: 0 auto 8px; height: 56px; width: auto; }
        .content { padding: 20px; }
        .button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white !important; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 0.8em; text-align: center; color: #777; }
        .details-box { background-color: #f1f3f5; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .details-box p { margin: 5px 0; }
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
            <h2>¡Hemos recibido tu solicitud!</h2>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $ticket->requester->user_name ?? 'Usuario' }}</strong>,</p>
            <p>Te confirmamos que tu solicitud de servicio ha sido registrada exitosamente en nuestro sistema.</p>
            
            <div class="details-box">
                <p><strong>Número de Ticket:</strong> <span style="color: #007bff; font-weight: bold; font-size: 1.1em;">{{ $ticket->ticket_number }}</span></p>
                <p><strong>Título:</strong> {{ $ticket->title }}</p>
                <p><strong>Tipo de Servicio:</strong> {{ $ticket->requestType->type_name ?? 'N/A' }}</p>
                @php
                    $priorities = [1 => 'Baja', 2 => 'Media', 3 => 'Alta (Afecta operación)', 4 => 'Urgente (Suspende operación)'];
                @endphp
                <p><strong>Prioridad Inicial:</strong> {{ $priorities[$ticket->priority] ?? 'No asignada' }}</p>
                @if($ticket->priority_sla_hours)
                <p><strong>Tiempo objetivo de respuesta:</strong> {{ $ticket->priority_sla_hours }} horas</p>
                @endif
                @if($ticket->requestType && $ticket->requestType->area)
                <p><strong>Área responsable:</strong> {{ $ticket->requestType->area->area_name }}</p>
                @endif
                <div style="margin-top: 10px;">
                    <strong>Descripción del Caso:</strong><br>
                    <div style="background: #fff; padding: 10px; border-radius: 4px; border: 1px solid #ddd; margin-top: 5px;">
                        {!! nl2br(e(strip_tags($ticket->requester_info))) !!}
                    </div>
                </div>
            </div>

            <p>Tu solicitud será atendida internamente por el área responsable según el tópico asignado. Puedes hacer seguimiento manual al estado de tu caso utilizando tu número de ticket a través de nuestro portal.</p>
            
            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ route('service-management.track', ['ticket_number' => $ticket->ticket_number]) }}" class="button">Consultar Estado del Ticket</a>
            </p>
        </div>
        <div class="footer">
            <p>Sistema A-DDIE - Gestión de Servicios Educativos<br>Universidad Católica Luis Amigó</p>
        </div>
    </div>
</body>
</html>
