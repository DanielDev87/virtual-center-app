<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Procesar login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');
        
        // Buscar usuario por email personalizado
        $user = User::where('user_email', $credentials['email'])->first();
        
        if ($user && Hash::check($credentials['password'], $user->password)) {
            if (!$user->is_active) {
                return back()->withErrors(['email' => 'Tu cuenta está desactivada.']);
            }

            Auth::login($user);
            $request->session()->regenerate();

            // Redirigir según el rol
            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors(['email' => 'Las credenciales no coinciden con nuestros registros.']);
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Redirigir según el rol del usuario
     */
    private function redirectBasedOnRole($user)
    {
        $roleName = strtolower($user->role->role_name ?? '');
        
        if (str_contains($roleName, 'admin') || str_contains($roleName, 'administrador')) {
            return redirect()->route('dashboard');
        } elseif (str_contains($roleName, 'colaborador') || str_contains($roleName, 'contributor')) {
            return redirect()->route('contributors.dashboard');
        } elseif (str_contains($roleName, 'monitor')) {
            return redirect()->route('monitor.index');
        } else {
            return redirect()->route('home');
        }
    }
}

