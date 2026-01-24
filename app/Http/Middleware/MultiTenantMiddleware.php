<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Propiedad;

class MultiTenantMiddleware
{
    /**
     * Handle an incoming request.
     * Asegura que el usuario solo acceda a datos de su propiedad asignada
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'No autenticado',
                'error' => 'Unauthenticated'
            ], 401);
        }

        $user = auth()->user();

        // El superadministrador puede acceder a todas las propiedades
        if ($user->hasRole('superadministrador')) {
            return $next($request);
        }

        // Obtener propiedad_id de la ruta o del request
        $propiedadId = $request->route('propiedad_id') 
                    ?? $request->route('propiedad') 
                    ?? $request->input('propiedad_id')
                    ?? $request->header('X-Propiedad-ID');

        if ($propiedadId) {
            // Verificar que el usuario tenga acceso a esta propiedad
            $tieneAcceso = $user->propiedadesConRol('administrador')
                            ->contains('id', $propiedadId)
                    || $user->propiedadesConRol('residente')
                            ->contains('id', $propiedadId)
                    || $user->propiedadesConRol('porteria')
                            ->contains('id', $propiedadId);

            if (!$tieneAcceso) {
                return response()->json([
                    'message' => 'No tiene acceso a esta propiedad',
                    'error' => 'Forbidden'
                ], 403);
            }

            // Agregar propiedad_id al request para uso posterior
            $request->merge(['propiedad_id' => $propiedadId]);
        }

        return $next($request);
    }
}
