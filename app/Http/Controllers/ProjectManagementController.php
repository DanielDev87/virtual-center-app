<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Sprint;
use App\Models\ProjectTask;
use App\Models\TicketProgress;
use Illuminate\Support\Facades\Auth;

class ProjectManagementController extends Controller
{
    /**
     * Mostrar el panel de gestión de proyecto para un ticket
     */
    public function index($ticketId)
    {
        $ticket = Ticket::with(['sprints.tasks', 'projectTasks.assignee', 'requester', 'mediator'])
            ->findOrFail($ticketId);
        
        // Obtener el sprint activo o el sprint seleccionado
        $activeSprint = null;
        if (request('sprint_id')) {
            $activeSprint = $ticket->sprints->where('sprint_id', request('sprint_id'))->first();
        } else {
            $activeSprint = $ticket->sprints()->where('status', 'active')->first();
        }
        
        // Obtener tareas del backlog (tareas no asignadas a ningún sprint)
        $backlogTasks = $ticket->projectTasks()->whereNull('sprint_id')->get();

        return view('admin.projects.dashboard', compact('ticket', 'activeSprint', 'backlogTasks'));
    }

    /**
     * Actualizar el estado del sprint
     */
    public function updateSprintStatus(Request $request, $sprintId)
    {
        $request->validate([
            'status' => 'required|in:planned,active,completed',
        ]);

        $sprint = Sprint::findOrFail($sprintId);
        $previousStatus = $sprint->status;
        
        // Si se activa un sprint, asegurarse de que no haya otro sprint activo para este ticket
        if ($request->status == 'active') {
            Sprint::where('ticket_id', $sprint->ticket_id)
                ->where('status', 'active')
                ->where('sprint_id', '!=', $sprintId)
                ->update(['status' => 'completed']); // O 'planned', pero normalmente se cierra el anterior
        }

        $sprint->update(['status' => $request->status]);

        $ticket = Ticket::find($sprint->ticket_id);
        if ($ticket) {
            $this->recalculateTicketProgress($ticket);

            if ($previousStatus !== 'completed' && $request->status === 'completed') {
                $ticket->refresh();
                $this->logAutoProgressEvent(
                    $sprint->ticket_id,
                    Auth::id(),
                    "Sprint completado: {$sprint->name}",
                    'sprint_completed',
                    (int) ($ticket->progress_percentage ?? 0)
                );
            }
        }

        return back()->with('success', 'Estado del sprint actualizado.');
    }

    /**
     * Actualizar la fase ADDIE del ticket
     */
    public function updatePhase(Request $request, $ticketId)
    {
        $request->validate([
            'phase' => 'required|in:Analysis,Design,Development,Implementation,Evaluation',
        ]);

        $ticket = Ticket::findOrFail($ticketId);
        $ticket->update(['current_phase' => $request->phase]);

        return back()->with('success', 'Fase del proyecto actualizada correctamente.');
    }

    /**
     * Registrar un nuevo sprint
     */
    public function storeSprint(Request $request, $ticketId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'goal' => 'nullable|string',
        ]);

        Sprint::create([
            'ticket_id' => $ticketId,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'goal' => $request->goal,
            'status' => 'planned',
        ]);

        return back()->with('success', 'Sprint creado exitosamente.');
    }

    /**
     * Registrar una nueva tarea de proyecto
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

        ProjectTask::create([
            'ticket_id' => $ticketId,
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
     * Asignar una tarea existente a un sprint no completado
     */
    public function assignTaskSprint(Request $request, $taskId)
    {
        $request->validate([
            'sprint_id' => 'required|exists:sprints,sprint_id',
        ]);

        $task = ProjectTask::findOrFail($taskId);

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
     * Actualizar el estado de una tarea (arrastrar y soltar Kanban)
     */
    public function updateTaskStatus(Request $request, $taskId)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);

        $task = ProjectTask::findOrFail($taskId);
        $previousStatus = $task->status;

        $ticket = Ticket::find($task->ticket_id);
        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket no encontrado.'], 404);
        }

        if (in_array((int) $ticket->status, [3, 4], true)) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden mover tareas en tickets completados o cancelados.'
            ], 422);
        }

        $sprint = $task->sprint_id ? Sprint::find($task->sprint_id) : null;
        if ($sprint && $sprint->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden mover tareas de un sprint completado.'
            ], 422);
        }

        $task->update(['status' => $request->status]);

        if ($ticket) {
            if ($ticket->status == 1 && in_array($request->status, ['in_progress', 'review', 'done'])) {
                $ticket->update(['status' => 2]);
            }

            $this->recalculateTicketProgress($ticket);

            if ($previousStatus !== 'done' && $request->status === 'done') {
                $ticket->refresh();
                $this->logAutoProgressEvent(
                    $task->ticket_id,
                    Auth::id(),
                    "Tarea completada: {$task->title}",
                    'task_completed',
                    (int) ($ticket->progress_percentage ?? 0)
                );
            }
        }

        return response()->json(['success' => true, 'message' => 'Estado de la tarea actualizado.']);
    }

    /**
     * Calcular el progreso automático desde las tareas de sprint (% de tareas terminadas sobre el total)
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
     * Recalcular y actualizar progress_percentage del ticket basado en tareas de sprint
     */
    private function recalculateTicketProgress($ticket)
    {
        $ticket->load('sprints.tasks');
        $progress = $this->computeAutoProgress($ticket);
        $updateData = ['progress_percentage' => $progress];
        if ($progress > 0 && $ticket->status == 1) {
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
}
