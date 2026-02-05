<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ZonaSocial;
use App\Models\ZonaSocialRegla;

class ZonaSocialReglaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📋 Creando reglas para zonas sociales...');

        // Obtener todas las zonas sociales activas
        $zonasSociales = ZonaSocial::where('activo', true)->get();

        if ($zonasSociales->isEmpty()) {
            $this->command->warn('⚠ No hay zonas sociales activas. Ejecuta primero ZonaSocialSeeder o DemoSeeder.');
            return;
        }

        foreach ($zonasSociales as $zona) {
            // Verificar si ya tiene reglas
            if ($zona->reglas()->count() > 0) {
                $this->command->info("   ⏭ Zona '{$zona->nombre}' ya tiene reglas, omitiendo...");
                continue;
            }

            // Reglas comunes para todas las zonas
            $reglas = [
                [
                    'clave' => ZonaSocialRegla::CLAVE_MAX_RESERVAS_MES,
                    'valor' => '3',
                    'descripcion' => 'Número máximo de reservas que un residente puede hacer por mes',
                ],
                [
                    'clave' => ZonaSocialRegla::CLAVE_DIAS_ANTICIPACION,
                    'valor' => '7',
                    'descripcion' => 'Días de anticipación mínimos para hacer una reserva',
                ],
                [
                    'clave' => ZonaSocialRegla::CLAVE_HORAS_CANCELACION,
                    'valor' => '48',
                    'descripcion' => 'Horas mínimas de anticipación para cancelar una reserva',
                ],
                [
                    'clave' => ZonaSocialRegla::CLAVE_PERMITE_INVITADOS,
                    'valor' => $zona->acepta_invitados ? 'true' : 'false',
                    'descripcion' => 'Indica si se permiten invitados en las reservas',
                ],
            ];

            // Reglas específicas según la configuración de la zona
            if ($zona->valor_deposito) {
                $reglas[] = [
                    'clave' => ZonaSocialRegla::CLAVE_REQUIERE_DEPOSITO,
                    'valor' => 'true',
                    'descripcion' => 'Indica si se requiere depósito para reservar la zona',
                ];
            }

            if (!$zona->permite_reservas_en_mora) {
                $reglas[] = [
                    'clave' => ZonaSocialRegla::CLAVE_BLOQUEAR_EN_MORA,
                    'valor' => 'true',
                    'descripcion' => 'Indica si se bloquean reservas para residentes en mora',
                ];
            }

            if ($zona->max_invitados_por_reserva) {
                $reglas[] = [
                    'clave' => ZonaSocialRegla::CLAVE_MAX_INVITADOS,
                    'valor' => (string) $zona->max_invitados_por_reserva,
                    'descripcion' => 'Número máximo de invitados permitidos',
                ];
            }

            // Crear las reglas
            foreach ($reglas as $regla) {
                ZonaSocialRegla::create([
                    'zona_social_id' => $zona->id,
                    'clave' => $regla['clave'],
                    'valor' => $regla['valor'],
                    'descripcion' => $regla['descripcion'],
                ]);
            }

            $this->command->info("   ✓ Reglas creadas para '{$zona->nombre}'");
        }

        $this->command->info('✅ Reglas de zonas sociales creadas exitosamente');
    }
}
