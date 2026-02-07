<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No autenticado',
                    'error' => 'Unauthenticated'
                ], 401);
            }
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        // El superadministrador tiene acceso a todo
        if ($user->hasRole('superadministrador')) {
            return $next($request);
        }

        // Verificar acceso al panel admin:
        // 1. Usuario con rol administrador tradicional
        // 2. Usuario con propiedad_id y roles asignados (creado por admin)
        $tieneAcceso = false;

        if ($user->hasRole('administrador')) {
            $tieneAcceso = true;
        } elseif ($user->propiedad_id && $user->roles()->count() > 0) {
            $tieneAcceso = true;
        }

        if (!$tieneAcceso) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No tienes permisos para acceder al panel de administrador.',
                    'error' => 'Forbidden'
                ], 403);
            }

            Auth::logout();
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'No tienes permisos para acceder al panel de administrador.']);
        }

        // Verificar que el usuario esté activo
        if (!$user->activo) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tu cuenta está desactivada.',
                    'error' => 'Forbidden'
                ], 403);
            }

            Auth::logout();
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Tu cuenta está desactivada.']);
        }

        return $next($request);
    }
}
