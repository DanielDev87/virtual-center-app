<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Manejar una petición entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        $userRole = auth()->user()->role?->role_name ?? null;

        // Solo lectura estricta para Monitor en rutas gestionadas
        if ($userRole === 'Monitor') {
            if ($request->is('dashboard')) {
                abort(403, 'Los auditores tienen su propio panel de monitor.');
            }
            if (!$request->isMethod('GET')) {
                return back()->with('error', 'Acceso denegado. Modo de sólo lectura.');
            }
        }

        // Admin Área no puede acceder a rutas del admin global ni al dashboard general
        if ($userRole === 'Admin Área') {
            if ($request->is('dashboard') || $request->routeIs('dashboard')) {
                return redirect()->route('area-admin.dashboard');
            }
            if ($request->is('admin/*') || $request->routeIs('admin.*')) {
                return redirect()->route('area-admin.dashboard')
                    ->with('error', 'No tienes acceso al panel de administración global.');
            }
        }

        if (!$userRole || !in_array($userRole, $roles)) {
            // Si el usuario es Monitor, puede observar CUALQUIER ruta mediante GET
            if ($userRole === 'Monitor') {
                return $next($request);
            }

            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
