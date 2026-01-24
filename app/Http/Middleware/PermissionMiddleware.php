<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'No autenticado',
                'error' => 'Unauthenticated'
            ], 401);
        }

        $user = auth()->user();

        // El superadministrador tiene acceso a todo
        if ($user->hasRole('superadministrador')) {
            return $next($request);
        }

        // Verificar si el usuario tiene alguno de los permisos requeridos
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            return response()->json([
                'message' => 'No autorizado. Se requiere uno de los siguientes permisos: ' . implode(', ', $permissions),
                'error' => 'Forbidden'
            ], 403);
        }

        return $next($request);
    }
}
