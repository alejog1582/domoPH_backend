<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permisos del Superadministrador
        $permisosSuperAdmin = [
            // Gestión de Propiedades
            ['nombre' => 'Ver propiedades', 'slug' => 'propiedades.view', 'modulo' => 'propiedades'],
            ['nombre' => 'Crear propiedades', 'slug' => 'propiedades.create', 'modulo' => 'propiedades'],
            ['nombre' => 'Editar propiedades', 'slug' => 'propiedades.edit', 'modulo' => 'propiedades'],
            ['nombre' => 'Eliminar propiedades', 'slug' => 'propiedades.delete', 'modulo' => 'propiedades'],
            
            // Gestión de Planes
            ['nombre' => 'Ver planes', 'slug' => 'planes.view', 'modulo' => 'planes'],
            ['nombre' => 'Crear planes', 'slug' => 'planes.create', 'modulo' => 'planes'],
            ['nombre' => 'Editar planes', 'slug' => 'planes.edit', 'modulo' => 'planes'],
            ['nombre' => 'Eliminar planes', 'slug' => 'planes.delete', 'modulo' => 'planes'],
            
            // Gestión de Módulos
            ['nombre' => 'Ver módulos', 'slug' => 'modulos.view', 'modulo' => 'modulos'],
            ['nombre' => 'Crear módulos', 'slug' => 'modulos.create', 'modulo' => 'modulos'],
            ['nombre' => 'Editar módulos', 'slug' => 'modulos.edit', 'modulo' => 'modulos'],
            
            // Gestión de Administradores
            ['nombre' => 'Ver administradores', 'slug' => 'administradores.view', 'modulo' => 'administradores'],
            ['nombre' => 'Crear administradores', 'slug' => 'administradores.create', 'modulo' => 'administradores'],
            ['nombre' => 'Editar administradores', 'slug' => 'administradores.edit', 'modulo' => 'administradores'],
            ['nombre' => 'Eliminar administradores', 'slug' => 'administradores.delete', 'modulo' => 'administradores'],
            
            // Configuraciones
            ['nombre' => 'Ver configuraciones', 'slug' => 'configuraciones.view', 'modulo' => 'configuraciones'],
            ['nombre' => 'Editar configuraciones', 'slug' => 'configuraciones.edit', 'modulo' => 'configuraciones'],
            
            // Auditoría
            ['nombre' => 'Ver auditoría', 'slug' => 'auditoria.view', 'modulo' => 'auditoria'],
        ];

        // Permisos del Administrador
        $permisosAdmin = [
            ['nombre' => 'Gestionar unidades', 'slug' => 'unidades.manage', 'modulo' => 'unidades'],
            ['nombre' => 'Gestionar residentes', 'slug' => 'residentes.manage', 'modulo' => 'residentes'],
            ['nombre' => 'Gestionar pagos', 'slug' => 'pagos.manage', 'modulo' => 'pagos'],
            ['nombre' => 'Gestionar reservas', 'slug' => 'reservas.manage', 'modulo' => 'reservas'],
            ['nombre' => 'Ver reportes', 'slug' => 'reportes.view', 'modulo' => 'reportes'],
        ];

        // Permisos del Residente
        $permisosResidente = [
            ['nombre' => 'Ver mi unidad', 'slug' => 'unidad.view', 'modulo' => 'unidades'],
            ['nombre' => 'Ver pagos', 'slug' => 'pagos.view', 'modulo' => 'pagos'],
            ['nombre' => 'Crear reservas', 'slug' => 'reservas.create', 'modulo' => 'reservas'],
        ];

        // Crear todos los permisos
        $todosPermisos = array_merge($permisosSuperAdmin, $permisosAdmin, $permisosResidente);
        
        foreach ($todosPermisos as $permiso) {
            Permission::updateOrCreate(
                ['slug' => $permiso['slug']],
                $permiso
            );
        }

        // Asignar permisos a roles
        $superAdmin = Role::where('slug', 'superadministrador')->first();
        $admin = Role::where('slug', 'administrador')->first();
        $residente = Role::where('slug', 'residente')->first();

        if ($superAdmin) {
            // Superadministrador tiene todos los permisos
            $superAdmin->permissions()->sync(
                Permission::whereIn('slug', array_column($todosPermisos, 'slug'))->pluck('id')
            );
        }

        if ($admin) {
            // Administrador tiene permisos de admin y residente
            $admin->permissions()->sync(
                Permission::whereIn('slug', array_merge(
                    array_column($permisosAdmin, 'slug'),
                    array_column($permisosResidente, 'slug')
                ))->pluck('id')
            );
        }

        if ($residente) {
            // Residente solo tiene sus permisos
            $residente->permissions()->sync(
                Permission::whereIn('slug', array_column($permisosResidente, 'slug'))->pluck('id')
            );
        }

        $this->command->info('Permisos creados y asignados exitosamente');
    }
}
