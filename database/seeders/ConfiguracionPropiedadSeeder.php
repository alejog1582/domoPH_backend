<?php

namespace Database\Seeders;

use App\Models\ConfiguracionPropiedad;
use App\Models\Propiedad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionPropiedadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Obtener todas las propiedades activas
        $propiedades = Propiedad::where('estado', 'activa')->get();

        if ($propiedades->isEmpty()) {
            $this->command->warn('No se encontraron propiedades activas para configurar.');
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return;
        }

        $this->command->info("Configurando {$propiedades->count()} propiedad(es) activa(s)...");

        foreach ($propiedades as $propiedad) {
            // Configuración 1: Día de vencimiento de cuentas de cobro (día 10 de cada mes)
            $this->crearOActualizarConfiguracion(
                $propiedad->id,
                'dia_vencimiento_cuenta_cobro',
                '10',
                'integer',
                'Día del mes en que vencen las cuentas de cobro (1-31)'
            );

            // Configuración 2: Porcentaje de descuento por pago a tiempo (8%)
            $this->crearOActualizarConfiguracion(
                $propiedad->id,
                'porcentaje_descuento_pago_tiempo',
                '8',
                'decimal',
                'Porcentaje de descuento aplicado cuando se paga la cuenta de cobro a tiempo'
            );

            $this->command->info("  ✓ Propiedad {$propiedad->nombre} (ID: {$propiedad->id}): Configuraciones establecidas");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info("\nSeeder completado exitosamente.");
    }

    /**
     * Crear o actualizar una configuración de propiedad
     *
     * @param int $propiedadId
     * @param string $clave
     * @param string $valor
     * @param string $tipo
     * @param string $descripcion
     * @return void
     */
    private function crearOActualizarConfiguracion($propiedadId, $clave, $valor, $tipo, $descripcion)
    {
        ConfiguracionPropiedad::updateOrCreate(
            [
                'propiedad_id' => $propiedadId,
                'clave' => $clave,
            ],
            [
                'valor' => $valor,
                'tipo' => $tipo,
                'descripcion' => $descripcion,
            ]
        );
    }
}
