<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Modulo;

class ModuloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modulos = [
            // ========================
            // CORE DEL SISTEMA
            // ========================
            [
                'nombre' => 'Dashboard',
                'slug' => 'dashboard',
                'descripcion' => 'Panel principal con métricas y accesos rápidos.',
                'icono' => 'layout-dashboard',
                'ruta' => '/dashboard',
                'activo' => true,
                'requiere_configuracion' => false,
                'orden' => 1,
                'configuracion_default' => [],
            ],
            [
                'nombre' => 'Usuarios y Roles',
                'slug' => 'usuarios-roles',
                'descripcion' => 'Gestión de usuarios, roles y permisos.',
                'icono' => 'users',
                'ruta' => '/usuarios',
                'activo' => true,
                'requiere_configuracion' => false,
                'orden' => 2,
                'configuracion_default' => [],
            ],
            [
                'nombre' => 'Copropiedades',
                'slug' => 'copropiedades',
                'descripcion' => 'Gestión de copropiedades y unidades.',
                'icono' => 'building',
                'ruta' => '/copropiedades',
                'activo' => true,
                'requiere_configuracion' => false,
                'orden' => 3,
                'configuracion_default' => [],
            ],
            [
                'nombre' => 'Configuración General',
                'slug' => 'configuracion-general',
                'descripcion' => 'Configuraciones globales del sistema.',
                'icono' => 'settings',
                'ruta' => '/configuracion',
                'activo' => true,
                'requiere_configuracion' => true,
                'orden' => 4,
                'configuracion_default' => [
                    'permitir_reservas_con_mora' => false,
                    'permitir_visitantes_con_mora' => true,
                    'habilitar_notificaciones_push' => true,
                ],
            ],

            // ========================
            // GESTIÓN ADMINISTRATIVA
            // ========================
            [
                'nombre' => 'Cartera y Pagos',
                'slug' => 'cartera',
                'descripcion' => 'Gestión de cartera, pagos y estados de cuenta.',
                'icono' => 'wallet',
                'ruta' => '/cartera',
                'activo' => true,
                'requiere_configuracion' => true,
                'orden' => 10,
                'configuracion_default' => [
                    'permitir_pagos_parciales' => true,
                    'aplicar_intereses_por_mora' => true,
                    'habilitar_pagos_en_linea' => true,
                ],
            ],
            [
                'nombre' => 'Reservas de Zonas Comunes',
                'slug' => 'reservas-zonas-comunes',
                'descripcion' => 'Gestión de reservas de áreas comunes.',
                'icono' => 'calendar',
                'ruta' => '/reservas',
                'activo' => true,
                'requiere_configuracion' => true,
                'orden' => 11,
                'configuracion_default' => [
                    'requerir_aprobacion_reservas' => false,
                    'limitar_reservas_por_mes' => 4,
                ],
            ],
            [
                'nombre' => 'Comunicados',
                'slug' => 'comunicados',
                'descripcion' => 'Publicación de comunicados a residentes.',
                'icono' => 'megaphone',
                'ruta' => '/comunicados',
                'activo' => true,
                'requiere_configuracion' => false,
                'orden' => 12,
                'configuracion_default' => [],
            ],
            [
                'nombre' => 'Correspondencia',
                'slug' => 'correspondencia',
                'descripcion' => 'Gestión de correspondencia recibida.',
                'icono' => 'mail',
                'ruta' => '/correspondencia',
                'activo' => true,
                'requiere_configuracion' => false,
                'orden' => 13,
                'configuracion_default' => [],
            ],
            [
                'nombre' => 'PQRS',
                'slug' => 'pqrs',
                'descripcion' => 'Gestión de peticiones, quejas, reclamos y sugerencias.',
                'icono' => 'message-square',
                'ruta' => '/pqrs',
                'activo' => true,
                'requiere_configuracion' => true,
                'orden' => 14,
                'configuracion_default' => [
                    'permitir_pqrs_anonimas' => false,
                    'habilitar_sla_pqrs' => true,
                ],
            ],
            [
                'nombre' => 'Comité de Convivencia',
                'slug' => 'comite-convivencia',
                'descripcion' => 'Gestión de llamados de atención y casos de convivencia.',
                'icono' => 'shield',
                'ruta' => '/convivencia',
                'activo' => true,
                'requiere_configuracion' => false,
                'orden' => 15,
                'configuracion_default' => [],
            ],
            [
                'nombre' => 'Citófono Digital',
                'slug' => 'citofono',
                'descripcion' => 'Gestión del citófono digital y llamadas.',
                'icono' => 'phone',
                'ruta' => '/citofono',
                'activo' => true,
                'requiere_configuracion' => true,
                'orden' => 16,
                'configuracion_default' => [
                    'bloquear_citofono_por_mora' => false,
                ],
            ],
            [
                'nombre' => 'Visitantes',
                'slug' => 'visitantes',
                'descripcion' => 'Gestión de visitantes y accesos.',
                'icono' => 'user-check',
                'ruta' => '/visitantes',
                'activo' => true,
                'requiere_configuracion' => true,
                'orden' => 17,
                'configuracion_default' => [
                    'habilitar_acceso_qr' => true,
                    'requerir_aprobacion_residente_visitas' => true,
                ],
            ],
            [
                'nombre' => 'Personal y Horarios',
                'slug' => 'personal-horarios',
                'descripcion' => 'Control de horarios del personal y proveedores.',
                'icono' => 'clock',
                'ruta' => '/personal',
                'activo' => true,
                'requiere_configuracion' => false,
                'orden' => 18,
                'configuracion_default' => [],
            ],

            // ========================
            // COMUNIDAD
            // ========================
            [
                'nombre' => 'Mascotas',
                'slug' => 'mascotas',
                'descripcion' => 'Registro y control de mascotas.',
                'icono' => 'paw-print',
                'ruta' => '/mascotas',
                'activo' => true,
                'requiere_configuracion' => true,
                'orden' => 30,
                'configuracion_default' => [
                    'limitar_mascotas_por_unidad' => 2,
                ],
            ],
            [
                'nombre' => 'Tienda PH',
                'slug' => 'tienda-ph',
                'descripcion' => 'Marketplace interno de la copropiedad.',
                'icono' => 'shopping-cart',
                'ruta' => '/tienda',
                'activo' => true,
                'requiere_configuracion' => true,
                'orden' => 31,
                'configuracion_default' => [
                    'permitir_ventas_entre_residentes' => true,
                ],
            ],
            [
                'nombre' => 'Parqueaderos',
                'slug' => 'parqueaderos',
                'descripcion' => 'Gestión de parqueaderos.',
                'icono' => 'car',
                'ruta' => '/parqueaderos',
                'activo' => true,
                'requiere_configuracion' => false,
                'orden' => 32,
                'configuracion_default' => [],
            ],
            [
                'nombre' => 'Encuestas y Votaciones',
                'slug' => 'encuestas-votaciones',
                'descripcion' => 'Encuestas y votaciones digitales.',
                'icono' => 'bar-chart',
                'ruta' => '/encuestas',
                'activo' => true,
                'requiere_configuracion' => false,
                'orden' => 33,
                'configuracion_default' => [],
            ],

            // ========================
            // CONTROL Y SEGURIDAD
            // ========================
            [
                'nombre' => 'Reportes',
                'slug' => 'reportes',
                'descripcion' => 'Reportes y estadísticas del sistema.',
                'icono' => 'file-text',
                'ruta' => '/reportes',
                'activo' => true,
                'requiere_configuracion' => false,
                'orden' => 50,
                'configuracion_default' => [],
            ],
            [
                'nombre' => 'Auditoría',
                'slug' => 'auditoria',
                'descripcion' => 'Registro de acciones del sistema.',
                'icono' => 'activity',
                'ruta' => '/auditoria',
                'activo' => true,
                'requiere_configuracion' => false,
                'orden' => 51,
                'configuracion_default' => [],
            ],
            [
                'nombre' => 'Notificaciones',
                'slug' => 'notificaciones',
                'descripcion' => 'Gestión de notificaciones del sistema.',
                'icono' => 'bell',
                'ruta' => '/notificaciones',
                'activo' => true,
                'requiere_configuracion' => false,
                'orden' => 52,
                'configuracion_default' => [],
            ],
        ];

        foreach ($modulos as $modulo) {
            Modulo::updateOrCreate(
                ['slug' => $modulo['slug']],
                $modulo
            );
        }

        $this->command->info('Módulos creados exitosamente: ' . count($modulos));
    }
}
