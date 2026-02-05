<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comunicado;
use App\Models\Propiedad;
use App\Models\User;
use Carbon\Carbon;

class ComunicadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📢 Iniciando creación de comunicados DEMO...');

        // Obtener la propiedad demo
        $propiedad = Propiedad::where('email', 'demo@domoph.com')->first();

        if (!$propiedad) {
            $this->command->error('   ✗ No se encontró la propiedad demo. Ejecuta primero el DemoSeeder.');
            return;
        }

        // Obtener usuario administrador para usar como autor
        $adminUser = User::where('email', 'demo@domoph.com')->first();

        if (!$adminUser) {
            $this->command->error('   ✗ No se encontró el usuario administrador demo.');
            return;
        }

        // Crear comunicados
        $comunicados = [
            [
                'titulo' => 'Corte de Agua Programado - Lavado de Tanques',
                'contenido' => '<p><strong>Estimados residentes,</strong></p>

<p>Les informamos que se realizará un <strong>corte de agua programado</strong> el próximo <strong>miércoles 15 de febrero de 2026, de 8:00 AM a 2:00 PM</strong>, debido al mantenimiento y lavado de los tanques de almacenamiento de agua.</p>

<p>Durante este período, no habrá suministro de agua en todo el edificio. Les recomendamos:</p>

<ul>
<li>Almacenar agua suficiente para el día</li>
<li>Evitar el uso de lavadoras y lavavajillas durante el corte</li>
<li>Cerrar todas las llaves de agua antes del corte programado</li>
</ul>

<p>El servicio será restablecido una vez finalizado el mantenimiento. Agradecemos su comprensión y colaboración.</p>

<p>Cualquier inconveniente, favor comunicarse con la administración.</p>',
                'resumen' => 'Corte de agua programado el miércoles 15 de febrero de 8:00 AM a 2:00 PM por mantenimiento de tanques.',
                'tipo' => Comunicado::TIPO_MANTENIMIENTO,
                'publicado' => true,
                'fecha_publicacion' => Carbon::now()->subDays(3),
                'visible_para' => Comunicado::VISIBLE_TODOS,
                'imagen_portada' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770332595/domoph/demo/ChatGPT_Image_5_feb_2026_05_48_08_p.m..png',
                'destacado' => true,
            ],
            [
                'titulo' => 'Servicio de Luz - Facturación Disponible en Portería',
                'contenido' => '<p><strong>Buenos días, comunidad.</strong></p>

<p>Les recordamos que el servicio de facturación de energía eléctrica se encuentra disponible en la portería del edificio.</p>

<p><strong>Horarios de atención:</strong></p>
<ul>
<li>Lunes a Viernes: 8:00 AM - 6:00 PM</li>
<li>Sábados: 9:00 AM - 1:00 PM</li>
<li>Domingos: Cerrado</li>
</ul>

<p>Pueden acercarse a la portería para:</p>
<ul>
<li>Consultar su factura de energía</li>
<li>Realizar el pago correspondiente</li>
<li>Solicitar información sobre su consumo</li>
</ul>

<p>Para mayor comodidad, también pueden consultar su factura a través del portal web de la empresa de energía o mediante la aplicación móvil.</p>

<p>Agradecemos su atención.</p>',
                'resumen' => 'El servicio de facturación de energía eléctrica está disponible en portería en horarios establecidos.',
                'tipo' => Comunicado::TIPO_INFORMATIVO,
                'publicado' => true,
                'fecha_publicacion' => Carbon::now()->subDays(5),
                'visible_para' => Comunicado::VISIBLE_TODOS,
                'imagen_portada' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770332732/domoph/demo/ChatGPT_Image_5_feb_2026_06_05_08_p.m..png',
                'destacado' => false,
            ],
            [
                'titulo' => 'Mantenimiento Preventivo de Ascensores',
                'contenido' => '<p><strong>Estimados residentes,</strong></p>

<p>La administración informa que se realizará <strong>mantenimiento preventivo de los ascensores</strong> del edificio el día <strong>viernes 20 de febrero de 2026, de 9:00 AM a 12:00 PM</strong>.</p>

<p>Durante este período, los ascensores estarán fuera de servicio para garantizar la seguridad de todos. Les pedimos:</p>

<ul>
<li>Utilizar las escaleras durante el mantenimiento</li>
<li>Planificar sus salidas y llegadas considerando este horario</li>
<li>Tener paciencia durante el proceso</li>
</ul>

<p>El mantenimiento es necesario para garantizar el correcto funcionamiento y seguridad de los ascensores. Una vez finalizado, el servicio será restablecido normalmente.</p>

<p>Disculpen las molestias que esto pueda ocasionar.</p>',
                'resumen' => 'Mantenimiento preventivo de ascensores el viernes 20 de febrero de 9:00 AM a 12:00 PM.',
                'tipo' => Comunicado::TIPO_MANTENIMIENTO,
                'publicado' => true,
                'fecha_publicacion' => Carbon::now()->subDays(1),
                'visible_para' => Comunicado::VISIBLE_TODOS,
                'imagen_portada' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770332597/domoph/demo/ChatGPT_Image_5_feb_2026_05_54_50_p.m..png',
                'destacado' => false,
            ],
            [
                'titulo' => 'Convocatoria a Reunión de Copropietarios',
                'contenido' => '<p><strong>Estimados copropietarios,</strong></p>

<p>Por medio de la presente, convocamos a la <strong>Asamblea General de Copropietarios</strong> que se llevará a cabo el día <strong>sábado 28 de febrero de 2026 a las 10:00 AM</strong> en el salón comunal del edificio.</p>

<p><strong>Orden del día:</strong></p>
<ol>
<li>Aprobación del orden del día</li>
<li>Informe de gestión administrativa del último trimestre</li>
<li>Presentación y aprobación del presupuesto anual 2026</li>
<li>Elección de miembros del comité de administración</li>
<li>Asuntos varios</li>
</ol>

<p>Es importante su asistencia, ya que se tratarán temas relevantes para la comunidad. La reunión tendrá quórum con la asistencia del <strong>50% más uno de los copropietarios</strong>.</p>

<p>Favor confirmar su asistencia antes del <strong>25 de febrero</strong> comunicándose con la administración.</p>

<p><strong>Cordialmente,<br>Administración</strong></p>',
                'resumen' => 'Convocatoria a Asamblea General de Copropietarios el sábado 28 de febrero a las 10:00 AM.',
                'tipo' => Comunicado::TIPO_URGENTE,
                'publicado' => true,
                'fecha_publicacion' => Carbon::now()->subDays(7),
                'visible_para' => Comunicado::VISIBLE_PROPIETARIOS,
                'imagen_portada' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770332596/domoph/demo/ChatGPT_Image_5_feb_2026_05_58_26_p.m..png',
                'destacado' => true,
            ],
            [
                'titulo' => 'Cambio de Horario de Piscina - Temporada Alta',
                'contenido' => '<p><strong>Buenos días, comunidad.</strong></p>

<p>Les informamos que debido a la temporada alta y para brindar un mejor servicio, se ha modificado el horario de uso de la piscina.</p>

<p><strong>Nuevos horarios</strong> (efectivos desde el <strong>1 de marzo de 2026</strong>):</p>
<ul>
<li>Lunes a Viernes: 6:00 AM - 8:00 PM</li>
<li>Sábados, Domingos y Festivos: 7:00 AM - 9:00 PM</li>
</ul>

<p><strong>Normas de uso:</strong></p>
<ul>
<li>Los menores de 14 años deben estar acompañados por un adulto responsable</li>
<li>Se prohíbe el ingreso de alimentos y bebidas alcohólicas</li>
<li>El uso de la piscina es bajo su propia responsabilidad</li>
<li>Se debe mantener el orden y respeto hacia los demás usuarios</li>
</ul>

<p>El servicio de salvavidas estará disponible los fines de semana de <strong>10:00 AM a 6:00 PM</strong>.</p>

<p>Agradecemos su colaboración para mantener las instalaciones en óptimas condiciones.</p>',
                'resumen' => 'Cambio de horario de piscina a partir del 1 de marzo. Nuevos horarios y normas de uso.',
                'tipo' => Comunicado::TIPO_INFORMATIVO,
                'publicado' => true,
                'fecha_publicacion' => Carbon::now()->subDays(2),
                'visible_para' => Comunicado::VISIBLE_TODOS,
                'imagen_portada' => 'https://res.cloudinary.com/dikbf3xct/image/upload/v1770332600/domoph/demo/ChatGPT_Image_5_feb_2026_06_02_47_p.m..png',
                'destacado' => false,
            ],
        ];

        $comunicadosCreados = 0;

        foreach ($comunicados as $comunicadoData) {
            Comunicado::updateOrCreate(
                [
                    'copropiedad_id' => $propiedad->id,
                    'slug' => \Illuminate\Support\Str::slug($comunicadoData['titulo']),
                ],
                array_merge($comunicadoData, [
                    'copropiedad_id' => $propiedad->id,
                    'autor_id' => $adminUser->id,
                    'activo' => true,
                ])
            );
            $comunicadosCreados++;
        }

        $this->command->info('   ✓ ' . $comunicadosCreados . ' comunicados creados exitosamente');
    }
}
