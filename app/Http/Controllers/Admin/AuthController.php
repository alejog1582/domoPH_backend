<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LogAuditoria;

class AuthController extends Controller
{
    /**
     * Mostrar el formulario de login para administradores
     */
    public function showLoginForm()
    {
        // Si ya está autenticado y tiene acceso al panel admin, redirigir al dashboard
        if (Auth::check()) {
            $user = Auth::user();
            // Verificar si tiene acceso: administrador o usuario con propiedad_id y roles
            if ($user->hasRole('administrador') || ($user->propiedad_id && $user->roles()->count() > 0)) {
                return redirect()->route('admin.dashboard');
            }
        }

        return view('admin.auth.login');
    }

    /**
     * Procesar el login de administradores
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

            // Verificar que el usuario esté activo
            if (!$user->activo) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tu cuenta está desactivada.',
                ])->withInput($request->only('email'));
            }

            // Verificar que el usuario tenga acceso al panel admin
            // Puede ser: administrador, o usuario con propiedad_id y roles asignados
            $tieneAcceso = false;
            $propiedadId = null;

            // Verificar si es administrador tradicional
            if ($user->hasRole('administrador')) {
                $tieneAcceso = true;
                // Obtener la primera propiedad del administrador (o la principal)
                $adminProp = $user->administracionesPropiedad()
                    ->where('es_principal', true)
                    ->first();

                // Si no tiene principal, tomar la primera
                if (!$adminProp) {
                    $adminProp = $user->administracionesPropiedad()->first();
                }

                if ($adminProp) {
                    $propiedadId = $adminProp->propiedad_id;
                }
            } 
            // Verificar si es usuario creado por admin (tiene propiedad_id y roles)
            elseif ($user->propiedad_id && $user->roles()->count() > 0) {
                $tieneAcceso = true;
                // Obtener la primera propiedad_id del usuario
                $propiedadesIds = $user->getPropiedadesIds();
                if (!empty($propiedadesIds)) {
                    $propiedadId = $propiedadesIds[0];
                }
            }

            if (!$tieneAcceso) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'No tienes permisos para acceder al panel de administrador.',
                ])->withInput($request->only('email'));
            }

            if (!$propiedadId) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'No tienes propiedades asignadas.',
                ])->withInput($request->only('email'));
            }

            // Guardar la propiedad activa en la sesión
            $request->session()->put('propiedad_activa_id', $propiedadId);

            $request->session()->regenerate();

            // Registrar auditoría
            LogAuditoria::create([
                'user_id' => $user->id,
                'propiedad_id' => $propiedadId,
                'accion' => 'login',
                'modelo' => 'User',
                'descripcion' => "Login exitoso: {$user->email}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'Admin',
            ]);

            return redirect()->intended(route('admin.dashboard'));
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
            $propiedadId = $request->session()->get('propiedad_activa_id');
            LogAuditoria::create([
                'user_id' => $user->id,
                'propiedad_id' => $propiedadId,
                'accion' => 'logout',
                'modelo' => 'User',
                'descripcion' => "Logout: {$user->email}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'modulo' => 'Admin',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
