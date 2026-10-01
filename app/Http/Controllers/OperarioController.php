<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketEvidence;
use App\Models\TicketProgress;
use App\Models\TicketAssignment;
use App\Services\LocalEvidenceStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OperarioController extends Controller
{
    public function dashboard(Request $request)
    {
        $userId = Auth::id();

        $tickets = Ticket::where(function ($query) use ($userId) {
                $query->where('mediator_id', $userId)
                    ->orWhereHas('assignments', function ($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId)
                            ->where('status', 'active');
                    });
            })
            ->whereNotIn('status', [3, 4])
            ->with(['requester', 'requestType', 'assignments' => function ($query) use ($userId) {
                $query->where('user_id', $userId)->where('status', 'active');
            }])
            ->orderByDesc('priority')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Ticket::where(function ($query) use ($userId) {
                $query->where('mediator_id', $userId)
                    ->orWhereHas('assignments', function ($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId)->where('status', 'active');
                    });
            })->whereNotIn('status', [3, 4])->count(),
            'pending' => Ticket::where(function ($query) use ($userId) {
                $query->where('mediator_id', $userId)
                    ->orWhereHas('assignments', function ($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId)->where('status', 'active');
                    });
            })->where('status', 1)->count(),
            'in_progress' => Ticket::where(function ($query) use ($userId) {
                $query->where('mediator_id', $userId)
                    ->orWhereHas('assignments', function ($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId)->where('status', 'active');
                    });
            })->whereIn('status', [2, 5])->count(),
        ];

        return view('operario.dashboard', compact('tickets', 'stats'));
    }

    public function show($id)
    {
        $userId = Auth::id();

        $ticket = Ticket::with(['requester', 'requestType', 'progress.user', 'evidences', 'assignments.mediator.role', 'mediator.role'])
            ->where(function ($query) use ($userId) {
                $query->where('mediator_id', $userId)
                    ->orWhereHas('assignments', function ($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId)->where('status', 'active');
                    });
            })
            ->findOrFail($id);

        $canModify = $this->canOperarioModify($ticket, $userId);

        return view('operario.show', compact('ticket', 'canModify'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:1,2,5',
            'note' => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();
        $ticket = Ticket::with(['assignments.mediator.role', 'assignments.assignedByUser', 'mediator.role', 'requester'])
            ->where(function ($query) use ($userId) {
                $query->where('mediator_id', $userId)
                    ->orWhereHas('assignments', function ($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId)->where('status', 'active');
                    });
            })
            ->findOrFail($id);

        if (!$this->canOperarioModify($ticket, $userId)) {
            return back()->withErrors(['status' => $this->operarioReadOnlyMessage($ticket)]);
        }

        if (in_array((int) $ticket->status, [3, 4], true)) {
            return back()->withErrors(['status' => 'No se puede cambiar el estado de un ticket finalizado o cancelado.']);
        }

        $statusMap = [
            1 => 'pendiente',
            2 => 'en_proceso',
            5 => 'realizado para auditoria',
        ];

        $note = trim((string) ($request->note ?? ''));
        $progressPercentage = (int) $request->status === 5
            ? 100
            : (int) ($ticket->progress_percentage ?? 0);

        DB::transaction(function () use ($ticket, $request, $note, $statusMap, $progressPercentage) {
            $ticket->update([
                'status' => (int) $request->status,
                'progress_percentage' => $progressPercentage,
            ]);

            $message = $note !== ''
                ? 'Operario actualizó el ticket a ' . $statusMap[(int) $request->status] . ': ' . $note
                : 'Operario actualizó el ticket a ' . $statusMap[(int) $request->status] . '.';

            TicketProgress::create([
                'ticket_id' => $ticket->ticket_id,
                'user_id' => Auth::id(),
                'progress_description' => $message,
                'progress_percentage' => $progressPercentage,
                'status_update' => 'operario_status_update',
            ]);
        });

        return redirect()->route('operario.tickets.show', $ticket->ticket_id)
            ->with('success', 'Estado actualizado correctamente.');
    }

    public function storeEvidence(Request $request, $id)
    {
        $request->validate([
            'evidence_files' => 'required|array|max:5',
            'evidence_files.*' => 'file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,webp',
            'note' => 'nullable|string|max:1000',
        ], [
            'evidence_files.*.max' => 'Cada archivo debe pesar máximo 2 MB.',
        ]);

        $userId = Auth::id();
        $ticket = Ticket::with(['assignments.mediator.role', 'mediator.role'])
            ->where(function ($query) use ($userId) {
                $query->where('mediator_id', $userId)
                    ->orWhereHas('assignments', function ($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId)->where('status', 'active');
                    });
            })
            ->findOrFail($id);

        if (!$this->canOperarioModify($ticket, $userId)) {
            return back()->withErrors(['evidence_files' => $this->operarioReadOnlyMessage($ticket)]);
        }

        $storage = app(LocalEvidenceStorageService::class);
        $notes = trim((string) ($request->note ?? ''));

        foreach ($request->file('evidence_files', []) as $file) {
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();
            $stored = $storage->store($file, (string) $ticket->ticket_number);

            TicketEvidence::create([
                'ticket_id' => $ticket->ticket_id,
                'uploaded_by' => Auth::id(),
                'file_name' => $originalName,
                'storage_disk' => 'filesystem',
                'file_path' => $stored['relative_path'],
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'external_url' => null,
            ]);
        }

        if ($notes !== '') {
            TicketProgress::create([
                'ticket_id' => $ticket->ticket_id,
                'user_id' => Auth::id(),
                'progress_description' => 'Evidencia adjuntada por operario: ' . $notes,
                'progress_percentage' => (int) ($ticket->progress_percentage ?? 0),
                'status_update' => 'operario_evidence',
            ]);
        }

        return redirect()->route('operario.tickets.show', $ticket->ticket_id)
            ->with('success', 'Evidencia adjuntada correctamente.');
    }

    public function returnTicket(Request $request, $id)
    {
        $request->validate([
            'return_reason' => 'required|string|min:10|max:1000',
        ], [
            'return_reason.required' => 'Debes indicar por qué devuelves el ticket.',
            'return_reason.min' => 'La razón debe tener mínimo 10 caracteres.',
        ]);

        $userId = Auth::id();
        $ticket = Ticket::with(['assignments.mediator.role', 'mediator.role'])
            ->where(function ($query) use ($userId) {
                $query->where('mediator_id', $userId)
                    ->orWhereHas('assignments', function ($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId)->where('status', 'active');
                    });
            })
            ->findOrFail($id);

        if (!$this->canOperarioModify($ticket, $userId)) {
            return back()->withErrors(['return_reason' => $this->operarioReadOnlyMessage($ticket)]);
        }

        if (in_array((int) $ticket->status, [3, 4], true)) {
            return back()->withErrors(['return_reason' => 'No se puede devolver un ticket finalizado o cancelado.']);
        }

        $reason = trim($request->return_reason);
        $assignment = $ticket->assignments
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->sortByDesc('assigned_at')
            ->first();
        $assigner = $assignment?->assignedByUser;

        DB::transaction(function () use ($ticket, $userId, $reason, $assigner) {
            TicketAssignment::where('ticket_id', $ticket->ticket_id)
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->update([
                    'status' => 'removed',
                    'notes' => 'Devuelto por operario: ' . $reason,
                ]);

            if ((int) $ticket->mediator_id === (int) $userId) {
                $ticket->update([
                    'mediator_id' => null,
                    'status' => 1,
                ]);
            }

            TicketProgress::create([
                'ticket_id' => $ticket->ticket_id,
                'user_id' => $userId,
                'progress_description' => 'Ticket devuelto a la cola de no asignados. Motivo: ' . $reason,
                'progress_percentage' => (int) ($ticket->progress_percentage ?? 0),
                'status_update' => 'operario_returned_ticket',
            ]);

            if ($assigner && $assigner->user_email) {
                try {
                    Mail::to($assigner->user_email)->send(new \App\Mail\TicketReturned($ticket, $reason, $assigner));
                } catch (\Throwable $e) {
                    \Log::error('Error sending ticket return email: ' . $e->getMessage(), [
                        'ticket_id' => $ticket->ticket_id,
                        'assigned_by' => $assigner->user_id,
                    ]);
                }
            }
        });

        return redirect()->route('operario.dashboard')
            ->with('success', 'Ticket devuelto a la lista de tickets no asignados.');
    }

    private function canOperarioModify(Ticket $ticket, int $userId): bool
    {
        if ((int) $ticket->mediator_id !== $userId) {
            return false;
        }

        return !$this->teamHasContributor($ticket);
    }

    private function teamHasContributor(Ticket $ticket): bool
    {
        $teamUsers = $ticket->assignments
            ->where('status', 'active')
            ->pluck('mediator')
            ->filter();

        if ($ticket->mediator) {
            $teamUsers->push($ticket->mediator);
        }

        return $teamUsers->contains(fn ($user) => $user->role?->role_name === 'Contributor');
    }

    private function operarioReadOnlyMessage(Ticket $ticket): string
    {
        if ($this->teamHasContributor($ticket)) {
            return 'Este ticket tiene un Contributor responsable. Los Operarios solo pueden consultar el trabajo del equipo.';
        }

        return 'Solo el Operario principal puede modificar este ticket.';
    }
}
