<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\LogAuditoria;

class AuthController extends Controller
{
    /**
     * Mostrar el formulario de login
     */
    public function showLoginForm()
    {
        // Si ya está autenticado y es superadministrador, redirigir al dashboard
        if (Auth::check() && Auth::user()->hasRole('superadministrador')) {
            return redirect()->route('superadmin.dashboard');
        }

        return view('superadmin.auth.login');
    }

    /**
     * Procesar el login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Intentar autenticación
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Verificar que el usuario tenga rol de superadministrador
            if (!$user->hasRole('superadministrador')) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'No tienes permisos para acceder al panel de superadministrador.',
                ])->withInput($request->only('email'));
            }

            // Verificar que el usuario esté activo
            if (!$user->activo) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tu cuenta está desactivada.',
                ])->withInput($request->only('email'));
            }

            $request->session()->regenerate();

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => $user->id,
                'accion' => 'login',
                'modelo' => 'User',
                'descripcion' => "Login exitoso: {$user->email}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);

            return redirect()->intended(route('superadmin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->withInput($request->only('email'));
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Registrar auditoría
        if ($user) {
            LogAuditoria::create([
                'user_id' => $user->id,
                'accion' => 'logout',
                'modelo' => 'User',
                'descripcion' => "Logout: {$user->email}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'SuperAdmin',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login');
    }
}
