# Manual Técnico - Sistema A-DDIE
## Documentación para Desarrolladores

---

## Tabla de Contenidos

1. [Información General](#información-general)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Requisitos del Sistema](#requisitos-del-sistema)
4. [Instalación y Configuración](#instalación-y-configuración)
5. [Estructura del Proyecto](#estructura-del-proyecto)
6. [Base de Datos](#base-de-datos)
7. [Modelos y Relaciones](#modelos-y-relaciones)
8. [Controladores](#controladores)
9. [Rutas](#rutas)
10. [Vistas y Frontend](#vistas-y-frontend)
11. [Autenticación y Autorización](#autenticación-y-autorización)
12. [Funcionalidades Principales](#funcionalidades-principales)
13. [APIs y Endpoints](#apis-y-endpoints)
14. [Mantenimiento](#mantenimiento)
15. [Solución de Problemas](#solución-de-problemas)

---

## Información General

### Descripción del Proyecto

**Nombre**: A-DDIE (Analysis, Design, Development, Implementation, Evaluation)  
**Versión**: 1.0  
**Framework**: Laravel 10.x  
**PHP**: 8.4.13  
**Base de Datos**: MySQL/MariaDB  

### Propósito

Sistema de gestión de servicios educativos que integra la metodología ADDIE con prácticas SCRUM para la gestión efectiva de proyectos y tickets de soporte.

### Características Principales

- ✅ Gestión de tickets con sistema multi-mediador
- ✅ Seguimiento de progreso acumulativo
- ✅ Metodología ADDIE integrada (5 fases)
- ✅ Gestión de proyectos con SCRUM (Sprints y Tareas)
- ✅ Sistema de calificación y evaluación
- ✅ Reapertura de tickets con seguimiento separado
- ✅ Reportes exportables a CSV
- ✅ Dashboard con métricas y gráficos (Chart.js)
- ✅ Paginación de 10 items por página
- ✅ Roles y permisos (Admin, Monitor, Contributor, Requester)

---

## Arquitectura del Sistema

### Patrón de Arquitectura

**MVC (Model-View-Controller)** implementado por Laravel

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Routes    │ (web.php)
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Controllers │ (Lógica de negocio)
└──────┬──────┘
       │
       ├──────────┐
       ▼          ▼
┌──────────┐  ┌──────┐
│  Models  │  │ Views│
└──────────┘  └──────┘
       │
       ▼
┌─────────────┐
│  Database   │
└─────────────┘
```

### Stack Tecnológico

**Backend**:
- Laravel 10.x (PHP Framework)
- Eloquent ORM
- Blade Template Engine
- Laravel Authentication

**Frontend**:
- Bootstrap 5.3.0
- Font Awesome 6.4.0
- Chart.js 3.9.1
- JavaScript (Vanilla)

**Base de Datos**:
- MySQL 8.0+ / MariaDB 10.3+

**Herramientas**:
- Composer (Gestión de dependencias PHP)
- NPM (Gestión de dependencias JS)
- Artisan (CLI de Laravel)

---

## Requisitos del Sistema

### Requisitos de Software

- **PHP**: >= 8.1
- **Composer**: >= 2.0
- **MySQL/MariaDB**: >= 8.0 / >= 10.3
- **Servidor Web**: Apache 2.4+ o Nginx 1.18+
- **Node.js**: >= 16.x (para desarrollo frontend)
- **NPM**: >= 8.x

### Extensiones PHP Requeridas

```
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- PDO_MySQL
- Tokenizer
- XML
```

### Requisitos de Hardware (Mínimos)

- **CPU**: 2 cores
- **RAM**: 2 GB
- **Disco**: 10 GB disponibles

---

## Instalación y Configuración

### 1. Clonar el Repositorio

```bash
git clone <repository-url>
cd virtual-center-app
```

### 2. Instalar Dependencias

```bash
# Dependencias PHP
composer install

# Dependencias JavaScript (opcional, para desarrollo)
npm install
```

### 3. Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### 4. Configurar Base de Datos

Editar `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=addie_db
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
```

### 5. Crear Base de Datos

```sql
CREATE DATABASE addie_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Ejecutar Migraciones

```bash
# Ejecutar todas las migraciones
php artisan migrate

# Si necesitas rehacer todo
php artisan migrate:fresh
```

### 7. Ejecutar Seeders (Opcional)

```bash
# Ejecutar todos los seeders
php artisan db:seed

# O ejecutar seeders específicos
php artisan db:seed --class=RequestTypeSeeder
php artisan db:seed --class=TestUsersSeeder
```

### 8. Configurar Permisos

```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Windows (ejecutar como administrador)
# No requiere cambios de permisos generalmente
```

### 9. Iniciar Servidor de Desarrollo

```bash
php artisan serve
```

Acceder a: `http://localhost:8000`

---

## Estructura del Proyecto

### Directorios Principales

```
virtual-center-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Controladores
│   │   └── Middleware/         # Middleware personalizado
│   ├── Models/                 # Modelos Eloquent
│   └── Providers/              # Service Providers
├── config/                     # Archivos de configuración
├── database/
│   ├── migrations/             # Migraciones de BD
│   └── seeders/                # Seeders
├── public/                     # Archivos públicos (CSS, JS, imágenes)
├── resources/
│   └── views/                  # Vistas Blade
│       ├── admin/              # Vistas de administrador
│       ├── contributors/       # Vistas de colaboradores
│       ├── service-management/ # Vistas de solicitantes
│       ├── dashboard/          # Dashboard principal
│       └── layouts/            # Layouts base
├── routes/
│   └── web.php                 # Definición de rutas
├── storage/                    # Almacenamiento (logs, cache)
└── manuales/                   # Documentación de usuario
```

### Archivos de Configuración Importantes

- `.env` - Variables de entorno
- `config/app.php` - Configuración general de la aplicación
- `config/database.php` - Configuración de base de datos
- `config/auth.php` - Configuración de autenticación

---

## Base de Datos

### Diagrama ER Simplificado

```
┌─────────────┐       ┌──────────────┐       ┌─────────────┐
│    users    │───────│   tickets    │───────│ticket_progress│
└─────────────┘       └──────────────┘       └─────────────┘
       │                     │
       │                     │
       ▼                     ▼
┌─────────────┐       ┌──────────────┐
│ user_roles  │       │ticket_assign.│
└─────────────┘       └──────────────┘
                             │
                             ▼
                      ┌──────────────┐
                      │job_positions │
                      └──────────────┘
```

### Tablas Principales

#### 1. `users`
Almacena información de usuarios del sistema.

**Campos clave**:
- `user_id` (PK)
- `user_name`
- `user_email` (unique)
- `password`
- `role_id` (FK → user_roles)
- `is_active`

#### 2. `tickets`
Tickets/solicitudes de servicio.

**Campos clave**:
- `ticket_id` (PK)
- `ticket_number` (unique)
- `title`
- `type`
- `status` (1=Pendiente, 2=En Progreso, 3=Completado, 4=Cancelado)
- `priority` (low, medium, high, urgent)
- `progress_percentage` (0-100)
- `current_phase` (Analysis, Design, Development, Implementation, Evaluation)
- `requester_id` (FK → users)
- `mediator_id` (FK → users) - Mediador principal
- `resource_link` - URL del recurso generado
- `is_reopened` - Indica si fue reabierto
- `reopened_at` - Fecha de reapertura
- `rating` (1-5) - Calificación del solicitante
- `feedback` - Retroalimentación textual

#### 3. `ticket_assignments`
Asignaciones de colaboradores a tickets (sistema multi-mediador).

**Campos clave**:
- `assignment_id` (PK)
- `ticket_id` (FK → tickets)
- `user_id` (FK → users)
- `job_position_id` (FK → job_positions)
- `status` (active, removed)
- `assigned_at`

#### 4. `ticket_progress`
Historial de avances de tickets.

**Campos clave**:
- `progress_id` (PK)
- `ticket_id` (FK → tickets)
- `user_id` (FK → users) - Quien registró el avance
- `progress_percentage` (0-100)
- `progress_description`
- `created_at`

#### 5. `user_roles`
Roles del sistema.

**Campos clave**:
- `role_id` (PK)
- `role_name` (Admin, Monitor, Contributor, Requester)
- `is_active`

#### 6. `job_positions`
Puestos de trabajo para asignaciones.

**Campos clave**:
- `job_position_id` (PK)
- `position_name`
- `description`
- `color`
- `is_active`

#### 7. `sprints`
Sprints de metodología SCRUM.

**Campos clave**:
- `sprint_id` (PK)
- `ticket_id` (FK → tickets)
- `sprint_name`
- `description`
- `status` (planned, active, completed)
- `start_date`
- `end_date`

#### 8. `project_tasks`
Tareas del proyecto (Kanban).

**Campos clave**:
- `task_id` (PK)
- `ticket_id` (FK → tickets)
- `sprint_id` (FK → sprints, nullable)
- `title`
- `description`
- `status` (to_do, in_progress, done)
- `assigned_to` (FK → users)

### Migraciones Importantes

```bash
# Orden de ejecución de migraciones principales:
2024_01_01_000001_create_user_roles_table.php
2024_01_01_000002_create_users_table.php
2024_01_01_000014_create_tickets_table.php
2025_11_25_203103_create_ticket_progress_table.php
2025_11_26_160700_add_progress_percentage_to_tickets_table.php
2025_11_27_102531_add_addie_fields_to_tickets_table.php
2025_11_27_102535_create_sprints_table.php
2025_11_27_102536_create_project_tasks_table.php
2025_11_27_174510_add_closing_fields_to_tickets_table.php
```

---

## Modelos y Relaciones

### Modelo: User

**Ubicación**: `app/Models/User.php`

**Relaciones**:
```php
// Relación con rol
public function role()
{
    return $this->belongsTo(UserRole::class, 'role_id', 'role_id');
}

// Relación con puestos de trabajo
public function jobPositions()
{
    return $this->belongsToMany(JobPosition::class, 'job_position_user');
}

// Tickets asignados (multi-mediador)
public function assignedTickets()
{
    return $this->belongsToMany(Ticket::class, 'ticket_assignments')
                ->wherePivot('status', 'active');
}
```

### Modelo: Ticket

**Ubicación**: `app/Models/Ticket.php`

**Relaciones**:
```php
// Solicitante
public function requester()
{
    return $this->belongsTo(User::class, 'requester_id', 'user_id');
}

// Mediador principal
public function mediator()
{
    return $this->belongsTo(User::class, 'mediator_id', 'user_id');
}

// Asignaciones de equipo
public function assignments()
{
    return $this->hasMany(TicketAssignment::class, 'ticket_id', 'ticket_id');
}

// Mediadores activos
public function activeMediators()
{
    return $this->belongsToMany(User::class, 'ticket_assignments')
                ->wherePivot('status', 'active')
                ->withPivot('job_position_id', 'assigned_at');
}

// Historial de progreso
public function progress()
{
    return $this->hasMany(TicketProgress::class, 'ticket_id', 'ticket_id');
}

// Sprints
public function sprints()
{
    return $this->hasMany(Sprint::class, 'ticket_id', 'ticket_id');
}

// Tareas del proyecto
public function projectTasks()
{
    return $this->hasMany(ProjectTask::class, 'ticket_id', 'ticket_id');
}
```

**Casts**:
```php
protected $casts = [
    'current_phase' => 'string',
    'reopened_at' => 'datetime',
];
```

---

## Controladores

### AdminTicketController

**Ubicación**: `app/Http/Controllers/AdminTicketController.php`

**Métodos principales**:

```php
// Listar tickets (paginado 10)
public function index(Request $request)

// Ver detalle de ticket
public function show($id)

// Asignar mediador al equipo
public function assign(Request $request, $id)

// Remover mediador del equipo
public function removeAssignment($assignmentId)

// Establecer prioridad
public function setPriority(Request $request, $id)

// Cerrar ticket (validaciones estrictas)
public function close(Request $request, $id)

// Reabrir ticket
public function reopen(Request $request, $id)

// Calificar ticket (evaluación ADDIE)
public function rate(Request $request, $id)
```

**Validaciones de cierre**:
```php
// Requisitos para marcar como "Completado":
1. progress_percentage == 100
2. current_phase in ['Implementation', 'Evaluation']
3. resource_link != null
```

### ContributorController

**Ubicación**: `app/Http/Controllers/ContributorController.php`

**Métodos principales**:

```php
// Dashboard del colaborador
public function dashboard()

// Ver ticket asignado
public function show($id)

// Registrar avance (progreso acumulativo)
public function storeProgress(Request $request, $id)
```

**Lógica de progreso acumulativo**:
```php
// El nuevo porcentaje reemplaza al anterior
$ticket->update([
    'progress_percentage' => $request->progress_percentage
]);

// Se crea registro en ticket_progress
TicketProgress::create([
    'ticket_id' => $ticket->ticket_id,
    'user_id' => Auth::id(),
    'progress_percentage' => $request->progress_percentage,
    'progress_description' => $request->progress_description
]);
```

### ServiceManagementController

**Ubicación**: `app/Http/Controllers/ServiceManagementController.php`

**Métodos principales**:

```php
// Dashboard del solicitante
public function index()

// Crear nueva solicitud
public function create()
public function store(Request $request)

// Ver detalle de solicitud
public function show($id)
```

### DashboardController

**Ubicación**: `app/Http/Controllers/DashboardController.php`

**Métricas calculadas**:

```php
// Estadísticas generales
$stats = [
    'total_tickets',
    'pending_tickets',
    'in_progress_tickets',
    'completed_tickets',
    'avg_progress',
    'high_priority'
];

// Métricas de rendimiento
$fastestTickets // 5 tickets más rápidos
$averageRating // Promedio de calificaciones
$ratingDistribution // Distribución 1-5 estrellas
$avgCompletionTime // Tiempo promedio en horas
```

### ReportsController

**Ubicación**: `app/Http/Controllers/ReportsController.php`

**Reportes disponibles**:

```php
// Reporte de tickets (CSV)
public function ticketsReport(Request $request)

// Reporte de colaboradores (CSV)
public function collaboratorsReport(Request $request)

// Reporte de progreso (CSV)
public function progressReport(Request $request)
```

---

## Rutas

### Archivo: `routes/web.php`

#### Rutas Públicas

```php
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
```

#### Rutas de Administrador

```php
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    // Tickets
    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{id}', [AdminTicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{id}/assign', [AdminTicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{id}/priority', [AdminTicketController::class, 'setPriority'])->name('tickets.priority');
    Route::post('/tickets/{id}/close', [AdminTicketController::class, 'close'])->name('tickets.close');
    Route::post('/tickets/{id}/reopen', [AdminTicketController::class, 'reopen'])->name('tickets.reopen');
    Route::post('/tickets/{id}/rate', [AdminTicketController::class, 'rate'])->name('tickets.rate');
    
    // Usuarios
    Route::resource('users', AdminUserController::class);
    
    // Roles
    Route::resource('roles', AdminRoleController::class);
    
    // Reportes
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::post('/reports/tickets', [ReportsController::class, 'ticketsReport'])->name('reports.tickets');
    Route::post('/reports/collaborators', [ReportsController::class, 'collaboratorsReport'])->name('reports.collaborators');
    Route::post('/reports/progress', [ReportsController::class, 'progressReport'])->name('reports.progress');
});
```

#### Rutas de Colaborador

```php
Route::middleware(['auth', 'role:Contributor'])->prefix('contributors')->name('contributors.')->group(function () {
    Route::get('/dashboard', [ContributorController::class, 'dashboard'])->name('dashboard');
    Route::get('/tickets/{id}', [ContributorController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{id}/progress', [ContributorController::class, 'storeProgress'])->name('tickets.progress');
});
```

#### Rutas de Solicitante

```php
Route::middleware(['auth', 'role:Requester'])->prefix('service-management')->name('service-management.')->group(function () {
    Route::get('/', [ServiceManagementController::class, 'index'])->name('index');
    Route::get('/create', [ServiceManagementController::class, 'create'])->name('create');
    Route::post('/', [ServiceManagementController::class, 'store'])->name('store');
    Route::get('/{id}', [ServiceManagementController::class, 'show'])->name('show');
});
```

---

## Vistas y Frontend

### Layouts

#### Layout de Administrador
**Archivo**: `resources/views/layouts/admin.blade.php`

**Características**:
- Sidebar fijo con navegación
- Nombre de la app "A-DDIE"
- Menú de navegación completo
- Área de contenido principal

#### Layout de Colaborador
**Archivo**: `resources/views/layouts/contributor.blade.php`

**Características**:
- Navbar superior
- Botón de tema (toggle)
- Área de contenido

#### Layout de Solicitante
**Archivo**: `resources/views/layouts/requester.blade.php`

**Características**:
- Navbar con botón "Nueva Solicitud"
- Diseño limpio y simple

### Componentes Reutilizables

#### Barra de Progreso
```blade
<div class="progress" style="height: 20px;">
    <div class="progress-bar bg-info" 
         style="width: {{ $ticket->progress_percentage }}%;">
        {{ $ticket->progress_percentage }}%
    </div>
</div>
```

#### Badge de Estado
```blade
@php
    $statusColors = [
        1 => 'secondary', // Pendiente
        2 => 'warning',   // En Progreso
        3 => 'success',   // Completado
        4 => 'danger'     // Cancelado
    ];
@endphp
<span class="badge bg-{{ $statusColors[$ticket->status] }}">
    {{ $statusNames[$ticket->status] }}
</span>
```

#### Calificación con Estrellas
```blade
<div id="rating-stars">
    @for($i = 1; $i <= 5; $i++)
        <i class="fas fa-star star" 
           data-rating="{{ $i }}" 
           onclick="setRating({{ $i }})"></i>
    @endfor
</div>
```

### Gráficos (Chart.js)

**Ubicación**: `resources/views/dashboard/index.blade.php`

```javascript
// Gráfico de distribución de calificaciones
new Chart(document.getElementById('ratingChart'), {
    type: 'pie',
    data: {
        labels: {!! json_encode($ratingDistribution->keys()) !!},
        datasets: [{
            data: {!! json_encode($ratingDistribution->values()) !!},
            backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#28a745']
        }]
    }
});
```

---

## Autenticación y Autorización

### Middleware de Roles

**Archivo**: `app/Http/Middleware/CheckRole.php`

```php
public function handle($request, Closure $next, $role)
{
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    
    if (Auth::user()->role->role_name !== $role) {
        abort(403, 'Acceso no autorizado');
    }
    
    return $next($request);
}
```

### Protección de Rutas

```php
// En routes/web.php
Route::middleware(['auth', 'role:Admin'])->group(function () {
    // Rutas solo para administradores
});
```

### Verificación en Vistas

```blade
@if(Auth::user()->role->role_name === 'Admin')
    <!-- Contenido solo para admin -->
@endif
```

---

## Funcionalidades Principales

### 1. Sistema Multi-Mediador

**Concepto**: Un ticket puede tener múltiples colaboradores con diferentes roles.

**Implementación**:
```php
// Tabla: ticket_assignments
- ticket_id
- user_id
- job_position_id
- status (active/removed)
```

**Flujo**:
1. Admin asigna colaborador + puesto de trabajo
2. Se crea registro en `ticket_assignments`
3. Colaborador ve el ticket en su dashboard
4. Puede registrar avances
5. Admin puede remover del equipo (status = 'removed')

### 2. Progreso Acumulativo

**Concepto**: El progreso es acumulativo, no incremental.

**Implementación**:
```php
// Al registrar avance:
$ticket->update(['progress_percentage' => $newPercentage]);

// Se guarda historial:
TicketProgress::create([
    'progress_percentage' => $newPercentage,
    'progress_description' => $description
]);
```

### 3. Reapertura de Tickets

**Concepto**: Permite reabrir tickets completados para hacer cambios.

**Efectos**:
```php
$ticket->update([
    'status' => 2,              // En Progreso
    'is_reopened' => true,
    'reopened_at' => now(),
    'progress_percentage' => 0, // Reinicia progreso
    'rating' => null,           // Borra calificación
    'feedback' => null
]);
```

**Vista de avances**:
- Separa avances en dos secciones
- "Avances de Reapertura" (después de `reopened_at`)
- "Avances Anteriores" (antes de `reopened_at`)

### 4. Validaciones de Cierre

**Requisitos para "Completado"**:
```php
if ($ticket->progress_percentage < 100) {
    return error('Progreso debe estar al 100%');
}

if (!in_array($ticket->current_phase, ['Implementation', 'Evaluation'])) {
    return error('Debe completar fases ADDIE');
}

if (empty($request->resource_link)) {
    return error('Debe proporcionar enlace al recurso');
}
```

### 5. Sistema de Calificación

**Implementación**:
```php
// Campos en tickets:
- rating (1-5)
- feedback (texto)

// Solo disponible para tickets completados
// Se borra al reabrir (permite nueva evaluación)
```

---

## APIs y Endpoints

### Endpoints de Tickets

```
GET    /admin/tickets              - Listar tickets (paginado)
GET    /admin/tickets/{id}         - Ver detalle
POST   /admin/tickets/{id}/assign  - Asignar mediador
POST   /admin/tickets/{id}/close   - Cerrar ticket
POST   /admin/tickets/{id}/reopen  - Reabrir ticket
POST   /admin/tickets/{id}/rate    - Calificar ticket
```

### Endpoints de Reportes

```
POST   /admin/reports/tickets       - Exportar reporte de tickets (CSV)
POST   /admin/reports/collaborators - Exportar reporte de colaboradores (CSV)
POST   /admin/reports/progress      - Exportar reporte de progreso (CSV)
```

### Respuestas

**Éxito**:
```php
return back()->with('success', 'Operación exitosa');
```

**Error**:
```php
return back()->with('error', 'Mensaje de error');
```

---

## Mantenimiento

### Comandos Artisan Útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ver rutas
php artisan route:list

# Ver modelos
php artisan model:show Ticket

# Crear migración
php artisan make:migration create_table_name

# Crear modelo
php artisan make:model ModelName

# Crear controlador
php artisan make:controller ControllerName
```

### Backup de Base de Datos

```bash
# Exportar
mysqldump -u root -p addie_db > backup_$(date +%Y%m%d).sql

# Importar
mysql -u root -p addie_db < backup_20241201.sql
```

### Logs

**Ubicación**: `storage/logs/laravel.log`

```bash
# Ver últimas líneas
tail -f storage/logs/laravel.log

# Limpiar logs
> storage/logs/laravel.log
```

---

## Solución de Problemas

### Error: "Cannot redeclare method"

**Causa**: Método duplicado en modelo  
**Solución**: Buscar y eliminar duplicados

### Error: "Ambiguous column 'status'"

**Causa**: Múltiples tablas con columna `status`  
**Solución**: Especificar tabla: `tickets.status`

### Error: "Call to undefined method assignedTickets()"

**Causa**: Relación no definida en modelo  
**Solución**: Agregar método de relación en `User.php`

### Paginación no funciona

**Causa**: Usando `get()` en lugar de `paginate()`  
**Solución**: Cambiar a `->paginate(10)`

### Gráficos no se muestran

**Causa**: Chart.js no cargado  
**Solución**: Verificar CDN en layout

---

## Información de Contacto

**Desarrollador**: Daniel Felipe Agudelo Molina  
**Email**: daniel_agudelo54232@elpoli.edu.co 
**Versión**: 1.0  
**Fecha**: Diciembre 2025

---

**Fin del Manual Técnico**
