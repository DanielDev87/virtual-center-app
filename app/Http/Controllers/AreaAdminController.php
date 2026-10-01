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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use App\Models\Institution;
use App\Services\TicketClosurePropagationService;
use App\Services\FinalTicketEvidenceService;
use App\Models\TicketAssociationRequest;
use App\Mail\TicketAssociationRequestNeedsReview;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AreaAdminController extends Controller
{
    /**
     * Obtener el area_id del usuario autenticado.
     */
    private function areaId(): ?int
    {
        return Auth::user()->area_id ? (int) Auth::user()->area_id : null;
    }

    /**
     * Scope base para tickets del área del admin.
     * Filtra por request_types cuyo area_id coincida con el del admin.
     */
    private function areaTicketsQuery()
    {
        $areaId = $this->areaId();
        $userId = (int) Auth::id();

        $query = Ticket::whereHas('requestType', function ($q) use ($areaId) {
            $q->where('area_id', $areaId);
        });

        if (!Schema::hasTable('request_type_regional_assignments')) {
            return $query;
        }

        // Si un tópico no maneja regionales, se conserva el comportamiento actual por área.
        // Si maneja regionales, el Admin Área solo ve tickets de la(s) regional(es) donde esté asignado.
        return $query->where(function ($ticketQuery) use ($userId) {
            $ticketQuery->whereHas('requestType', function ($requestTypeQuery) {
                $requestTypeQuery->whereDoesntHave('regionalAssignments');
            })->orWhereHas('requestType.regionalAssignments', function ($regionalAssignmentsQuery) use ($userId) {
                $regionalAssignmentsQuery
                    ->where('user_id', $userId)
                    ->whereColumn('request_type_regional_assignments.institution_id', 'tickets.institution_id');
            });
        });
    }

    /**
     * Tópicos visibles para el Admin Área según regionalización.
     */
    private function visibleAreaRequestTypesQuery()
    {
        $areaId = $this->areaId();
        $userId = (int) Auth::id();

        $query = RequestType::where('area_id', $areaId)
            ->where('is_active', true);

        if (!Schema::hasTable('request_type_regional_assignments')) {
            return $query;
        }

        return $query->where(function ($requestTypeQuery) use ($userId) {
            $requestTypeQuery->whereDoesntHave('regionalAssignments')
                ->orWhereHas('regionalAssignments', function ($regionalAssignmentsQuery) use ($userId) {
                    $regionalAssignmentsQuery->where('user_id', $userId);
                });
        });
    }

    /**
     * Obtiene alcance regional visible para el Admin Área en su área.
     */
    private function regionalScopeMeta(): array
    {
        if (!Schema::hasTable('request_type_regional_assignments')) {
            return [
                'isRegionalScoped' => false,
                'regionalScopeNames' => collect(),
            ];
        }

        $areaId = $this->areaId();
        $userId = (int) Auth::id();

        $topicIds = RequestType::where('area_id', $areaId)->pluck('type_id');

        $institutionIds = DB::table('request_type_regional_assignments')
            ->whereIn('request_type_id', $topicIds)
            ->where('user_id', $userId)
            ->pluck('institution_id')
            ->unique()
            ->values();

        $regionalScopeNames = Institution::whereIn('institution_id', $institutionIds)
            ->orderBy('institution_name')
            ->pluck('institution_name');

        return [
            'isRegionalScoped' => $regionalScopeNames->isNotEmpty(),
            'regionalScopeNames' => $regionalScopeNames,
        ];
    }

    /**
     * Calcular el progreso automático desde las tareas de sprint (% completadas sobre el total).
     */
    private function computeAutoProgress($ticket): int
    {
        $sprintTasks = $ticket->sprints->flatMap(fn ($sprint) => $sprint->tasks);
        $total = $sprintTasks->count();

        if ($total === 0) {
            return (int) ($ticket->progress_percentage ?? 0);
        }

        $done = $sprintTasks->where('status', 'done')->count();
        return (int) round(($done / $total) * 100);
    }

    /**
     * Dashboard del admin de área.
     */
    public function dashboard(Request $request)
    {
        $areaId = $this->areaId();
        $user   = Auth::user();

        if (!$areaId) {
            return redirect()->route('profile.edit')
                ->with('error', 'Tu usuario no tiene un área asignada. Contacta al administrador.');
        }

        $area = $user->area;
        $regionalScope = $this->regionalScopeMeta();

        // Tópicos visibles para el admin del área (respeta regionalización cuando aplique).
        $areaTopics = $this->visibleAreaRequestTypesQuery()->pluck('type_id');
        $incidentTopics = $this->visibleAreaRequestTypesQuery()->orderBy('type_name')->get(['type_id', 'type_name', 'incident_active', 'incident_title', 'incident_message']);

        // Estadísticas filtradas por área
        $baseQuery = fn () => $this->areaTicketsQuery();

        $ticketsForProgress = $baseQuery()
            ->with('sprints.tasks')
            ->where('status', '!=', 4)
            ->get();

        $avgProgress = $ticketsForProgress->count() > 0
            ? $ticketsForProgress->map(fn ($t) => $this->computeAutoProgress($t))->avg()
            : 0;

        $stats = [
            'total_tickets'       => $baseQuery()->count(),
            'pending_tickets'     => $baseQuery()->where('status', 1)->count(),
            'in_progress_tickets' => $baseQuery()->where('status', 2)->count(),
            'completed_tickets'   => $baseQuery()->where('status', 3)->count(),
            'avg_progress'        => $avgProgress,
            'high_priority'       => $baseQuery()->whereIn('priority', [3, 4])->whereIn('status', [1, 2])->count(),
            'avg_rating'          => $baseQuery()->where('status', 3)->whereNotNull('rating')->avg('rating'),
        ];

        // Tickets recientes del área
        $recentTickets = $baseQuery()
            ->with(['requester', 'mediator', 'requestType', 'sprints.tasks'])
            ->orderByDesc('priority')
            ->latest()
            ->paginate(10);

        $recentTickets->getCollection()->transform(function ($ticket) {
            $ticket->auto_progress = $this->computeAutoProgress($ticket);
            return $ticket;
        });

        $returnedTickets = TicketAssignment::with(['ticket.requestType', 'ticket.mediator'])
            ->where('assigned_by', $user->user_id)
            ->where('status', 'removed')
            ->where('notes', 'like', 'Devuelto por operario:%')
            ->whereNull('returned_alert_read_at')
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->filter(fn ($assignment) => $assignment->ticket && $this->areaTicketsQuery()->whereKey($assignment->ticket_id)->exists())
            ->values();

        $associationRequests = TicketAssociationRequest::with(['parentTicket', 'requester'])
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->filter(fn ($associationRequest) => $associationRequest->parentTicket
                && $this->areaTicketsQuery()->whereKey($associationRequest->parent_ticket_id)->exists())
            ->groupBy(fn ($associationRequest) => $associationRequest->request_group ?: 'single-' . $associationRequest->association_request_id)
            ->map(fn ($requests) => $requests->first())
            ->take(10)
            ->values();

        // Tickets por estado
        $ticketsByStatus = [
            'Pendiente'   => $baseQuery()->where('status', 1)->count(),
            'En Progreso' => $baseQuery()->where('status', 2)->count(),
            'Realizado por Operario' => $baseQuery()->where('status', 5)->count(),
            'Completado'  => $baseQuery()->where('status', 3)->count(),
            'Cancelado'   => $baseQuery()->where('status', 4)->count(),
        ];

        // Tickets por tópico dentro del área
        $ticketsByTopic = $baseQuery()
            ->with('requestType')
            ->select('request_type_id', DB::raw('count(*) as count'))
            ->whereNotNull('request_type_id')
            ->groupBy('request_type_id')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->requestType->type_name ?? 'Sin tipo' => $item->count]);

        // Calificaciones recientes
        $recentRatings = $baseQuery()
            ->with(['requester', 'requestType'])
            ->where('status', 3)
            ->whereNotNull('rating')
            ->latest()
            ->limit(5)
            ->get();

        // Distribución de calificaciones (1-5 estrellas) del área
        $ratingDistribution = $baseQuery()
            ->where('status', 3)
            ->whereNotNull('rating')
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->rating . ' ★' => $item->count]);

        // Tickets más rápidos del área
        $fastestTickets = $baseQuery()
            ->where('status', 3)
            ->whereNotNull('updated_at')
            ->with(['requester', 'requestType'])
            ->select('*', DB::raw('TIMESTAMPDIFF(HOUR, created_at, updated_at) as completion_hours'))
            ->orderBy('completion_hours', 'asc')
            ->limit(5)
            ->get();

        // Evolución mensual por tópico del área
        $allowedTopicTrendMonths = [3, 6, 12];
        $topicTrendMonths = (int) $request->query('topic_trend_months', 6);
        if (!in_array($topicTrendMonths, $allowedTopicTrendMonths, true)) {
            $topicTrendMonths = 6;
        }

        $monthKeys = collect(range(0, $topicTrendMonths - 1))
            ->map(fn ($offset) => now()->copy()->subMonths(($topicTrendMonths - 1) - $offset)->startOfMonth()->format('Y-m'));

        $topicTrendLabels = collect(range(0, $topicTrendMonths - 1))
            ->map(fn ($offset) => now()->copy()->subMonths(($topicTrendMonths - 1) - $offset)->startOfMonth()->format('m/Y'));

        $visibleTicketIds = $baseQuery()->select('tickets.ticket_id');

        $topicMonthlyRaw = Ticket::leftJoin('request_types', 'tickets.request_type_id', '=', 'request_types.type_id')
            ->selectRaw("DATE_FORMAT(tickets.created_at, '%Y-%m') as month_key")
            ->selectRaw("COALESCE(request_types.type_name, 'Sin tópico') as topic_name")
            ->selectRaw('COUNT(*) as total')
            ->whereIn('tickets.ticket_id', $visibleTicketIds)
            ->whereDate('tickets.created_at', '>=', now()->copy()->subMonths($topicTrendMonths - 1)->startOfMonth())
            ->groupBy('month_key', 'topic_name')
            ->orderBy('month_key')
            ->get();

        $topTopicNames = $topicMonthlyRaw
            ->groupBy('topic_name')
            ->map(fn ($rows) => (int) $rows->sum('total'))
            ->sortDesc()
            ->take(5)
            ->keys()
            ->values();

        $topicTrendDatasets = $topTopicNames->map(function ($topicName) use ($topicMonthlyRaw, $monthKeys) {
            $data = $monthKeys->map(function ($monthKey) use ($topicMonthlyRaw, $topicName) {
                $match = $topicMonthlyRaw->first(fn ($row) => $row->topic_name === $topicName && $row->month_key === $monthKey);
                return $match ? (int) $match->total : 0;
            });
            return ['label' => $topicName, 'data' => $data->values()->all()];
        });

        return view('area-admin.dashboard', compact(
            'area',
            'incidentTopics',
            'stats',
            'recentTickets',
            'returnedTickets',
            'associationRequests',
            'ticketsByStatus',
            'ticketsByTopic',
            'recentRatings',
            'ratingDistribution',
            'fastestTickets',
            'topicTrendLabels',
            'topicTrendDatasets',
            'topicTrendMonths',
            'allowedTopicTrendMonths',
            'regionalScope'
        ));
    }

    public function updateTopicIncident(Request $request, $id)
    {
        $request->validate([
            'incident_active' => 'nullable|boolean',
            'incident_title' => 'required_if:incident_active,1|nullable|string|max:255',
            'incident_message' => 'required_if:incident_active,1|nullable|string|max:2000',
        ]);

        $topic = $this->visibleAreaRequestTypesQuery()->where('type_id', $id)->firstOrFail();
        $active = $request->boolean('incident_active');
        $topic->update([
            'incident_active' => $active,
            'incident_title' => $active ? trim((string) $request->incident_title) : null,
            'incident_message' => $active ? trim((string) $request->incident_message) : null,
            'incident_started_at' => $active ? ($topic->incident_started_at ?: now()) : null,
        ]);

        return back()->with('success', $active ? 'La alerta del tópico quedó activa.' : 'La alerta del tópico fue desactivada.');
    }

    /**
     * Listado de tickets del área.
     */
    public function tickets(Request $request)
    {
        $areaId = $this->areaId();

        if (!$areaId) {
            return redirect()->route('area-admin.dashboard')
                ->with('error', 'Tu usuario no tiene un área asignada.');
        }

        $query = $this->areaTicketsQuery()
            ->with(['requester', 'mediator', 'requestType', 'sprints.tasks']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('request_type_id')) {
            $query->where('request_type_id', $request->request_type_id);
        }

        if ($request->filled('mediator_id')) {
            $query->where('mediator_id', $request->mediator_id);
        }

        $areaTopics = $this->visibleAreaRequestTypesQuery()
            ->get(['type_id', 'type_name']);

        // Mediadores que tienen al menos un ticket en el área
        $areaMediators = User::whereIn(
            'user_id',
            $this->areaTicketsQuery()->whereNotNull('mediator_id')->pluck('mediator_id')
        )->orderBy('user_name')->get(['user_id', 'user_name']);

        $tickets = $query->orderByDesc('priority')->latest()->paginate(15)->withQueryString();
        $regionalScope = $this->regionalScopeMeta();

        $tickets->getCollection()->transform(function ($ticket) {
            $ticket->auto_progress = $this->computeAutoProgress($ticket);
            return $ticket;
        });

        return view('area-admin.tickets.index', compact('tickets', 'areaTopics', 'areaMediators', 'regionalScope'));
    }

    /**
     * Detalle de un ticket del área.
     * 403 si el ticket no pertenece al área del admin.
     */
    public function showTicket($id)
    {
        $ticket = $this->areaTicketsQuery()
            ->with([
                'requester',
                'mediator',
                'requestType.collaborators',
                'requestType.regionalAssignments',
                'associationRequests.requester',
                'evidences',
                'assignments.mediator',
                'assignments.jobPosition',
                'progress.user',
                'sprints.tasks',
                'requestType',
            ])
            ->findOrFail($id);

        $autoProgress = $this->computeAutoProgress($ticket);

        $mediators = User::with('jobPositions')
            ->whereIn('user_id', $this->topicMediatorIds($ticket))
            ->where('is_active', true)
            ->orderBy('user_name')
            ->get();

        $jobPositions = JobPosition::where('is_active', true)->get();
        $availableTickets = $this->areaTicketsQuery()
            ->whereNotIn('status', [3, 4])
            ->whereNull('parent_ticket_id')
            ->where('tickets.ticket_id', '!=', $ticket->ticket_id)
            ->orderByDesc('priority')->latest('ticket_id')->take(100)
            ->get(['tickets.ticket_id', 'ticket_number', 'title']);

        return view('area-admin.tickets.show', compact('ticket', 'mediators', 'jobPositions', 'availableTickets', 'autoProgress'));
    }

    public function approveAssociationRequest(Request $request, $associationRequestId)
    {
        return $this->reviewAssociationRequest($request, $associationRequestId, 'approved');
    }

    public function rejectAssociationRequest(Request $request, $associationRequestId)
    {
        return $this->reviewAssociationRequest($request, $associationRequestId, 'rejected');
    }

    private function reviewAssociationRequest(Request $request, $associationRequestId, string $decision)
    {
        $request->validate(['review_note' => 'nullable|string|max:500']);
        $associationRequest = TicketAssociationRequest::with(['parentTicket', 'childTicket'])
            ->where('association_request_id', $associationRequestId)
            ->where('status', 'pending')
            ->firstOrFail();
        $parent = $this->areaTicketsQuery()->findOrFail($associationRequest->parent_ticket_id);
        $groupRequests = $associationRequest->request_group
            ? TicketAssociationRequest::where('request_group', $associationRequest->request_group)
                ->where('status', 'pending')->get()
            : collect([$associationRequest]);
        $childIds = $groupRequests->pluck('child_ticket_id')->unique()->values();
        $children = $this->areaTicketsQuery()->whereIn('ticket_id', $childIds)->get();

        if ($children->count() !== $childIds->count()) {
            $outOfScopeReason = 'Uno o más tickets seleccionados están fuera de tu área o regional autorizada. Verifica el tópico y el equipo del ticket relacionado antes de reenviar la solicitud.';

            TicketAssociationRequest::whereIn('association_request_id', $groupRequests->pluck('association_request_id'))
                ->update([
                    'status' => 'rejected',
                    'reviewed_by' => Auth::id(),
                    'review_note' => $outOfScopeReason,
                    'reviewed_at' => now(),
                ]);

            $requesters = User::whereIn('user_id', $groupRequests->pluck('requested_by')->unique())->get();
            foreach ($requesters as $requester) {
                if (!$requester->user_email) {
                    continue;
                }

                try {
                    Mail::to($requester->user_email)->send(new TicketAssociationRequestNeedsReview($parent, $outOfScopeReason));
                } catch (\Throwable $exception) {
                    Log::error('Error sending association out-of-scope email: ' . $exception->getMessage(), [
                        'association_request_id' => $associationRequestId,
                        'requester_id' => $requester->user_id,
                    ]);
                }
            }

            return redirect()->route('area-admin.dashboard')
                ->with('error', 'No se pudo procesar la solicitud porque uno o más tickets están fuera de tu área o regional autorizada.');
        }

        DB::transaction(function () use ($groupRequests, $parent, $children, $decision, $request) {
            TicketAssociationRequest::whereIn('association_request_id', $groupRequests->pluck('association_request_id'))
                ->update([
                    'status' => $decision,
                    'reviewed_by' => Auth::id(),
                    'review_note' => $request->input('review_note'),
                    'reviewed_at' => now(),
                ]);

            if ($decision === 'approved') {
                foreach ($children as $child) {
                    if (!$child->parent_ticket_id) {
                        $child->update(['parent_ticket_id' => $parent->ticket_id]);
                    }
                }
            }
        });

        $count = $groupRequests->count();
        return back()->with('success', $decision === 'approved'
            ? "Se aprobaron {$count} asociación(es)."
            : "Se rechazaron {$count} asociación(es).");
    }

    public function associateTicket(Request $request, $id)
    {
        $request->validate(['child_ticket_id' => 'required|exists:tickets,ticket_id']);
        $parent = $this->areaTicketsQuery()->findOrFail($id);
        $child = $this->areaTicketsQuery()->whereKey($request->child_ticket_id)->firstOrFail();

        if ($parent->ticket_id === $child->ticket_id || $child->parent_ticket_id) {
            return back()->withErrors(['child_ticket_id' => 'El ticket seleccionado ya tiene una asociación o es el mismo ticket.']);
        }
        if (in_array((int) $parent->status, [3, 4], true) || in_array((int) $child->status, [3, 4], true)) {
            return back()->withErrors(['child_ticket_id' => 'Solo se pueden asociar tickets abiertos.']);
        }

        $child->update(['parent_ticket_id' => $parent->ticket_id]);
        return back()->with('success', 'Ticket asociado al ticket principal.');
    }

    /**
     * Asignar mediador a ticket del área.
     */
    public function assignMediator(Request $request, $id)
    {
        $request->validate([
            'mediator_id' => 'required|exists:users,user_id',
        ]);

        $ticket = $this->areaTicketsQuery()
            ->with(['requestType.collaborators', 'requestType.regionalAssignments'])
            ->findOrFail($id);

        if ($this->isLocked($ticket)) {
            return back()->with('error', 'No se puede modificar un ticket cerrado o cancelado. Debes reabrirlo primero.');
        }

        if (!$this->topicMediatorIds($ticket)->contains((int) $request->mediator_id)) {
            return back()->withErrors(['mediator_id' => 'El usuario seleccionado no pertenece al equipo del tópico.']);
        }

        $ticket->update([
            'mediator_id' => $request->mediator_id,
            'status'      => 2,
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
     * Asignar mediador adicional al ticket (multi-asignación).
     */
    public function assignMediatorToTicket(Request $request, $id)
    {
        $request->validate([
            'mediator_id'     => 'required|exists:users,user_id',
            'job_position_id' => 'nullable|exists:job_positions,job_position_id',
            'notes'           => 'nullable|string|max:500',
        ]);

        $ticket = $this->areaTicketsQuery()
            ->with(['requestType.collaborators', 'requestType.regionalAssignments'])
            ->findOrFail($id);

        if ($this->isLocked($ticket)) {
            return back()->with('error', 'No se puede modificar un ticket cerrado o cancelado. Debes reabrirlo primero.');
        }

        if (!$this->topicMediatorIds($ticket)->contains((int) $request->mediator_id)) {
            return back()->withErrors(['mediator_id' => 'El usuario seleccionado no pertenece al equipo del tópico.']);
        }

        $existing = TicketAssignment::where('ticket_id', $ticket->ticket_id)
            ->where('user_id', $request->mediator_id)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return back()->with('error', 'Este mediador ya está asignado activamente a este ticket.');
        }

        TicketAssignment::create([
            'ticket_id'       => $ticket->ticket_id,
            'user_id'         => $request->mediator_id,
            'job_position_id' => $request->job_position_id,
            'assigned_by'     => Auth::id(),
            'assigned_at'     => now(),
            'notes'           => $request->notes,
            'status'          => 'active',
        ]);

        if ($ticket->status === 1) {
            $ticket->update(['status' => 2]);
        }

        return back()->with('success', 'Mediador asignado al ticket exitosamente.');
    }

    /**
     * Eliminar asignación de mediador del ticket.
     */
    public function removeAssignment($ticketId, $assignmentId)
    {
        $ticket = $this->areaTicketsQuery()->findOrFail($ticketId);

        if ($this->isLocked($ticket)) {
            return back()->with('error', 'No se puede modificar un ticket cerrado o cancelado. Debes reabrirlo primero.');
        }

        $assignment = TicketAssignment::where('ticket_id', $ticket->ticket_id)
            ->findOrFail($assignmentId);

        $assignment->update(['status' => 'removed']);

        return back()->with('success', 'Asignación eliminada exitosamente.');
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

    /**
     * Establecer la prioridad del ticket.
     */
    public function setPriority(Request $request, $id)
    {
        $request->validate([
            'priority' => 'required|in:1,2,3,4',
        ]);

        $ticket = $this->areaTicketsQuery()->findOrFail($id);

        if ($this->isLocked($ticket)) {
            return back()->with('error', 'No se puede modificar un ticket cerrado o cancelado. Debes reabrirlo primero.');
        }

        $ticket->update(['priority' => $request->priority]);

        $priorityNames = [1 => 'Baja', 2 => 'Media', 3 => 'Alta (Afecta operación)', 4 => 'Urgente (Suspende operación)'];
        return back()->with('success', "Prioridad actualizada a: {$priorityNames[$request->priority]}.");
    }

    /**
     * Cerrar o cancelar un ticket del área.
     */
    public function closeTicket(Request $request, $id)
    {
        $request->validate([
            'status'              => 'required|in:3,4',
            'solution_detail'     => 'exclude_unless:status,3|required|string|min:10',
            'cancellation_reason' => 'exclude_unless:status,4|required|string|min:10|max:500',
            'resource_link'       => 'exclude_unless:status,3|nullable|url',
            'final_evidence_files' => 'nullable|array|max:5',
            'final_evidence_files.*' => 'file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,webp',
        ]);

        $ticket = $this->areaTicketsQuery()->with('sprints.tasks')->findOrFail($id);

        if ($this->isLocked($ticket)) {
            return back()->with('error', 'No se puede modificar un ticket cerrado o cancelado. Debes reabrirlo primero.');
        }

        $autoProgress = $this->computeAutoProgress($ticket);

        if ($request->status == 3) {
            if ($autoProgress < 100) {
                return back()->with('error', 'No se puede cerrar el ticket. El progreso debe estar al 100%.');
            }

            if ($ticket->current_phase !== 'Evaluation') {
                return back()->with('error', 'No se puede cerrar el ticket. Debe estar en la fase final de ADDIE (Evaluación).');
            }

            $ticket->update([
                'status'          => $request->status,
                'progress_percentage' => $autoProgress,
                'resource_link'   => $request->resource_link,
            ]);

            $finalEvidenceIds = $request->hasFile('final_evidence_files')
                ? app(FinalTicketEvidenceService::class)->store($ticket, $request->file('final_evidence_files'), Auth::id())
                : [];

            TicketProgress::create([
                'ticket_id'           => $ticket->ticket_id,
                'user_id'             => Auth::id(),
                'progress_description' => 'Cierre por admin de área: ' . $request->solution_detail
                    . ($finalEvidenceIds ? "\n\n[attachments:" . implode(',', $finalEvidenceIds) . "]" : ''),
                'progress_percentage' => 100,
                'status_update'       => 'service_closed_area_admin',
            ]);

            app(TicketClosurePropagationService::class)->closeChildren(
                $ticket,
                $request->solution_detail,
                $request->resource_link
            );

            try {
                if ($ticket->requester && $ticket->requester->user_email) {
                    \Illuminate\Support\Facades\Mail::to($ticket->requester->user_email)
                        ->send(new \App\Mail\TicketClosed($ticket));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error sending closure email: ' . $e->getMessage());
            }
        } else {
            $cancelReason = trim((string) $request->input('cancellation_reason'));

            $ticket->update([
                'status'        => $request->status,
                'resource_link' => null,
            ]);

            TicketProgress::create([
                'ticket_id'           => $ticket->ticket_id,
                'user_id'             => Auth::id(),
                'progress_description' => 'Cancelación por admin de área. Motivo: ' . $cancelReason,
                'progress_percentage' => (int) ($ticket->progress_percentage ?? $autoProgress),
                'status_update'       => 'service_cancelled_area_admin',
            ]);

            try {
                $ticket->loadMissing('requester');
                if ($ticket->requester && $ticket->requester->user_email) {
                    \Illuminate\Support\Facades\Mail::to($ticket->requester->user_email)
                        ->send(new \App\Mail\TicketCancelled($ticket, $cancelReason));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error sending area cancellation email: ' . $e->getMessage(), [
                    'ticket_id' => $ticket->ticket_id,
                ]);
            }
        }

        $statusText = $request->status == 3 ? 'completado' : 'cancelado';
        return back()->with('success', "Ticket {$statusText} exitosamente.");
    }

    /**
     * Aprobar el resultado reportado por un operario y cerrar el ticket.
     */
    public function approveOperarioCompletion(Request $request, $id)
    {
        $request->validate([
            'solution_detail' => 'required|string|min:10|max:2000',
            'final_evidence_files' => 'nullable|array|max:5',
            'final_evidence_files.*' => 'file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,webp',
        ]);

        $ticket = $this->areaTicketsQuery()->findOrFail($id);

        if ((int) $ticket->status !== 5) {
            return back()->with('error', 'Solo se pueden auditar tickets marcados como realizados por un operario.');
        }

        $ticket->update([
            'status' => 3,
            'progress_percentage' => 100,
        ]);

        $finalEvidenceIds = $request->hasFile('final_evidence_files')
            ? app(FinalTicketEvidenceService::class)->store($ticket, $request->file('final_evidence_files'), Auth::id())
            : [];

        TicketProgress::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => Auth::id(),
            'progress_description' => 'Auditoría aprobada por admin de área: ' . trim($request->solution_detail)
                . ($finalEvidenceIds ? "\n\n[attachments:" . implode(',', $finalEvidenceIds) . "]" : ''),
            'progress_percentage' => 100,
            'status_update' => 'operario_completion_approved',
        ]);

        try {
            $ticket->loadMissing('requester');
            if ($ticket->requester && $ticket->requester->user_email) {
                \Illuminate\Support\Facades\Mail::to($ticket->requester->user_email)
                    ->send(new \App\Mail\TicketClosed($ticket));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error sending closure email after Operario audit: ' . $e->getMessage(), [
                'ticket_id' => $ticket->ticket_id,
            ]);
        }

        return back()->with('success', 'El ticket fue auditado y marcado como completado.');
    }

    /**
     * Devolver al operario un resultado que requiere corrección.
     */
    public function rejectOperarioCompletion(Request $request, $id)
    {
        $request->validate([
            'audit_reason' => 'required|string|min:10|max:1000',
        ], [
            'audit_reason.required' => 'Debes indicar qué debe corregirse.',
            'audit_reason.min' => 'La observación debe tener mínimo 10 caracteres.',
        ]);

        $ticket = $this->areaTicketsQuery()->findOrFail($id);

        if ((int) $ticket->status !== 5) {
            return back()->with('error', 'Solo se pueden devolver tickets marcados como realizados por un operario.');
        }

        $ticket->update(['status' => 2]);

        TicketProgress::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => Auth::id(),
            'progress_description' => 'Auditoría requiere corrección: ' . trim($request->audit_reason),
            'progress_percentage' => (int) ($ticket->progress_percentage ?? 0),
            'status_update' => 'operario_completion_rejected',
        ]);

        return back()->with('success', 'El ticket fue devuelto a estado En Proceso para corrección.');
    }

    /**
     * Reabrir un ticket del área.
     */
    public function reopenTicket($id)
    {
        $ticket = $this->areaTicketsQuery()->findOrFail($id);

        if ($ticket->status != 3 && $ticket->status != 4) {
            return back()->with('error', 'Solo se pueden reabrir tickets cerrados o cancelados.');
        }

        $ticket->update([
            'status'             => 2,
            'is_reopened'        => true,
            'reopened_at'        => now(),
            'progress_percentage' => 0,
            'rating'             => null,
            'feedback'           => null,
        ]);

        return back()->with('success', 'Ticket reabierto exitosamente.');
    }

    /**
     * Calificar un ticket del área.
     */
    public function rateTicket(Request $request, $id)
    {
        $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string',
        ]);

        $ticket = $this->areaTicketsQuery()->findOrFail($id);

        if ($ticket->status != 3) {
            return back()->with('error', 'Solo se pueden calificar tickets completados.');
        }

        $ticket->update([
            'rating'        => $request->rating,
            'feedback'      => $request->feedback,
            'current_phase' => 'Evaluation',
        ]);

        return back()->with('success', 'Evaluación registrada exitosamente.');
    }

    /**
     * Panel de reportes del área.
     */
    public function reports(Request $request)
    {
        $areaId = $this->areaId();

        if (!$areaId) {
            return redirect()->route('area-admin.dashboard')
                ->with('error', 'Tu usuario no tiene un área asignada.');
        }

        $area = Auth::user()->area;
        $regionalScope = $this->regionalScopeMeta();

        $baseQuery = fn () => $this->areaTicketsQuery();

        // Si no hay filtro activo, solo mostrar la UI sin datos
        $hasFilter = $request->hasAny(['start_date', 'end_date', 'status', 'priority', 'current_phase', 'request_type_id']);

        $reportData = null;

        if ($hasFilter) {
            $query = $baseQuery()->with(['requester', 'mediator', 'requestType', 'faculty', 'program']);

            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }
            if ($request->filled('current_phase')) {
                $query->where('current_phase', $request->current_phase);
            }
            if ($request->filled('request_type_id')) {
                $query->where('request_type_id', $request->request_type_id);
            }

            $tickets = $query->latest()->get();

            $statusNames   = [1 => 'Pendiente', 2 => 'En Progreso', 3 => 'Completado', 4 => 'Cancelado'];
            $phaseNames    = ['Analysis' => 'Análisis', 'Design' => 'Diseño', 'Development' => 'Desarrollo', 'Implementation' => 'Implementación', 'Evaluation' => 'Evaluación'];
            $priorityNames = [1 => 'Baja', 2 => 'Media', 3 => 'Alta (Afecta operación)', 4 => 'Urgente (Suspende operación)', 'low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta (Afecta operación)', 'urgent' => 'Urgente (Suspende operación)'];

            if ($request->boolean('export')) {
                $columns = [
                    'Número', 'Título', 'Tópico', 'Estado', 'Prioridad', 'Fase ADDIE', 'Progreso',
                    'Solicitante', 'Mediador', 'Fecha Creación', 'Última Actualización'
                ];

                $rows = [];
                foreach ($tickets as $ticket) {
                    $rows[] = [
                        $ticket->ticket_number,
                        $ticket->title,
                        $ticket->requestType->type_name ?? 'N/A',
                        $statusNames[$ticket->status] ?? 'Desconocido',
                        $priorityNames[(string) ($ticket->priority ?? '')] ?? ($ticket->priority ?? 'N/A'),
                        $phaseNames[$ticket->current_phase] ?? ($ticket->current_phase ?? 'N/A'),
                        ($ticket->progress_percentage ?? 0) . '%',
                        $ticket->requester->user_name ?? 'N/A',
                        $ticket->mediator->user_name ?? 'Sin asignar',
                        optional($ticket->created_at)->format('Y-m-d H:i:s') ?? 'N/A',
                        optional($ticket->updated_at)->format('Y-m-d H:i:s') ?? 'N/A',
                    ];
                }

                $filters = [
                    'Área' => $area->area_name ?? 'N/A',
                    'Fecha inicio' => $request->start_date,
                    'Fecha fin' => $request->end_date,
                    'Estado' => $request->filled('status') ? ($statusNames[$request->status] ?? $request->status) : null,
                    'Prioridad' => $request->filled('priority') ? ($priorityNames[(string) $request->priority] ?? $request->priority) : null,
                    'Fase ADDIE' => $request->filled('current_phase') ? ($phaseNames[$request->current_phase] ?? $request->current_phase) : null,
                    'Tópico' => $request->filled('request_type_id')
                        ? (optional(RequestType::find($request->request_type_id))->type_name ?? $request->request_type_id)
                        : null,
                ];

                return $this->exportAreaReport(
                    'Reporte de Tickets del Área',
                    $columns,
                    $rows,
                    $this->cleanFilters($filters),
                    $request
                );
            }

            $reportData = [
                'tickets'       => $tickets,
                'statusNames'   => $statusNames,
                'phaseNames'    => $phaseNames,
                'priorityNames' => $priorityNames,
                'generated_at'  => now()->format('Y-m-d H:i:s'),
                'total'         => $tickets->count(),
                'by_status'     => $tickets->groupBy('status')->map->count(),
                'avg_rating'    => $tickets->whereNotNull('rating')->avg('rating'),
            ];
        }

        $areaTopics = $this->visibleAreaRequestTypesQuery()
            ->get(['type_id', 'type_name']);

        return view('area-admin.reports.index', compact('area', 'reportData', 'areaTopics', 'regionalScope'));
    }

    /**
     * Resolver formato de exportación de reportes.
     */
    private function resolveExportFormat(Request $request): string
    {
        $format = strtolower((string) $request->get('format', 'excel'));
        return in_array($format, ['excel', 'csv', 'pdf'], true) ? $format : 'excel';
    }

    /**
     * Exportar reporte del área según formato.
     */
    private function exportAreaReport(string $title, array $columns, array $rows, array $filters, Request $request)
    {
        $timestamp = date('Y-m-d_His');
        $format = $this->resolveExportFormat($request);

        if ($format === 'csv') {
            return $this->exportCsv("reporte_area_{$timestamp}.csv", $title, $columns, $rows, $filters);
        }

        if ($format === 'pdf') {
            return $this->exportPrintablePdf($title, $columns, $rows, $filters, '#1a3a5c');
        }

        return $this->exportStyledExcel("reporte_area_{$timestamp}.xls", $title, $columns, $rows, $filters, '#1a3a5c');
    }

    /**
     * Remover filtros vacíos, conservando valores significativos como "0".
     */
    private function cleanFilters(array $filters): array
    {
        return array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Exportar como CSV.
     */
    private function exportCsv(string $filename, string $title, array $columns, array $rows, array $filters = [])
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ];

        $callback = function () use ($title, $columns, $rows, $filters) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, [$title]);
            fputcsv($file, ['Generado', date('Y-m-d H:i:s')]);

            if (!empty($filters)) {
                fputcsv($file, ['Filtros aplicados']);
                foreach ($filters as $label => $value) {
                    fputcsv($file, [$label, $value]);
                }
            }

            fputcsv($file, []);
            fputcsv($file, $columns);

            if (empty($rows)) {
                fputcsv($file, ['Sin resultados para los filtros aplicados.']);
            } else {
                foreach ($rows as $row) {
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Exportar como Excel (HTML compatible).
     */
    private function exportStyledExcel(string $filename, string $title, array $columns, array $rows, array $filters = [], string $accentColor = '#1a3a5c')
    {
        $html = $this->buildStyledReportHtml($title, $columns, $rows, $filters, $accentColor, false);

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ];

        return Response::make("\xEF\xBB\xBF" . $html, 200, $headers);
    }

    /**
     * Exportar como versión imprimible (para guardar como PDF desde navegador).
     */
    private function exportPrintablePdf(string $title, array $columns, array $rows, array $filters = [], string $accentColor = '#1a3a5c')
    {
        $html = $this->buildStyledReportHtml($title, $columns, $rows, $filters, $accentColor, true);
        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Construir HTML base para exportación de reportes.
     */
    private function buildStyledReportHtml(string $title, array $columns, array $rows, array $filters = [], string $accentColor = '#1a3a5c', bool $printMode = false): string
    {
        $safeAccentColor = htmlspecialchars($accentColor, ENT_QUOTES, 'UTF-8');

        $filtersHtml = '';
        if (!empty($filters)) {
            $filtersHtml .= '<div class="meta"><strong>Filtros aplicados:</strong> ';
            $parts = [];
            foreach ($filters as $label => $value) {
                $parts[] = htmlspecialchars($label . ': ' . $value, ENT_QUOTES, 'UTF-8');
            }
            $filtersHtml .= implode(' | ', $parts) . '</div>';
        }

        $headerCells = '';
        foreach ($columns as $column) {
            $headerCells .= '<th>' . htmlspecialchars($column, ENT_QUOTES, 'UTF-8') . '</th>';
        }

        $bodyRows = '';
        if (empty($rows)) {
            $bodyRows = '<tr><td colspan="' . count($columns) . '" class="empty">Sin resultados para los filtros aplicados.</td></tr>';
        } else {
            foreach ($rows as $row) {
                $bodyRows .= '<tr>';
                foreach ($row as $cell) {
                    $bodyRows .= '<td>' . htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                $bodyRows .= '</tr>';
            }
        }

        $generatedAt = date('Y-m-d H:i:s');
        $printBlock = $printMode
            ? '<script>window.addEventListener("load", function(){ window.print(); });</script>'
            : '';

        return '<html><head><meta charset="UTF-8"><style>
            body { font-family: Arial, Helvetica, sans-serif; color: #1f2937; margin: 20px; }
            .title { font-size: 18px; font-weight: bold; color: ' . $safeAccentColor . '; margin-bottom: 4px; }
            .meta { font-size: 12px; color: #4b5563; margin-bottom: 8px; }
            table { border-collapse: collapse; width: 100%; }
            th { background: ' . $safeAccentColor . '; color: #ffffff; font-weight: bold; border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
            td { border: 1px solid #d1d5db; padding: 7px; vertical-align: top; }
            tr:nth-child(even) td { background: #f8fafc; }
            .empty { text-align: center; color: #6b7280; font-style: italic; padding: 14px; }
            @media print { body { margin: 10px; } }
        </style></head><body>
            <div class="title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div>
            <div class="meta"><strong>Generado:</strong> ' . htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8') . '</div>'
            . $filtersHtml .
            '<table><thead><tr>' . $headerCells . '</tr></thead><tbody>' . $bodyRows . '</tbody></table>'
            . $printBlock .
        '</body></html>';
    }
}
