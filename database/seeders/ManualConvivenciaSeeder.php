<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManualConvivenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📚 Creando manual de convivencia...');

        $copropiedadId = 1; // ID de la copropiedad demo

        // Verificar que existe la copropiedad
        $propiedad = DB::table('propiedades')->where('id', $copropiedadId)->first();
        if (!$propiedad) {
            $this->command->warn("⚠️  No se encontró la copropiedad con ID {$copropiedadId}. Saltando seeder.");
            return;
        }

        $manualUrl = 'https://res.cloudinary.com/dikbf3xct/image/upload/v1771000333/domoph/demo/Manual-de-convivencia-2024-2.pdf';

        $principalesDeberes = '<h2>Principales Deberes de los Residentes</h2>
<p>Los residentes de la copropiedad tienen los siguientes deberes fundamentales:</p>

<h3>1. Pago Oportuno de Cuotas</h3>
<ul>
    <li>Cumplir con el pago puntual de las cuotas de administración y demás obligaciones económicas.</li>
    <li>Realizar los pagos en las fechas establecidas para evitar intereses de mora.</li>
    <li>Mantener al día todas las obligaciones financieras con la copropiedad.</li>
</ul>

<h3>2. Respeto a las Normas de Convivencia</h3>
<ul>
    <li>Respetar los horarios de silencio establecidos en el reglamento.</li>
    <li>Mantener un comportamiento adecuado en las zonas comunes.</li>
    <li>Evitar ruidos excesivos que puedan molestar a otros residentes.</li>
</ul>

<h3>3. Mantenimiento de la Unidad</h3>
<ul>
    <li>Mantener su unidad en buen estado de conservación.</li>
    <li>Realizar las reparaciones necesarias dentro de su unidad.</li>
    <li>Notificar a la administración sobre daños en áreas comunes.</li>
</ul>

<h3>4. Uso Adecuado de Zonas Comunes</h3>
<ul>
    <li>Utilizar las zonas comunes de manera responsable y respetuosa.</li>
    <li>Respetar las reservas y horarios establecidos para zonas sociales.</li>
    <li>Mantener limpias las áreas comunes después de su uso.</li>
</ul>

<h3>5. Participación en Asambleas</h3>
<ul>
    <li>Asistir a las asambleas de copropietarios cuando sea requerido.</li>
    <li>Participar activamente en las decisiones de la copropiedad.</li>
    <li>Votar de manera responsable en los temas propuestos.</li>
</ul>';

        $principalesObligaciones = '<h2>Principales Obligaciones de los Residentes</h2>
<p>De acuerdo con la Ley 675 de 2001 y el reglamento de propiedad horizontal, los residentes tienen las siguientes obligaciones:</p>

<h3>1. Obligaciones Económicas</h3>
<ul>
    <li><strong>Cuotas de Administración:</strong> Pago mensual de las cuotas de administración establecidas por la asamblea.</li>
    <li><strong>Fondo de Reserva:</strong> Contribución al fondo de reserva para mantenimiento y mejoras.</li>
    <li><strong>Gastos Extraordinarios:</strong> Participación proporcional en gastos extraordinarios aprobados.</li>
    <li><strong>Multas y Sanciones:</strong> Pago de multas y sanciones impuestas por incumplimiento de normas.</li>
</ul>

<h3>2. Obligaciones de Conservación</h3>
<ul>
    <li>Conservar y mantener en buen estado su unidad privada.</li>
    <li>No realizar modificaciones estructurales sin autorización previa.</li>
    <li>Permitir el acceso a su unidad para inspecciones y reparaciones necesarias.</li>
    <li>Reportar inmediatamente cualquier daño o riesgo en la estructura del edificio.</li>
</ul>

<h3>3. Obligaciones de Convivencia</h3>
<ul>
    <li>Cumplir con los horarios de silencio (generalmente de 10:00 PM a 6:00 AM).</li>
    <li>No realizar actividades que generen ruidos molestos o contaminación.</li>
    <li>Respetar la privacidad y tranquilidad de los demás residentes.</li>
    <li>Mantener un comportamiento respetuoso en todas las áreas comunes.</li>
</ul>

<h3>4. Obligaciones con Mascotas</h3>
<ul>
    <li>Registrar todas las mascotas ante la administración.</li>
    <li>Mantener las mascotas bajo control en áreas comunes.</li>
    <li>Recoger los desechos de las mascotas inmediatamente.</li>
    <li>Evitar que las mascotas generen ruidos excesivos.</li>
</ul>

<h3>5. Obligaciones de Seguridad</h3>
<ul>
    <li>Mantener cerradas las puertas de acceso común.</li>
    <li>No permitir el acceso de personas no autorizadas.</li>
    <li>Reportar situaciones sospechosas o de riesgo a la administración.</li>
    <li>Colaborar con las medidas de seguridad establecidas.</li>
</ul>

<h3>6. Obligaciones con Visitantes</h3>
<ul>
    <li>Registrar a los visitantes según el procedimiento establecido.</li>
    <li>Responsabilizarse del comportamiento de sus visitantes.</li>
    <li>Asegurar que los visitantes cumplan con las normas de la copropiedad.</li>
    <li>Notificar la salida de visitantes cuando sea requerido.</li>
</ul>

<h3>7. Obligaciones de Información</h3>
<ul>
    <li>Proporcionar información actualizada de contacto a la administración.</li>
    <li>Notificar cambios en la composición familiar o en el uso de la unidad.</li>
    <li>Responder oportunamente a las comunicaciones de la administración.</li>
    <li>Participar en los procesos de comunicación y notificación establecidos.</li>
</ul>';

        DB::table('manual_convivencia')->updateOrInsert(
            [
                'copropiedad_id' => $copropiedadId,
            ],
            [
                'manual_url' => $manualUrl,
                'principales_deberes' => $principalesDeberes,
                'principales_obligaciones' => $principalesObligaciones,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('✅ Manual de convivencia creado exitosamente.');
        $this->command->info('   URL del manual: ' . $manualUrl);
    }
}
