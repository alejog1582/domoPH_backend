<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Visita;
use App\Models\LiquidacionParqueaderoVisitante;
use App\Models\Parqueadero;
use App\Models\Unidad;
use App\Models\Residente;
use App\Models\Propiedad;
use App\Models\User;
use Carbon\Carbon;

class VisitasParqueaderosVisitantesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚗 Creando visitas y liquidaciones de parqueaderos visitantes...');

        // Obtener la propiedad demo (id = 1)
        $propiedad = Propiedad::find(1);
        
        if (!$propiedad) {
            $this->command->warn('   ⚠ No se encontró la propiedad con id 1');
            return;
        }

        // Verificar si el cobro está activo
        $cobroParqVisitantes = DB::table('configuraciones_propiedad')
            ->where('propiedad_id', $propiedad->id)
            ->where('clave', 'cobro_parq_visitantes')
            ->value('valor');

        $cobroActivo = $cobroParqVisitantes === 'true';

        if (!$cobroActivo) {
            $this->command->warn('   ⚠ El cobro de parqueaderos visitantes no está activo. Se crearán visitas sin liquidaciones.');
        }

        // Obtener configuración de minutos de gracia y valor por minuto
        $minutosGracia = (int) DB::table('configuraciones_propiedad')
            ->where('propiedad_id', $propiedad->id)
            ->where('clave', 'minutos_gracia_parq_visitantes')
            ->value('valor') ?? 0;

        $valorMinuto = (float) DB::table('configuraciones_propiedad')
            ->where('propiedad_id', $propiedad->id)
            ->where('clave', 'valor_minuto_parq_visitantes')
            ->value('valor') ?? 0;

        // Obtener parqueaderos de visitantes
        $parqueaderos = Parqueadero::where('copropiedad_id', $propiedad->id)
            ->where('tipo', 'visitantes')
            ->where('activo', true)
            ->get();

        if ($parqueaderos->isEmpty()) {
            $this->command->warn('   ⚠ No se encontraron parqueaderos de visitantes');
            return;
        }

        // Obtener unidades y residentes
        $unidades = Unidad::where('propiedad_id', $propiedad->id)->get();
        $residentes = Residente::whereHas('unidad', function($q) use ($propiedad) {
            $q->where('propiedad_id', $propiedad->id);
        })->activos()->get();

        if ($unidades->isEmpty()) {
            $this->command->warn('   ⚠ No se encontraron unidades');
            return;
        }

        // Obtener usuario administrador para registrada_por
        $adminUser = User::where('email', 'demo@domoph.com')->first();
        $registradaPor = $adminUser ? $adminUser->id : 1;

        // Nombres de visitantes para generar datos realistas
        $nombresVisitantes = [
            'Juan Pérez', 'María González', 'Carlos Rodríguez', 'Ana Martínez', 'Luis Fernández',
            'Laura Sánchez', 'Pedro López', 'Carmen Torres', 'Roberto Díaz', 'Sofía Ramírez',
            'Miguel Herrera', 'Isabel Morales', 'Fernando Castro', 'Patricia Vargas', 'Diego Ruiz',
            'Andrea Jiménez', 'Jorge Mendoza', 'Valentina Ortiz', 'Ricardo Silva', 'Natalia Rojas',
            'Andrés Moreno', 'Daniela Vega', 'Sergio Paredes', 'Camila Herrera', 'Felipe Cárdenas',
        ];

        $placas = ['ABC123', 'XYZ789', 'DEF456', 'GHI012', 'JKL345', 'MNO678', 'PQR901', 'STU234', 'VWX567', 'YZA890'];

        $contadorVisitas = 0;
        $contadorLiquidaciones = 0;

        // Generar visitas de los últimos 15 días
        for ($dia = 0; $dia < 15; $dia++) {
            $fechaBase = Carbon::now()->subDays($dia);
            
            // Generar entre 2 y 5 visitas por día
            $numVisitasDia = rand(2, 5);
            
            for ($i = 0; $i < $numVisitasDia; $i++) {
                // Seleccionar parqueadero aleatorio
                $parqueadero = $parqueaderos->random();
                
                // Seleccionar unidad aleatoria
                $unidad = $unidades->random();
                
                // Seleccionar residente aleatorio de la unidad (opcional)
                $residentesUnidad = $residentes->where('unidad_id', $unidad->id);
                $residente = $residentesUnidad->isNotEmpty() ? $residentesUnidad->random() : null;
                
                // Generar fecha de ingreso (durante el día)
                $horaIngreso = $fechaBase->copy()->setTime(rand(8, 20), rand(0, 59), rand(0, 59));
                
                // Generar duración de la visita (entre 15 minutos y 3 horas)
                $duracionMinutos = rand(15, 180);
                $fechaSalida = $horaIngreso->copy()->addMinutes($duracionMinutos);
                
                // Asegurar que la fecha de salida no sea en el futuro
                if ($fechaSalida->isFuture()) {
                    $fechaSalida = Carbon::now()->subMinutes(rand(1, 30));
                    $horaIngreso = $fechaSalida->copy()->subMinutes($duracionMinutos);
                }
                
                // Crear la visita
                $visita = Visita::create([
                    'copropiedad_id' => $propiedad->id,
                    'unidad_id' => $unidad->id,
                    'residente_id' => $residente ? $residente->id : null,
                    'nombre_visitante' => $nombresVisitantes[array_rand($nombresVisitantes)],
                    'documento_visitante' => (string) rand(10000000, 99999999),
                    'tipo_visita' => 'vehicular',
                    'placa_vehiculo' => $placas[array_rand($placas)] . rand(100, 999),
                    'parqueadero_id' => $parqueadero->id,
                    'motivo' => 'Visita familiar',
                    'fecha_ingreso' => $horaIngreso,
                    'fecha_salida' => $fechaSalida,
                    'estado' => 'finalizada',
                    'registrada_por' => $registradaPor,
                    'observaciones' => null,
                    'activo' => false,
                ]);
                
                $contadorVisitas++;

                // Si el cobro está activo, crear liquidación
                if ($cobroActivo && $valorMinuto > 0) {
                    // Calcular minutos totales
                    $minutosTotales = $horaIngreso->diffInMinutes($fechaSalida);
                    
                    // Calcular minutos cobrados (restando minutos de gracia)
                    $minutosCobrados = max(0, $minutosTotales - $minutosGracia);
                    
                    // Calcular valor total
                    $valorTotal = round($minutosCobrados * $valorMinuto, 2);
                    
                    // Seleccionar método de pago aleatorio
                    $metodosPago = ['efectivo', 'billetera_virtual'];
                    $metodoPago = $metodosPago[array_rand($metodosPago)];

                    // Crear liquidación
                    LiquidacionParqueaderoVisitante::create([
                        'visita_id' => $visita->id,
                        'parqueadero_id' => $parqueadero->id,
                        'hora_llegada' => $horaIngreso,
                        'hora_salida' => $fechaSalida,
                        'minutos_totales' => $minutosTotales,
                        'minutos_gracia' => $minutosGracia,
                        'minutos_cobrados' => $minutosCobrados,
                        'valor_minuto' => $valorMinuto,
                        'valor_total' => $valorTotal,
                        'estado' => 'pagado',
                        'fecha_liquidacion' => $fechaSalida->toDateString(),
                        'usuario_liquidador_id' => $registradaPor,
                        'metodo_pago' => $metodoPago,
                        'observaciones' => null,
                        'activo' => false,
                    ]);
                    
                    $contadorLiquidaciones++;
                }
            }
        }

        $this->command->info("   ✓ {$contadorVisitas} visitas creadas");
        if ($cobroActivo) {
            $this->command->info("   ✓ {$contadorLiquidaciones} liquidaciones creadas");
        }
    }
}
