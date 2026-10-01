<!DOCTYPE html>
<html>
<head>
    <title>Nuevo mensaje del colaborador</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #f8f9fa; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; }
        .content { padding: 20px; }
        .message-box { background-color: #f1f3f5; padding: 14px; border-radius: 5px; margin: 14px 0; border-left: 4px solid #198754; }
        .button { display: inline-block; padding: 10px 18px; background-color: #198754; color: #fff !important; text-decoration: none; border-radius: 5px; }
        .footer { margin-top: 20px; font-size: 0.8em; text-align: center; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nuevo mensaje en tu ticket</h2>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $ticket->requester->user_name ?? 'Solicitante' }}</strong>,</p>
            <p>El colaborador <strong>{{ $senderName }}</strong> envio un nuevo mensaje sobre el ticket <strong>#{{ $ticket->ticket_number }}</strong>.</p>
            <p><strong>Titulo:</strong> {{ $ticket->title }}</p>

            <div class="message-box">
                {{ $messageBody }}
            </div>

            <p style="text-align:center; margin-top: 24px;">
                <a href="{{ route('service-management.track', ['ticket_number' => $ticket->ticket_number, 'document_number' => $ticket->requester->document_number ?? '']) }}" class="button">Consultar ticket</a>
            </p>
        </div>
        <div class="footer">
            Sistema A-DDIE - Gestion de Servicios Educativos
        </div>
    </div>
</body>
</html>
