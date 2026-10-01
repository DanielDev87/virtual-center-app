<?php

namespace App\Console\Commands;

use App\Mail\TicketResponseOverdue;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyOverdueTickets extends Command
{
    protected $signature = 'tickets:notify-overdue {--dry-run : Show tickets without sending emails}';
    protected $description = 'Notify requesters and responsible collaborators about tickets outside SLA';

    public function handle(): int
    {
        $tickets = Ticket::with(['requester', 'mediator', 'assignments.mediator'])
            ->whereNotIn('status', [3, 4])
            ->whereNotNull('priority')
            ->whereNull('response_overdue_notified_at')
            ->get()
            ->filter(fn (Ticket $ticket) => $ticket->is_response_overdue);

        foreach ($tickets as $ticket) {
            if ($this->option('dry-run')) {
                $this->line("#{$ticket->ticket_number} - {$ticket->title}");
                continue;
            }

            $recipients = collect([$ticket->requester, $ticket->mediator])
                ->merge($ticket->assignments->where('status', 'active')->pluck('mediator'))
                ->filter(fn ($user) => $user && $user->user_email)
                ->unique('user_id');

            foreach ($recipients as $recipient) {
                try {
                    Mail::to($recipient->user_email)->send(new TicketResponseOverdue($ticket));
                } catch (\Throwable $exception) {
                    Log::error('Error sending overdue ticket notification: ' . $exception->getMessage(), [
                        'ticket_id' => $ticket->ticket_id,
                        'recipient_id' => $recipient->user_id,
                    ]);
                }
            }

            $ticket->update(['response_overdue_notified_at' => now()]);
        }

        $this->info($this->option('dry-run')
            ? "Tickets fuera de SLA detectados: {$tickets->count()}"
            : "Notificaciones procesadas: {$tickets->count()}");

        return self::SUCCESS;
    }
}
