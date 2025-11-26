<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketProgress;
use Illuminate\Support\Facades\Auth;

class ContributorController extends Controller
{
    /**
     * Show dashboard with assigned tickets
     */
    /**
     * Show dashboard with assigned tickets
     */
    public function dashboard()
    {
        $userId = Auth::id();
        
        // Get tickets where user is primary mediator OR assigned as team member
        $tickets = Ticket::where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })
            ->with(['requester', 'requestType', 'assignments' => function($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->latest()
            ->paginate(15);
        
        // Helper for stats query
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
        
        // Statistics
        $stats = [
            'total' => $statsQuery(),
            'pending' => $statsQuery(1),
            'in_progress' => $statsQuery(2),
            'completed' => $statsQuery(3),
        ];

        return view('contributors.dashboard', compact('tickets', 'stats'));
    }

    /**
     * Show ticket details
     */
    public function show($id)
    {
        $userId = Auth::id();

        $ticket = Ticket::with(['requester', 'requestType', 'progress.user', 'assignments.mediator', 'assignments.jobPosition'])
            ->where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })
            ->findOrFail($id);

        return view('contributors.show', compact('ticket'));
    }

    /**
     * Store progress update
     */
    public function storeProgress(Request $request, $id)
    {
        $request->validate([
            'progress_description' => 'required|string',
            'progress_percentage' => 'required|integer|min:0|max:100',
        ]);

        $userId = Auth::id();

        $ticket = Ticket::where(function($query) use ($userId) {
                $query->where('mediator_id', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('user_id', $userId)->where('status', 'active');
                      });
            })
            ->findOrFail($id);

        // Calculate new total progress
        $currentProgress = $ticket->progress_percentage ?? 0;
        $newProgress = $request->progress_percentage;
        $totalProgress = $currentProgress + $newProgress;

        if ($totalProgress > 100) {
            return back()->withErrors(['progress_percentage' => "El avance total no puede superar el 100%. El progreso actual es {$currentProgress}%."])->withInput();
        }

        TicketProgress::create([
            'ticket_id' => $id,
            'user_id' => $userId,
            'progress_description' => $request->progress_description,
            'progress_percentage' => $newProgress,
            'status_update' => $request->status_update,
        ]);

        // Update ticket cumulative progress
        $ticket->update(['progress_percentage' => $totalProgress]);

        // Optional: Update status if requested
        if ($request->status_update) {
            $ticket->update(['status' => $request->status_update]);
        }

        return redirect()->route('contributors.show', $id)
            ->with('success', 'Avance registrado exitosamente.');
    }
            'progress_percentage' => $request->progress_percentage,
        ]);

        // Update ticket status to "In Progress" if it was pending
        if ($ticket->status == 1) {
            $ticket->update(['status' => 2]);
        }

        // TODO: Notify requester of progress update

        return back()->with('success', 'Avance registrado exitosamente.');
    }
}
