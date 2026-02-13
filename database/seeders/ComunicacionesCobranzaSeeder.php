<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComunicacionesCobranzaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📧 Creando comunicaciones de cobranza automatizadas...');

        $copropiedadId = 1; // ID de la copropiedad demo

        // Verificar que existe la copropiedad
        $propiedad = DB::table('propiedades')->where('id', $copropiedadId)->first();
        if (!$propiedad) {
            $this->command->warn("⚠️  No se encontró la copropiedad con ID {$copropiedadId}. Saltando seeder.");
            return;
        }

        $comunicaciones = [
            // 1. Generación cuenta de cobro mensual
            [
                'copropiedad_id' => $copropiedadId,
                'nombre' => 'Generación cuenta de cobro mensual',
                'descripcion' => 'Comunicación enviada cuando se genera la cuenta de cobro del mes',
                'canal' => 'ambos',
                'dia_envio_mes' => 1,
                'dias_mora_desde' => 0,
                'dias_mora_hasta' => 0,
                'asunto' => 'Cuenta de cobro generada',
                'mensaje_email' => 'Se ha generado su cuenta de cobro correspondiente al mes actual. Puede consultarla desde la app Domoph y realizar su pago oportunamente para evitar intereses.',
                'mensaje_whatsapp' => 'Se ha generado su cuenta de cobro correspondiente al mes actual. Puede consultarla desde la app Domoph y realizar su pago oportunamente para evitar intereses.',
                'activo' => true,
            ],

            // 2. Recordatorio previo a primer vencimiento
            [
                'copropiedad_id' => $copropiedadId,
                'nombre' => 'Recordatorio previo a primer vencimiento',
                'descripcion' => 'Recordatorio enviado antes de la primera fecha de vencimiento',
                'canal' => 'ambos',
                'dia_envio_mes' => 12,
                'dias_mora_desde' => 0,
                'dias_mora_hasta' => 0,
                'asunto' => 'Se aproxima la primera fecha de vencimiento',
                'mensaje_email' => 'Le recordamos que se aproxima la primera fecha de vencimiento de su cuenta de cobro. Realice su pago antes de la fecha límite y conserve el beneficio de descuento.',
                'mensaje_whatsapp' => 'Le recordamos que se aproxima la primera fecha de vencimiento de su cuenta de cobro. Realice su pago antes de la fecha límite y conserve el beneficio de descuento.',
                'activo' => true,
            ],

            // 3. Vencimiento primera fecha
            [
                'copropiedad_id' => $copropiedadId,
                'nombre' => 'Vencimiento primera fecha',
                'descripcion' => 'Comunicación enviada el día de vencimiento de la primera fecha de pago',
                'canal' => 'ambos',
                'dia_envio_mes' => 15,
                'dias_mora_desde' => 0,
                'dias_mora_hasta' => 0,
                'asunto' => 'Hoy vence la primera fecha de pago',
                'mensaje_email' => 'Hoy vence la primera fecha de pago de su cuenta de cobro. Le recomendamos realizar el pago hoy mismo para evitar recargos o pérdida de beneficios.',
                'mensaje_whatsapp' => 'Hoy vence la primera fecha de pago de su cuenta de cobro. Le recomendamos realizar el pago hoy mismo para evitar recargos o pérdida de beneficios.',
                'activo' => true,
            ],

            // 4. Prevención de mora
            [
                'copropiedad_id' => $copropiedadId,
                'nombre' => 'Prevención de mora',
                'descripcion' => 'Comunicación preventiva para evitar que el residente entre en mora',
                'canal' => 'ambos',
                'dia_envio_mes' => 20,
                'dias_mora_desde' => 0,
                'dias_mora_hasta' => 0,
                'asunto' => 'Evite entrar en mora',
                'mensaje_email' => 'Aún está a tiempo de realizar el pago de su cuenta de cobro. Evite entrar en mora y posibles cargos adicionales realizando su pago oportunamente.',
                'mensaje_whatsapp' => 'Aún está a tiempo de realizar el pago de su cuenta de cobro. Evite entrar en mora y posibles cargos adicionales realizando su pago oportunamente.',
                'activo' => true,
            ],

            // 5. Último recordatorio del mes
            [
                'copropiedad_id' => $copropiedadId,
                'nombre' => 'Último recordatorio del mes',
                'descripcion' => 'Último recordatorio antes de que finalice el mes',
                'canal' => 'ambos',
                'dia_envio_mes' => 28,
                'dias_mora_desde' => 0,
                'dias_mora_hasta' => 0,
                'asunto' => 'Último recordatorio de pago del mes',
                'mensaje_email' => 'El mes está por finalizar y aún no hemos recibido el pago de su cuenta de cobro. Evite intereses y procesos de cobranza realizando su pago lo antes posible.',
                'mensaje_whatsapp' => 'El mes está por finalizar y aún no hemos recibido el pago de su cuenta de cobro. Evite intereses y procesos de cobranza realizando su pago lo antes posible.',
                'activo' => true,
            ],

            // 6. Vencimiento final del mes
            [
                'copropiedad_id' => $copropiedadId,
                'nombre' => 'Vencimiento final del mes',
                'descripcion' => 'Comunicación enviada el día de vencimiento final de la factura',
                'canal' => 'ambos',
                'dia_envio_mes' => 30,
                'dias_mora_desde' => 0,
                'dias_mora_hasta' => 0,
                'asunto' => 'Su factura vence hoy',
                'mensaje_email' => 'Hoy vence su factura. Le recomendamos realizar el pago inmediatamente para evitar cargos por mora e intereses.',
                'mensaje_whatsapp' => 'Hoy vence su factura. Le recomendamos realizar el pago inmediatamente para evitar cargos por mora e intereses.',
                'activo' => true,
            ],

            // 7. Mora mayor a 30 días
            [
                'copropiedad_id' => $copropiedadId,
                'nombre' => 'Mora mayor a 30 días',
                'descripcion' => 'Comunicación para residentes con mora entre 31 y 60 días',
                'canal' => 'ambos',
                'dia_envio_mes' => 5,
                'dias_mora_desde' => 31,
                'dias_mora_hasta' => 60,
                'asunto' => 'Su cuenta presenta mora superior a 30 días',
                'mensaje_email' => 'Su cuenta presenta una mora superior a 30 días. Le invitamos a regularizar su pago lo antes posible para evitar intereses adicionales.',
                'mensaje_whatsapp' => 'Su cuenta presenta una mora superior a 30 días. Le invitamos a regularizar su pago lo antes posible para evitar intereses adicionales.',
                'activo' => true,
            ],

            // 8. Mora mayor a 60 días
            [
                'copropiedad_id' => $copropiedadId,
                'nombre' => 'Mora mayor a 60 días',
                'descripcion' => 'Comunicación para residentes con mora entre 61 y 90 días',
                'canal' => 'ambos',
                'dia_envio_mes' => 10,
                'dias_mora_desde' => 61,
                'dias_mora_hasta' => 90,
                'asunto' => 'Mora superior a 60 días',
                'mensaje_email' => 'Su cuenta presenta mora superior a 60 días. Es importante regularizar su situación para evitar acciones administrativas adicionales.',
                'mensaje_whatsapp' => 'Su cuenta presenta mora superior a 60 días. Es importante regularizar su situación para evitar acciones administrativas adicionales.',
                'activo' => true,
            ],

            // 9. Mora superior a 90 días
            [
                'copropiedad_id' => $copropiedadId,
                'nombre' => 'Mora superior a 90 días',
                'descripcion' => 'Comunicación para residentes con mora superior a 90 días (mora crítica)',
                'canal' => 'ambos',
                'dia_envio_mes' => 15,
                'dias_mora_desde' => 91,
                'dias_mora_hasta' => null, // Sin límite superior
                'asunto' => 'Mora crítica superior a 90 días',
                'mensaje_email' => 'Su cuenta presenta mora superior a 90 días. Su caso puede ser remitido a proceso jurídico. Comuníquese con la administración para evitar acciones legales.',
                'mensaje_whatsapp' => 'Su cuenta presenta mora superior a 90 días. Su caso puede ser remitido a proceso jurídico. Comuníquese con la administración para evitar acciones legales.',
                'activo' => true,
            ],
        ];

        foreach ($comunicaciones as $comunicacion) {
            DB::table('comunicaciones_cobranza')->updateOrInsert(
                [
                    'copropiedad_id' => $comunicacion['copropiedad_id'],
                    'nombre' => $comunicacion['nombre'],
                ],
                array_merge($comunicacion, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ Comunicaciones de cobranza creadas exitosamente.');
        $this->command->info('   Total: ' . count($comunicaciones) . ' comunicaciones configuradas.');
    }
}
