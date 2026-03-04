<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CollaboratorsController;
use App\Http\Controllers\RadioStationController;
use App\Http\Controllers\ServiceManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Ruta principal - Página de inicio
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas públicas
Route::get('/collaborators', [CollaboratorsController::class, 'index'])->name('collaborators');
Route::get('/radio-station', [RadioStationController::class, 'index'])->name('radio-station');

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    
    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Perfil de Usuario (accesible para todos los usuarios autenticados)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('update');
        Route::delete('/avatar', [\App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
    });
    
    // Gestión de Servicios (Solicitantes)
    Route::prefix('service-management')->name('service-management.')->group(function () {
        Route::get('/', [ServiceManagementController::class, 'index'])->name('index');
        Route::get('/create', [ServiceManagementController::class, 'create'])->name('create');
        Route::post('/store', [ServiceManagementController::class, 'store'])->name('store');
        Route::get('/{id}', [ServiceManagementController::class, 'show'])->name('show');
        Route::post('/{id}/rate', [ServiceManagementController::class, 'rate'])->name('rate');
        Route::get('/{id}/edit', [ServiceManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ServiceManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [ServiceManagementController::class, 'destroy'])->name('destroy');
    });
    
    // Monitor
    Route::prefix('monitor')->name('monitor.')->group(function () {
        Route::get('/', [MonitorController::class, 'index'])->name('index');
        Route::get('/reports', [MonitorController::class, 'reports'])->name('reports');
        Route::get('/analytics', [MonitorController::class, 'analytics'])->name('analytics');
    });
    
    // Colaboradores (área protegida)
    Route::prefix('contributors')->name('contributors.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ContributorController::class, 'dashboard'])->name('dashboard');
        Route::get('/tickets/{id}', [\App\Http\Controllers\ContributorController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{id}/progress', [\App\Http\Controllers\ContributorController::class, 'storeProgress'])->name('tickets.progress');
    });

    // Rutas de Administrador
    Route::prefix('admin')->name('admin.')->group(function () {
        // Gestión de Roles
        Route::resource('roles', \App\Http\Controllers\AdminRoleController::class);
        
        // Gestión de Usuarios
        Route::resource('users', \App\Http\Controllers\AdminUserController::class);
        
        // Gestión de Tickets
        Route::get('tickets', [\App\Http\Controllers\AdminTicketController::class, 'index'])->name('tickets.index');
        Route::get('tickets/{id}', [\App\Http\Controllers\AdminTicketController::class, 'show'])->name('tickets.show');
        Route::post('tickets/{id}/assign', [\App\Http\Controllers\AdminTicketController::class, 'assignMediator'])->name('tickets.assign');
        Route::post('tickets/{id}/priority', [\App\Http\Controllers\AdminTicketController::class, 'setPriority'])->name('tickets.priority');
        Route::post('tickets/{id}/close', [\App\Http\Controllers\AdminTicketController::class, 'close'])->name('tickets.close');
        Route::post('tickets/{id}/reopen', [\App\Http\Controllers\AdminTicketController::class, 'reopen'])->name('tickets.reopen');
        Route::post('tickets/{id}/rate', [\App\Http\Controllers\AdminTicketController::class, 'rate'])->name('tickets.rate');
        
        // Multi-Mediator Assignments
        Route::post('tickets/{id}/assign-mediator', [\App\Http\Controllers\AdminTicketController::class, 'assignMediatorToTicket'])->name('tickets.assign-mediator');
        Route::delete('tickets/{ticketId}/assignments/{assignmentId}', [\App\Http\Controllers\AdminTicketController::class, 'removeAssignment'])->name('tickets.remove-assignment');

        // Project Management (ADDIE + SCRUM)
        Route::prefix('projects')->name('projects.')->group(function () {
            Route::get('/{ticketId}/dashboard', [\App\Http\Controllers\ProjectManagementController::class, 'index'])->name('dashboard');
            Route::patch('/{ticketId}/phase', [\App\Http\Controllers\ProjectManagementController::class, 'updatePhase'])->name('update-phase');
            Route::post('/{ticketId}/sprints', [\App\Http\Controllers\ProjectManagementController::class, 'storeSprint'])->name('store-sprint');
            Route::patch('/sprints/{sprintId}/status', [\App\Http\Controllers\ProjectManagementController::class, 'updateSprintStatus'])->name('update-sprint-status');
            Route::post('/{ticketId}/tasks', [\App\Http\Controllers\ProjectManagementController::class, 'storeTask'])->name('store-task');
            Route::patch('/tasks/{taskId}/update-status', [\App\Http\Controllers\ProjectManagementController::class, 'updateTaskStatus'])->name('update-task-status');
        });

        // Gestión Académica
        Route::prefix('academic')->name('academic.')->group(function () {
            Route::resource('institutions', \App\Http\Controllers\Admin\AdminInstitutionController::class);
            Route::resource('faculties', \App\Http\Controllers\Admin\AdminFacultyController::class);
            Route::resource('programs', \App\Http\Controllers\Admin\AdminProgramController::class);
            Route::resource('courses', \App\Http\Controllers\Admin\AdminCourseController::class);
        });
        
        // Reports Module
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ReportsController::class, 'index'])->name('index');
            Route::get('/tickets', [\App\Http\Controllers\ReportsController::class, 'ticketsReport'])->name('tickets');
            Route::get('/collaborators', [\App\Http\Controllers\ReportsController::class, 'collaboratorsReport'])->name('collaborators');
            Route::get('/progress', [\App\Http\Controllers\ReportsController::class, 'progressReport'])->name('progress');
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
});

// Ruta de fallback para páginas no encontradas
Route::fallback(function () {
    return view('errors.404');
});

