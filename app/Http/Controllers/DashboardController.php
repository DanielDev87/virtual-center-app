<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserRole;
use App\Models\RequestType;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Mostrar dashboard principal para admin
     */
    public function index()
    {
        // Estadísticas generales
        $stats = [
            'total_tickets' => Ticket::count(),
            'pending_tickets' => Ticket::where('status', 1)->count(),
            'in_progress_tickets' => Ticket::where('status', 2)->count(),
            'completed_tickets' => Ticket::where('status', 3)->count(),
            'total_users' => User::where('is_active', true)->count(),
            'total_roles' => UserRole::where('is_active', true)->count(),
        ];

        // Tickets recientes
        $recentTickets = Ticket::with(['requester', 'mediator', 'requestType'])
            ->latest()
            ->take(10)
            ->get();

        // Tickets por estado
        $ticketsByStatus = [
            'Pendiente' => Ticket::where('status', 1)->count(),
            'En Progreso' => Ticket::where('status', 2)->count(),
            'Completado' => Ticket::where('status', 3)->count(),
            'Cancelado' => Ticket::where('status', 4)->count(),
        ];

        // Tickets por tipo
        $ticketsByType = Ticket::with('requestType')
            ->select('request_type_id', DB::raw('count(*) as count'))
            ->whereNotNull('request_type_id')
            ->groupBy('request_type_id')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->requestType->type_name ?? 'Sin tipo' => $item->count];
            });

        return view('dashboard.index', compact(
            'stats', 
            'recentTickets', 
            'ticketsByStatus', 
            'ticketsByType'
        ));
    }
}

