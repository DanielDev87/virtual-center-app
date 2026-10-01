<?php

namespace App\Http\Controllers;

use App\Mail\RequesterMessageReceived;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\RequestType;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Course;
use App\Models\Institution;
use App\Models\AppSetting;
use App\Models\TicketEvidence;
use App\Models\TicketProgress;
use App\Models\User;
use App\Services\CustomObjectStorageService;
use App\Services\GoogleDriveStorageService;
use App\Services\LocalEvidenceStorageService;
use App\Services\LocalRichTextImageStorageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Services\BusinessHoursService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

        // Solicitudes completadas pendientes de calificar.
        $completedRequestsToRate = $this->pendingCompletedTicketsQuery((int) $userId)
            ->with(['requestType'])
            ->latest()
            ->take(5)
            ->get();

        return view('service-management.index', compact('tickets', 'stats', 'completedRequestsToRate'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        if (Auth::check() && $this->hasPendingCompletedTicketToRate((int) Auth::id())) {
            return redirect()->route('service-management.index')
                ->with('error', 'Debes calificar tus solicitudes completadas pendientes antes de crear una nueva solicitud.');
        }

        $supportsRegionalAssignments = $this->supportsRegionalAssignments();

        $requestTypesQuery = RequestType::where('is_active', true);
        if ($supportsRegionalAssignments) {
            $requestTypesQuery->with(['regionalAssignments.institution']);
        }
        $requestTypes = $requestTypesQuery->get();

        $institutions = $supportsRegionalAssignments
            ? Institution::where('is_active', true)->orderBy('institution_name')->get()
            : collect();

        $requestTypeRegionalMap = $supportsRegionalAssignments
            ? $requestTypes->mapWithKeys(function ($type) {
                return [
                    $type->type_id => $type->regionalAssignments
                        ->filter(fn ($assignment) => $assignment->institution)
                        ->map(fn ($assignment) => [
                            'institution_id' => (int) $assignment->institution_id,
                            'institution_name' => $assignment->institution->institution_name,
                        ])
                        ->unique('institution_id')
                        ->values()
                        ->all(),
                ];
            })
            : collect();

        $faculties = Faculty::where('is_active', true)->get();
        $programs = Program::where('is_active', true)->get();
        $courses = Course::where('is_active', true)->get();
        $businessHours = app(BusinessHoursService::class);
        $outsideBusinessHours = !$businessHours->isWithinBusinessHours();
        $nextBusinessStart = $outsideBusinessHours
            ? $businessHours->nextBusinessStart()
            : null;
        
        return view('service-management.create', compact('requestTypes', 'institutions', 'requestTypeRegionalMap', 'faculties', 'programs', 'courses', 'outsideBusinessHours', 'nextBusinessStart'));
    }

    /**
     * Guardar nuevo ticket
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'request_type_id' => 'required|exists:request_types,type_id',
            'institution_id' => 'nullable|exists:institutions,institution_id',
            'faculty_id' => 'nullable|exists:faculties,faculty_id',
            'program_id' => 'nullable|exists:programs,program_id',
            'course_id' => 'nullable|exists:courses,course_id',
            'priority' => 'required|in:1,2,3,4',
            'priority_justification' => [
                'nullable',
                'string',
                'min:20',
                'max:1000',
                Rule::requiredIf(fn () => in_array((int) $request->input('priority'), [3, 4], true)),
            ],
            'evidence_drive_link' => 'nullable|url|max:2048',
            'evidence_files' => 'nullable|array|max:5',
            'evidence_files.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar',
        ];

        if (!Auth::check()) {
            $rules['document_number'] = 'required|string';
            $rules['requester_name'] = 'required|string|max:255';
            $rules['requester_email'] = [
                'required',
                'string',
                'email',
                'max:255',
                'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/'
            ];
            $rules['institution_link'] = 'required|string|max:255';
            $rules['policy_accepted'] = 'accepted';
        }

        $request->validate($rules, [
            'requester_email.email' => 'El correo electrónico debe tener un formato válido.',
            'requester_email.regex' => 'El correo electrónico debe tener una estructura válida (ejemplo: usuario@dominio.com).',
            'priority_justification.required' => 'Debes justificar por qué la prioridad es alta o urgente.',
            'priority_justification.min' => 'La justificación de prioridad debe tener mínimo 20 caracteres.',
        ]);

        $priority = (int) $request->input('priority');
        $priorityJustification = trim((string) $request->input('priority_justification', ''));

        if (in_array($priority, [3, 4], true) && !$this->hasMeaningfulText($priorityJustification, 20, 12)) {
            return redirect()->route('service-management.create')
                ->withInput()
                ->withErrors([
                    'priority_justification' => 'La justificación de prioridad debe tener mínimo 20 caracteres con contenido descriptivo.',
                ]);
        }

        $requesterId = Auth::id();

        if (!$requesterId) {
            $roleId = \App\Models\UserRole::where('role_name', 'Requester')->value('role_id') ?? 4;
            
            // Buscar o crear por numero de documento
            $user = \App\Models\User::where('document_number', $request->document_number)->first();
            if ($user) {
                // Actualizar sus datos si cambiaron
                $user->update([
                    'user_name' => $request->requester_name,
                    'user_email' => $request->requester_email,
                    'institution_link' => $request->institution_link
                ]);
            } else {
                // Intenta buscar por email para evitar colision, sino crea
                $user = \App\Models\User::firstOrCreate(
                    ['user_email' => $request->requester_email],
                    [
                        'user_name' => $request->requester_name,
                        'document_number' => $request->document_number,
                        'institution_link' => $request->institution_link,
                        'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                        'role_id' => $roleId,
                        'is_active' => true
                    ]
                );
                // Si el usuario ya existia por correo pero no tenia documento, lo actualizamos
                if(empty($user->document_number)) {
                    $user->update([
                        'document_number' => $request->document_number,
                        'institution_link' => $request->institution_link
                    ]);
                }
            }
            $requesterId = $user->user_id;
        }

        if ($this->hasPendingCompletedTicketToRate((int) $requesterId)) {
            return redirect()->route('service-management.create')
                ->withInput()
                ->withErrors([
                    'rating_pending' => 'Debes calificar tus solicitudes completadas pendientes antes de crear una nueva solicitud.',
                ]);
        }

        // Generar número de ticket único basado en marca de tiempo (solo numérico para campo entero)
        $ticketNumber = date('YmdHis'); // Formato: 20251126150037

        $supportsRegionalAssignments = $this->supportsRegionalAssignments();

        if (Schema::hasTable('request_type_user')) {
            $requestType = $supportsRegionalAssignments
                ? RequestType::with(['collaborators', 'regionalAssignments'])->find($request->request_type_id)
                : RequestType::with(['collaborators'])->find($request->request_type_id);
        } else {
            $requestType = $supportsRegionalAssignments
                ? RequestType::with(['regionalAssignments'])->find($request->request_type_id)
                : RequestType::find($request->request_type_id);

        }

        if ($requestType?->incident_active) {
            return back()->withInput()->with('error', ($requestType->incident_title ?: 'Tópico temporalmente bloqueado') . ': ' . ($requestType->incident_message ?: 'Este tópico no está disponible mientras se atiende una incidencia.'));
        }

        $usesRegionalRouting = $supportsRegionalAssignments
            && $requestType
            && $requestType->relationLoaded('regionalAssignments')
            && $requestType->regionalAssignments->isNotEmpty();

        if ($usesRegionalRouting) {
            $request->validate([
                'institution_id' => 'required|exists:institutions,institution_id',
            ]);
        }

        $preferredMediatorId = null;
        if ($requestType) {
            if ($usesRegionalRouting) {
                $regionalAssignments = $requestType->regionalAssignments
                    ->where('institution_id', (int) $request->institution_id)
                    ->values();

                if ($regionalAssignments->isEmpty()) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'institution_id' => 'La regional seleccionada no tiene un responsable configurado para este tópico.',
                        ]);
                }

                // Si hay un solo responsable regional, asigna automáticamente.
                if ($regionalAssignments->count() === 1) {
                    $preferredMediatorId = (int) $regionalAssignments->first()->user_id;
                }
            } else {
                $collaboratorCount = (Schema::hasTable('request_type_user') && $requestType->relationLoaded('collaborators'))
                    ? $requestType->collaborators->count()
                    : 0;

                // For shared topics, leave tickets in a queue for collaborators to self-assign.
                if ($collaboratorCount <= 1) {
                    $preferredMediatorId = $requestType->resolvePreferredMediatorId();
                }
            }
        }
        $sanitizedDescription = $this->sanitizeRichText($request->description);
        
        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'title' => $request->title,
            'requester_id' => $requesterId,
            'request_type_id' => $request->request_type_id,
            'institution_id' => $request->institution_id,
            'status' => 1, // Pendiente
            'requester_info' => '',
            'requester_url' => $request->evidence_drive_link,
            'faculty_id' => $request->faculty_id,
            'program_id' => $request->program_id,
            'course_id' => $request->course_id,
            'priority' => $priority,
            'mediator_id' => $preferredMediatorId,
        ]);

        $richTextStorage = app(LocalRichTextImageStorageService::class);
        $processedDescription = $this->processRichTextDescription($request->description, $ticket, $requesterId, $richTextStorage);
        $finalRequesterInfo = $processedDescription !== '' ? $processedDescription : $sanitizedDescription;
        $finalRequesterInfo = $this->appendPriorityJustificationToRequesterInfo($finalRequesterInfo, $priority, $priorityJustification);

        $ticket->update([
            'requester_info' => $finalRequesterInfo,
        ]);

        if ($request->hasFile('evidence_files')) {
            $localEvidenceStorage = app(LocalEvidenceStorageService::class);
            foreach ($request->file('evidence_files') as $uploadedFile) {
                $this->storeEvidenceFile($ticket, $uploadedFile, $requesterId, $localEvidenceStorage);
            }
        }

        if ($requestType && $preferredMediatorId) {
            // Registrar la creación o asignación
            \App\Models\TicketAssignment::create([
                'ticket_id' => $ticket->ticket_id,
                'user_id' => $preferredMediatorId,
                'assigned_by' => $requesterId,
                'status' => 'active',
                'assigned_at' => now(),
            ]);
        }

        // Enviar correo al solicitante
        try {
            $ticket->load('requester', 'requestType', 'mediator');
            if ($ticket->requester && $ticket->requester->user_email) {
                \Illuminate\Support\Facades\Mail::to($ticket->requester->user_email)->send(new \App\Mail\TicketCreated($ticket));
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error('Error sending creation email: ' . $e->getMessage());
        }

        // Cargar relaciones para la sesión
        $ticket->load('requestType');

        if (Auth::check()) {
            return redirect()->route('service-management.index')
                ->with('new_ticket', $ticket);
        } else {
            return back()->with('new_ticket', $ticket);
        }
    }

    private function supportsRegionalAssignments(): bool
    {
        return Schema::hasTable('request_type_regional_assignments');
    }

    /**
     * Mostrar vista de consulta de estado pública
     */
    public function track(Request $request)
    {
        if ($request->has('ticket_number') && $request->has('document_number')) {
            $ticket = Ticket::with(['requestType', 'progress.user', 'evidences'])
                ->where('ticket_number', $request->ticket_number)
                ->whereHas('requester', function ($query) use ($request) {
                    $query->where('document_number', $request->document_number);
                })
                ->first();
            
            if ($ticket) {
                return view('service-management.track', compact('ticket'));
            } else {
                return view('service-management.track')->with('error', 'No se encontró un ticket con ese número de documento y número de ticket.');
            }
        }
        return view('service-management.track');
    }
    
    /**
     * Verificar si un solicitante existe por su documento (AJAX)
     */
    public function checkRequester(Request $request)
    {
        $request->validate(['document_number' => 'required|string']);
        
        $user = \App\Models\User::where('document_number', $request->document_number)
            ->select('user_id', 'user_name', 'user_email', 'institution_link')
            ->first();

        if ($user) {
            $pendingTickets = $this->pendingCompletedTicketsQuery((int) $user->user_id)
                ->select(['ticket_id', 'ticket_number', 'title', 'created_at'])
                ->latest('ticket_id')
                ->take(5)
                ->get();

            return response()->json([
                'exists' => true,
                'user' => $user,
                'has_pending_ratings' => $pendingTickets->isNotEmpty(),
                'pending_tickets' => $pendingTickets,
            ]);
        }

        return response()->json([
            'exists' => false,
            'has_pending_ratings' => false,
            'pending_tickets' => [],
        ]);
    }

    /**
     * Buscar estado de un ticket por su número
     */
    public function searchTrack(Request $request)
    {
        $request->validate([
            'document_number' => 'required|string|max:30',
            'ticket_number' => 'required|numeric'
        ]);

        $ticket = Ticket::with(['requestType', 'progress.user', 'evidences'])
            ->where('ticket_number', $request->ticket_number)
            ->whereHas('requester', function ($query) use ($request) {
                $query->where('document_number', $request->document_number);
            })
            ->first();

        if (!$ticket) {
            return back()->with('error', 'No se encontró un ticket con ese número de documento y número de ticket. Verifique e intente de nuevo.');
        }

        return view('service-management.track', compact('ticket'));
    }

    /**
     * Mostrar ticket específico
     */
    public function show($id)
    {
        $ticket = Ticket::with(['requestType.area', 'evidences', 'progress'])
            ->where('requester_id', Auth::id())
            ->findOrFail($id);

        return view('service-management.show', compact('ticket'));
    }

    /**
     * Registrar mensaje del solicitante desde la interfaz publica de seguimiento.
     */
    public function postPublicMessage(Request $request)
    {
        $request->validate([
            'document_number' => 'required|string|max:30',
            'ticket_number' => 'required|numeric',
            'message' => 'required|string|min:3|max:1500',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,webp',
        ]);

        $ticket = Ticket::where('ticket_number', $request->ticket_number)
            ->whereHas('requester', function ($query) use ($request) {
                $query->where('document_number', $request->document_number);
            })
            ->first();

        if (!$ticket) {
            return redirect()->route('service-management.track', [
                'ticket_number' => $request->ticket_number,
                'document_number' => $request->document_number,
            ])->with('error', 'No se encontró un ticket válido para enviar el mensaje.');
        }

        if ($this->isTicketClosedOrTerminated($ticket)) {
            return redirect()->route('service-management.track', [
                'ticket_number' => $request->ticket_number,
                'document_number' => $request->document_number,
            ])->with('error', 'La comunicación está deshabilitada para tickets cerrados o terminados.');
        }

        $attachmentIds = [];
        if ($request->hasFile('attachments')) {
            $localEvidenceStorage = app(LocalEvidenceStorageService::class);
            foreach ($request->file('attachments') as $uploadedFile) {
                $storedEvidence = $this->storeEvidenceFile($ticket, $uploadedFile, $ticket->requester_id, $localEvidenceStorage);
                if ($storedEvidence) {
                    $attachmentIds[] = $storedEvidence->evidence_id;
                }
            }
        }

        TicketProgress::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $ticket->requester_id,
            'progress_description' => $this->composeMessageWithAttachmentIds(trim($request->message), $attachmentIds),
            'progress_percentage' => (int) ($ticket->progress_percentage ?? 0),
            'status_update' => 'requester_message',
        ]);

        try {
            $recipientIds = collect();
            if ($ticket->mediator_id) {
                $recipientIds->push((int) $ticket->mediator_id);
            }

            $activeAssignmentIds = \App\Models\TicketAssignment::where('ticket_id', $ticket->ticket_id)
                ->where('status', 'active')
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $recipientIds = $recipientIds
                ->merge($activeAssignmentIds)
                ->unique()
                ->values();

            if ($recipientIds->isNotEmpty()) {
                $recipients = User::whereIn('user_id', $recipientIds)
                    ->where('is_active', true)
                    ->get();

                foreach ($recipients as $recipient) {
                    if ($recipient->user_email) {
                        \Illuminate\Support\Facades\Mail::to($recipient->user_email)
                            ->send(new RequesterMessageReceived($ticket, trim($request->message)));
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error sending requester message email: ' . $e->getMessage(), [
                'ticket_id' => $ticket->ticket_id,
                'requester_id' => $ticket->requester_id,
            ]);
        }

        return redirect()->route('service-management.track', [
            'ticket_number' => $request->ticket_number,
            'document_number' => $request->document_number,
        ])->with('success', 'Mensaje enviado al colaborador correctamente.');
    }

    /**
     * Descargar evidencia desde seguimiento publico validando documento + ticket.
     */
    public function trackEvidence(Request $request, TicketEvidence $evidence)
    {
        $request->validate([
            'document_number' => 'required|string|max:30',
            'ticket_number' => 'required|numeric',
        ]);

        $ticket = Ticket::where('ticket_number', $request->ticket_number)
            ->whereHas('requester', function ($query) use ($request) {
                $query->where('document_number', $request->document_number);
            })
            ->first();

        if (!$ticket || (int) $ticket->ticket_id !== (int) $evidence->ticket_id) {
            abort(403, 'No autorizado para acceder a esta evidencia.');
        }

        if ($evidence->storage_disk === 'google_drive' && !empty($evidence->external_url)) {
            return redirect()->away($evidence->external_url);
        }

        if ($evidence->storage_disk === 'public' && !empty($evidence->file_path)) {
            if (!Storage::disk('public')->exists($evidence->file_path)) {
                abort(404, 'La evidencia no existe en el almacenamiento.');
            }

            return Storage::disk('public')->download($evidence->file_path, $evidence->file_name);
        }

        if ($evidence->storage_disk === 'filesystem' && !empty($evidence->file_path)) {
            $storage = app(LocalEvidenceStorageService::class);
            $absolutePath = $storage->resolveAbsolutePath($evidence->file_path);

            if (!File::exists($absolutePath)) {
                abort(404, 'La evidencia no existe en el almacenamiento fisico configurado.');
            }

            return response()->download($absolutePath, $evidence->file_name);
        }

        if ($evidence->storage_disk === 'custom' && !empty($evidence->file_path)) {
            $customStorage = app(CustomObjectStorageService::class);
            $downloadResponse = $customStorage->download($evidence->file_path, $evidence->file_name);

            if ($downloadResponse !== null) {
                return $downloadResponse;
            }
        }

        abort(404, 'No se encontró un origen válido para la evidencia.');
    }

    private function sanitizeRichText(string $html): string
    {
        $cleaned = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $cleaned = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $cleaned);
        $cleaned = preg_replace('/on\\w+\\s*=\\s*"[^"]*"/i', '', $cleaned ?? '');
        $cleaned = preg_replace('/on\\w+\\s*=\\s*\'[^\']*\'/i', '', $cleaned ?? '');
        $cleaned = preg_replace('/javascript\\s*:/i', '', $cleaned ?? '');

        return strip_tags(
            $cleaned ?? '',
            '<p><br><strong><b><em><i><u><ul><ol><li><a><blockquote><code><h3><h4><h5><h6><img>'
        );
    }

    private function processRichTextDescription(
        string $html,
        Ticket $ticket,
        int $requesterId,
        LocalRichTextImageStorageService $richTextStorage
    ): string {
        if (trim($html) === '') {
            return '';
        }

        $maxEmbeddedImages = 5;
        $maxImageSizeBytes = 5 * 1024 * 1024;
        $processedCount = 0;
        $processedHtml = $html;

        $previousLibXmlState = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        if ($loaded) {
            $images = [];
            foreach ($dom->getElementsByTagName('img') as $img) {
                $images[] = $img;
            }

            foreach ($images as $img) {
                $src = trim((string) $img->getAttribute('src'));
                if ($src === '') {
                    if ($img->parentNode) {
                        $img->parentNode->removeChild($img);
                    }
                    continue;
                }

                if (str_starts_with($src, 'data:image/')) {
                    if ($processedCount >= $maxEmbeddedImages) {
                        if ($img->parentNode) {
                            $img->parentNode->removeChild($img);
                        }
                        continue;
                    }

                    $decoded = $this->decodeEmbeddedImageDataUri($src);
                    if ($decoded === null || strlen($decoded['binary']) > $maxImageSizeBytes) {
                        if ($img->parentNode) {
                            $img->parentNode->removeChild($img);
                        }
                        continue;
                    }

                    $stored = $richTextStorage->storeBinary($decoded['binary'], $decoded['extension'], (string) $ticket->ticket_number);
                    $evidence = TicketEvidence::create([
                        'ticket_id' => $ticket->ticket_id,
                        'uploaded_by' => $requesterId,
                        'file_name' => 'descripcion_' . now()->format('Ymd_His') . '_' . $processedCount . '.' . $decoded['extension'],
                        'storage_disk' => 'richtext_filesystem',
                        'file_path' => $stored['relative_path'],
                        'mime_type' => $decoded['mime'],
                        'file_size' => strlen($decoded['binary']),
                        'external_url' => null,
                    ]);

                    $img->setAttribute('src', route('evidences.inline', $evidence->evidence_id));
                    if (!$img->hasAttribute('alt')) {
                        $img->setAttribute('alt', 'Imagen adjunta en la descripcion');
                    }
                    $img->removeAttribute('srcset');
                    $processedCount++;
                    continue;
                }

                if (!$this->isSafeImageSource($src)) {
                    if ($img->parentNode) {
                        $img->parentNode->removeChild($img);
                    }
                }
            }

            $processedHtml = $dom->saveHTML() ?: $html;
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousLibXmlState);

        $sanitized = $this->sanitizeRichText($processedHtml);

        $safeDom = new \DOMDocument('1.0', 'UTF-8');
        $loadedSafeDom = $safeDom->loadHTML('<?xml encoding="utf-8" ?>' . $sanitized, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        if (!$loadedSafeDom) {
            return $sanitized;
        }

        $images = [];
        foreach ($safeDom->getElementsByTagName('img') as $img) {
            $images[] = $img;
        }

        foreach ($images as $img) {
            $src = trim((string) $img->getAttribute('src'));
            if (!$this->isSafeImageSource($src)) {
                if ($img->parentNode) {
                    $img->parentNode->removeChild($img);
                }
            }
        }

        return $safeDom->saveHTML() ?: $sanitized;
    }

    private function decodeEmbeddedImageDataUri(string $source): ?array
    {
        if (!preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.+)$/s', $source, $matches)) {
            return null;
        }

        $mime = strtolower($matches[1]);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        if (!array_key_exists($mime, $allowed)) {
            return null;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false || $binary === '') {
            return null;
        }

        return [
            'mime' => $mime,
            'extension' => $allowed[$mime],
            'binary' => $binary,
        ];
    }

    private function isSafeImageSource(string $src): bool
    {
        if ($src === '') {
            return false;
        }

        if (str_starts_with($src, '/')) {
            return true;
        }

        return str_starts_with($src, 'http://') || str_starts_with($src, 'https://');
    }

    private function storeEvidenceFile(Ticket $ticket, $uploadedFile, int $requesterId, LocalEvidenceStorageService $localEvidenceStorage): ?TicketEvidence
    {
        $originalName = $uploadedFile->getClientOriginalName();
        $mimeType = $uploadedFile->getMimeType();
        $fileSize = $uploadedFile->getSize();

        $provider = AppSetting::getValue('evidence_storage_provider', 'filesystem');

        if ($provider === 'google_drive') {
            $googleDrive = app(GoogleDriveStorageService::class);
            if ($googleDrive->isConfigured()) {
                $uploaded = $googleDrive->uploadEvidence($uploadedFile);
                if ($uploaded) {
                    $evidence = TicketEvidence::create([
                        'ticket_id' => $ticket->ticket_id,
                        'uploaded_by' => $requesterId,
                        'file_name' => $originalName,
                        'storage_disk' => 'google_drive',
                        'file_path' => $uploaded['file_id'] ?? null,
                        'mime_type' => $uploaded['mime_type'] ?? $mimeType,
                        'file_size' => $uploaded['size'] ?? $fileSize,
                        'external_url' => $uploaded['web_view_link'] ?? $uploaded['web_content_link'] ?? null,
                    ]);
                    return $evidence;
                }
            }

            Log::warning('Google Drive seleccionado pero no disponible. Se usara almacenamiento local.', [
                'ticket_id' => $ticket->ticket_id,
            ]);
        }

        if ($provider === 'custom') {
            $customStorage = app(CustomObjectStorageService::class);
            if ($customStorage->isConfigured()) {
                $uploaded = $customStorage->uploadEvidence($uploadedFile, (string) $ticket->ticket_number);
                if ($uploaded) {
                    $evidence = TicketEvidence::create([
                        'ticket_id' => $ticket->ticket_id,
                        'uploaded_by' => $requesterId,
                        'file_name' => $originalName,
                        'storage_disk' => 'custom',
                        'file_path' => $uploaded['path'] ?? null,
                        'mime_type' => $uploaded['mime_type'] ?? $mimeType,
                        'file_size' => $uploaded['size'] ?? $fileSize,
                        'external_url' => $uploaded['url'] ?? null,
                    ]);
                    return $evidence;
                }
            }

            Log::warning('Proveedor custom seleccionado pero no disponible/configurado. Se usara almacenamiento local.', [
                'ticket_id' => $ticket->ticket_id,
            ]);
        }

        $stored = $localEvidenceStorage->store($uploadedFile, (string) $ticket->ticket_number);

        $evidence = TicketEvidence::create([
            'ticket_id' => $ticket->ticket_id,
            'uploaded_by' => $requesterId,
            'file_name' => $originalName,
            'storage_disk' => 'filesystem',
            'file_path' => $stored['relative_path'],
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'external_url' => null,
        ]);

        return $evidence;
    }

    /**
     * Calificar ticket
     */
    public function rate(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => [
                'nullable',
                'string',
                Rule::requiredIf(fn () => (int) $request->input('rating') !== 5),
            ],
        ], [
            'comment.required' => 'Debes agregar una observación cuando la calificación sea menor a 5 estrellas.',
        ]);

        $ticket = Ticket::where('requester_id', Auth::id())->findOrFail($id);
        
        if ($ticket->status != 3) {
             return back()->with('error', 'Solo se pueden calificar tickets completados.');
        }

        if ((int) $request->rating !== 5 && !$this->hasMeaningfulFeedback($request->comment)) {
            return back()
                ->withInput()
                ->withErrors([
                    'comment' => 'La observación es obligatoria y debe tener mínimo 15 caracteres con contenido descriptivo.',
                ]);
        }

        $ticket->update([
            'rating' => $request->rating,
            'feedback' => $request->comment // Mapping 'comment' input to 'feedback' column
        ]);

        // Send email to Admins
        try {
            $adminRole = \App\Models\UserRole::where('role_name', 'Admin')->first();
            if ($adminRole) {
                // Get all active admins
                $admins = \App\Models\User::where('role_id', $adminRole->role_id)->where('is_active', true)->get();
                
                foreach ($admins as $admin) {
                    if ($admin->user_email) {
                        \Illuminate\Support\Facades\Mail::to($admin->user_email)->send(new \App\Mail\ServiceRated($ticket));
                    }
                }
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error('Error sending rating email: ' . $e->getMessage());
        }

        return back()->with('success', 'Gracias por tu calificación.');
    }

    /**
     * Calificar ticket desde la vista pública
     */
    public function ratePublic(Request $request)
    {
        $request->validate([
            'ticket_number' => 'required|numeric',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => [
                'nullable',
                'string',
                Rule::requiredIf(fn () => (int) $request->input('rating') !== 5),
            ],
        ], [
            'comment.required' => 'Debes agregar una observación cuando la calificación sea menor a 5 estrellas.',
        ]);

        $ticket = Ticket::where('ticket_number', $request->ticket_number)->firstOrFail();
        
        if ($ticket->status != 3) {
             return back()->with('error', 'Solo se pueden calificar tickets completados.');
        }

        if ((int) $request->rating !== 5 && !$this->hasMeaningfulFeedback($request->comment)) {
            return back()
                ->withInput()
                ->withErrors([
                    'comment' => 'La observación es obligatoria y debe tener mínimo 15 caracteres con contenido descriptivo.',
                ]);
        }

        $ticket->update([
            'rating' => $request->rating,
            'feedback' => $request->comment
        ]);

        // Send email to Admins
        try {
            $adminRole = \App\Models\UserRole::where('role_name', 'Admin')->first();
            if ($adminRole) {
                $admins = \App\Models\User::where('role_id', $adminRole->role_id)->where('is_active', true)->get();
                foreach ($admins as $admin) {
                    if ($admin->user_email) {
                        \Illuminate\Support\Facades\Mail::to($admin->user_email)->send(new \App\Mail\ServiceRated($ticket));
                    }
                }
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error('Error sending rating email: ' . $e->getMessage());
        }

        return redirect()->route('service-management.track', ['ticket_number' => $request->ticket_number])->with('success', 'Gracias por tu calificación.');
    }

    private function isTicketClosedOrTerminated(Ticket $ticket): bool
    {
        return in_array((int) $ticket->status, [3, 4], true);
    }

    private function composeMessageWithAttachmentIds(string $message, array $attachmentIds): string
    {
        $cleanMessage = trim($message);
        if (empty($attachmentIds)) {
            return $cleanMessage;
        }

        $serializedIds = implode(',', array_map('intval', $attachmentIds));
        return $cleanMessage . "\n\n[attachments:" . $serializedIds . "]";
    }

    private function hasPendingCompletedTicketToRate(int $requesterId): bool
    {
        return $this->pendingCompletedTicketsQuery($requesterId)->exists();
    }

    private function pendingCompletedTicketsQuery(int $requesterId)
    {
        return Ticket::where('requester_id', $requesterId)
            ->where('status', 3)
            ->where(function ($query) {
                $query->whereNull('rating')
                    ->orWhere('rating', 0);
            });
    }

    private function hasMeaningfulFeedback(?string $comment): bool
    {
        $text = trim((string) $comment);
        return $this->hasMeaningfulText($text, 15, 10);
    }

    private function hasMeaningfulText(?string $text, int $minLength, int $minAlnum): bool
    {
        $value = trim((string) $text);
        if ($value === '') {
            return false;
        }

        if (mb_strlen($value) < $minLength) {
            return false;
        }

        $alnumOnly = preg_replace('/[^\p{L}\p{N}]+/u', '', $value) ?? '';
        return mb_strlen($alnumOnly) >= $minAlnum;
    }

    private function appendPriorityJustificationToRequesterInfo(string $requesterInfoHtml, int $priority, string $justification): string
    {
        if (!in_array($priority, [3, 4], true) || trim($justification) === '') {
            return $requesterInfoHtml;
        }

        $priorityLabel = $priority === 4 ? 'Justificacion de prioridad urgente' : 'Justificacion de prioridad alta';
        $safeJustification = nl2br(e(trim($justification)));

        $segment = '<p><strong>' . $priorityLabel . ':</strong><br>' . $safeJustification . '</p>';

        return trim($requesterInfoHtml) !== ''
            ? rtrim($requesterInfoHtml) . '<hr>' . $segment
            : $segment;
    }
}

