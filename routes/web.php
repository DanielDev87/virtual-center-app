<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CollaboratorsController;
use App\Http\Controllers\ServiceManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí puede registrar las rutas web de la aplicación. Estas rutas son
| cargadas por el RouteServiceProvider y todas se asignarán al grupo de
| middleware "web".
|
*/

// Ruta principal - Página de inicio
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/branding-assets/{filename}', [\App\Http\Controllers\TechnicalBrandingSettingsController::class, 'asset'])
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('branding-assets.show');

// Rutas públicas
Route::get('/collaborators', [CollaboratorsController::class, 'index'])->name('collaborators');

// Endpoint retirado: bloquear cualquier acceso legado
Route::any('/radio-station', function () {
    abort(404);
});

Route::prefix('service-management')->name('service-management.')->group(function () {
    Route::get('/create', [ServiceManagementController::class, 'create'])->name('create');
    Route::post('/store', [ServiceManagementController::class, 'store'])->name('store');
    Route::get('/track-ticket', [ServiceManagementController::class, 'track'])->name('track');
    Route::post('/track-ticket', [ServiceManagementController::class, 'searchTrack'])->name('searchTrack');
    Route::get('/track-ticket/evidences/{evidence}', [ServiceManagementController::class, 'trackEvidence'])->name('trackEvidence');
    Route::post('/track-ticket/message', [ServiceManagementController::class, 'postPublicMessage'])->name('trackMessage');
    Route::post('/track-ticket/rate', [ServiceManagementController::class, 'ratePublic'])->name('ratePublic');
    Route::post('/check-requester', [ServiceManagementController::class, 'checkRequester'])->name('checkRequester');
});

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {

    // Configuracion tecnica del almacenamiento fisico de evidencias
    Route::prefix('technical')->name('technical.')->middleware('role:Super Admin Tecnico')->group(function () {
        Route::get('/storage-settings', [\App\Http\Controllers\TechnicalStorageSettingsController::class, 'edit'])->name('storage-settings.edit');
        Route::put('/storage-settings', [\App\Http\Controllers\TechnicalStorageSettingsController::class, 'update'])->name('storage-settings.update');
        Route::get('/branding-settings', [\App\Http\Controllers\TechnicalBrandingSettingsController::class, 'edit'])->name('branding-settings.edit');
        Route::put('/branding-settings', [\App\Http\Controllers\TechnicalBrandingSettingsController::class, 'update'])->name('branding-settings.update');
        Route::get('/branding-settings/export', [\App\Http\Controllers\TechnicalBrandingSettingsController::class, 'export'])->name('branding-settings.export');
        Route::post('/branding-settings/import', [\App\Http\Controllers\TechnicalBrandingSettingsController::class, 'import'])->name('branding-settings.import');
    });

    // Evidencias de tickets (acceso autorizado por rol/relación al ticket)
    Route::get('/evidences/{evidence}', [\App\Http\Controllers\TicketEvidenceController::class, 'view'])->name('evidences.view');
    Route::get('/evidences/{evidence}/inline', [\App\Http\Controllers\TicketEvidenceController::class, 'inline'])->name('evidences.inline');
    
    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('role:Admin');
    Route::post('/returned-alerts/{assignmentId}/read', [DashboardController::class, 'markReturnedAlertAsRead'])
        ->name('returned-alerts.read');
    
    // Perfil de Usuario (accesible para todos los usuarios autenticados)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('update');
        Route::delete('/avatar', [\App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
    });
    
    // Gestión de Servicios (Solicitantes autenticados)
    Route::prefix('service-management')->name('service-management.')->group(function () {
        Route::get('/', [ServiceManagementController::class, 'index'])->name('index');
        Route::get('/{id}', [ServiceManagementController::class, 'show'])->name('show');
        Route::post('/{id}/rate', [ServiceManagementController::class, 'rate'])->name('rate');
        Route::get('/{id}/edit', [ServiceManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ServiceManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [ServiceManagementController::class, 'destroy'])->name('destroy');
    });
    
    // Monitor
    Route::prefix('monitor')->name('monitor.')->middleware('role:Monitor,Admin')->group(function () {
        Route::get('/', [MonitorController::class, 'index'])->name('index');
        Route::get('/reports', [MonitorController::class, 'reports'])->name('reports');
        Route::get('/analytics', [MonitorController::class, 'analytics'])->name('analytics');
    });
    
    // Colaboradores (área protegida)
    Route::prefix('contributors')->name('contributors.')->middleware('role:Contributor')->group(function () {
        Route::get('/', [\App\Http\Controllers\ContributorController::class, 'dashboard'])->name('dashboard');
        Route::get('/reports/requesters', [\App\Http\Controllers\ReportsController::class, 'requestersReportPage'])->name('reports.requesters');
        Route::get('/topic-tickets', [\App\Http\Controllers\ContributorController::class, 'topicTickets'])->name('topic-tickets.index');
        Route::get('/topic-tickets/count', [\App\Http\Controllers\ContributorController::class, 'topicTicketsCount'])->name('topic-tickets.count');
        Route::post('/topic-tickets/{id}/self-assign', [\App\Http\Controllers\ContributorController::class, 'selfAssignTopicTicket'])->name('topic-tickets.self-assign');
        Route::post('/tickets/{id}/join-request', [\App\Http\Controllers\ContributorController::class, 'requestToJoinTicket'])->name('tickets.join-request');
        Route::post('/join-requests/{joinRequestId}/approve', [\App\Http\Controllers\ContributorController::class, 'approveJoinRequest'])->name('join-requests.approve');
        Route::post('/join-requests/{joinRequestId}/reject', [\App\Http\Controllers\ContributorController::class, 'rejectJoinRequest'])->name('join-requests.reject');
        Route::get('/tickets/{id}', [\App\Http\Controllers\ContributorController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{id}/progress', [\App\Http\Controllers\ContributorController::class, 'storeProgress'])->name('tickets.progress');
        Route::post('/tickets/{id}/message', [\App\Http\Controllers\ContributorController::class, 'sendMessageToRequester'])->name('tickets.message');
        Route::post('/tickets/{id}/priority', [\App\Http\Controllers\ContributorController::class, 'setPriority'])->name('tickets.priority');
        Route::post('/tickets/{id}/transfer', [\App\Http\Controllers\ContributorController::class, 'transferTicket'])->name('tickets.transfer');
        Route::post('/tickets/{id}/associate', [\App\Http\Controllers\ContributorController::class, 'associateTicket'])->name('tickets.associate');
        Route::post('/{ticketId}/sprints', [\App\Http\Controllers\ContributorController::class, 'storeSprint'])->name('tickets.store-sprint');
        Route::post('/{ticketId}/tasks', [\App\Http\Controllers\ContributorController::class, 'storeTask'])->name('tickets.store-task');
        Route::patch('/tasks/{taskId}/assign-sprint', [\App\Http\Controllers\ContributorController::class, 'assignTaskSprint'])->name('tasks.assign-sprint');
        Route::patch('/{ticketId}/phase', [\App\Http\Controllers\ContributorController::class, 'updatePhase'])->name('tickets.update-phase');
        Route::patch('/sprints/{sprintId}/status', [\App\Http\Controllers\ContributorController::class, 'updateSprintStatus'])->name('tickets.update-sprint-status');
        Route::patch('/tasks/{taskId}/update-status', [\App\Http\Controllers\ContributorController::class, 'updateTaskStatus'])->name('tasks.update-status');
        Route::patch('/tickets/{id}/close', [\App\Http\Controllers\ContributorController::class, 'closeTicket'])->name('tickets.close');
    });

    // Operarios (vista móvil y resolutiva, sin gestión avanzada de sprints)
    Route::prefix('operario')->name('operario.')->middleware('role:Operario')->group(function () {
        Route::get('/', [\App\Http\Controllers\OperarioController::class, 'dashboard'])->name('dashboard');
        Route::get('/tickets/{id}', [\App\Http\Controllers\OperarioController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{id}/status', [\App\Http\Controllers\OperarioController::class, 'updateStatus'])->name('tickets.status');
        Route::post('/tickets/{id}/evidence', [\App\Http\Controllers\OperarioController::class, 'storeEvidence'])->name('tickets.evidence');
        Route::post('/tickets/{id}/return', [\App\Http\Controllers\OperarioController::class, 'returnTicket'])->name('tickets.return');
    });

    // Rutas de Admin de Área (acceso restringido al área asignada al usuario)
    Route::prefix('area-admin')->name('area-admin.')->middleware('role:Admin Área')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AreaAdminController::class, 'dashboard'])->name('dashboard');
        Route::patch('/topics/{id}/incident', [\App\Http\Controllers\AreaAdminController::class, 'updateTopicIncident'])->name('topics.incident');

        // Tickets
        Route::get('/tickets', [\App\Http\Controllers\AreaAdminController::class, 'tickets'])->name('tickets.index');
        Route::get('/tickets/{id}', [\App\Http\Controllers\AreaAdminController::class, 'showTicket'])->name('tickets.show');
        Route::post('/tickets/{id}/assign', [\App\Http\Controllers\AreaAdminController::class, 'assignMediator'])->name('tickets.assign');
        Route::post('/tickets/{id}/assign-mediator', [\App\Http\Controllers\AreaAdminController::class, 'assignMediatorToTicket'])->name('tickets.assign-mediator');
        Route::delete('/tickets/{ticketId}/assignments/{assignmentId}', [\App\Http\Controllers\AreaAdminController::class, 'removeAssignment'])->name('tickets.remove-assignment');
        Route::post('/tickets/{id}/priority', [\App\Http\Controllers\AreaAdminController::class, 'setPriority'])->name('tickets.priority');
        Route::post('/tickets/{id}/close', [\App\Http\Controllers\AreaAdminController::class, 'closeTicket'])->name('tickets.close');
        Route::post('/tickets/{id}/associate', [\App\Http\Controllers\AreaAdminController::class, 'associateTicket'])->name('tickets.associate');
        Route::post('/association-requests/{associationRequestId}/approve', [\App\Http\Controllers\AreaAdminController::class, 'approveAssociationRequest'])->name('association-requests.approve');
        Route::post('/association-requests/{associationRequestId}/reject', [\App\Http\Controllers\AreaAdminController::class, 'rejectAssociationRequest'])->name('association-requests.reject');
        Route::post('/tickets/{id}/audit/approve', [\App\Http\Controllers\AreaAdminController::class, 'approveOperarioCompletion'])->name('tickets.audit.approve');
        Route::post('/tickets/{id}/audit/reject', [\App\Http\Controllers\AreaAdminController::class, 'rejectOperarioCompletion'])->name('tickets.audit.reject');
        Route::post('/tickets/{id}/reopen', [\App\Http\Controllers\AreaAdminController::class, 'reopenTicket'])->name('tickets.reopen');
        Route::post('/tickets/{id}/rate', [\App\Http\Controllers\AreaAdminController::class, 'rateTicket'])->name('tickets.rate');

        // Reportes
        Route::get('/reports', [\App\Http\Controllers\AreaAdminController::class, 'reports'])->name('reports.index');
        Route::get('/reports/requesters', [\App\Http\Controllers\ReportsController::class, 'requestersReportPage'])->name('reports.requesters');
    });

    // Rutas de Administrador
    Route::prefix('admin')->name('admin.')->middleware('role:Admin,Monitor')->group(function () {
        // Carga masiva por archivo (solo Admin)
        Route::get('bulk-import', [\App\Http\Controllers\Admin\AdminBulkImportController::class, 'index'])
            ->middleware('role:Admin')
            ->name('bulk-import.index');
        Route::get('bulk-import/template/{entity}', [\App\Http\Controllers\Admin\AdminBulkImportController::class, 'downloadTemplate'])
            ->middleware('role:Admin')
            ->name('bulk-import.template');
        Route::post('bulk-import', [\App\Http\Controllers\Admin\AdminBulkImportController::class, 'store'])
            ->middleware('role:Admin')
            ->name('bulk-import.store');

        // Gestión de Roles
        Route::resource('roles', \App\Http\Controllers\AdminRoleController::class);
        
        // request-types (Tópicos)
        Route::resource('request-types', \App\Http\Controllers\Admin\AdminRequestTypeController::class)->except(['show']);
        
        // Gestión de Usuarios
        Route::resource('users', \App\Http\Controllers\AdminUserController::class);
        
        // Gestión de Tickets
        Route::get('tickets', [\App\Http\Controllers\AdminTicketController::class, 'index'])->name('tickets.index');
        Route::get('tickets/{id}', [\App\Http\Controllers\AdminTicketController::class, 'show'])->name('tickets.show');
        Route::post('tickets/{id}/assign', [\App\Http\Controllers\AdminTicketController::class, 'assignMediator'])->middleware('role:Admin')->name('tickets.assign');
        Route::post('tickets/{id}/priority', [\App\Http\Controllers\AdminTicketController::class, 'setPriority'])->middleware('role:Admin')->name('tickets.priority');
        Route::post('tickets/{id}/close', [\App\Http\Controllers\AdminTicketController::class, 'close'])->middleware('role:Admin')->name('tickets.close');
        Route::post('tickets/{id}/associate', [\App\Http\Controllers\AdminTicketController::class, 'associateTicket'])->middleware('role:Admin')->name('tickets.associate');
        Route::post('tickets/{id}/reopen', [\App\Http\Controllers\AdminTicketController::class, 'reopen'])->middleware('role:Admin')->name('tickets.reopen');
        Route::post('tickets/{id}/rate', [\App\Http\Controllers\AdminTicketController::class, 'rate'])->middleware('role:Admin')->name('tickets.rate');
        
        // Asignaciones Multi-Mediador
        Route::post('tickets/{id}/assign-mediator', [\App\Http\Controllers\AdminTicketController::class, 'assignMediatorToTicket'])->middleware('role:Admin')->name('tickets.assign-mediator');
        Route::delete('tickets/{ticketId}/assignments/{assignmentId}', [\App\Http\Controllers\AdminTicketController::class, 'removeAssignment'])->middleware('role:Admin')->name('tickets.remove-assignment');

        // Gestión de Proyectos (ADDIE + SCRUM)
        Route::prefix('projects')->name('projects.')->group(function () {
            Route::get('/{ticketId}/dashboard', [\App\Http\Controllers\ProjectManagementController::class, 'index'])->name('dashboard');
            Route::patch('/{ticketId}/phase', [\App\Http\Controllers\ProjectManagementController::class, 'updatePhase'])->name('update-phase');
            Route::post('/{ticketId}/sprints', [\App\Http\Controllers\ProjectManagementController::class, 'storeSprint'])->name('store-sprint');
            Route::patch('/sprints/{sprintId}/status', [\App\Http\Controllers\ProjectManagementController::class, 'updateSprintStatus'])->name('update-sprint-status');
            Route::post('/{ticketId}/tasks', [\App\Http\Controllers\ProjectManagementController::class, 'storeTask'])->name('store-task');
            Route::patch('/tasks/{taskId}/assign-sprint', [\App\Http\Controllers\ProjectManagementController::class, 'assignTaskSprint'])->name('assign-task-sprint');
            Route::patch('/tasks/{taskId}/update-status', [\App\Http\Controllers\ProjectManagementController::class, 'updateTaskStatus'])->name('update-task-status');
        });

        // Gestión Académica
        Route::prefix('academic')->name('academic.')->group(function () {
            Route::resource('institutions', \App\Http\Controllers\Admin\AdminInstitutionController::class);
            Route::resource('faculties', \App\Http\Controllers\Admin\AdminFacultyController::class);
            Route::resource('programs', \App\Http\Controllers\Admin\AdminProgramController::class);
            Route::resource('courses', \App\Http\Controllers\Admin\AdminCourseController::class);
            Route::resource('areas', \App\Http\Controllers\Admin\AdminAreaController::class);
        });
        
        // Módulo de Reportes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ReportsController::class, 'index'])->name('index');
            Route::get('/tickets', [\App\Http\Controllers\ReportsController::class, 'ticketsReport'])->name('tickets');
            Route::get('/collaborators', [\App\Http\Controllers\ReportsController::class, 'collaboratorsReport'])->name('collaborators');
            Route::get('/progress', [\App\Http\Controllers\ReportsController::class, 'progressReport'])->name('progress');
            Route::get('/topics', [\App\Http\Controllers\ReportsController::class, 'topicsReport'])->name('topics');
            Route::get('/requesters', [\App\Http\Controllers\ReportsController::class, 'requestersReport'])->name('requesters');
            Route::get('/requesters/view', [\App\Http\Controllers\ReportsController::class, 'requestersReportPage'])->name('requesters.view');
        });
        
        // Job Positions Management
        Route::resource('job-positions', \App\Http\Controllers\Admin\AdminJobPositionController::class);
    });
});

// Rutas AJAX para funcionalidades dinámicas
Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('/search', [HomeController::class, 'search'])->name('search');
    Route::get('/project-details/{id}', [HomeController::class, 'projectDetails'])->name('project-details');
    Route::post('/send-status', [HomeController::class, 'sendStatus'])->name('send-status');
    
    // Theme persistence
    Route::post('/theme', function (\Illuminate\Http\Request $request) {
        $request->validate(['theme' => 'required|in:light,dark']);
        session(['theme' => $request->theme]);
        session()->save(); // Explicitly save session for AJAX
        return response()->json(['success' => true]);
    })->name('theme');
});

// Ruta de fallback para páginas no encontradas
Route::fallback(function () {
    return view('errors.404');
});

