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
        // Permisos del Superadministrador (Panel Super Admin)
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

        // Permisos del Administrador (Panel Admin - basados en el menú real)
        $permisosAdmin = [
            // Dashboard
            ['nombre' => 'Ver dashboard', 'slug' => 'dashboard.view', 'modulo' => 'dashboard'],
            
            // Copropiedad
            ['nombre' => 'Ver unidades', 'slug' => 'unidades.view', 'modulo' => 'unidades'],
            ['nombre' => 'Crear unidades', 'slug' => 'unidades.create', 'modulo' => 'unidades'],
            ['nombre' => 'Editar unidades', 'slug' => 'unidades.edit', 'modulo' => 'unidades'],
            ['nombre' => 'Eliminar unidades', 'slug' => 'unidades.delete', 'modulo' => 'unidades'],
            
            ['nombre' => 'Ver residentes', 'slug' => 'residentes.view', 'modulo' => 'residentes'],
            ['nombre' => 'Crear residentes', 'slug' => 'residentes.create', 'modulo' => 'residentes'],
            ['nombre' => 'Editar residentes', 'slug' => 'residentes.edit', 'modulo' => 'residentes'],
            ['nombre' => 'Eliminar residentes', 'slug' => 'residentes.delete', 'modulo' => 'residentes'],
            
            ['nombre' => 'Ver mascotas', 'slug' => 'mascotas.view', 'modulo' => 'mascotas'],
            ['nombre' => 'Crear mascotas', 'slug' => 'mascotas.create', 'modulo' => 'mascotas'],
            ['nombre' => 'Editar mascotas', 'slug' => 'mascotas.edit', 'modulo' => 'mascotas'],
            ['nombre' => 'Eliminar mascotas', 'slug' => 'mascotas.delete', 'modulo' => 'mascotas'],
            
            ['nombre' => 'Ver parqueaderos', 'slug' => 'parqueaderos.view', 'modulo' => 'parqueaderos'],
            ['nombre' => 'Crear parqueaderos', 'slug' => 'parqueaderos.create', 'modulo' => 'parqueaderos'],
            ['nombre' => 'Editar parqueaderos', 'slug' => 'parqueaderos.edit', 'modulo' => 'parqueaderos'],
            ['nombre' => 'Eliminar parqueaderos', 'slug' => 'parqueaderos.delete', 'modulo' => 'parqueaderos'],
            
            ['nombre' => 'Ver depósitos', 'slug' => 'depositos.view', 'modulo' => 'depositos'],
            ['nombre' => 'Crear depósitos', 'slug' => 'depositos.create', 'modulo' => 'depositos'],
            ['nombre' => 'Editar depósitos', 'slug' => 'depositos.edit', 'modulo' => 'depositos'],
            ['nombre' => 'Eliminar depósitos', 'slug' => 'depositos.delete', 'modulo' => 'depositos'],
            
            ['nombre' => 'Ver zonas comunes', 'slug' => 'zonas-sociales.view', 'modulo' => 'zonas-sociales'],
            ['nombre' => 'Crear zonas comunes', 'slug' => 'zonas-sociales.create', 'modulo' => 'zonas-sociales'],
            ['nombre' => 'Editar zonas comunes', 'slug' => 'zonas-sociales.edit', 'modulo' => 'zonas-sociales'],
            ['nombre' => 'Eliminar zonas comunes', 'slug' => 'zonas-sociales.delete', 'modulo' => 'zonas-sociales'],
            
            // Cartera
            ['nombre' => 'Ver cuotas administración', 'slug' => 'cuotas-administracion.view', 'modulo' => 'cartera'],
            ['nombre' => 'Crear cuotas administración', 'slug' => 'cuotas-administracion.create', 'modulo' => 'cartera'],
            ['nombre' => 'Editar cuotas administración', 'slug' => 'cuotas-administracion.edit', 'modulo' => 'cartera'],
            ['nombre' => 'Eliminar cuotas administración', 'slug' => 'cuotas-administracion.delete', 'modulo' => 'cartera'],
            
            ['nombre' => 'Ver cartera', 'slug' => 'cartera.view', 'modulo' => 'cartera'],
            
            ['nombre' => 'Ver cuentas de cobro', 'slug' => 'cuentas-cobro.view', 'modulo' => 'cartera'],
            ['nombre' => 'Crear cuentas de cobro', 'slug' => 'cuentas-cobro.create', 'modulo' => 'cartera'],
            ['nombre' => 'Editar cuentas de cobro', 'slug' => 'cuentas-cobro.edit', 'modulo' => 'cartera'],
            ['nombre' => 'Eliminar cuentas de cobro', 'slug' => 'cuentas-cobro.delete', 'modulo' => 'cartera'],
            
            ['nombre' => 'Ver recaudos', 'slug' => 'recaudos.view', 'modulo' => 'cartera'],
            ['nombre' => 'Crear recaudos', 'slug' => 'recaudos.create', 'modulo' => 'cartera'],
            ['nombre' => 'Editar recaudos', 'slug' => 'recaudos.edit', 'modulo' => 'cartera'],
            ['nombre' => 'Eliminar recaudos', 'slug' => 'recaudos.delete', 'modulo' => 'cartera'],
            
            ['nombre' => 'Ver acuerdos de pago', 'slug' => 'acuerdos-pagos.view', 'modulo' => 'cartera'],
            ['nombre' => 'Crear acuerdos de pago', 'slug' => 'acuerdos-pagos.create', 'modulo' => 'cartera'],
            ['nombre' => 'Editar acuerdos de pago', 'slug' => 'acuerdos-pagos.edit', 'modulo' => 'cartera'],
            ['nombre' => 'Eliminar acuerdos de pago', 'slug' => 'acuerdos-pagos.delete', 'modulo' => 'cartera'],
            
            // Gestión
            ['nombre' => 'Ver comunicados', 'slug' => 'comunicados.view', 'modulo' => 'gestion'],
            ['nombre' => 'Crear comunicados', 'slug' => 'comunicados.create', 'modulo' => 'gestion'],
            ['nombre' => 'Editar comunicados', 'slug' => 'comunicados.edit', 'modulo' => 'gestion'],
            ['nombre' => 'Eliminar comunicados', 'slug' => 'comunicados.delete', 'modulo' => 'gestion'],
            
            ['nombre' => 'Ver correspondencias', 'slug' => 'correspondencias.view', 'modulo' => 'gestion'],
            ['nombre' => 'Crear correspondencias', 'slug' => 'correspondencias.create', 'modulo' => 'gestion'],
            ['nombre' => 'Editar correspondencias', 'slug' => 'correspondencias.edit', 'modulo' => 'gestion'],
            ['nombre' => 'Eliminar correspondencias', 'slug' => 'correspondencias.delete', 'modulo' => 'gestion'],
            
            ['nombre' => 'Ver visitas', 'slug' => 'visitas.view', 'modulo' => 'gestion'],
            ['nombre' => 'Crear visitas', 'slug' => 'visitas.create', 'modulo' => 'gestion'],
            ['nombre' => 'Editar visitas', 'slug' => 'visitas.edit', 'modulo' => 'gestion'],
            ['nombre' => 'Eliminar visitas', 'slug' => 'visitas.delete', 'modulo' => 'gestion'],
            
            ['nombre' => 'Ver autorizaciones', 'slug' => 'autorizaciones.view', 'modulo' => 'gestion'],
            ['nombre' => 'Crear autorizaciones', 'slug' => 'autorizaciones.create', 'modulo' => 'gestion'],
            ['nombre' => 'Editar autorizaciones', 'slug' => 'autorizaciones.edit', 'modulo' => 'gestion'],
            ['nombre' => 'Eliminar autorizaciones', 'slug' => 'autorizaciones.delete', 'modulo' => 'gestion'],
            
            // Convivencia
            ['nombre' => 'Ver llamados de atención', 'slug' => 'llamados-atencion.view', 'modulo' => 'convivencia'],
            ['nombre' => 'Crear llamados de atención', 'slug' => 'llamados-atencion.create', 'modulo' => 'convivencia'],
            ['nombre' => 'Editar llamados de atención', 'slug' => 'llamados-atencion.edit', 'modulo' => 'convivencia'],
            ['nombre' => 'Eliminar llamados de atención', 'slug' => 'llamados-atencion.delete', 'modulo' => 'convivencia'],
            
            ['nombre' => 'Ver PQRS', 'slug' => 'pqrs.view', 'modulo' => 'convivencia'],
            ['nombre' => 'Crear PQRS', 'slug' => 'pqrs.create', 'modulo' => 'convivencia'],
            ['nombre' => 'Editar PQRS', 'slug' => 'pqrs.edit', 'modulo' => 'convivencia'],
            ['nombre' => 'Eliminar PQRS', 'slug' => 'pqrs.delete', 'modulo' => 'convivencia'],
            
            // Reservas
            ['nombre' => 'Ver reservas', 'slug' => 'reservas.view', 'modulo' => 'reservas'],
            ['nombre' => 'Crear reservas', 'slug' => 'reservas.create', 'modulo' => 'reservas'],
            ['nombre' => 'Editar reservas', 'slug' => 'reservas.edit', 'modulo' => 'reservas'],
            ['nombre' => 'Eliminar reservas', 'slug' => 'reservas.delete', 'modulo' => 'reservas'],
            
            // Sorteos Parqueaderos
            ['nombre' => 'Ver sorteos parqueaderos', 'slug' => 'sorteos-parqueadero.view', 'modulo' => 'sorteos-parqueadero'],
            ['nombre' => 'Crear sorteos parqueaderos', 'slug' => 'sorteos-parqueadero.create', 'modulo' => 'sorteos-parqueadero'],
            ['nombre' => 'Editar sorteos parqueaderos', 'slug' => 'sorteos-parqueadero.edit', 'modulo' => 'sorteos-parqueadero'],
            ['nombre' => 'Eliminar sorteos parqueaderos', 'slug' => 'sorteos-parqueadero.delete', 'modulo' => 'sorteos-parqueadero'],
            
            // Manual de Convivencia
            ['nombre' => 'Ver manual de convivencia', 'slug' => 'manual-convivencia.view', 'modulo' => 'manual-convivencia'],
            ['nombre' => 'Editar manual de convivencia', 'slug' => 'manual-convivencia.edit', 'modulo' => 'manual-convivencia'],
        ];

        // Permisos del Residente (Frontend)
        $permisosResidente = [
            ['nombre' => 'Ver mi unidad', 'slug' => 'unidad.view', 'modulo' => 'unidades'],
            ['nombre' => 'Ver pagos', 'slug' => 'pagos.view', 'modulo' => 'pagos'],
            ['nombre' => 'Crear reservas', 'slug' => 'reservas.create', 'modulo' => 'reservas'],
            ['nombre' => 'Ver comunicados', 'slug' => 'comunicados.view', 'modulo' => 'comunicados'],
            ['nombre' => 'Ver manual de convivencia', 'slug' => 'manual-convivencia.view', 'modulo' => 'manual-convivencia'],
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
