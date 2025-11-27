<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - A-DDIE')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #212529;
            width: 250px;
        }
        
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
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
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #0d6efd;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
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
            background-color: #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .content-wrapper {
            padding-top: 56px;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-sticky">
            <div class="px-3 py-2 mb-3 border-bottom border-secondary">
                <h5 class="text-white mb-0">
                    <i class="fas fa-user-shield me-2"></i>Panel Admin
                </h5>
            </div>
            
            <ul class="nav flex-column">
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
                    <a class="nav-link {{ request()->routeIs('admin.job-positions.*') ? 'active' : '' }}" 
                       href="{{ route('admin.job-positions.index') }}">
                        <i class="fas fa-briefcase"></i>
                        Puestos de Trabajo
                    </a>
                </li>
            </ul>

            <!-- Academic Management Section -->
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
            
            <h6 class="sidebar-heading">Sistema</h6>
            <ul class="nav flex-column">
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                        <i class="fas fa-user-edit"></i>
                        Mi Perfil
                    </a>
                </li>
                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                            <i class="fas fa-sign-out-alt"></i>
                            Cerrar Sesión
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Top Navbar -->
    <nav class="navbar navbar-light">
        <div class="container-fluid">
            <span class="navbar-text d-flex align-items-center">
                @if(Auth::user()->user_avatar)
                    <img src="{{ asset('storage/' . Auth::user()->user_avatar) }}" 
                         alt="Avatar" class="rounded-circle me-2" 
                         style="width: 32px; height: 32px; object-fit: cover;">
                @else
                    <i class="fas fa-user-circle me-2" style="font-size: 32px;"></i>
                @endif
                {{ Auth::user()->user_name }}
            </span>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="content-wrapper">
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @stack('scripts')
</body>
</html>
