<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\RequestType;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

class ServiceManagementController extends Controller
{
    /**
     * Mostrar lista de tickets del usuario con estadísticas
     */
    public function index()
    {
        $userId = Auth::id();
        
        $tickets = Ticket::where('requester_id', $userId)
            ->latest()
            ->paginate(10);
        
        // Estadísticas
        $stats = [
            'total' => Ticket::where('requester_id', $userId)->count(),
            'pending' => Ticket::where('requester_id', $userId)->where('status', 1)->count(),
            'in_progress' => Ticket::where('requester_id', $userId)->where('status', 2)->count(),
            'completed' => Ticket::where('requester_id', $userId)->where('status', 3)->count(),
        ];

        return view('service-management.index', compact('tickets', 'stats'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $requestTypes = RequestType::where('is_active', true)->get();
        $faculties = Faculty::where('is_active', true)->get();
        $programs = Program::where('is_active', true)->get();
        $courses = Course::where('is_active', true)->get();
        
        return view('service-management.create', compact('requestTypes', 'faculties', 'programs', 'courses'));
    }

    /**
     * Guardar nuevo ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'request_type_id' => 'required|exists:request_types,type_id',
            'faculty_id' => 'nullable|exists:faculties,faculty_id',
            'program_id' => 'nullable|exists:programs,program_id',
            'course_id' => 'nullable|exists:courses,course_id',
        ]);

        // Generate unique ticket number based on timestamp (numeric only for integer field)
        $ticketNumber = date('YmdHis'); // Format: 20251126150037

        Ticket::create([
            'ticket_number' => $ticketNumber,
            'title' => $request->title,
            'requester_id' => Auth::id(),
            'request_type_id' => $request->request_type_id,
            'status' => 1, // Pending
            'requester_info' => $request->description,
            'faculty_id' => $request->faculty_id,
            'program_id' => $request->program_id,
            'course_id' => $request->course_id,
        ]);

        return redirect()->route('service-management.index')
            ->with('success', 'Solicitud creada exitosamente. Número de ticket: ' . $ticketNumber);
    }

    /**
     * Mostrar ticket específico
     */
    public function show($id)
    {
        $ticket = Ticket::with(['requester', 'mediator', 'faculty', 'program', 'course', 'assignments.mediator', 'assignments.jobPosition'])
            ->where('requester_id', Auth::id())
            ->findOrFail($id);

        return view('service-management.show', compact('ticket'));
    }

    /**
     * Calificar ticket
     */
    public function rate(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $ticket = Ticket::where('requester_id', Auth::id())->findOrFail($id);
        
        if ($ticket->status != 3) {
             return back()->with('error', 'Solo se pueden calificar tickets completados.');
        }

        $ticket->update([
            'rating' => $request->rating,
        ]);

        return back()->with('success', 'Gracias por tu calificación.');
    }
}
