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
        if (!Auth::check() || !Auth::user()->hasRole('administrador')) {
            return null;
        }

        $user = Auth::user();
        $propiedadId = Session::get('propiedad_activa_id');

        if ($propiedadId) {
            $propiedad = \App\Models\Propiedad::find($propiedadId);
            if ($propiedad && $user->administracionesPropiedad()->where('propiedad_id', $propiedadId)->exists()) {
                return $propiedad;
            }
        }

        // Si no hay propiedad en sesión, obtener la principal o la primera
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
            return $user->roles()
                ->where('role_user.propiedad_id', $propiedad->id)
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
