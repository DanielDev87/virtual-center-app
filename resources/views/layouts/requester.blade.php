<!DOCTYPE html>
<html lang="es" data-bs-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="max-upload-size" content="{{ ini_get('upload_max_filesize') }}">
    <title>@yield('title', 'Mis Solicitudes - ' . ($branding['app_name'] ?? 'Sistema de Soporte Universitario'))</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="{{ $branding['favicon_mime'] ?? 'image/png' }}" href="{{ $branding['favicon_url'] ?? asset('img/logomsula.png') }}">
    <link rel="apple-touch-icon" href="{{ $branding['favicon_url'] ?? asset('img/logo-soporte-Fondo-Trasnparente.png') }}">
    <style>
        :root {
            --brand-primary: {{ $branding['primary_color'] ?? '#667eea' }};
            --brand-secondary: {{ $branding['secondary_color'] ?? '#764ba2' }};
        }
    </style>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--brand-primary, #667eea) 0%, var(--brand-secondary, #764ba2) 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
            position: sticky;
            top: 0;
            z-index: 1030;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .navbar-custom .navbar-brand {
            color: #fff;
            font-weight: 600;
            font-size: 1.25rem;
        }

        .navbar-brand-logo {
            height: 42px;
            width: auto;
            background-color: rgba(255, 255, 255, 0.96);
            border-radius: 0.5rem;
            padding: 0.25rem 0.4rem;
        }
        
        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }
        
        .navbar-custom .nav-link:hover,
        .navbar-custom .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 0.25rem;
        }
        
        .navbar-custom .nav-link i {
            margin-right: 0.5rem;
        }
        
        .content-wrapper {
            padding: 2rem 0;
            min-height: calc(100vh - 56px);
        }
        
        .stat-card {
            border-radius: 0.5rem;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .stat-card .card-body {
            padding: 1.5rem;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, var(--brand-primary, #667eea) 0%, var(--brand-secondary, #764ba2) 100%);
            border: none;
            color: white;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(135deg, var(--brand-secondary, #764ba2) 0%, var(--brand-primary, #667eea) 100%);
            color: white;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('service-management.index') }}">
                <img src="{{ $branding['logo_url'] ?? asset('img/logomsula.png') }}" alt="{{ $branding['app_name'] ?? 'Sistema de Soporte Universitario' }}" class="navbar-brand-logo me-2">{{ $branding['short_name'] ?? 'Soporte Univ.' }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('service-management.index') ? 'active' : '' }}" 
                           href="{{ route('service-management.index') }}">
                            <i class="fas fa-list"></i>Mis Solicitudes
                        </a>
                    </li>
                    @endauth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('service-management.create') ? 'active' : '' }}" 
                           href="{{ route('service-management.create') }}">
                            <i class="fas fa-plus-circle"></i>Nueva Solicitud
                        </a>
                </ul>
                <ul class="navbar-nav">
                    <!-- Theme Toggle -->
                    <li class="nav-item">
                        <button class="btn btn-outline-light btn-sm me-2" id="themeToggle" title="Cambiar tema">
                            <i class="fas fa-moon" id="themeIcon"></i>
                        </button>
                    </li>
                    
                    @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown">
                            @if(Auth::user()->user_avatar)
                                <img src="{{ asset('storage/' . Auth::user()->user_avatar) }}" 
                                     alt="Avatar" class="rounded-circle me-2" 
                                     style="width: 32px; height: 32px; object-fit: cover;">
                            @else
                                <i class="fas fa-user-circle me-2"></i>
                            @endif
                            {{ Auth::user()->user_name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user-edit me-2"></i>Mi Perfil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @else
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link" style="border: 1px solid rgba(255,255,255,0.5); border-radius: 0.25rem;">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </a>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="container-fluid">
            @yield('content')
        </div>
    </div>

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

    @stack('scripts')
</body>
</html>
