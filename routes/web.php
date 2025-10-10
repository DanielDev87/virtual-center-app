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
    
    // Gestión de servicios
    Route::prefix('service-management')->name('service-management.')->group(function () {
        Route::get('/', [ServiceManagementController::class, 'index'])->name('index');
        Route::get('/create', [ServiceManagementController::class, 'create'])->name('create');
        Route::post('/store', [ServiceManagementController::class, 'store'])->name('store');
        Route::get('/{id}', [ServiceManagementController::class, 'show'])->name('show');
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
        Route::get('/', [CollaboratorsController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [CollaboratorsController::class, 'profile'])->name('profile');
        Route::get('/tasks', [CollaboratorsController::class, 'tasks'])->name('tasks');
        Route::get('/projects', [CollaboratorsController::class, 'projects'])->name('projects');
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

