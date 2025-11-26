<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketAssignment;
use App\Models\JobPosition;
use Illuminate\Support\Facades\Auth;

class AdminTicketController extends Controller
{
    /**
     * Display a listing of all tickets
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['requester', 'mediator']);

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest()->paginate(15);
        
        return view('admin.tickets.index', compact('tickets'));
    }

    /**
     * Display the specified ticket
     */
    public function show($id)
    {
        $ticket = Ticket::with(['requester', 'mediator', 'assignments.mediator', 'assignments.jobPosition'])
            ->findOrFail($id);
        
        // Get available mediators (users with Contributor or Monitor role)
        $mediators = User::with('jobPositions')->whereHas('role', function($query) {
            $query->whereIn('role_name', ['Contributor', 'Monitor']);
        })->where('is_active', true)->get();
        
        // Get active job positions
        $jobPositions = JobPosition::where('is_active', true)->get();

        return view('admin.tickets.show', compact('ticket', 'mediators', 'jobPositions'));
    }

    /**
     * Assign a mediator to a ticket (legacy single mediator)
     */
    public function assignMediator(Request $request, $id)
    {
        $request->validate([
            'mediator_id' => 'required|exists:users,user_id'
        ]);

        $ticket = Ticket::findOrFail($id);
        
        $ticket->update([
            'mediator_id' => $request->mediator_id,
            'status' => 2, // In Progress
        ]);

        return back()->with('success', 'Mediador asignado exitosamente.');
    }

    /**
     * Set ticket priority
     */
    public function setPriority(Request $request, $id)
    {
        $request->validate([
            'priority' => 'required|in:1,2,3,4' // 1=Low, 2=Medium, 3=High, 4=Urgent
        ]);

        $ticket = Ticket::findOrFail($id);
        
        $ticket->update([
            'priority' => $request->priority,
        ]);

        $priorityNames = [1 => 'Baja', 2 => 'Media', 3 => 'Alta', 4 => 'Urgente'];
        return back()->with('success', "Prioridad actualizada a: {$priorityNames[$request->priority]}");
    }

    /**
     * Close a ticket
     */
    public function close(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:3,4', // 3 = Completed, 4 = Cancelled
            'admin_notes' => 'nullable|string'
        ]);

        $ticket = Ticket::findOrFail($id);
        
        $ticket->update([
            'status' => $request->status,
        ]);

        $statusText = $request->status == 3 ? 'completado' : 'cancelado';
        return back()->with('success', "Ticket {$statusText} exitosamente.");
    }

    /**
     * Assign a mediator with a job position to a ticket (Multi-Mediator System)
     */
    public function assignMediatorToTicket(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'job_position_id' => 'required|exists:job_positions,job_position_id',
            'notes' => 'nullable|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        // Check if this user is already assigned to this ticket
        $existingAssignment = TicketAssignment::where('ticket_id', $id)
            ->where('user_id', $request->user_id)
            ->where('status', 'active')
            ->first();

        if ($existingAssignment) {
            return back()->with('error', 'Este mediador ya está asignado a este ticket.');
        }

        // Create assignment
        TicketAssignment::create([
            'ticket_id' => $id,
            'user_id' => $request->user_id,
            'job_position_id' => $request->job_position_id,
            'assigned_by' => Auth::id(),
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        // Update ticket status to "In Progress" if it's still pending
        if ($ticket->status == 1) {
            $ticket->update(['status' => 2]);
        }

        return back()->with('success', 'Mediador asignado exitosamente al equipo de trabajo.');
    }

    /**
     * Remove a mediator assignment from a ticket
     */
    public function removeAssignment($ticketId, $assignmentId)
    {
        $assignment = TicketAssignment::where('assignment_id', $assignmentId)
            ->where('ticket_id', $ticketId)
            ->firstOrFail();

        $assignment->update(['status' => 'removed']);

        return back()->with('success', 'Mediador removido del equipo de trabajo.');
    }
}
