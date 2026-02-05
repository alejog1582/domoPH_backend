<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ZonaSocial;
use App\Models\ZonaSocialImagen;
use App\Models\ZonaSocialHorario;
use App\Models\ZonaSocialRegla;
use App\Models\Propiedad;

class ZonaSocialSeeder extends Seeder
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

        // Crear zonas sociales de ejemplo
        $zonasSociales = [
            [
                'nombre' => 'Salón Social Torre 1',
                'descripcion' => 'Amplio salón social con capacidad para eventos y reuniones. Incluye cocina, sistema de sonido y proyector.',
                'ubicacion' => 'Torre 1 - Piso 1',
                'capacidad_maxima' => 80,
                'max_invitados_por_reserva' => 10,
                'tiempo_minimo_uso_horas' => 2,
                'tiempo_maximo_uso_horas' => 8,
                'reservas_simultaneas' => true,
                'valor_alquiler' => 150000.00,
                'valor_deposito' => 300000.00,
                'requiere_aprobacion' => true,
                'permite_reservas_en_mora' => false,
                'reglamento_url' => 'https://ejemplo.com/reglamento/salon-social',
                'estado' => ZonaSocial::ESTADO_ACTIVA,
                'activo' => true,
            ],
            [
                'nombre' => 'Piscina Principal',
                'descripcion' => 'Piscina olímpica con área de descanso, duchas y vestuarios. Incluye área de juegos infantiles.',
                'ubicacion' => 'Exterior - Zona Central',
                'capacidad_maxima' => 50,
                'max_invitados_por_reserva' => 5,
                'tiempo_minimo_uso_horas' => 1,
                'tiempo_maximo_uso_horas' => 6,
                'reservas_simultaneas' => true,
                'valor_alquiler' => null,
                'valor_deposito' => 200000.00,
                'requiere_aprobacion' => false,
                'permite_reservas_en_mora' => false,
                'reglamento_url' => null,
                'estado' => ZonaSocial::ESTADO_ACTIVA,
                'activo' => true,
            ],
            [
                'nombre' => 'Gimnasio',
                'descripcion' => 'Gimnasio completamente equipado con máquinas de cardio, pesas y área de entrenamiento funcional.',
                'ubicacion' => 'Torre 2 - Piso 1',
                'capacidad_maxima' => 20,
                'max_invitados_por_reserva' => 2,
                'tiempo_minimo_uso_horas' => 1,
                'tiempo_maximo_uso_horas' => 3,
                'reservas_simultaneas' => true,
                'valor_alquiler' => null,
                'valor_deposito' => null,
                'requiere_aprobacion' => false,
                'permite_reservas_en_mora' => true,
                'reglamento_url' => null,
                'estado' => ZonaSocial::ESTADO_ACTIVA,
                'activo' => true,
            ],
            [
                'nombre' => 'Cancha de Tenis',
                'descripcion' => 'Cancha de tenis profesional con iluminación nocturna y área de descanso.',
                'ubicacion' => 'Exterior - Zona Deportiva',
                'capacidad_maxima' => 8,
                'max_invitados_por_reserva' => 2,
                'tiempo_minimo_uso_horas' => 1,
                'tiempo_maximo_uso_horas' => 2,
                'reservas_simultaneas' => true,
                'valor_alquiler' => 50000.00,
                'valor_deposito' => 100000.00,
                'requiere_aprobacion' => false,
                'permite_reservas_en_mora' => false,
                'reglamento_url' => null,
                'estado' => ZonaSocial::ESTADO_ACTIVA,
                'activo' => true,
            ],
            [
                'nombre' => 'Zona BBQ',
                'descripcion' => 'Área de parrillas con mesas, sillas y zona de descanso. Ideal para reuniones familiares.',
                'ubicacion' => 'Exterior - Zona Recreativa',
                'capacidad_maxima' => 30,
                'max_invitados_por_reserva' => 8,
                'tiempo_minimo_uso_horas' => 2,
                'tiempo_maximo_uso_horas' => 6,
                'reservas_simultaneas' => true,
                'valor_alquiler' => 80000.00,
                'valor_deposito' => 150000.00,
                'requiere_aprobacion' => true,
                'permite_reservas_en_mora' => false,
                'reglamento_url' => null,
                'estado' => ZonaSocial::ESTADO_ACTIVA,
                'activo' => true,
            ],
        ];

        foreach ($zonasSociales as $zonaData) {
            $zonaData['propiedad_id'] = $propiedad->id;
            $zona = ZonaSocial::create($zonaData);

            // Crear imágenes para cada zona (2-4 imágenes)
            $numImagenes = rand(2, 4);
            for ($i = 0; $i < $numImagenes; $i++) {
                ZonaSocialImagen::create([
                    'zona_social_id' => $zona->id,
                    'url_imagen' => 'https://via.placeholder.com/800x600?text=' . urlencode($zona->nombre) . '+' . ($i + 1),
                    'orden' => $i,
                    'activo' => true,
                ]);
            }

            // Crear horarios para cada zona (horarios de lunes a domingo)
            $horarios = [
                ['dia_semana' => ZonaSocialHorario::DIA_LUNES, 'hora_inicio' => '06:00', 'hora_fin' => '22:00'],
                ['dia_semana' => ZonaSocialHorario::DIA_MARTES, 'hora_inicio' => '06:00', 'hora_fin' => '22:00'],
                ['dia_semana' => ZonaSocialHorario::DIA_MIERCOLES, 'hora_inicio' => '06:00', 'hora_fin' => '22:00'],
                ['dia_semana' => ZonaSocialHorario::DIA_JUEVES, 'hora_inicio' => '06:00', 'hora_fin' => '22:00'],
                ['dia_semana' => ZonaSocialHorario::DIA_VIERNES, 'hora_inicio' => '06:00', 'hora_fin' => '22:00'],
                ['dia_semana' => ZonaSocialHorario::DIA_SABADO, 'hora_inicio' => '08:00', 'hora_fin' => '20:00'],
                ['dia_semana' => ZonaSocialHorario::DIA_DOMINGO, 'hora_inicio' => '08:00', 'hora_fin' => '20:00'],
            ];

            foreach ($horarios as $horario) {
                ZonaSocialHorario::create([
                    'zona_social_id' => $zona->id,
                    'dia_semana' => $horario['dia_semana'],
                    'hora_inicio' => $horario['hora_inicio'],
                    'hora_fin' => $horario['hora_fin'],
                    'activo' => true,
                ]);
            }

            // Crear reglas comunes para cada zona
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
                    'valor' => 'true',
                    'descripcion' => 'Indica si se permiten invitados en las reservas',
                ],
            ];

            // Agregar reglas específicas según el tipo de zona
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

            foreach ($reglas as $regla) {
                ZonaSocialRegla::create([
                    'zona_social_id' => $zona->id,
                    'clave' => $regla['clave'],
                    'valor' => $regla['valor'],
                    'descripcion' => $regla['descripcion'],
                ]);
            }
        }

        $this->command->info('Se han creado ' . count($zonasSociales) . ' zonas sociales de ejemplo exitosamente.');
    }
}
