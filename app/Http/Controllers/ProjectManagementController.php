<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Sprint;
use App\Models\ProjectTask;
use Illuminate\Support\Facades\Auth;

class ProjectManagementController extends Controller
{
    /**
     * Show the project management dashboard for a ticket
     */
    public function index($ticketId)
    {
        $ticket = Ticket::with(['sprints.tasks', 'projectTasks.assignee', 'requester', 'mediator'])
            ->findOrFail($ticketId);
        
        // Get active sprint or selected sprint
        $activeSprint = null;
        if (request('sprint_id')) {
            $activeSprint = $ticket->sprints->where('sprint_id', request('sprint_id'))->first();
        } else {
            $activeSprint = $ticket->sprints()->where('status', 'active')->first();
        }
        
        // Get backlog tasks (tasks not assigned to any sprint or assigned to future sprints)
        $backlogTasks = $ticket->projectTasks()->whereNull('sprint_id')->get();

        return view('admin.projects.dashboard', compact('ticket', 'activeSprint', 'backlogTasks'));
    }

    /**
     * Update sprint status
     */
    public function updateSprintStatus(Request $request, $sprintId)
    {
        $request->validate([
            'status' => 'required|in:planned,active,completed',
        ]);

        $sprint = Sprint::findOrFail($sprintId);
        
        // If activating a sprint, ensure no other sprint is active for this ticket
        if ($request->status == 'active') {
            Sprint::where('ticket_id', $sprint->ticket_id)
                ->where('status', 'active')
                ->where('sprint_id', '!=', $sprintId)
                ->update(['status' => 'completed']); // Or 'planned', but usually we close the previous one
        }

        $sprint->update(['status' => $request->status]);

        return back()->with('success', 'Estado del sprint actualizado.');
    }

    /**
     * Update the ADDIE phase of the ticket
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
     * Store a new sprint
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
     * Store a new project task
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
     * Update task status (Kanban drag & drop)
     */
    public function updateTaskStatus(Request $request, $taskId)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);

        $task = ProjectTask::findOrFail($taskId);
        $task->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Estado de la tarea actualizado.']);
    }
}
