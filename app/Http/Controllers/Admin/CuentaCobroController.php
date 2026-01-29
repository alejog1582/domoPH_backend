<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CuentaCobro;
use App\Models\Recaudo;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        $query = CuentaCobro::with(['unidad', 'detalles'])
            ->where('copropiedad_id', $propiedad->id);

        // Filtro por estado (por defecto: pendiente del mes actual)
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        } else {
            // Por defecto: solo pendientes
            $query->where('estado', 'pendiente');
        }

        // Filtro por periodo (por defecto: mes actual)
        if ($request->filled('periodo')) {
            $query->where('periodo', $request->periodo);
        } else {
            // Por defecto: mes actual
            $query->where('periodo', $mesActual);
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
}
