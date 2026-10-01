<?php

namespace App\Http\Controllers;

use App\Mail\CollaboratorMessageReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;
use App\Models\TicketProgress;
use App\Models\ProjectTask;
use App\Models\Sprint;
use App\Models\RequestType;
use App\Models\TicketAssignment;
use App\Models\TicketJoinRequest;
use App\Models\TicketAssociationRequest;
use App\Services\TicketClosurePropagationService;
use App\Services\FinalTicketEvidenceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ContributorController extends Controller
{
    /**
     * Mostrar tickets de los tópicos del colaborador aún no autoasignados.
     */
    public function topicTickets(Request $request)
    {
        $userId = Auth::id();
        $topicIds = $this->resolveContributorTopicIds($userId);
        $topics = RequestType::whereIn('type_id', $topicIds)
            ->orderBy('type_name')
            ->get(['type_id', 'type_name']);

        $selectedTopicId = $request->filled('topic_id')
            ? (int) $request->query('topic_id')
            : null;

        if ($selectedTopicId !== null && !$topics->pluck('type_id')->contains($selectedTopicId)) {
            $selectedTopicId = null;
        }

        $topicNames = $topics->pluck('type_name');

        if ($selectedTopicId !== null) {
            $topicHeaderName = optional($topics->firstWhere('type_id', $selectedTopicId))->type_name ?? 'Sin tópico asignado';
        } elseif ($topics->count() === 1) {
            $topicHeaderName = $topics->first()->type_name;
        } elseif ($topics->count() > 1) {
            $topicHeaderName = 'Varios tópicos';
        } else {
            $topicHeaderName = 'Sin tópico asignado';
        }

        $ticketsQuery = $this->buildTopicQueueQuery($topicIds)
            ->with(['requester', 'requestType', 'institution', 'mediator.role', 'assignments.mediator.role']);

        if ($selectedTopicId !== null) {
            $ticketsQuery->where('request_type_id', $selectedTopicId);
        }

        $tickets = $ticketsQuery
            ->latest()
            ->paginate(10);

        $tickets->getCollection()->transform(function ($ticket) {
            $ticket->pool_available = !$this->ticketHasActiveContributor($ticket);
            return $ticket;
        });

        return view('contributors.topic-tickets', compact('tickets', 'topicNames', 'topics', 'selectedTopicId', 'topicHeaderName'));
    }

    /**
     * Obtener conteo de tickets pendientes en cola de tópicos del colaborador.
     */
    public function topicTicketsCount()
    {
        $userId = Auth::id();
        $topicIds = $this->resolveContributorTopicIds($userId);
        $count = $this->buildTopicQueueQuery($topicIds, $userId)->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Autoasignar un ticket disponible de un tópico del colaborador.
     */
    public function selfAssignTopicTicket($id)
    {
        $userId = Auth::id();
        $topicIds = $this->resolveContributorTopicIds($userId);

        $assignedTicketId = DB::transaction(function () use ($id, $userId, $topicIds) {
            $ticketQuery = Ticket::where('ticket_id', $id)
                ->whereIn('request_type_id', $topicIds)
                ->lockForUpdate();

            $this->applyRegionalQueueScope($ticketQuery, $userId);

            $ticket = $ticketQuery->firstOrFail();

            if ($this->isFinalizado($ticket)) {
                abort(422, 'No se puede autoasignar un ticket finalizado.');
            }

            $ticket->loadMissing(['mediator.role', 'assignments.mediator.role']);
            if ($this->ticketHasActiveContributor($ticket)) {
                return null;
            }

            $existingActiveAssignment = TicketAssignment::where('ticket_id', $ticket->ticket_id)
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->first();

            if ($existingActiveAssignment) {
                return $ticket->ticket_id;
            }

            TicketAssignment::where('ticket_id', $ticket->ticket_id)
                ->where('status', 'active')
                ->whereDoesntHave('mediator.role', function ($query) {
                    $query->where('role_name', 'Contributor');
                })
                ->update([
                    'status' => 'removed',
                    'notes' => 'Reasignado a colaborador desde cola de tópico el ' . now()->format('d/m/Y H:i'),
                ]);

            $shouldSetPrimaryMediator = empty($ticket->mediator_id)
                || optional($ticket->mediator)->role?->role_name === 'Admin Área';

            if ($shouldSetPrimaryMediator) {
                $ticket->update([
                    'mediator_id' => $userId,
                ]);
            }

            TicketAssignment::create([
                'ticket_id' => $ticket->ticket_id,
                'user_id' => $userId,
                'job_position_id' => null,
                'assigned_by' => $userId,
                'status' => 'active',
                'notes' => 'Autoasignación desde cola de tópico',
                'assigned_at' => now(),
            ]);

            TicketProgress::create([
                'ticket_id' => $ticket->ticket_id,
                'user_id' => $userId,
                'progress_description' => 'Ticket autoasignado por colaborador responsable del tópico.',
                'progress_percentage' => (int) ($ticket->progress_percentage ?? 0),
                'status_update' => 'self_assigned',
            ]);

            return $ticket->ticket_id;
        });

        if (!$assignedTicketId) {
            return back()->withErrors(['ticket' => 'Este ticket ya está siendo atendido. Solicita unirte al equipo para trabajar en conjunto.']);
        }

        return redirect()->route('contributors.tickets.show', $assignedTicketId)
            ->with('success', 'Ticket asignado correctamente.');
    }

    public function requestToJoinTicket(Request $request, $id)
    {
        $request->validate([
            'request_note' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();
        $topicIds = $this->resolveContributorTopicIds($userId);
        $ticket = Ticket::with(['requestType', 'mediator.role', 'assignments.mediator.role'])
            ->whereIn('request_type_id', $topicIds)
            ->findOrFail($id);

        if (!$this->ticketHasActiveContributor($ticket)) {
            return back()->withErrors(['join_request' => 'El ticket está disponible para autoasignación; puedes tomarlo directamente.']);
        }

        if ((int) $ticket->mediator_id === $userId || $ticket->assignments->where('status', 'active')->contains('user_id', $userId)) {
            return back()->withErrors(['join_request' => 'Ya formas parte del equipo de este ticket.']);
        }

        $existing = TicketJoinRequest::where('ticket_id', $ticket->ticket_id)
            ->where('requester_id', $userId)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return back()->with('success', 'Ya existe una solicitud pendiente para unirte a este equipo.');
        }

        TicketJoinRequest::create([
            'ticket_id' => $ticket->ticket_id,
            'requester_id' => $userId,
            'status' => 'pending',
            'request_note' => $request->input('request_note'),
        ]);

        return back()->with('success', 'Solicitud enviada al colaborador responsable.');
    }

    public function approveJoinRequest(Request $request, $joinRequestId)
    {
        return $this->reviewJoinRequest($request, $joinRequestId, 'approved');
    }

    public function rejectJoinRequest(Request $request, $joinRequestId)
    {
        return $this->reviewJoinRequest($request, $joinRequestId, 'rejected');
    }

    private function reviewJoinRequest(Request $request, $joinRequestId, string $decision)
    {
        $request->validate(['review_note' => 'nullable|string|max:500']);
        $reviewerId = Auth::id();
        $joinRequest = TicketJoinRequest::with(['ticket.mediator.role', 'ticket.assignments'])
            ->where('join_request_id', $joinRequestId)
            ->where('status', 'pending')
            ->firstOrFail();
        $ticket = $joinRequest->ticket;

        $isResponsible = (int) $ticket->mediator_id === $reviewerId
            || $ticket->assignments->where('status', 'active')->contains('user_id', $reviewerId);
        if (!$isResponsible || $ticket->mediator?->role?->role_name !== 'Contributor') {
            abort(403, 'Solo el Contributor responsable puede aprobar solicitudes de unión.');
        }

        DB::transaction(function () use ($joinRequest, $ticket, $reviewerId, $decision, $request) {
            $joinRequest->update([
                'status' => $decision,
                'reviewed_by' => $reviewerId,
                'review_note' => $request->input('review_note'),
                'reviewed_at' => now(),
            ]);

            if ($decision === 'approved') {
                TicketAssignment::updateOrCreate(
                    ['ticket_id' => $ticket->ticket_id, 'user_id' => $joinRequest->requester_id],
                    [
                        'job_position_id' => null,
                        'assigned_by' => $reviewerId,
                        'status' => 'active',
                        'assigned_at' => now(),
                        'notes' => 'Ingreso al equipo aprobado por el Contributor responsable.',
                    ]
                );
            }
        });

        return back()->with('success', $decision === 'approved'
            ? 'El colaborador fue agregado al equipo.'
            : 'La solicitud de unión fue rechazada.');
    }

    /**
     * Mostrar el panel con los tickets asignados
     */
    public function dashboard(Request $request)
    {
        $userId = Auth::id();

        $selectedStatus = $request->filled('status') ? (int) $request->status : null;
        $allowedStatuses = [1, 2, 3, 4];
        if (!in_array($selectedStatus, $allowedStatuses, true)) {
            $selectedStatus = null;
        }
        
        // Obtener tickets donde el usuario es mediador principal O está asignado como miembro del equipo
        $tickets = Ticket::where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })
            ->when($selectedStatus !== null, function ($query) use ($selectedStatus) {
                $query->where('status', $selectedStatus);
            })
            ->with(['requester', 'requestType', 'assignments' => function($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->orderByDesc('priority')
            ->latest()
            ->paginate(10)
            ->withQueryString();
        
        // Función auxiliar para la consulta de estadísticas
        $statsQuery = function($status = null) use ($userId) {
            return Ticket::where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })->when($status, function($q) use ($status) {
                return $q->where('status', $status);
            })->count();
        };
        
        // Estadísticas
        $stats = [
            'total' => $statsQuery(),
            'pending' => $statsQuery(1),
            'in_progress' => $statsQuery(2),
            'completed' => $statsQuery(3),
        ];

        // Calificaciones del Gestor
        $completedTicketsQuery = Ticket::where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })->whereNotNull('rating');

        $averageRating = round($completedTicketsQuery->avg('rating') ?? 0, 1);
        
        $ratingDistribution = $completedTicketsQuery->select('rating', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->rating => $item->count];
            });

        return view('contributors.dashboard', compact('tickets', 'stats', 'averageRating', 'ratingDistribution'));
    }

    /**
     * Mostrar detalles del ticket
     */
    public function show($id)
    {
        $userId = Auth::id();
        $topicIds = $this->resolveContributorTopicIds($userId);

        $ticket = Ticket::with(['requester', 'requestType', 'progress.user', 'evidences', 'assignments.mediator', 'assignments.jobPosition', 'joinRequests.requester', 'sprints.tasks', 'projectTasks.assignee'])
            ->where(function($query) use ($userId, $topicIds) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      })
                      ->orWhereIn('request_type_id', $topicIds);
            })
            ->findOrFail($id);

        // Obtener el sprint activo o el sprint seleccionado
        $activeSprint = null;
        if (request('sprint_id')) {
            $activeSprint = $ticket->sprints->where('sprint_id', request('sprint_id'))->first();
        } else {
            $activeSprint = $ticket->sprints()->where('status', 'active')->first();
        }
        
        // Obtener tareas del backlog (tareas no asignadas a ningún sprint)
        $backlogTasks = $ticket->projectTasks()->whereNull('sprint_id')->get();

        $transferRequestTypes = collect();
        $transferCollaborators = collect();
        $supportsCollaboratorAssignments = Schema::hasTable('request_type_user');
        if ((int) $ticket->mediator_id === (int) $userId) {
            $currentAreaId = optional($ticket->requestType)->area_id;
            $transferRequestTypesQuery = RequestType::with($supportsCollaboratorAssignments ? ['gestor', 'collaborators'] : ['gestor'])
                ->where('is_active', true)
                ->where('type_id', '!=', $ticket->request_type_id)
                ->orderBy('type_name');

            if ($supportsCollaboratorAssignments) {
                $transferRequestTypesQuery->where(function ($query) {
                    $query->whereHas('collaborators')
                        ->orWhereNotNull('gestor_id');
                });
            } else {
                $transferRequestTypesQuery->whereNotNull('gestor_id');
            }

            $transferRequestTypes = $transferRequestTypesQuery->get();
            $transferCollaborators = $transferRequestTypes
                ->flatMap(fn ($requestType) => $supportsCollaboratorAssignments
                    ? $requestType->collaborators
                    : collect([$requestType->gestor]))
                ->filter()
                ->unique('user_id')
                ->sortBy('user_name')
                ->values();
        }

        $hasActiveAssignment = $ticket->assignments
            ->where('status', 'active')
            ->contains(function ($assignment) use ($userId) {
                return (int) $assignment->user_id === (int) $userId;
            });

        $isTopicPreview = (int) $ticket->mediator_id !== (int) $userId && !$hasActiveAssignment;
        $pendingJoinRequests = $ticket->joinRequests->where('status', 'pending');
        $canReviewJoinRequests = (int) $ticket->mediator_id === $userId
            && optional($ticket->mediator)->role?->role_name === 'Contributor';
        $hasPendingJoinRequest = $pendingJoinRequests->contains('requester_id', $userId);
        $availableTickets = Ticket::where(function ($query) use ($userId) {
                $query->where('mediator_id', $userId)
                    ->orWhereHas('assignments', fn ($subQuery) => $subQuery->where('user_id', $userId)->where('status', 'active'));
            })
            ->whereNotIn('status', [3, 4])
            ->whereNull('parent_ticket_id')
            ->where('ticket_id', '!=', $ticket->ticket_id)
            ->latest('ticket_id')->take(100)->get(['ticket_id', 'ticket_number', 'title']);

        return view('contributors.show', compact('ticket', 'activeSprint', 'backlogTasks', 'transferRequestTypes', 'transferCollaborators', 'supportsCollaboratorAssignments', 'isTopicPreview', 'pendingJoinRequests', 'canReviewJoinRequests', 'hasPendingJoinRequest', 'availableTickets'));
    }

    public function associateTicket(Request $request, $id)
    {
        $request->validate([
            'child_ticket_ids' => 'nullable|array|max:50',
            'child_ticket_ids.*' => 'integer|exists:tickets,ticket_id',
            'child_ticket_id' => 'nullable|integer|exists:tickets,ticket_id',
            'request_note' => 'nullable|string|max:500',
        ]);
        $childTicketIds = collect($request->input('child_ticket_ids', []))
            ->merge($request->filled('child_ticket_id') ? [$request->input('child_ticket_id')] : [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($childTicketIds)) {
            return back()->withErrors(['child_ticket_ids' => 'Selecciona al menos un ticket relacionado.']);
        }
        $userId = Auth::id();
        $topicIds = $this->resolveContributorTopicIds($userId);
        $parent = Ticket::where(function ($query) use ($userId) {
                $query->where('mediator_id', $userId)
                    ->orWhereHas('assignments', fn ($subQuery) => $subQuery->where('user_id', $userId)->where('status', 'active'));
            })->findOrFail($id);
        $childTickets = Ticket::whereIn('ticket_id', $childTicketIds)
            ->where(function ($query) use ($userId, $topicIds) {
                $query->whereIn('request_type_id', $topicIds)
                    ->orWhere('mediator_id', $userId)
                    ->orWhereHas('assignments', fn ($subQuery) => $subQuery->where('user_id', $userId)->where('status', 'active'));
            })
            ->get();

        if ($childTickets->count() !== count($childTicketIds)) {
            return back()->withErrors(['child_ticket_ids' => 'Uno o más tickets seleccionados no están disponibles para asociar.']);
        }
        if ($childTickets->contains(fn ($child) => (int) $child->ticket_id === (int) $parent->ticket_id || $child->parent_ticket_id)) {
            return back()->withErrors(['child_ticket_ids' => 'No puedes seleccionar el ticket principal ni tickets ya asociados.']);
        }
        if (in_array((int) $parent->status, [3, 4], true) || $childTickets->contains(fn ($child) => in_array((int) $child->status, [3, 4], true))) {
            return back()->withErrors(['child_ticket_ids' => 'Solo se pueden asociar tickets abiertos.']);
        }

        $pending = TicketAssociationRequest::where('parent_ticket_id', $parent->ticket_id)
            ->where('requested_by', $userId)
            ->where('status', 'pending')
            ->whereIn('child_ticket_id', $childTickets->pluck('ticket_id'))
            ->pluck('child_ticket_id');

        $newChildren = $childTickets->whereNotIn('ticket_id', $pending);
        if ($newChildren->isEmpty()) {
            return back()->with('success', 'Los tickets seleccionados ya tienen solicitudes pendientes.');
        }

        $requestGroup = (string) Str::uuid();
        foreach ($newChildren as $child) {
            TicketAssociationRequest::create([
                'parent_ticket_id' => $parent->ticket_id,
                'child_ticket_id' => $child->ticket_id,
                'requested_by' => $userId,
                'request_group' => $requestGroup,
                'status' => 'pending',
                'request_note' => $request->input('request_note'),
            ]);
        }

        return back()->with('success', 'Solicitud de asociación enviada para ' . $newChildren->count() . ' ticket(s).');
    }

    /**
     * Actualizar la prioridad del ticket gestionado por el colaborador
     */
    public function setPriority(Request $request, $id)
    {
        $request->validate([
            'priority' => 'required|in:1,2,3,4',
        ]);

        $userId = Auth::id();

        $ticket = Ticket::where('ticket_id', $id)
            ->where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })
            ->firstOrFail();

        if ($this->isFinalizado($ticket)) {
            return back()->withErrors(['priority' => 'No se puede modificar un ticket finalizado.']);
        }

        $ticket->update([
            'priority' => $request->priority,
        ]);

        $priorityNames = [1 => 'Baja', 2 => 'Media', 3 => 'Alta (Afecta operación)', 4 => 'Urgente (Suspende operación)'];
        $slaHours = $ticket->priority_sla_hours;

        return back()->with('success', "Prioridad actualizada a: {$priorityNames[$request->priority]} (Tiempo objetivo: {$slaHours} horas).");
    }

    /**
     * Transferir el ticket a otro gestor mediante un tipo de solicitud o tópico diferente.
     */
    public function transferTicket(Request $request, $id)
    {
        $request->validate([
            'new_request_type_id' => 'required|exists:request_types,type_id',
            'target_mediator_id' => 'nullable|exists:users,user_id',
            'transfer_note' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();

        $ticket = Ticket::with(['requestType', 'mediator'])
            ->where('ticket_id', $id)
            ->where('mediator_id', $userId)
            ->firstOrFail();

        if ($this->isFinalizado($ticket)) {
            return back()->withErrors(['new_request_type_id' => 'No se puede transferir un ticket finalizado.']);
        }

        $supportsCollaboratorAssignments = Schema::hasTable('request_type_user');
        $targetRequestType = RequestType::with($supportsCollaboratorAssignments ? ['gestor', 'collaborators'] : ['gestor'])
            ->where('type_id', $request->new_request_type_id)
            ->where('is_active', true)
            ->firstOrFail();

        $newMediatorId = $request->filled('target_mediator_id')
            ? (int) $request->target_mediator_id
            : $targetRequestType->resolvePreferredMediatorId((int) $ticket->mediator_id);

        $allowedMediatorIds = $supportsCollaboratorAssignments
            ? $targetRequestType->collaborators->pluck('user_id')->map(fn ($id) => (int) $id)
            : collect([$targetRequestType->gestor_id])->filter()->map(fn ($id) => (int) $id);

        if (!$allowedMediatorIds->contains($newMediatorId)) {
            return back()->withErrors([
                'target_mediator_id' => 'El colaborador seleccionado no pertenece al equipo del tópico destino.',
            ])->withInput();
        }

        if (!$newMediatorId) {
            return back()->withErrors([
                'new_request_type_id' => 'El tópico seleccionado no tiene un colaborador disponible para la transferencia.',
            ])->withInput();
        }

        if ((int) $ticket->request_type_id === (int) $targetRequestType->type_id) {
            return back()->withErrors([
                'new_request_type_id' => 'Debes seleccionar un tópico diferente para transferir el ticket.',
            ])->withInput();
        }

        $previousMediatorId = (int) $ticket->mediator_id;

        if ($previousMediatorId === $newMediatorId) {
            return back()->withErrors([
                'new_request_type_id' => 'El tópico seleccionado mantiene el mismo colaborador responsable. Selecciona otro tópico/colaborador.',
            ])->withInput();
        }

        $targetMediator = $supportsCollaboratorAssignments
            ? $targetRequestType->collaborators->firstWhere('user_id', $newMediatorId)
            : null;
        if (!$targetMediator && $targetRequestType->gestor && (int) $targetRequestType->gestor->user_id === $newMediatorId) {
            $targetMediator = $targetRequestType->gestor;
        }

        $targetMediatorName = $targetMediator ? $targetMediator->user_name : 'Gestor asignado';

        DB::transaction(function () use ($ticket, $targetRequestType, $previousMediatorId, $newMediatorId, $userId, $request, $targetMediatorName) {
            $previousTopicName = optional($ticket->requestType)->type_name ?? 'Sin tópico';
            $previousMediatorName = optional($ticket->mediator)->user_name ?? 'Sin gestor';

            $ticket->update([
                'request_type_id' => $targetRequestType->type_id,
                'mediator_id' => $newMediatorId,
            ]);

            TicketAssignment::where('ticket_id', $ticket->ticket_id)
                ->where('user_id', $previousMediatorId)
                ->where('status', 'active')
                ->update([
                    'status' => 'removed',
                    'notes' => 'Transferido a otro gestor el ' . now()->format('d/m/Y H:i'),
                ]);

            $targetAssignment = TicketAssignment::where('ticket_id', $ticket->ticket_id)
                ->where('user_id', $newMediatorId)
                ->latest('assignment_id')
                ->first();

            if ($targetAssignment) {
                $targetAssignment->update([
                    'status' => 'active',
                    'assigned_by' => $userId,
                    'assigned_at' => now(),
                    'notes' => 'Transferencia de ticket desde tópico ' . $previousTopicName,
                ]);
            } else {
                TicketAssignment::create([
                    'ticket_id' => $ticket->ticket_id,
                    'user_id' => $newMediatorId,
                    'job_position_id' => null,
                    'assigned_by' => $userId,
                    'status' => 'active',
                    'notes' => 'Transferencia de ticket desde tópico ' . $previousTopicName,
                    'assigned_at' => now(),
                ]);
            }

            $transferDescription = sprintf(
                'Ticket transferido de %s (%s) a %s (%s).%s',
                $previousTopicName,
                $previousMediatorName,
                $targetRequestType->type_name,
                $targetMediatorName,
                $request->filled('transfer_note') ? ' Nota: ' . $request->transfer_note : ''
            );

            TicketProgress::create([
                'ticket_id' => $ticket->ticket_id,
                'user_id' => $userId,
                'progress_description' => $transferDescription,
                'progress_percentage' => (int) ($ticket->progress_percentage ?? 0),
                'status_update' => 'ticket_transferred',
            ]);
        });

        return redirect()->route('contributors.dashboard')
            ->with('success', 'Ticket transferido exitosamente al nuevo gestor responsable.');
    }

    /**
     * Registrar actualización de progreso
     */
    public function storeProgress(Request $request, $id)
    {
        $request->validate([
            'progress_description' => 'required|string',
            'progress_percentage' => 'integer|min:0|max:100',
        ]);

        $userId = Auth::id();

        $ticket = Ticket::where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })
            ->findOrFail($id);

        if ($this->isFinalizado($ticket)) {
            return back()->withErrors(['progress_description' => 'No se puede agregar notas a un ticket finalizado.']);
        }

        TicketProgress::create([
            'ticket_id' => $id,
            'user_id' => $userId,
            'progress_description' => $request->progress_description,
            'progress_percentage' => $request->progress_percentage ?? 0,
            'status_update' => $request->status_update ?? 'manual_note',
        ]);

        return redirect()->route('contributors.tickets.show', $id)
            ->with('success', 'Nota registrada exitosamente.');
    }

    /**
     * Enviar mensaje del colaborador al solicitante dentro del seguimiento del ticket.
     */
    public function sendMessageToRequester(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|min:3|max:1500',
        ]);

        $userId = Auth::id();

        $ticket = Ticket::where('ticket_id', $id)
            ->where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })
            ->firstOrFail();

        if ($this->isFinalizado($ticket)) {
            return back()->withErrors(['message' => 'La comunicación está deshabilitada para tickets cerrados o terminados.']);
        }

        TicketProgress::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $userId,
            'progress_description' => trim($request->message),
            'progress_percentage' => (int) ($ticket->progress_percentage ?? 0),
            'status_update' => 'collaborator_message',
        ]);

        try {
            $ticket->loadMissing(['requester', 'mediator']);
            if ($ticket->requester && $ticket->requester->user_email) {
                $senderName = optional($ticket->mediator)->user_name ?? 'Colaborador';
                Mail::to($ticket->requester->user_email)
                    ->send(new CollaboratorMessageReceived($ticket, trim($request->message), $senderName));
            }
        } catch (\Throwable $e) {
            \Log::error('Error sending collaborator message email: ' . $e->getMessage(), [
                'ticket_id' => $ticket->ticket_id,
                'user_id' => $userId,
            ]);
        }

        return back()->with('success', 'Mensaje enviado al solicitante correctamente.');
    }

    /**
     * Actualizar el estado de una tarea (arrastrar y soltar Kanban)
     */
    public function updateTaskStatus(Request $request, $taskId)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);

        $task = ProjectTask::findOrFail($taskId);

        $userId = Auth::id();
        $ticketId = $task->ticket_id;

        // Verificar que el usuario tiene acceso a este ticket
        $ticket = Ticket::where('ticket_id', $ticketId)
            ->where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })->first();

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para actualizar esta tarea.'], 403);
        }

        if ($this->isFinalizado($ticket)) {
            return response()->json(['success' => false, 'message' => 'No se puede modificar un ticket finalizado.'], 422);
        }

        $previousStatus = $task->status;
        $task->update(['status' => $request->status]);

        if ($ticket->status == 1 && in_array($request->status, ['in_progress', 'review', 'done'])) {
            $ticket->update(['status' => 2]);
        }
        $this->recalculateTicketProgress($ticket);

        // Registrar el evento de historial solo cuando una tarea se completa por primera vez.
        if ($previousStatus !== 'done' && $request->status === 'done') {
            $ticket->refresh();
            $this->logAutoProgressEvent(
                $ticketId,
                $userId,
                "Tarea completada: {$task->title}",
                'task_completed',
                (int) ($ticket->progress_percentage ?? 0)
            );
        }

        return response()->json(['success' => true, 'message' => 'Estado de la tarea actualizado.']);
    }

    /**
     * Registrar un nuevo sprint para un ticket gestionado por el colaborador
     */
    public function storeSprint(Request $request, $ticketId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'goal' => 'nullable|string',
        ]);

        $userId = Auth::id();

        $ticket = Ticket::where('ticket_id', $ticketId)
            ->where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })
            ->firstOrFail();

        if ($this->isFinalizado($ticket)) {
            return back()->withErrors(['name' => 'No se puede crear un sprint en un ticket finalizado.']);
        }

        Sprint::create([
            'ticket_id' => $ticket->ticket_id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'goal' => $request->goal,
            'status' => 'planned',
        ]);

        return back()->with('success', 'Sprint creado exitosamente.');
    }

    /**
     * Store a new project task for a contributor-managed ticket
     */
    public function storeTask(Request $request, $ticketId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'sprint_id' => 'nullable|exists:sprints,sprint_id',
            'assigned_to' => 'nullable|exists:users,user_id',
        ]);

        if ($request->sprint_id) {
            $completedSprint = Sprint::where('sprint_id', $request->sprint_id)
                ->where('status', 'completed')
                ->exists();

            if ($completedSprint) {
                return back()->withErrors(['sprint_id' => 'No se puede asignar una tarea a un sprint completado.'])->withInput();
            }
        }

        $userId = Auth::id();

        $ticket = Ticket::where('ticket_id', $ticketId)
            ->where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })
            ->firstOrFail();

        if ($this->isFinalizado($ticket)) {
            return back()->withErrors(['title' => 'No se puede crear una tarea en un ticket finalizado.']);
        }

        ProjectTask::create([
            'ticket_id' => $ticket->ticket_id,
            'sprint_id' => $request->sprint_id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'assigned_to' => $request->assigned_to,
            'status' => 'todo',
        ]);

        return back()->with('success', 'Tarea creada exitosamente.');
    }

    /**
     * Update sprint status for a contributor-managed ticket
     */
    public function updateSprintStatus(Request $request, $sprintId)
    {
        $request->validate([
            'status' => 'required|in:planned,active,completed',
        ]);

        $sprint = Sprint::findOrFail($sprintId);
        $userId = Auth::id();

        $hasAccess = Ticket::where('ticket_id', $sprint->ticket_id)
            ->where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })->first();

        if (!$hasAccess) {
            return back()->withErrors(['status' => 'No tienes permiso para actualizar este sprint.']);
        }

        if ($this->isFinalizado($hasAccess)) {
            return back()->withErrors(['status' => 'No se puede modificar un sprint de un ticket finalizado.']);
        }

        $previousStatus = $sprint->status;

        if ($request->status == 'active') {
            Sprint::where('ticket_id', $sprint->ticket_id)
                ->where('status', 'active')
                ->where('sprint_id', '!=', $sprintId)
                ->update(['status' => 'completed']);
        }

        $sprint->update(['status' => $request->status]);

        $ticket = $hasAccess;
        $this->recalculateTicketProgress($ticket);

        // Log history event only when sprint transitions to completed.
        if ($previousStatus !== 'completed' && $request->status === 'completed') {
            $ticket->refresh();
            $this->logAutoProgressEvent(
                $sprint->ticket_id,
                $userId,
                "Sprint completado: {$sprint->name}",
                'sprint_completed',
                (int) ($ticket->progress_percentage ?? 0)
            );
        }

        return back()->with('success', 'Estado del sprint actualizado.');
    }

    /**
     * Assign an existing task to a non-completed sprint
     */
    public function assignTaskSprint(Request $request, $taskId)
    {
        $request->validate([
            'sprint_id' => 'required|exists:sprints,sprint_id',
        ]);

        $task = ProjectTask::findOrFail($taskId);
        $userId = Auth::id();

        $ticket = Ticket::where('ticket_id', $task->ticket_id)
            ->where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })
            ->firstOrFail();

        if ($this->isFinalizado($ticket)) {
            return back()->withErrors(['sprint_id' => 'No se puede reasignar tareas en un ticket finalizado.']);
        }

        $sprint = Sprint::where('sprint_id', $request->sprint_id)
            ->where('ticket_id', $task->ticket_id)
            ->firstOrFail();

        if ($sprint->status === 'completed') {
            return back()->withErrors(['sprint_id' => 'No se puede asignar una tarea a un sprint completado.']);
        }

        $task->update(['sprint_id' => $sprint->sprint_id]);

        return back()->with('success', 'Tarea asignada al sprint correctamente.');
    }

    /**
     * Close ticket (only allowed when progress is 100%)
     */
    public function closeTicket(Request $request, $ticketId)
    {
        $request->validate([
            'solution_detail' => 'required|string|min:10',
            'resource_link' => 'nullable|url',
            'final_evidence_files' => 'nullable|array|max:5',
            'final_evidence_files.*' => 'file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,webp',
        ]);

        $userId = Auth::id();

        $ticket = Ticket::with('sprints.tasks')
            ->where('ticket_id', $ticketId)
            ->where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })
            ->firstOrFail();

        if ($this->isFinalizado($ticket)) {
            return back()->withErrors(['close' => 'Este ticket ya está finalizado.']);
        }

        $autoProgress = $this->computeAutoProgress($ticket);

        if ($autoProgress < 100) {
            return back()->withErrors(['close' => 'No puedes cerrar el ticket hasta que el avance sea del 100%.']);
        }

        if ($ticket->current_phase !== 'Evaluation') {
            return back()->withErrors(['close' => 'No puedes cerrar el ticket hasta estar en la fase final de ADDIE (Evaluación).']);
        }

        $ticket->update([
            'status' => 3,
            'progress_percentage' => 100,
            'resource_link' => $request->resource_link,
        ]);

        $finalEvidenceIds = $request->hasFile('final_evidence_files')
            ? app(FinalTicketEvidenceService::class)->store($ticket, $request->file('final_evidence_files'), $userId)
            : [];

        TicketProgress::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $userId,
            'progress_description' => 'Cierre del servicio: ' . $request->solution_detail
                . ($finalEvidenceIds ? "\n\n[attachments:" . implode(',', $finalEvidenceIds) . "]" : ''),
            'progress_percentage' => 100,
            'status_update' => 'service_closed',
        ]);

        app(TicketClosurePropagationService::class)->closeChildren(
            $ticket,
            $request->solution_detail,
            $request->resource_link
        );

        // Send email to requester
        try {
            if ($ticket->requester && $ticket->requester->user_email) {
                Mail::to($ticket->requester->user_email)->send(new \App\Mail\TicketClosed($ticket));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error sending closure email: ' . $e->getMessage());
        }

        return redirect()->route('contributors.dashboard')
            ->with('success', 'Servicio cerrado exitosamente.');
    }

    /**
     * Compute auto progress from sprint tasks (% of done tasks across all sprint tasks)
     */
    private function computeAutoProgress($ticket)
    {
        $sprintTasks = $ticket->sprints->flatMap(fn($s) => $s->tasks);
        $total = $sprintTasks->count();
        if ($total === 0) return 0;
        $done = $sprintTasks->where('status', 'done')->count();
        return (int) round(($done / $total) * 100);
    }

    /**
     * Recalculate and update ticket progress_percentage based on sprint tasks
     */
    private function recalculateTicketProgress($ticket)
    {
        $ticket->load('sprints.tasks');
        $progress = $this->computeAutoProgress($ticket);
        $updateData = ['progress_percentage' => $progress];
        if ($progress === 100 && $ticket->status == 2) {
            // Keep status as in_progress until manually closed
        } elseif ($progress > 0 && $ticket->status == 1) {
            $updateData['status'] = 2;
        }
        $ticket->update($updateData);
    }

    /**
     * Persist an automatic history entry related to task/sprint completion.
     */
    private function logAutoProgressEvent($ticketId, $userId, $description, $statusUpdate, $progressPercentage)
    {
        TicketProgress::create([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'progress_description' => $description,
            'progress_percentage' => $progressPercentage,
            'status_update' => $statusUpdate,
        ]);
    }

    /**
     * Update ADDIE phase for a contributor-managed ticket
     */
    public function updatePhase(Request $request, $ticketId)
    {
        $request->validate([
            'phase' => 'required|in:Analysis,Design,Development,Implementation,Evaluation',
        ]);

        $userId = Auth::id();

        $ticket = Ticket::where('ticket_id', $ticketId)
            ->where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })
            ->firstOrFail();

        if ($this->isFinalizado($ticket)) {
            return back()->withErrors(['phase' => 'No se puede modificar la fase de un ticket finalizado.']);
        }

        $ticket->update(['current_phase' => $request->phase]);

        return back()->with('success', 'Fase del proyecto actualizada correctamente.');
    }

    /**
     * Determines whether a ticket is in a finalized state (Completado or Cancelado).
     * No modifications should be allowed on finalized tickets.
     */
    private function isFinalizado(Ticket $ticket): bool
    {
        return in_array((int) $ticket->status, [3, 4], true);
    }

    /**
     * Resolver IDs de tópicos donde el colaborador puede tomar tickets.
     */
    private function resolveContributorTopicIds(int $userId)
    {
        $topicIds = collect();

        if (Schema::hasTable('request_type_user')) {
            $topicIds = RequestType::where(function ($query) use ($userId) {
                    $query->whereHas('collaborators', function ($collaboratorsQuery) use ($userId) {
                        $collaboratorsQuery->where('users.user_id', $userId);
                    })->orWhere('gestor_id', $userId);
                })
                ->pluck('type_id');
        } else {
            $topicIds = RequestType::where('gestor_id', $userId)->pluck('type_id');
        }

        if ($this->supportsRegionalAssignments()) {
            $regionalTopicIds = DB::table('request_type_regional_assignments')
                ->where('user_id', $userId)
                ->pluck('request_type_id');

            $topicIds = $topicIds
                ->merge($regionalTopicIds)
                ->unique()
                ->values();
        }

        return $topicIds;
    }

    /**
     * Base query for contributor topic queue tickets.
     */
    private function buildTopicQueueQuery($topicIds, ?int $userId = null)
    {
        $userId = $userId ?? (int) Auth::id();

        $query = Ticket::whereIn('request_type_id', $topicIds)
            ->whereNotIn('status', [3, 4]);

        $this->applyRegionalQueueScope($query, $userId);

        return $query;
    }

    private function supportsRegionalAssignments(): bool
    {
        return Schema::hasTable('request_type_regional_assignments');
    }

    /**
     * For regionalized topics, only show tickets from regionals explicitly assigned to the contributor.
     */
    private function applyRegionalQueueScope($query, int $userId): void
    {
        if (!$this->supportsRegionalAssignments()) {
            return;
        }

        $query->where(function ($scope) use ($userId) {
            $scope->whereNotExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('request_type_regional_assignments as regional_any')
                    ->whereColumn('regional_any.request_type_id', 'tickets.request_type_id');
            })->orWhereExists(function ($subQuery) use ($userId) {
                $subQuery->select(DB::raw(1))
                    ->from('request_type_regional_assignments as regional_user')
                    ->whereColumn('regional_user.request_type_id', 'tickets.request_type_id')
                    ->whereColumn('regional_user.institution_id', 'tickets.institution_id')
                    ->where('regional_user.user_id', $userId);
            });
        });
    }

    private function ticketHasActiveContributor(Ticket $ticket): bool
    {
        if ($ticket->mediator?->role?->role_name === 'Contributor') {
            return true;
        }

        return $ticket->assignments
            ->where('status', 'active')
            ->contains(fn ($assignment) => $assignment->mediator?->role?->role_name === 'Contributor');
    }
}
