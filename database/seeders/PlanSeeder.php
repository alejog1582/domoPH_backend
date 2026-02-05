<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plan Base domoPH
        Plan::updateOrCreate(
            ['slug' => 'plan-base-domoph'],
            [
                'nombre' => 'Plan Base domoPH',
                'slug' => 'plan-base-domoph',
                'descripcion' => 'Plan estándar para copropiedades. Se cobra por unidad habitacional (apartamento o casa). Incluye todos los módulos esenciales de domoPH.',
                
                // Precio por unidad (apartamento) en pesos colombianos
                'precio_mensual' => 2000.00,
                'precio_anual' => 24000.00, // 2000 * 12
                
                // Límites del plan (null = ilimitado)
                'max_unidades' => null,
                'max_usuarios' => null,
                'max_almacenamiento_mb' => 10240, // 10 GB
                
                'soporte_prioritario' => false,
                'activo' => true,
                'orden' => 1,

                // Características del plan en array (el modelo lo convierte a JSON automáticamente)
                'caracteristicas' => [
                    'modelo_cobro' => 'por_unidad',
                    'precio_por_unidad' => 2000,
                    'modulos' => [
                        // GESTIÓN ADMINISTRATIVA
                        'cartera',
                        'reservas-zonas-comunes',
                        'comunicados',
                        'correspondencia',
                        'visitas',
                        'autorizaciones',
                        'llamados-atencion',
                        'pqrs',
                        'sorteos-parqueadero',
                        'manual-convivencia',
                        // COMUNIDAD
                        'mascotas',
                        'parqueaderos',
                        'zonas-comunes',
                    ],
                    'beneficios' => [
                        'Acceso completo a módulos básicos',
                        'Soporte estándar',
                        'Actualizaciones incluidas',
                        'Dashboard administrativo',
                    ],
                ],
            ]
        );

        $this->command->info('Plan Base domoPH creado exitosamente');
    }
}
