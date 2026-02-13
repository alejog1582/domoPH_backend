<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CuentaCobro;
use App\Models\Recaudo;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;

class CuentaCobroController extends Controller
{
    /**
     * Mostrar la lista de cuentas de cobro
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $mesActual = Carbon::now()->format('Y-m');
        
        // Query base: cuentas de cobro con sus relaciones
        $query = CuentaCobro::with(['unidad', 'detalles', 'recaudos' => function($q) {
                $q->where('estado', '!=', 'anulado')
                  ->where('activo', true);
            }])
            ->where('copropiedad_id', $propiedad->id);

        // Filtro por periodo
        if ($request->filled('periodo')) {
            $query->where('periodo', $request->periodo);
        }
        // Si NO se especifica período: mostrar todos los períodos (sin filtro de período)
        
        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        } else {
            // Por defecto: pendientes y vencidas de todos los períodos
            $query->whereIn('estado', ['pendiente', 'vencida']);
        }

        // Filtro por unidad
        if ($request->filled('unidad_id')) {
            $query->where('unidad_id', $request->unidad_id);
        }

        // Filtro por búsqueda de unidad (número, torre, bloque)
        if ($request->filled('buscar_unidad')) {
            $buscar = $request->buscar_unidad;
            $query->whereHas('unidad', function($q) use ($buscar) {
                $q->where('numero', 'like', "%{$buscar}%")
                  ->orWhere('torre', 'like', "%{$buscar}%")
                  ->orWhere('bloque', 'like', "%{$buscar}%");
            });
        }

        // Ordenar por fecha de emisión descendente
        $cuentasCobro = $query->orderBy('fecha_emision', 'desc')
            ->orderBy('unidad_id')
            ->paginate(15)
            ->appends($request->query());

        // Calcular resúmenes
        $resumenes = $this->calcularResumenes($propiedad->id, $mesActual);

        // Obtener unidades para el filtro
        $unidades = DB::table('unidades')
            ->where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get(['id', 'numero', 'torre', 'bloque']);

        // Obtener períodos disponibles para el filtro
        $periodos = CuentaCobro::where('copropiedad_id', $propiedad->id)
            ->select('periodo')
            ->distinct()
            ->orderBy('periodo', 'desc')
            ->pluck('periodo');

        return view('admin.cuentas-cobro.index', compact(
            'cuentasCobro', 
            'propiedad', 
            'resumenes', 
            'unidades', 
            'periodos',
            'mesActual'
        ));
    }

    /**
     * Calcular los resúmenes de cuentas de cobro
     *
     * @param int $propiedadId
     * @param string $mesActual
     * @return array
     */
    private function calcularResumenes($propiedadId, $mesActual): array
    {
        // Total de cuentas de cobro del mes actual
        $totalMesActual = CuentaCobro::where('copropiedad_id', $propiedadId)
            ->where('periodo', $mesActual)
            ->sum('valor_total');

        // Total de cuentas de cobro de meses anteriores (pendientes)
        $totalMesesAnteriores = CuentaCobro::where('copropiedad_id', $propiedadId)
            ->where('periodo', '<', $mesActual)
            ->where('estado', 'pendiente')
            ->sum('valor_total');

        // Total de recaudos del mes actual
        $totalRecaudosMes = Recaudo::where('copropiedad_id', $propiedadId)
            ->where('activo', true)
            ->whereYear('fecha_pago', Carbon::now()->year)
            ->whereMonth('fecha_pago', Carbon::now()->month)
            ->where('estado', '!=', 'anulado')
            ->sum('valor_pagado');

        return [
            'total_mes_actual' => $totalMesActual,
            'total_meses_anteriores' => $totalMesesAnteriores,
            'total_recaudos_mes' => $totalRecaudosMes,
        ];
    }

    /**
     * Obtener los detalles de un recaudo
     */
    public function obtenerRecaudo($recaudoId)
    {
        $recaudo = Recaudo::with([
            'cuentaCobro.unidad',
            'unidad',
            'registradoPor',
            'detalles.cuentaCobroDetalle'
        ])
        ->where('id', $recaudoId)
        ->where('activo', true)
        ->first();

        if (!$recaudo) {
            return response()->json(['error' => 'Recaudo no encontrado'], 404);
        }

        return response()->json([
            'recaudo' => $recaudo,
            'formatted' => [
                'numero_recaudo' => $recaudo->numero_recaudo,
                'fecha_pago' => $recaudo->fecha_pago ? Carbon::parse($recaudo->fecha_pago)->format('d/m/Y') : '-',
                'valor_pagado' => number_format($recaudo->valor_pagado, 2, ',', '.'),
                'tipo_pago' => ucfirst($recaudo->tipo_pago),
                'medio_pago' => ucfirst($recaudo->medio_pago),
                'estado' => ucfirst($recaudo->estado),
                'unidad' => $recaudo->unidad ? $recaudo->unidad->numero . ($recaudo->unidad->torre ? ' - Torre ' . $recaudo->unidad->torre : '') : 'N/A',
                'registrado_por' => $recaudo->registradoPor ? $recaudo->registradoPor->nombre : 'N/A',
            ]
        ]);
    }

    /**
     * Generar y descargar PDF de cuenta de cobro
     */
    public function descargarPdf($id)
    {
        $cuentaCobro = CuentaCobro::with([
            'copropiedad',
            'unidad',
            'detalles',
            'recaudos' => function($q) {
                $q->where('estado', '!=', 'anulado')
                  ->where('activo', true);
            }
        ])->findOrFail($id);

        $propiedad = $cuentaCobro->copropiedad;
        $unidad = $cuentaCobro->unidad;
        
        // Obtener residente principal
        $residentePrincipal = \App\Models\Residente::where('unidad_id', $unidad->id)
            ->where('es_principal', true)
            ->with('user')
            ->first();
        $usuario = $residentePrincipal && $residentePrincipal->user ? $residentePrincipal->user : null;

        // Calcular saldo anterior (del mes anterior)
        $periodoActual = Carbon::createFromFormat('Y-m', $cuentaCobro->periodo);
        $periodoAnterior = $periodoActual->copy()->subMonth();
        
        $cuentaAnterior = CuentaCobro::where('copropiedad_id', $cuentaCobro->copropiedad_id)
            ->where('unidad_id', $cuentaCobro->unidad_id)
            ->where('periodo', $periodoAnterior->format('Y-m'))
            ->first();
        
        $saldoAnterior = 0;
        if ($cuentaAnterior) {
            $totalRecaudadoAnterior = Recaudo::where('cuenta_cobro_id', $cuentaAnterior->id)
                ->where('estado', '!=', 'anulado')
                ->where('activo', true)
                ->sum('valor_pagado');
            $saldoAnterior = max(0, $cuentaAnterior->valor_total - $totalRecaudadoAnterior);
        }

        // Calcular valores por concepto
        $conceptos = [];
        $totalCuotasMes = 0;
        $totalInteresesMes = 0;
        $totalHonorariosMes = 0;

        foreach ($cuentaCobro->detalles as $detalle) {
            $concepto = strtolower($detalle->concepto);
            
            if (strpos($concepto, 'cuota') !== false || strpos($concepto, 'administracion') !== false || strpos($concepto, 'copropietario') !== false) {
                $totalCuotasMes += $detalle->valor;
            } elseif (strpos($concepto, 'interes') !== false || strpos($concepto, 'mora') !== false) {
                $totalInteresesMes += $detalle->valor;
            } elseif (strpos($concepto, 'honorario') !== false || strpos($concepto, 'abogado') !== false) {
                $totalHonorariosMes += $detalle->valor;
            }
        }

        // Agregar conceptos a la tabla
        // El saldo anterior se aplica a las cuotas de administración
        $saldoAnteriorCuotas = $saldoAnterior;
        if ($saldoAnteriorCuotas > 0 || $totalCuotasMes > 0) {
            $conceptos[] = [
                'concepto' => 'Cuotas Admon Copropietarios',
                'saldo_anterior' => $saldoAnteriorCuotas,
                'cuota_mes' => $totalCuotasMes,
                'nuevo_saldo' => $saldoAnteriorCuotas + $totalCuotasMes
            ];
        }
        
        if ($totalInteresesMes > 0) {
            $conceptos[] = [
                'concepto' => 'Intereses De Mora',
                'saldo_anterior' => 0,
                'cuota_mes' => $totalInteresesMes,
                'nuevo_saldo' => $totalInteresesMes
            ];
        }
        
        if ($totalHonorariosMes > 0) {
            $conceptos[] = [
                'concepto' => 'Honorarios Abogado',
                'saldo_anterior' => 0,
                'cuota_mes' => 0,
                'nuevo_saldo' => $totalHonorariosMes
            ];
        }

        // Calcular totales
        $totalSinDescuento = $cuentaCobro->valor_total;
        $descuento = $cuentaCobro->valor_descuentos;
        $totalConDescuento = $totalSinDescuento - $descuento;

        // Calcular porcentaje de descuento
        $porcentajeDescuento = $totalSinDescuento > 0 ? ($descuento / $totalSinDescuento) * 100 : 0;
        
        // Fecha límite para descuento (15 días después de la emisión)
        $fechaLimiteDescuento = $cuentaCobro->fecha_emision->copy()->addDays(15);

        // Convertir colores hex a RGB para usar en el PDF
        $primaryColor = $propiedad->color_primario ?? '#3b82f6';
        $secondaryColor = $propiedad->color_secundario ?? '#10b981';
        
        // Función para convertir hex a RGB
        $hexToRgb = function($hex) {
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) == 6) {
                return [
                    hexdec(substr($hex, 0, 2)),
                    hexdec(substr($hex, 2, 2)),
                    hexdec(substr($hex, 4, 2))
                ];
            }
            return [59, 130, 246]; // default blue
        };
        
        $primaryRgb = $hexToRgb($primaryColor);
        $secondaryRgb = $hexToRgb($secondaryColor);
        
        // Obtener comentario de cuenta de cobro desde configuraciones
        $comentarioCuentaCobro = DB::table('configuraciones_propiedad')
            ->where('propiedad_id', $propiedad->id)
            ->where('clave', 'comentarios_cuentas_cobro')
            ->value('valor');
        
        // Si no hay comentario configurado, usar el mensaje por defecto
        if (empty($comentarioCuentaCobro)) {
            $comentarioCuentaCobro = '<p><strong>¡Gracias por su pago y por contribuir al buen funcionamiento de la copropiedad!</strong></p>';
        }
        
        // Preparar datos para la vista
        $data = [
            'cuentaCobro' => $cuentaCobro,
            'propiedad' => $propiedad,
            'unidad' => $unidad,
            'usuario' => $usuario,
            'conceptos' => $conceptos,
            'saldoAnterior' => $saldoAnterior,
            'totalSinDescuento' => $totalSinDescuento,
            'descuento' => $descuento,
            'totalConDescuento' => $totalConDescuento,
            'porcentajeDescuento' => $porcentajeDescuento,
            'fechaLimiteDescuento' => $fechaLimiteDescuento,
            'periodoActual' => $periodoActual,
            'periodoAnterior' => $periodoAnterior,
            'totalCuotasMes' => $totalCuotasMes,
            'totalInteresesMes' => $totalInteresesMes,
            'primaryColor' => $primaryColor,
            'secondaryColor' => $secondaryColor,
            'primaryRgb' => $primaryRgb,
            'secondaryRgb' => $secondaryRgb,
            'comentarioCuentaCobro' => $comentarioCuentaCobro,
        ];

        // Generar HTML
        $html = view('admin.cuentas-cobro.pdf', $data)->render();

        // Configurar opciones de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', public_path());

        // Crear instancia de Dompdf
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->set_option('defaultMediaType', 'print');
        $dompdf->set_option('isPhpEnabled', true);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->render();

        // Generar nombre del archivo
        $nombreArchivo = 'cuenta_cobro_' . $unidad->numero . '_' . $cuentaCobro->periodo . '.pdf';

        // Descargar PDF
        return response()->streamDownload(function() use ($dompdf) {
            echo $dompdf->output();
        }, $nombreArchivo, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
