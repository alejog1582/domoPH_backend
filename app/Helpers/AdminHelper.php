<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AdminHelper
{
    /**
     * Obtener la propiedad activa del administrador actual
     *
     * @return \App\Models\Propiedad|null
     */
    public static function getPropiedadActiva()
    {
        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();
        $propiedadId = Session::get('propiedad_activa_id');

        if ($propiedadId) {
            $propiedad = \App\Models\Propiedad::find($propiedadId);
            
            if ($propiedad) {
                // Verificar acceso según el tipo de usuario
                // Si es administrador tradicional, verificar administracionesPropiedad
                if ($user->hasRole('administrador')) {
                    if ($user->administracionesPropiedad()->where('propiedad_id', $propiedadId)->exists()) {
                        return $propiedad;
                    }
                } 
                // Si es usuario creado por admin, verificar propiedad_id
                elseif ($user->propiedad_id) {
                    $propiedadesIds = $user->getPropiedadesIds();
                    if (in_array($propiedadId, $propiedadesIds)) {
                        return $propiedad;
                    }
                }
            }
        }

        // Si no hay propiedad en sesión, obtener la primera disponible
        // Para administradores tradicionales
        if ($user->hasRole('administrador')) {
            $adminProp = $user->administracionesPropiedad()
                ->where('es_principal', true)
                ->first();

            if (!$adminProp) {
                $adminProp = $user->administracionesPropiedad()->first();
            }

            if ($adminProp) {
                Session::put('propiedad_activa_id', $adminProp->propiedad_id);
                return $adminProp->propiedad;
            }
        }
        // Para usuarios creados por admin
        elseif ($user->propiedad_id) {
            $propiedadesIds = $user->getPropiedadesIds();
            if (!empty($propiedadesIds)) {
                $propiedadId = $propiedadesIds[0];
                $propiedad = \App\Models\Propiedad::find($propiedadId);
                if ($propiedad) {
                    Session::put('propiedad_activa_id', $propiedadId);
                    return $propiedad;
                }
            }
        }

        return null;
    }

    /**
     * Obtener los módulos activos de la propiedad activa
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getModulosActivos()
    {
        $propiedad = self::getPropiedadActiva();
        
        if (!$propiedad) {
            return collect([]);
        }

        return $propiedad->modulos()
            ->wherePivot('activo', true)
            ->where('modulos.activo', true)
            ->ordenados()
            ->get();
    }

    /**
     * Obtener los módulos activos de la propiedad activa que el usuario tiene permisos para ver
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getModulosActivosConPermisos()
    {
        if (!Auth::check()) {
            return collect([]);
        }

        $propiedad = self::getPropiedadActiva();
        
        if (!$propiedad) {
            return collect([]);
        }

        $user = Auth::user();

        // Obtener todos los módulos activos de la propiedad
        $modulosPropiedad = $propiedad->modulos()
            ->wherePivot('activo', true)
            ->where('modulos.activo', true)
            ->ordenados()
            ->get();

        // Si es superadministrador, devolver todos los módulos
        if ($user->hasRole('superadministrador')) {
            return $modulosPropiedad;
        }

        // Obtener los slugs de los módulos para los cuales el usuario tiene permisos
        $modulosConPermisos = collect([]);

        foreach ($modulosPropiedad as $modulo) {
            // Verificar si el usuario tiene algún permiso relacionado con este módulo
            // Buscar permisos cuyo campo 'modulo' coincida con el slug del módulo
            $tienePermiso = $user->roles()
                ->where(function($query) use ($propiedad) {
                    $query->where('role_user.propiedad_id', $propiedad->id)
                          ->orWhereNull('role_user.propiedad_id');
                })
                ->whereHas('permissions', function ($query) use ($modulo) {
                    $query->where('modulo', $modulo->slug);
                })
                ->exists();

            if ($tienePermiso) {
                $modulosConPermisos->push($modulo);
            }
        }

        return $modulosConPermisos;
    }

    /**
     * Cambiar la propiedad activa
     *
     * @param int $propiedadId
     * @return bool
     */
    public static function cambiarPropiedadActiva($propiedadId)
    {
        if (!Auth::check() || !Auth::user()->hasRole('administrador')) {
            return false;
        }

        $user = Auth::user();
        
        // Verificar que el usuario tenga acceso a esta propiedad
        if ($user->administracionesPropiedad()->where('propiedad_id', $propiedadId)->exists()) {
            Session::put('propiedad_activa_id', $propiedadId);
            return true;
        }

        return false;
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     * Considera la propiedad activa si está disponible
     *
     * @param string $permissionSlug
     * @return bool
     */
    public static function hasPermission($permissionSlug)
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        $propiedad = self::getPropiedadActiva();

        // Si hay propiedad activa, verificar permisos del rol específico de esa propiedad
        if ($propiedad) {
            // Verificar si el usuario tiene el permiso en algún rol asociado a esta propiedad
            return $user->roles()
                ->where(function($query) use ($propiedad) {
                    $query->where('role_user.propiedad_id', $propiedad->id)
                          ->orWhereNull('role_user.propiedad_id'); // También roles sin propiedad específica
                })
                ->whereHas('permissions', function ($query) use ($permissionSlug) {
                    $query->where('slug', $permissionSlug);
                })
                ->exists();
        }

        // Si no hay propiedad activa, verificar todos los roles
        return $user->hasPermission($permissionSlug);
    }

    /**
     * Verificar si el usuario tiene al menos uno de los permisos especificados
     * Considera la propiedad activa si está disponible
     *
     * @param array $permissionSlugs
     * @return bool
     */
    public static function hasAnyPermission(array $permissionSlugs)
    {
        if (!Auth::check()) {
            return false;
        }

        foreach ($permissionSlugs as $permissionSlug) {
            if (self::hasPermission($permissionSlug)) {
                return true;
            }
        }

        return false;
    }
}
