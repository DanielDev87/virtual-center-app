<!DOCTYPE html>
<html lang="es" data-bs-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="max-upload-size" content="{{ ini_get('upload_max_filesize') }}">
    <title>@yield('title', 'Admin - ' . ($branding['app_name'] ?? 'Sistema de Soporte Universitario'))</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="{{ $branding['favicon_mime'] ?? 'image/png' }}" href="{{ $branding['favicon_url'] ?? asset('img/logomsula.png') }}">
    <link rel="apple-touch-icon" href="{{ $branding['favicon_url'] ?? asset('img/logomsula.png') }}">
    <style>
        :root {
            --brand-primary: {{ $branding['primary_color'] ?? '#0d6efd' }};
            --brand-secondary: {{ $branding['secondary_color'] ?? '#17a2b8' }};
        }
    </style>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            padding: 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #212529;
            width: 280px;
        }
        
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: 100%;
            min-height: 100vh;
            padding-top: 0;
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* Desktop: sidebar fija al lado izquierdo */
        @media (min-width: 992px) {
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 100;
                width: 250px;
            }
        }

        /* Móvil: sin margen izquierdo en main/navbar */
        @media (max-width: 991.98px) {
            main {
                margin-left: 0 !important;
            }
            .navbar {
                left: 0 !important;
            }
        }

        .sidebar-branding {
            position: sticky;
            top: 0;
            z-index: 3;
            background-color: #212529;
            min-height: 74px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .sidebar .nav-link {
            font-weight: 500;
            color: #adb5bd;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Evita que el texto desaparezca en hover cuando el tema es claro. */
        [data-bs-theme="light"] .sidebar .nav-link:hover,
        [data-bs-theme="light"] .sidebar .nav-link:focus {
            color: #fff !important;
            background-color: rgba(13, 110, 253, 0.65);
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: var(--brand-primary, #0d6efd);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }

        /* Boton de cierre de sesion: mantener contraste correcto en cualquier tema. */
        .sidebar .logout-nav-btn {
            background-color: transparent !important;
            color: #adb5bd;
        }

        .sidebar .logout-nav-btn:hover,
        .sidebar .logout-nav-btn:focus {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.1) !important;
        }

        [data-bs-theme="light"] .sidebar .logout-nav-btn:hover,
        [data-bs-theme="light"] .sidebar .logout-nav-btn:focus {
            color: #fff !important;
            background-color: rgba(13, 110, 253, 0.65) !important;
        }
        
        .sidebar-heading {
            font-size: .75rem;
            text-transform: uppercase;
            color: #6c757d;
            padding: 1rem 1rem 0.5rem;
            font-weight: 600;
        }
        
        main {
            margin-left: 250px;
        }
        
        .navbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 250px;
            z-index: 99;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            background-color: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }

        .navbar-top-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: #343a40;
            line-height: 1.2;
            min-width: 0;
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Desktop: mostrar titulo completo sin recorte */
        @media (min-width: 992px) {
            .navbar-top-title {
                white-space: normal;
                overflow: visible;
                text-overflow: clip;
            }
        }
        
        .content-wrapper {
            padding-top: 56px;
        }

        .admin-topbar-actions {
            gap: 0.5rem;
        }

        .admin-topbar-monitor-btn {
            white-space: nowrap;
        }

        @media (max-width: 991.98px) {
            .navbar-top-title {
                max-width: calc(100vw - 210px);
            }

            .admin-topbar-actions {
                gap: 0.35rem;
            }

            .admin-topbar-monitor-btn,
            #themeToggle {
                padding: 0.25rem 0.45rem;
            }

            #themeToggle {
                margin-right: 0 !important;
            }

            .admin-user-name {
                display: none;
            }
        }

        @media (max-width: 575.98px) {
            .navbar-top-title {
                max-width: calc(100vw - 180px);
                font-size: 0.95rem;
            }

            .admin-topbar-monitor-btn {
                min-width: 32px;
            }

            .navbar-text .rounded-circle,
            .navbar-text .fa-user-circle {
                margin-right: 0 !important;
            }
        }

        /* Adjustments for dark mode in admin panel */
        [data-bs-theme="dark"] .navbar {
            background-color: rgba(45, 45, 45, 0.96) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        [data-bs-theme="dark"] .navbar-top-title {
            color: #f1f3f5;
        }
        
        [data-bs-theme="dark"] body {
            background-color: var(--vc-dark-bg);
            color: var(--vc-dark-text);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    @php
        $currentRoleName = auth()->user()->role->role_name ?? null;
        $isMonitorUser = $currentRoleName === 'Monitor';
        $isTechnicalSuperAdmin = $currentRoleName === 'Super Admin Tecnico';
    @endphp

    <!-- Sidebar (offcanvas en móvil, fija en desktop) -->
    <nav class="sidebar offcanvas-lg offcanvas-start" id="adminSidebar" tabindex="-1" aria-labelledby="adminSidebarLabel">
        <div class="sidebar-sticky">
            <div class="sidebar-branding px-3 py-2 border-bottom border-secondary text-center position-relative">
                <!-- Botón cerrar solo en móvil -->
                <button type="button" class="btn-close btn-close-white d-lg-none position-absolute top-0 end-0 m-2"
                        data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Cerrar"></button>
                <img src="{{ $branding['logo_url'] ?? asset('img/logomsula.png') }}" alt="{{ $branding['app_name'] ?? 'Sistema de Soporte Universitario' }}" class="img-fluid mb-2 bg-white rounded p-2" style="max-height: 72px;">
                <small class="text-white">Panel Administrativo</small>
            </div>
            
            <ul class="nav flex-column">
                @if(!$isTechnicalSuperAdmin)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                       href="{{ route('dashboard') }}">
                        <i class="fas fa-chart-line"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}" 
                       href="{{ route('admin.tickets.index') }}">
                        <i class="fas fa-ticket-alt"></i>
                        Tickets
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
                       href="{{ route('admin.users.index') }}">
                        <i class="fas fa-users"></i>
                        Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" 
                       href="{{ route('admin.roles.index') }}">
                        <i class="fas fa-user-tag"></i>
                        Roles
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.request-types.*') ? 'active' : '' }}" 
                       href="{{ route('admin.request-types.index') }}">
                        <i class="fas fa-tags"></i>
                        Tópicos de Servicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.bulk-import.*') ? 'active' : '' }}" 
                       href="{{ route('admin.bulk-import.index') }}">
                        <i class="fas fa-file-import"></i>
                        Carga Masiva
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.job-positions.*') ? 'active' : '' }}" 
                       href="{{ route('admin.job-positions.index') }}">
                        <i class="fas fa-briefcase"></i>
                        Puestos de Trabajo
                    </a>
                </li>
                @endif
                @if($isTechnicalSuperAdmin)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('technical.storage-settings.*') ? 'active' : '' }}" 
                       href="{{ route('technical.storage-settings.edit') }}">
                        <i class="fas fa-folder-tree"></i>
                        Almacenamiento Fisico
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('technical.branding-settings.*') ? 'active' : '' }}" 
                       href="{{ route('technical.branding-settings.edit') }}">
                        <i class="fas fa-palette"></i>
                        Personalizacion App
                    </a>
                </li>
                @endif
            </ul>

            <!-- Academic Management Section -->
            @if(!$isTechnicalSuperAdmin)
            <h6 class="sidebar-heading">Gestión Académica</h6>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.academic.institutions.*') ? 'active' : '' }}" 
                       href="{{ route('admin.academic.institutions.index') }}">
                        <i class="fas fa-university"></i>
                        Instituciones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.academic.faculties.*') ? 'active' : '' }}" 
                       href="{{ route('admin.academic.faculties.index') }}">
                        <i class="fas fa-building"></i>
                        Facultades
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.academic.areas.*') ? 'active' : '' }}" 
                       href="{{ route('admin.academic.areas.index') }}">
                        <i class="fas fa-sitemap"></i>
                        Áreas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.academic.programs.*') ? 'active' : '' }}" 
                       href="{{ route('admin.academic.programs.index') }}">
                        <i class="fas fa-graduation-cap"></i>
                        Programas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.academic.courses.*') ? 'active' : '' }}" 
                       href="{{ route('admin.academic.courses.index') }}">
                        <i class="fas fa-book"></i>
                        Cursos
                    </a>
                </li>
            </ul>
            
            <h6 class="sidebar-heading">Reportes</h6>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" 
                       href="{{ route('admin.reports.index') }}">
                        <i class="fas fa-file-export"></i>
                        Módulo de Reportes
                    </a>
                </li>
            </ul>
            @endif
            
            <h6 class="sidebar-heading">Sistema</h6>
            <ul class="nav flex-column">
                </li>
                @if($isMonitorUser)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('monitor.*') ? 'active' : '' }}" href="{{ route('monitor.index') }}">
                        <i class="fas fa-arrow-left"></i>
                        Volver a Auditoria
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                        <i class="fas fa-user-edit"></i>
                        Mi Perfil
                    </a>
                </li>
                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="nav-link logout-nav-btn border-0 w-100 text-start">
                            <i class="fas fa-sign-out-alt"></i>
                            Cerrar Sesión
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            @php
                $topbarTitle = trim($__env->yieldContent('topbar_title'));

                if ($topbarTitle === '') {
                    if (request()->routeIs('dashboard')) {
                        $topbarTitle = 'Dashboard';
                    } elseif (request()->routeIs('admin.tickets.*') || request()->routeIs('admin.projects.*')) {
                        $topbarTitle = 'Tickets';
                    } elseif (request()->routeIs('admin.users.*')) {
                        $topbarTitle = 'Usuarios';
                    } elseif (request()->routeIs('admin.roles.*')) {
                        $topbarTitle = 'Roles';
                    } elseif (request()->routeIs('admin.request-types.*')) {
                        $topbarTitle = 'Topicos de Servicio';
                    } elseif (request()->routeIs('admin.bulk-import.*')) {
                        $topbarTitle = 'Carga Masiva';
                    } elseif (request()->routeIs('admin.job-positions.*')) {
                        $topbarTitle = 'Puestos de Trabajo';
                    } elseif (request()->routeIs('admin.academic.institutions.*')) {
                        $topbarTitle = 'Instituciones';
                    } elseif (request()->routeIs('admin.academic.faculties.*')) {
                        $topbarTitle = 'Facultades';
                    } elseif (request()->routeIs('admin.academic.areas.*')) {
                        $topbarTitle = 'Areas';
                    } elseif (request()->routeIs('admin.academic.programs.*')) {
                        $topbarTitle = 'Programas';
                    } elseif (request()->routeIs('admin.academic.courses.*')) {
                        $topbarTitle = 'Cursos';
                    } elseif (request()->routeIs('admin.reports.*')) {
                        $topbarTitle = 'Modulo de Reportes';
                    } elseif (request()->routeIs('technical.storage-settings.*')) {
                        $topbarTitle = 'Configuracion Tecnica de Archivos';
                    } elseif (request()->routeIs('technical.branding-settings.*')) {
                        $topbarTitle = 'Personalizacion de App';
                    } elseif (request()->routeIs('profile.*')) {
                        $topbarTitle = 'Mi Perfil';
                    } else {
                        $topbarTitle = 'Panel Administrativo';
                    }
                }
            @endphp
            <div class="w-100 d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                    <!-- Hamburguesa solo en móvil -->
                    <button class="btn btn-sm me-2 d-lg-none border-0 text-secondary" type="button"
                            data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar"
                            title="Menú">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                    <span class="navbar-top-title">{{ $topbarTitle }}</span>
                </div>

                <div class="d-flex align-items-center flex-shrink-0 admin-topbar-actions">
                @if($isMonitorUser)
                <a href="{{ route('monitor.index') }}" class="btn btn-outline-primary btn-sm d-inline-flex admin-topbar-monitor-btn monitor-allow-create-text" title="Volver al panel de auditoria">
                    <i class="fas fa-arrow-left me-1"></i>
                    <span class="d-none d-lg-inline">Auditoria</span>
                </a>
                @endif
                <!-- Theme Toggle -->
                <button class="btn btn-outline-secondary btn-sm" id="themeToggle" title="Cambiar tema">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>

                <span class="navbar-text d-flex align-items-center">
                    @if(Auth::user()->user_avatar)
                        <img src="{{ asset('storage/' . Auth::user()->user_avatar) }}" 
                             alt="Avatar" class="rounded-circle me-2" 
                             style="width: 32px; height: 32px; object-fit: cover;">
                    @else
                        <i class="fas fa-user-circle me-2" style="font-size: 32px;"></i>
                    @endif
                    <span class="admin-user-name">{{ Auth::user()->user_name }}</span>
                </span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="content-wrapper">
        @if($isMonitorUser)
        <div class="container-fluid pt-3">
            <div class="alert alert-warning mb-3" role="alert">
                <i class="fas fa-eye me-2"></i>
                Perfil Auditor: no tienes permisos para editar, bloquear o eliminar. Tu acceso es solo de observación.
            </div>
        </div>
        @endif
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Modal para archivos demasiado grandes -->
    <div class="modal fade" id="fileTooLargeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fileTooLargeModalTitle">Aviso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="fileTooLargeModalBody">
                    El archivo que intentó subir excede el tamaño máximo permitido.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            function parsePhpIniSize(sizeStr){
                if(!sizeStr) return 0;
                var m = /^\s*(\d+)\s*([KMG])?\s*$/i.exec(sizeStr);
                if(!m) return 0;
                var val = parseInt(m[1],10);
                var unit = (m[2]||'').toUpperCase();
                var units = {K:1024, M:1024*1024, G:1024*1024*1024};
                return unit ? val * units[unit] : val;
            }

            var maxSizeStr = document.querySelector('meta[name="max-upload-size"]')?.getAttribute('content') || '';
            var maxBytes = parsePhpIniSize(maxSizeStr);

            function showFileTooLarge(message){
                var body = document.getElementById('fileTooLargeModalBody');
                var title = document.getElementById('fileTooLargeModalTitle');
                var normalizedMessage = (message || '').toLowerCase();
                var isUploadError = /archivo|tamaño|tamano|upload|413|excede|subir/.test(normalizedMessage);

                if(title){
                    title.textContent = isUploadError ? 'Archivo demasiado grande' : 'No se pudo completar la acción';
                }
                if(body) body.textContent = message || body.textContent;
                var modalEl = document.getElementById('fileTooLargeModal');
                if(modalEl){
                    var modal = new bootstrap.Modal(modalEl);
                    modal.show();
                } else {
                    alert(message || 'El archivo es demasiado grande.');
                }
            }

            document.addEventListener('change', function(e){
                var target = e.target;
                if(target && target.type === 'file' && target.files && target.files.length){
                    for(var i=0;i<target.files.length;i++){
                        var f = target.files[i];
                        if(maxBytes && f.size > maxBytes){
                            e.preventDefault();
                            target.value = '';
                            showFileTooLarge('El archivo "' + f.name + '" excede el máximo permitido de ' + maxSizeStr + '.');
                            break;
                        }
                    }
                }
            }, true);

            if(window.jQuery){
                $(document).ajaxError(function(event, jqxhr){
                    try {
                        if(jqxhr && jqxhr.status === 413){
                            var msg = (jqxhr.responseJSON && jqxhr.responseJSON.message) ? jqxhr.responseJSON.message : 'El archivo es demasiado grande.';
                            showFileTooLarge(msg);
                        }
                    } catch(err){
                        console.error(err);
                    }
                });
            }

            if(window.fetch){
                var _origFetch = window.fetch.bind(window);
                window.fetch = function(){
                    return _origFetch.apply(this, arguments).then(function(response){
                        if(response && response.status === 413){
                            response.clone().text().then(function(txt){
                                try {
                                    var json = JSON.parse(txt || '{}');
                                    showFileTooLarge(json.message || 'El archivo es demasiado grande.');
                                } catch(e){
                                    showFileTooLarge('El archivo es demasiado grande.');
                                }
                            }).catch(function(){
                                showFileTooLarge('El archivo es demasiado grande.');
                            });
                        }
                        return response;
                    });
                };
            }

            @if(session('error'))
                @php $err = session('error'); @endphp
                document.addEventListener('DOMContentLoaded', function(){
                    showFileTooLarge(@json($err));
                });
            @endif
        })();
    </script>

    @if($isMonitorUser)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const restrictedActionKeywords = /\b(crear|nuevo|nueva|agregar|registrar|editar|edita|bloquear|eliminar|activar|desactivar)\b/i;
            const protectedSelector = 'a, button, input[type="submit"], input[type="button"]';

            document.querySelectorAll(protectedSelector).forEach(function (el) {
                const text = (el.textContent || el.value || '').trim();
                const title = (el.getAttribute('title') || '').trim();
                const ariaLabel = (el.getAttribute('aria-label') || '').trim();
                const combinedText = [text, title, ariaLabel].join(' ').trim();

                if (!combinedText || !restrictedActionKeywords.test(combinedText)) {
                    return;
                }

                // Keep the "Volver a Auditoria" shortcut visible.
                if (el.closest('.monitor-allow-create-text')) {
                    return;
                }

                el.classList.add('d-none');

                const wrapper = el.closest('.btn-toolbar, .btn-group, .d-flex, .card-header, .card-body');
                if (wrapper && wrapper.children.length === 1) {
                    wrapper.classList.add('d-none');
                }
            });
        });
    </script>
    @endif
    
    @stack('scripts')
</body>
</html>
