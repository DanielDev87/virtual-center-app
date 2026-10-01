<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\RequestType;
use App\Models\TicketAssignment;
use App\Models\JobPosition;
use App\Models\TicketProgress;
use Illuminate\Support\Facades\Auth;
use App\Services\TicketClosurePropagationService;
use App\Services\FinalTicketEvidenceService;

class AdminTicketController extends Controller
{
    /**
     * Calcular el progreso automático desde las tareas de sprint (% completadas sobre el total).
     */
    private function computeAutoProgress($ticket): int
    {
        $sprintTasks = $ticket->sprints->flatMap(fn($sprint) => $sprint->tasks);
        $total = $sprintTasks->count();

        if ($total === 0) {
            return 0;
        }

        $done = $sprintTasks->where('status', 'done')->count();
        return (int) round(($done / $total) * 100);
    }

    /**
     * Mostrar el listado de todos los tickets
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['requester', 'mediator', 'requestType', 'sprints.tasks']);

        // Filtrar por estado si se proporcionó
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtrar por tópico si se proporcionó
        if ($request->filled('request_type_id')) {
            $query->where('request_type_id', $request->request_type_id);
        }

        $requestTypes = RequestType::where('is_active', true)
            ->orderBy('type_name')
            ->get(['type_id', 'type_name']);

        $tickets = $query->orderByDesc('priority')->latest()->paginate(10);
        
        $tickets->getCollection()->transform(function ($ticket) {
            $ticket->auto_progress = $this->computeAutoProgress($ticket);
            return $ticket;
        });
        
        return view('admin.tickets.index', compact('tickets', 'requestTypes'));
    }

    /**
     * Mostrar el ticket especificado
     */
    public function show($id)
    {
        $ticket = Ticket::with([
                'requester',
                'mediator',
                'requestType.collaborators',
                'requestType.regionalAssignments',
                'evidences',
                'assignments.mediator',
                'assignments.jobPosition',
                'progress.user',
                'sprints.tasks'
            ])
            ->findOrFail($id);

        $autoProgress = $this->computeAutoProgress($ticket);
        
        // Obtener mediadores disponibles para asignación de tickets.
        $mediators = User::with('jobPositions')
            ->whereIn('user_id', $this->topicMediatorIds($ticket))
            ->where('is_active', true)
            ->orderBy('user_name')
            ->get();
        
        // Obtener los cargos activos
        $jobPositions = JobPosition::where('is_active', true)->get();
        $availableTickets = Ticket::whereNotIn('status', [3, 4])
            ->whereNull('parent_ticket_id')
            ->where('ticket_id', '!=', $ticket->ticket_id)
            ->orderByDesc('priority')->latest('ticket_id')->take(100)->get(['ticket_id', 'ticket_number', 'title']);

        return view('admin.tickets.show', compact('ticket', 'mediators', 'jobPositions', 'availableTickets', 'autoProgress'));
    }

    public function associateTicket(Request $request, $id)
    {
        $request->validate(['child_ticket_id' => 'required|exists:tickets,ticket_id']);
        $parent = Ticket::findOrFail($id);
        $child = Ticket::findOrFail($request->child_ticket_id);

        if ($parent->ticket_id === $child->ticket_id || $child->parent_ticket_id) {
            return back()->withErrors(['child_ticket_id' => 'El ticket seleccionado ya tiene una asociación o es el mismo ticket.']);
        }
        if (in_array((int) $parent->status, [3, 4], true) || in_array((int) $child->status, [3, 4], true)) {
            return back()->withErrors(['child_ticket_id' => 'Solo se pueden asociar tickets abiertos.']);
        }
        if ($parent->childTickets()->whereKey($child->ticket_id)->exists()) {
            return back()->withErrors(['child_ticket_id' => 'El ticket ya está asociado.']);
        }

        $child->update(['parent_ticket_id' => $parent->ticket_id]);
        return back()->with('success', 'Ticket asociado al ticket principal.');
    }

    /**
     * Asignar un mediador a un ticket (asignación única heredada)
     */
    public function assignMediator(Request $request, $id)
    {
        $request->validate([
            'mediator_id' => 'required|exists:users,user_id'
        ]);

        $ticket = Ticket::with(['requestType.collaborators', 'requestType.regionalAssignments'])->findOrFail($id);

        if ($this->isLocked($ticket)) {
            return back()->with('error', 'No se puede modificar un ticket cerrado o cancelado. Debes reabrirlo primero.');
        }

        if (!$this->topicMediatorIds($ticket)->contains((int) $request->mediator_id)) {
            return back()->withErrors(['mediator_id' => 'El usuario seleccionado no pertenece al equipo del tópico.']);
        }
        
        $ticket->update([
            'mediator_id' => $request->mediator_id,
            'status' => 2, // En Progreso
        ]);

        TicketAssignment::updateOrCreate(
            [
                'ticket_id' => $ticket->ticket_id,
                'user_id' => $request->mediator_id,
            ],
            [
                'job_position_id' => null,
                'assigned_by' => Auth::id(),
                'status' => 'active',
                'assigned_at' => now(),
                'notes' => 'Asignación principal desde la opción Asignar Mediador',
            ]
        );

        return back()->with('success', 'Mediador asignado exitosamente.');
    }

    /**
     * Establecer la prioridad del ticket
     */
    public function setPriority(Request $request, $id)
    {
        $request->validate([
            'priority' => 'required|in:1,2,3,4' // 1=Baja, 2=Media, 3=Alta, 4=Urgente
        ]);

        $ticket = Ticket::findOrFail($id);

        if ($this->isLocked($ticket)) {
            return back()->with('error', 'No se puede modificar un ticket cerrado o cancelado. Debes reabrirlo primero.');
        }
        
        $ticket->update([
            'priority' => $request->priority,
        ]);

        $priorityNames = [1 => 'Baja', 2 => 'Media', 3 => 'Alta (Afecta operación)', 4 => 'Urgente (Suspende operación)'];
        $slaHours = $ticket->priority_sla_hours;
        return back()->with('success', "Prioridad actualizada a: {$priorityNames[$request->priority]} (Tiempo objetivo: {$slaHours} horas).");
    }

    /**
     * Cerrar un ticket
     */
    public function close(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:3,4', // 3 = Completado, 4 = Cancelado
            'solution_detail' => 'exclude_unless:status,3|required|string|min:10',
            'cancellation_reason' => 'exclude_unless:status,4|required|string|min:10|max:500',
            'resource_link' => 'exclude_unless:status,3|nullable|url'
            ,'final_evidence_files' => 'nullable|array|max:5'
            ,'final_evidence_files.*' => 'file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,webp'
        ]);

        $ticket = Ticket::with('sprints.tasks')->findOrFail($id);

        if ($this->isLocked($ticket)) {
            return back()->with('error', 'No se puede modificar un ticket cerrado o cancelado. Debes reabrirlo primero.');
        }

        $autoProgress = $this->computeAutoProgress($ticket);
        
        if ($request->status == 3) {
            // Validación estricta para el cierre
            if ($autoProgress < 100) {
                return back()->with('error', 'No se puede cerrar el ticket. El progreso debe estar al 100%.');
            }

            // El cierre solo es válido en la fase final de ADDIE.
            if ($ticket->current_phase !== 'Evaluation') {
                return back()->with('error', 'No se puede cerrar el ticket. Debe estar en la fase final de ADDIE (Evaluación).');
            }

            $ticket->update([
                'status' => $request->status,
                'progress_percentage' => $autoProgress,
                'resource_link' => $request->resource_link
            ]);

            $finalEvidenceIds = $request->hasFile('final_evidence_files')
                ? app(FinalTicketEvidenceService::class)->store($ticket, $request->file('final_evidence_files'), Auth::id())
                : [];

            TicketProgress::create([
                'ticket_id' => $ticket->ticket_id,
                'user_id' => Auth::id(),
                'progress_description' => 'Cierre administrativo del servicio: ' . $request->solution_detail
                    . ($finalEvidenceIds ? "\n\n[attachments:" . implode(',', $finalEvidenceIds) . "]" : ''),
                'progress_percentage' => 100,
                'status_update' => 'service_closed_admin',
            ]);

            app(TicketClosurePropagationService::class)->closeChildren(
                $ticket,
                $request->solution_detail,
                $request->resource_link
            );

            // Enviar correo al solicitante
            try {
                if ($ticket->requester && $ticket->requester->user_email) {
                    \Illuminate\Support\Facades\Mail::to($ticket->requester->user_email)->send(new \App\Mail\TicketClosed($ticket));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error sending closure email: ' . $e->getMessage());
            }
        } else {
            $cancelReason = trim((string) $request->input('cancellation_reason'));

            $ticket->update([
                'status' => $request->status,
                'resource_link' => null,
            ]);

            TicketProgress::create([
                'ticket_id' => $ticket->ticket_id,
                'user_id' => Auth::id(),
                'progress_description' => 'Cancelación administrativa del ticket. Motivo: ' . $cancelReason,
                'progress_percentage' => (int) ($ticket->progress_percentage ?? $autoProgress),
                'status_update' => 'service_cancelled_admin',
            ]);

            try {
                $ticket->loadMissing('requester');
                if ($ticket->requester && $ticket->requester->user_email) {
                    \Illuminate\Support\Facades\Mail::to($ticket->requester->user_email)
                        ->send(new \App\Mail\TicketCancelled($ticket, $cancelReason));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error sending cancellation email: ' . $e->getMessage(), [
                    'ticket_id' => $ticket->ticket_id,
                ]);
            }
        }

        $statusText = $request->status == 3 ? 'completado' : 'cancelado';
        return back()->with('success', "Ticket {$statusText} exitosamente.");
    }

    /**
     * Reabrir un ticket
     */
    public function reopen(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->status != 3 && $ticket->status != 4) {
            return back()->with('error', 'Solo se pueden reabrir tickets cerrados o cancelados.');
        }

        $ticket->update([
            'status' => 2, // En Progreso
            'is_reopened' => true,
            'reopened_at' => now(),
            'progress_percentage' => 0,
            'rating' => null, // Restablecer la calificación para nueva evaluación
            'feedback' => null, // Restablecer el comentario
        ]);
        
        // Restablecer el progreso a 0 para la fase de reapertura
        $ticket->progress_percentage = 0;
        $ticket->save();

        return back()->with('success', 'Ticket reabierto exitosamente. Se ha habilitado la sección de avances adicionales y la opción de calificar nuevamente.');
    }

    /**
     * Calificar un ticket (Evaluación ADDIE)
     */
    public function rate(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string'
        ]);

        $ticket = Ticket::findOrFail($id);

        if ($ticket->status != 3) {
            return back()->with('error', 'Solo se pueden calificar tickets completados.');
        }

        $ticket->update([
            'rating' => $request->rating,
            'feedback' => $request->feedback,
            'current_phase' => 'Evaluation' // Marcar como fase de Evaluación
        ]);

        return back()->with('success', 'Evaluación registrada exitosamente.');
    }

    /**
     * Asignar un mediador con cargo a un ticket (Sistema Multi-Mediador)
     */
    public function assignMediatorToTicket(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'job_position_id' => 'required|exists:job_positions,job_position_id',
            'notes' => 'nullable|string',
        ]);

        $ticket = Ticket::with(['requestType.collaborators', 'requestType.regionalAssignments'])->findOrFail($id);

        if ($this->isLocked($ticket)) {
            return back()->with('error', 'No se puede modificar un ticket cerrado o cancelado. Debes reabrirlo primero.');
        }

        if ($ticket->requestType && !$this->topicMediatorIds($ticket)->contains((int) $request->user_id)) {
            return back()->withErrors(['user_id' => 'El usuario seleccionado no pertenece al equipo del tópico.']);
        }

        // Verificar si este usuario ya está asignado a este ticket
        $existingAssignment = TicketAssignment::where('ticket_id', $id)
            ->where('user_id', $request->user_id)
            ->where('status', 'active')
            ->first();

        if ($existingAssignment) {
            return back()->with('error', 'Este mediador ya está asignado a este ticket.');
        }

        // Crear asignación
        TicketAssignment::create([
            'ticket_id' => $id,
            'user_id' => $request->user_id,
            'job_position_id' => $request->job_position_id,
            'assigned_by' => Auth::id(),
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        // Actualizar el estado del ticket a "En Progreso" si aún está pendiente
        if ($ticket->status == 1) {
            $ticket->update(['status' => 2]);
        }

        return back()->with('success', 'Mediador asignado exitosamente al equipo de trabajo.');
    }

    /**
     * Eliminar la asignación de un mediador de un ticket
     */
    public function removeAssignment($ticketId, $assignmentId)
    {
        $assignment = TicketAssignment::where('assignment_id', $assignmentId)
            ->where('ticket_id', $ticketId)
            ->firstOrFail();

        if ($this->isLocked($assignment->ticket()->firstOrFail())) {
            return back()->with('error', 'No se puede modificar un ticket cerrado o cancelado. Debes reabrirlo primero.');
        }

        $assignment->update(['status' => 'removed']);

        return back()->with('success', 'Mediador removido del equipo de trabajo.');
    }

    private function topicMediatorIds(Ticket $ticket)
    {
        $requestType = $ticket->requestType;
        if (!$requestType) {
            return collect();
        }

        return $requestType->collaborators->pluck('user_id')
            ->merge($requestType->regionalAssignments->pluck('user_id'))
            ->merge([$requestType->gestor_id])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    private function isLocked(Ticket $ticket): bool
    {
        return in_array((int) $ticket->status, [3, 4], true);
    }
}
