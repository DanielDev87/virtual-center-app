<!DOCTYPE html>
<html lang="es" data-bs-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="max-upload-size" content="{{ ini_get('upload_max_filesize') }}">
    <title>@yield('title', 'Admin de Área - ' . ($branding['app_name'] ?? 'Sistema de Soporte Universitario'))</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="{{ $branding['favicon_mime'] ?? 'image/png' }}" href="{{ $branding['favicon_url'] ?? asset('img/logomsula.png') }}">
    <style>
        :root {
            --brand-primary: {{ $branding['primary_color'] ?? '#0d6efd' }};
            --brand-secondary: {{ $branding['secondary_color'] ?? '#17a2b8' }};
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        .sidebar {
            padding: 0;
            box-shadow: inset -1px 0 0 rgba(0,0,0,.1);
            background-color: #1a3a5c;
            width: 280px;
        }

        .sidebar-sticky {
            position: relative; top: 0; height: 100%;
            min-height: 100vh; padding-top: 0;
            overflow-x: hidden; overflow-y: auto;
        }

        @media (min-width: 992px) {
            .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; width: 250px; }
        }

        @media (max-width: 991.98px) {
            main { margin-left: 0 !important; }
            .navbar { left: 0 !important; }
        }

        .sidebar-branding {
            position: sticky; top: 0; z-index: 3;
            background-color: #1a3a5c;
            min-height: 74px; display: flex;
            flex-direction: column; align-items: center; justify-content: center;
        }

        .sidebar .nav-link {
            font-weight: 500; color: #b8d4f0; padding: 0.75rem 1rem; transition: all 0.3s;
        }
        .sidebar .nav-link:hover { color: #fff; background-color: rgba(255,255,255,0.12); }
        [data-bs-theme="light"] .sidebar .nav-link:hover,
        [data-bs-theme="light"] .sidebar .nav-link:focus { color: #fff !important; background-color: rgba(13,110,253,0.65); }
        .sidebar .nav-link.active { color: #fff; background-color: var(--brand-primary, #0d6efd); }
        .sidebar .nav-link i { margin-right: 0.5rem; width: 20px; text-align: center; }

        /* Boton de cierre de sesion: mantener contraste correcto en cualquier tema. */
        .sidebar .logout-nav-btn {
            background-color: transparent !important;
            color: #b8d4f0;
        }
        .sidebar .logout-nav-btn:hover,
        .sidebar .logout-nav-btn:focus {
            color: #fff !important;
            background-color: rgba(255,255,255,0.12) !important;
        }
        [data-bs-theme="light"] .sidebar .logout-nav-btn:hover,
        [data-bs-theme="light"] .sidebar .logout-nav-btn:focus {
            color: #fff !important;
            background-color: rgba(13,110,253,0.65) !important;
        }

        .sidebar-heading {
            font-size: .75rem; text-transform: uppercase;
            color: #8ab0cc; padding: 1rem 1rem 0.5rem; font-weight: 600;
        }

        main { margin-left: 250px; }

        .navbar {
            position: fixed; top: 0; right: 0; left: 250px; z-index: 99;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            background-color: rgba(255,255,255,0.96);
            backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        .navbar-top-title {
            font-size: 1.05rem; font-weight: 600; color: #343a40; line-height: 1.2;
            min-width: 0; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        @media (min-width: 992px) {
            .navbar-top-title { white-space: normal; overflow: visible; text-overflow: clip; }
        }
        .content-wrapper { padding-top: 56px; }
        [data-bs-theme="dark"] .navbar { background-color: rgba(45,45,45,0.96) !important; border-bottom: 1px solid rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .navbar-top-title { color: #f1f3f5; }
        @media (max-width: 991.98px) {
            .navbar-top-title { max-width: calc(100vw - 210px); }
            .admin-user-name { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $areaAdminArea = auth()->user()->area ?? null;
        $areaLabel     = $areaAdminArea ? $areaAdminArea->area_name : 'Área sin asignar';
    @endphp

    <!-- Sidebar -->
    <nav class="sidebar offcanvas-lg offcanvas-start" id="areaAdminSidebar" tabindex="-1" aria-labelledby="areaAdminSidebarLabel">
        <div class="sidebar-sticky">
            <div class="sidebar-branding px-3 py-2 border-bottom border-secondary text-center position-relative">
                <button type="button" class="btn-close btn-close-white d-lg-none position-absolute top-0 end-0 m-2"
                        data-bs-dismiss="offcanvas" data-bs-target="#areaAdminSidebar" aria-label="Cerrar"></button>
                <img src="{{ $branding['logo_url'] ?? asset('img/logomsula.png') }}"
                     alt="{{ $branding['app_name'] ?? 'Sistema' }}"
                     class="img-fluid mb-2 bg-white rounded p-2" style="max-height: 72px;">
                <small class="text-white d-block">Panel Admin de Área</small>
                <span class="badge bg-primary mt-1">{{ $areaLabel }}</span>
            </div>

            <ul class="nav flex-column mt-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('area-admin.dashboard') ? 'active' : '' }}"
                       href="{{ route('area-admin.dashboard') }}">
                        <i class="fas fa-chart-bar"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('area-admin.tickets.*') ? 'active' : '' }}"
                       href="{{ route('area-admin.tickets.index') }}">
                        <i class="fas fa-ticket-alt"></i> Tickets del Área
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('area-admin.reports.*') ? 'active' : '' }}"
                       href="{{ route('area-admin.reports.index') }}">
                        <i class="fas fa-file-alt"></i> Reportes
                    </a>
                </li>
            </ul>

            <h6 class="sidebar-heading">Sistema</h6>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                       href="{{ route('profile.edit') }}">
                        <i class="fas fa-user-edit"></i> Mi Perfil
                    </a>
                </li>
                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="nav-link logout-nav-btn border-0 w-100 text-start">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
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
                    if (request()->routeIs('area-admin.dashboard'))       $topbarTitle = 'Dashboard';
                    elseif (request()->routeIs('area-admin.tickets.*'))   $topbarTitle = 'Tickets del Área';
                    elseif (request()->routeIs('area-admin.reports.*'))   $topbarTitle = 'Reportes';
                    elseif (request()->routeIs('profile.*'))              $topbarTitle = 'Mi Perfil';
                    else                                                  $topbarTitle = 'Admin de Área';
                }
            @endphp
            <div class="w-100 d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                    <button class="btn btn-sm me-2 d-lg-none border-0 text-secondary" type="button"
                            data-bs-toggle="offcanvas" data-bs-target="#areaAdminSidebar"
                            aria-controls="areaAdminSidebar" title="Menú">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                    <span class="navbar-top-title">{{ $topbarTitle }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
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
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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

    @stack('scripts')
</body>
</html>
