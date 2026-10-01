<!DOCTYPE html>
<html lang="es" data-bs-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="max-upload-size" content="{{ ini_get('upload_max_filesize') }}">
    <title>@yield('title', $branding['app_name'] ?? 'Sistema de Soporte Universitario')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="{{ $branding['favicon_mime'] ?? 'image/png' }}" href="{{ $branding['favicon_url'] ?? asset('img/logomsula.png') }}">
    <link rel="apple-touch-icon" href="{{ $branding['favicon_url'] ?? asset('img/logomsula.png') }}">
    <style>
        :root {
            --brand-primary: {{ $branding['primary_color'] ?? '#0280AE' }};
            --brand-secondary: {{ $branding['secondary_color'] ?? '#17a2b8' }};
        }
    </style>
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-main sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <img
                    src="{{ $branding['logo_url'] ?? asset('img/logomsula.png') }}"
                    alt="{{ $branding['app_name'] ?? 'Sistema de Soporte Universitario' }}"
                    class="navbar-brand-logo {{ request()->routeIs('home') ? 'navbar-brand-logo-home' : '' }}"
                >
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
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
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>{{ auth()->user()->user_name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @php
                                    $userRole = auth()->user()->role->role_name ?? null;
                                @endphp
                                
                                @if($userRole === 'Admin')
                                    <li><a class="dropdown-item" href="{{ route('dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                    </a></li>
                                @elseif($userRole === 'Monitor')
                                    <li><a class="dropdown-item" href="{{ route('monitor.index') }}">
                                        <i class="fas fa-desktop me-2"></i>Monitor
                                    </a></li>
                                @elseif($userRole === 'Contributor')
                                    <li><a class="dropdown-item" href="{{ route('contributors.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2"></i>Mi Dashboard
                                    </a></li>
                                @elseif($userRole === 'Operario')
                                    <li><a class="dropdown-item" href="{{ route('operario.dashboard') }}">
                                        <i class="fas fa-tools me-2"></i>Panel Operario
                                    </a></li>
                                @endif
                                
                                @if(in_array($userRole, ['Requester', 'Contributor']))
                                    <li><a class="dropdown-item" href="{{ route('service-management.index') }}">
                                        <i class="fas fa-cogs me-2"></i>Mis Solicitudes
                                    </a></li>
                                @endif
                                
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
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
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5 app-main-footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center app-footer-branding">
                        <img src="{{ $branding['footer_logo_url'] ?? asset('img/LogoCampus.png') }}" alt="Logo" height="56" class="me-3 bg-white rounded p-1 app-footer-logo">
                        <div>
                            <h5 class="mb-1">{{ $branding['app_name'] ?? 'Sistema de Soporte Universitario' }}</h5>
                            
                        </div>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-4 mt-md-0 app-footer-meta">
                    <p class="mb-0">&copy; {{ date('Y') }} {{ $branding['app_name'] ?? 'Sistema de Soporte Universitario' }}.</p>
                    <p class="mb-0 small">Desarrollado por: Gestión TICS - All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <style>
        .navbar-brand-logo {
            height: 40px;
            width: auto;
            max-width: 220px;
            object-fit: contain;
            transition: transform 0.2s ease;
        }

        .navbar-brand-logo-home {
            height: 52px;
            max-width: 280px;
        }

        .navbar-brand:hover .navbar-brand-logo {
            transform: scale(1.02);
        }

        @media (max-width: 991.98px) {
            .navbar-brand-logo {
                height: 36px;
                max-width: 190px;
            }

            .navbar-brand-logo-home {
                height: 44px;
                max-width: 240px;
            }
        }

        @media (max-width: 575.98px) {
            .navbar-brand-logo {
                height: 32px;
                max-width: 165px;
            }

            .navbar-brand-logo-home {
                height: 40px;
                max-width: 210px;
            }
        }

        @media (max-width: 767.98px) {
            .app-main-footer {
                margin-top: 1.5rem !important;
                padding-top: 1.2rem !important;
                padding-bottom: 1.2rem !important;
            }

            .app-footer-branding {
                flex-direction: column;
                text-align: center;
                gap: 0.6rem;
            }

            .app-footer-logo {
                margin-right: 0 !important;
                height: 44px;
            }

            .app-footer-branding h5 {
                font-size: 1rem;
            }

            .app-footer-subtitle {
                font-size: 0.78rem;
                line-height: 1.35;
            }

            .app-footer-meta {
                text-align: center !important;
                margin-top: 0.9rem !important;
                padding-top: 0.9rem;
                border-top: 1px solid rgba(255, 255, 255, 0.14);
            }
        }
    </style>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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

            // Validación cliente antes de enviar el formulario
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

            // Manejo global de respuestas AJAX con 413 (jQuery)
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

            // Manejo global para fetch() (interceptar respuestas 413)
            if(window.fetch){
                var _origFetch = window.fetch.bind(window);
                window.fetch = function(){
                    return _origFetch.apply(this, arguments).then(function(response){
                        if(response && response.status === 413){
                            // intentar parsear JSON si lo trae
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

            // Si la sesión trae un error de tamaño, mostrar modal al cargar
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



