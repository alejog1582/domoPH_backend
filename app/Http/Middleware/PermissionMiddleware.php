<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\AdminHelper;

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
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No autenticado',
                    'error' => 'Unauthenticated'
                ], 401);
            }
            return redirect()->route('admin.login');
        }

        $user = auth()->user();

        // El superadministrador tiene acceso a todo
        if ($user->hasRole('superadministrador')) {
            return $next($request);
        }

        // Verificar si el usuario tiene alguno de los permisos requeridos
        // Usar AdminHelper para considerar la propiedad activa
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if (AdminHelper::hasPermission($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No autorizado. Se requiere uno de los siguientes permisos: ' . implode(', ', $permissions),
                    'error' => 'Forbidden'
                ], 403);
            }

            // Para peticiones web, mostrar vista amigable
            $routeName = $request->route() ? $request->route()->getName() : null;
            return response()->view('admin.errors.no-permission', [
                'permissions' => $permissions,
                'route' => $routeName ?? $request->path()
            ], 403);
        }

        return $next($request);
    }
}
