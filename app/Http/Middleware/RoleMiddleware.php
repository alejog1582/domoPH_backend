<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
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

        // Verificar si el usuario tiene alguno de los roles requeridos
        $hasRole = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            return response()->json([
                'message' => 'No autorizado. Se requiere uno de los siguientes roles: ' . implode(', ', $roles),
                'error' => 'Forbidden'
            ], 403);
        }

        return $next($request);
    }
}
