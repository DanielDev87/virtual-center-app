<?php

namespace App\Http\Controllers;

use App\Services\TicketSuggestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketSuggestionController extends Controller
{
    public function similar(Request $request, TicketSuggestionService $service)
    {
        $data = $request->validate([
            'request_type_id' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $results = $service->suggest(
            (int) $data['request_type_id'],
            (string) ($data['title'] ?? ''),
            (string) ($data['description'] ?? ''),
            Auth::user()
        );

        return response()->json(['results' => $results]);
    }
}