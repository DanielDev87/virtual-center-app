<?php

namespace App\Services;

use App\Mail\TicketClosed;
use App\Models\Ticket;
use App\Models\TicketProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TicketClosurePropagationService
{
    /**
     * Close open descendants of a completed parent ticket.
     */
    public function closeChildren(Ticket $parent, string $solutionDetail, ?string $resourceLink = null): int
    {
        $children = $this->descendants($parent);
        $closedCount = 0;

        foreach ($children as $child) {
            if (in_array((int) $child->status, [3, 4], true)) {
                continue;
            }

            DB::transaction(function () use ($child, $solutionDetail, $resourceLink, &$closedCount): void {
                $child->update([
                    'status' => 3,
                    'progress_percentage' => 100,
                    'resource_link' => $resourceLink ?: $child->resource_link,
                ]);

                TicketProgress::create([
                    'ticket_id' => $child->ticket_id,
                    'user_id' => auth()->id(),
                    'progress_description' => 'Cierre propagado desde ticket principal: ' . trim($solutionDetail),
                    'progress_percentage' => 100,
                    'status_update' => 'service_closed_associated_ticket',
                ]);

                $child->loadMissing('requester');
                if ($child->requester && $child->requester->user_email) {
                    try {
                        Mail::to($child->requester->user_email)->send(new TicketClosed($child));
                    } catch (\Throwable $exception) {
                        Log::error('Error sending associated ticket closure email: ' . $exception->getMessage(), [
                            'ticket_id' => $child->ticket_id,
                            'parent_ticket_id' => $child->parent_ticket_id,
                        ]);
                    }
                }

                $closedCount++;
            });
        }

        return $closedCount;
    }

    private function descendants(Ticket $parent): array
    {
        $result = [];
        $pending = $parent->childTickets()->with('childTickets')->get()->all();

        while ($pending) {
            $ticket = array_shift($pending);
            $result[] = $ticket;
            foreach ($ticket->childTickets as $child) {
                $pending[] = $child;
            }
        }

        return $result;
    }
}
