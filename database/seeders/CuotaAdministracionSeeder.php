<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CuotaAdministracion;
use App\Models\Propiedad;

class CuotaAdministracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener una propiedad existente o crear una
        $propiedad = Propiedad::first();

        if (!$propiedad) {
            $this->command->warn('No hay propiedades disponibles. Por favor, ejecuta primero los seeders de propiedades.');
            return;
        }

        // Crear ejemplos de cuotas de administración
        $cuotas = [
            // Cuota ordinaria por coeficiente (indefinida)
            [
                'propiedad_id' => $propiedad->id,
                'concepto' => CuotaAdministracion::CONCEPTO_CUOTA_ORDINARIA,
                'coeficiente' => 0.001, // Ejemplo: 0.1% del valor base
                'valor' => 5000000.00, // Valor base para cálculo proporcional
                'mes_desde' => null,
                'mes_hasta' => null,
                'activo' => true,
            ],
            
            // Cuota extraordinaria fija (indefinida)
            [
                'propiedad_id' => $propiedad->id,
                'concepto' => CuotaAdministracion::CONCEPTO_CUOTA_EXTRAORDINARIA,
                'coeficiente' => null, // Cuota fija
                'valor' => 150000.00, // Valor fijo por unidad
                'mes_desde' => null,
                'mes_hasta' => null,
                'activo' => true,
            ],
            
            // Cuota extraordinaria con rango de meses
            [
                'propiedad_id' => $propiedad->id,
                'concepto' => CuotaAdministracion::CONCEPTO_CUOTA_EXTRAORDINARIA,
                'coeficiente' => null, // Cuota fija
                'valor' => 200000.00, // Valor fijo por unidad
                'mes_desde' => now()->startOfMonth()->format('Y-m-d'),
                'mes_hasta' => now()->addMonths(3)->endOfMonth()->format('Y-m-d'),
                'activo' => true,
            ],
            
            // Cuota ordinaria por coeficiente con rango
            [
                'propiedad_id' => $propiedad->id,
                'concepto' => CuotaAdministracion::CONCEPTO_CUOTA_ORDINARIA,
                'coeficiente' => 0.0008,
                'valor' => 6000000.00,
                'mes_desde' => now()->subMonths(2)->startOfMonth()->format('Y-m-d'),
                'mes_hasta' => now()->addMonths(6)->endOfMonth()->format('Y-m-d'),
                'activo' => true,
            ],
            
            // Cuota extraordinaria por coeficiente (indefinida)
            [
                'propiedad_id' => $propiedad->id,
                'concepto' => CuotaAdministracion::CONCEPTO_CUOTA_EXTRAORDINARIA,
                'coeficiente' => 0.002,
                'valor' => 3000000.00,
                'mes_desde' => null,
                'mes_hasta' => null,
                'activo' => true,
            ],
        ];

        foreach ($cuotas as $cuotaData) {
            CuotaAdministracion::create($cuotaData);
        }

        $this->command->info('Se han creado ' . count($cuotas) . ' cuotas de administración de ejemplo exitosamente.');
    }
}
