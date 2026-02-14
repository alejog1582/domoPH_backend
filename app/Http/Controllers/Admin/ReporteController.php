<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use App\Models\Visita;
use App\Models\LiquidacionParqueaderoVisitante;
use App\Models\Parqueadero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteController extends Controller
{
    /**
     * Reporte de parqueaderos visitantes
     */
    public function parqueaderosVisitantes(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener fechas del filtro (por defecto últimos 30 días)
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subDays(30)->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

        // Verificar si el cobro está activo
        $cobroActivo = DB::table('configuraciones_propiedad')
            ->where('propiedad_id', $propiedad->id)
            ->where('clave', 'cobro_parq_visitantes')
            ->value('valor') === 'true';

        // Obtener parqueaderos de visitantes
        $parqueaderos = Parqueadero::where('copropiedad_id', $propiedad->id)
            ->where('tipo', 'visitantes')
            ->where('activo', true)
            ->get();

        // Obtener visitas en el rango de fechas
        $visitas = Visita::where('copropiedad_id', $propiedad->id)
            ->where('tipo_visita', 'vehicular')
            ->whereNotNull('parqueadero_id')
            ->whereDate('fecha_ingreso', '>=', $fechaInicio)
            ->whereDate('fecha_ingreso', '<=', $fechaFin)
            ->with(['parqueadero', 'unidad'])
            ->get();

        // Estadísticas generales
        $totalVisitas = $visitas->count();
        $visitasFinalizadas = $visitas->where('estado', 'finalizada')->count();
        $visitasActivas = $visitas->where('estado', 'activa')->count();

        // Estadísticas por parqueadero
        $estadisticasPorParqueadero = [];
        foreach ($parqueaderos as $parqueadero) {
            $visitasParqueadero = $visitas->where('parqueadero_id', $parqueadero->id);
            $estadisticasPorParqueadero[$parqueadero->id] = [
                'parqueadero' => $parqueadero,
                'total_visitas' => $visitasParqueadero->count(),
                'visitas_finalizadas' => $visitasParqueadero->where('estado', 'finalizada')->count(),
                'visitas_activas' => $visitasParqueadero->where('estado', 'activa')->count(),
                'horas_ocupacion' => $visitasParqueadero->where('estado', 'finalizada')
                    ->reduce(function($total, $visita) {
                        if ($visita->fecha_ingreso && $visita->fecha_salida) {
                            $horaIngreso = Carbon::parse($visita->fecha_ingreso);
                            $horaSalida = Carbon::parse($visita->fecha_salida);
                            return $total + $horaIngreso->diffInHours($horaSalida);
                        }
                        return $total;
                    }, 0),
            ];
        }

        // Estadísticas por día (para gráficos)
        $estadisticasPorDia = [];
        $fechaActual = Carbon::parse($fechaInicio);
        $fechaFinCarbon = Carbon::parse($fechaFin);
        
        while ($fechaActual <= $fechaFinCarbon) {
            $fechaStr = $fechaActual->format('Y-m-d');
            $visitasDia = $visitas->filter(function($visita) use ($fechaStr) {
                return $visita->fecha_ingreso && $visita->fecha_ingreso->format('Y-m-d') === $fechaStr;
            });
            
            $estadisticasPorDia[] = [
                'fecha' => $fechaActual->format('d/m/Y'),
                'fecha_str' => $fechaStr,
                'total_visitas' => $visitasDia->count(),
                'visitas_finalizadas' => $visitasDia->where('estado', 'finalizada')->count(),
            ];
            
            $fechaActual->addDay();
        }

        // Estadísticas de recaudo (si el cobro está activo)
        $recaudoTotal = 0;
        $recaudoPorDia = [];
        $recaudoPorParqueadero = [];
        
        if ($cobroActivo) {
            $liquidaciones = LiquidacionParqueaderoVisitante::whereHas('visita', function($q) use ($propiedad, $fechaInicio, $fechaFin) {
                $q->where('copropiedad_id', $propiedad->id)
                  ->whereDate('fecha_ingreso', '>=', $fechaInicio)
                  ->whereDate('fecha_ingreso', '<=', $fechaFin);
            })
            ->where('estado', 'pagado')
            ->where('activo', false)
            ->with('visita')
            ->get();

            $recaudoTotal = $liquidaciones->sum('valor_total');

            // Recaudo por día
            $fechaActual = Carbon::parse($fechaInicio);
            while ($fechaActual <= $fechaFinCarbon) {
                $fechaStr = $fechaActual->format('Y-m-d');
                $liquidacionesDia = $liquidaciones->filter(function($liquidacion) use ($fechaStr) {
                    // Usar fecha_liquidacion si existe, sino usar fecha_ingreso de la visita
                    if ($liquidacion->fecha_liquidacion) {
                        return Carbon::parse($liquidacion->fecha_liquidacion)->format('Y-m-d') === $fechaStr;
                    } elseif ($liquidacion->visita && $liquidacion->visita->fecha_ingreso) {
                        return Carbon::parse($liquidacion->visita->fecha_ingreso)->format('Y-m-d') === $fechaStr;
                    }
                    return false;
                });
                
                $recaudoPorDia[] = [
                    'fecha' => $fechaActual->format('d/m/Y'),
                    'fecha_str' => $fechaStr,
                    'recaudo' => $liquidacionesDia->sum('valor_total'),
                ];
                
                $fechaActual->addDay();
            }

            // Recaudo por parqueadero
            foreach ($parqueaderos as $parqueadero) {
                $liquidacionesParqueadero = $liquidaciones->where('parqueadero_id', $parqueadero->id);
                $recaudoPorParqueadero[$parqueadero->id] = [
                    'parqueadero' => $parqueadero,
                    'recaudo' => $liquidacionesParqueadero->sum('valor_total'),
                    'total_liquidaciones' => $liquidacionesParqueadero->count(),
                ];
            }
        }

        return view('admin.reportes.parqueaderos-visitantes', compact(
            'propiedad',
            'fechaInicio',
            'fechaFin',
            'cobroActivo',
            'parqueaderos',
            'totalVisitas',
            'visitasFinalizadas',
            'visitasActivas',
            'estadisticasPorParqueadero',
            'estadisticasPorDia',
            'recaudoTotal',
            'recaudoPorDia',
            'recaudoPorParqueadero'
        ));
    }
}
