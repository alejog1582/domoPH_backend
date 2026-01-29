<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recaudo;
use App\Models\Unidad;
use App\Models\CuentaCobro;
use App\Models\Cartera;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class RecaudoController extends Controller
{
    /**
     * Mostrar la lista de recaudos
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $mesActual = Carbon::now()->format('Y-m');
        
        // Query base: recaudos con sus relaciones
        $query = Recaudo::with(['unidad', 'cuentaCobro'])
            ->where('copropiedad_id', $propiedad->id)
            ->where('activo', true);

        // Filtro por fecha (por defecto: mes actual)
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_pago', '>=', $request->fecha_desde);
        } else {
            // Por defecto: mes actual
            $query->whereYear('fecha_pago', Carbon::now()->year)
                  ->whereMonth('fecha_pago', Carbon::now()->month);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_pago', '<=', $request->fecha_hasta);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por unidad
        if ($request->filled('unidad_id')) {
            $query->where('unidad_id', $request->unidad_id);
        }

        // Filtro por búsqueda de unidad
        if ($request->filled('buscar_unidad')) {
            $buscar = $request->buscar_unidad;
            $query->whereHas('unidad', function($q) use ($buscar) {
                $q->where('numero', 'like', "%{$buscar}%")
                  ->orWhere('torre', 'like', "%{$buscar}%")
                  ->orWhere('bloque', 'like', "%{$buscar}%");
            });
        }

        // Filtro por medio de pago
        if ($request->filled('medio_pago')) {
            $query->where('medio_pago', $request->medio_pago);
        }

        // Ordenar por fecha de pago descendente
        $recaudos = $query->orderBy('fecha_pago', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Obtener unidades para el filtro
        $unidades = DB::table('unidades')
            ->where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get(['id', 'numero', 'torre', 'bloque']);

        return view('admin.recaudos.index', compact(
            'recaudos', 
            'propiedad', 
            'unidades',
            'mesActual'
        ));
    }

    /**
     * Mostrar la vista de carga de recaudos con instrucciones
     */
    public function showCargarRecaudos()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        return view('admin.recaudos.cargar-recaudos', compact('propiedad'));
    }

    /**
     * Descargar plantilla para carga de recaudos
     */
    public function downloadTemplate()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $headers = [
            'numero_unidad',
            'torre_unidad',
            'bloque_unidad',
            'numero_recaudo',
            'fecha_pago',
            'tipo_pago',
            'medio_pago',
            'referencia_pago',
            'descripcion',
            'valor_pagado'
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Estilo para encabezados
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle('A1:J1')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Ajustar ancho de columnas
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Ejemplo de datos
        $sheet->setCellValue('A2', '101');
        $sheet->setCellValue('B2', '1');
        $sheet->setCellValue('C2', 'A');
        $sheet->setCellValue('D2', 'REC-001');
        $sheet->setCellValue('E2', Carbon::now()->format('Y-m-d H:i:s'));
        $sheet->setCellValue('F2', 'parcial');
        $sheet->setCellValue('G2', 'transferencia');
        $sheet->setCellValue('H2', 'TRF-123456');
        $sheet->setCellValue('I2', 'Pago parcial de administración');
        $sheet->setCellValue('J2', '500000');

        $sheet->getStyle('A2:J2')->getFont()->getColor()->setARGB('FF808080');

        $filename = 'plantilla_recaudos_' . date('Y-m-d') . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $response = response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename);

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        
        return $response;
    }

    /**
     * Procesar la importación de recaudos
     */
    public function importRecaudos(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls,csv|max:10240',
        ], [
            'archivo.required' => 'Debe seleccionar un archivo.',
            'archivo.mimes' => 'El archivo debe ser Excel (.xlsx, .xls) o CSV.',
            'archivo.max' => 'El archivo no debe superar los 10MB.',
        ]);

        try {
            $file = $request->file('archivo');
            $extension = $file->getClientOriginalExtension();
            
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows) || count($rows) < 2) {
                return back()->with('error', 'El archivo está vacío o no tiene datos.');
            }

            // Obtener encabezados (primera fila)
            $headers = array_map('strtolower', array_map('trim', $rows[0]));
            
            // Mapear encabezados
            $headerMap = [
                'numero_unidad' => ['numero_unidad', 'numero', 'número', 'num', 'nro'],
                'torre_unidad' => ['torre_unidad', 'torre'],
                'bloque_unidad' => ['bloque_unidad', 'bloque', 'block'],
                'numero_recaudo' => ['numero_recaudo', 'numero recaudo', 'nro recaudo', 'consecutivo'],
                'fecha_pago' => ['fecha_pago', 'fecha', 'fecha pago'],
                'tipo_pago' => ['tipo_pago', 'tipo', 'tipo pago'],
                'medio_pago' => ['medio_pago', 'medio', 'medio pago'],
                'referencia_pago' => ['referencia_pago', 'referencia', 'referencia pago', 'ref'],
                'descripcion' => ['descripcion', 'descripción', 'observaciones'],
                'valor_pagado' => ['valor_pagado', 'valor', 'valor pagado', 'monto'],
            ];

            $columnIndexes = [];
            foreach ($headerMap as $field => $possibleHeaders) {
                foreach ($possibleHeaders as $header) {
                    $index = array_search(strtolower($header), $headers);
                    if ($index !== false) {
                        $columnIndexes[$field] = $index;
                        break;
                    }
                }
            }

            // Validar columnas obligatorias
            $requiredFields = ['numero_unidad', 'numero_recaudo', 'fecha_pago', 'valor_pagado'];
            foreach ($requiredFields as $field) {
                if (!isset($columnIndexes[$field])) {
                    return back()->with('error', "No se encontró la columna requerida: {$field}");
                }
            }

            DB::beginTransaction();

            $procesados = 0;
            $errores = [];

            // Procesar cada fila (empezar desde la fila 2, índice 1)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Saltar filas vacías
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Obtener datos de la fila
                    $numeroUnidad = trim($row[$columnIndexes['numero_unidad']] ?? '');
                    $torreUnidad = isset($columnIndexes['torre_unidad']) ? trim($row[$columnIndexes['torre_unidad']] ?? '') : null;
                    $bloqueUnidad = isset($columnIndexes['bloque_unidad']) ? trim($row[$columnIndexes['bloque_unidad']] ?? '') : null;
                    $numeroRecaudo = trim($row[$columnIndexes['numero_recaudo']] ?? '');
                    $fechaPago = trim($row[$columnIndexes['fecha_pago']] ?? '');
                    $tipoPago = isset($columnIndexes['tipo_pago']) ? trim($row[$columnIndexes['tipo_pago']] ?? 'parcial') : 'parcial';
                    $medioPago = isset($columnIndexes['medio_pago']) ? trim($row[$columnIndexes['medio_pago']] ?? 'efectivo') : 'efectivo';
                    $referenciaPago = isset($columnIndexes['referencia_pago']) ? trim($row[$columnIndexes['referencia_pago']] ?? '') : null;
                    $descripcion = isset($columnIndexes['descripcion']) ? trim($row[$columnIndexes['descripcion']] ?? '') : null;
                    $valorPagado = isset($columnIndexes['valor_pagado']) ? (float) str_replace(',', '', $row[$columnIndexes['valor_pagado']] ?? 0) : 0;

                    // Validaciones básicas
                    if (empty($numeroUnidad)) {
                        $errores[] = "Fila " . ($i + 1) . ": El número de unidad está vacío.";
                        continue;
                    }

                    if (empty($numeroRecaudo)) {
                        $errores[] = "Fila " . ($i + 1) . ": El número de recaudo está vacío.";
                        continue;
                    }

                    if (empty($fechaPago)) {
                        $errores[] = "Fila " . ($i + 1) . ": La fecha de pago está vacía.";
                        continue;
                    }

                    if ($valorPagado <= 0) {
                        $errores[] = "Fila " . ($i + 1) . ": El valor pagado debe ser mayor a cero.";
                        continue;
                    }

                    // Verificar si el número de recaudo ya existe
                    $recaudoExistente = Recaudo::where('numero_recaudo', $numeroRecaudo)->first();
                    if ($recaudoExistente) {
                        $errores[] = "Fila " . ($i + 1) . ": El número de recaudo '{$numeroRecaudo}' ya existe.";
                        continue;
                    }

                    // Buscar unidad
                    $queryUnidad = Unidad::where('propiedad_id', $propiedad->id)
                        ->where('numero', $numeroUnidad);

                    if ($torreUnidad !== null && $torreUnidad !== '') {
                        $queryUnidad->where('torre', $torreUnidad);
                    } else {
                        $queryUnidad->whereNull('torre');
                    }

                    if ($bloqueUnidad !== null && $bloqueUnidad !== '') {
                        $queryUnidad->where('bloque', $bloqueUnidad);
                    } else {
                        $queryUnidad->whereNull('bloque');
                    }

                    $unidad = $queryUnidad->first();

                    if (!$unidad) {
                        $errores[] = "Fila " . ($i + 1) . ": No se encontró la unidad {$numeroUnidad}" . 
                            ($torreUnidad ? " - Torre {$torreUnidad}" : "") . 
                            ($bloqueUnidad ? " - Bloque {$bloqueUnidad}" : "");
                        continue;
                    }

                    // Convertir fecha de pago
                    try {
                        $fechaPagoObj = Carbon::parse($fechaPago);
                    } catch (\Exception $e) {
                        $errores[] = "Fila " . ($i + 1) . ": La fecha de pago '{$fechaPago}' no es válida.";
                        continue;
                    }

                    // Validar tipo de pago
                    $tiposPagoValidos = ['parcial', 'total', 'anticipo'];
                    if (!in_array(strtolower($tipoPago), $tiposPagoValidos)) {
                        $tipoPago = 'parcial';
                    }

                    // Validar medio de pago
                    $mediosPagoValidos = ['efectivo', 'transferencia', 'consignacion', 'tarjeta', 'pse', 'otro'];
                    if (!in_array(strtolower($medioPago), $mediosPagoValidos)) {
                        $medioPago = 'efectivo';
                    }

                    // Aplicar el pago a las cuentas de cobro pendientes
                    $saldoRestante = $valorPagado;
                    $contadorAplicacion = 0;

                    // Obtener cuentas de cobro pendientes de la unidad, ordenadas de más vieja a más reciente
                    $cuentasPendientes = CuentaCobro::where('copropiedad_id', $propiedad->id)
                        ->where('unidad_id', $unidad->id)
                        ->where('estado', 'pendiente')
                        ->orderBy('periodo', 'asc')
                        ->orderBy('fecha_emision', 'asc')
                        ->get();

                    if ($cuentasPendientes->isEmpty()) {
                        // Si no hay cuentas pendientes, crear recaudo sin cuenta de cobro (abono general)
                        // Generar número único si ya existe
                        $numeroRecaudoFinal = $numeroRecaudo;
                        $contador = 1;
                        while (Recaudo::where('numero_recaudo', $numeroRecaudoFinal)->exists()) {
                            $numeroRecaudoFinal = $numeroRecaudo . '-ABONO-' . $contador;
                            $contador++;
                        }

                        Recaudo::create([
                            'copropiedad_id' => $propiedad->id,
                            'unidad_id' => $unidad->id,
                            'cuenta_cobro_id' => null,
                            'numero_recaudo' => $numeroRecaudoFinal,
                            'fecha_pago' => $fechaPagoObj,
                            'tipo_pago' => strtolower($tipoPago),
                            'medio_pago' => strtolower($medioPago),
                            'referencia_pago' => $referenciaPago,
                            'descripcion' => $descripcion,
                            'valor_pagado' => $valorPagado,
                            'estado' => 'aplicado',
                            'registrado_por' => Auth::id(),
                            'activo' => true,
                        ]);

                        $procesados++;
                        continue;
                    }

                    // Aplicar el pago a cada cuenta de cobro pendiente
                    foreach ($cuentasPendientes as $cuentaCobro) {
                        if ($saldoRestante <= 0) {
                            break;
                        }

                        $saldoPendiente = $cuentaCobro->calcularSaldoPendiente();
                        
                        if ($saldoPendiente <= 0) {
                            continue; // Esta cuenta ya está pagada
                        }

                        // Calcular cuánto aplicar a esta cuenta
                        $valorAplicar = min($saldoRestante, $saldoPendiente);

                        // Generar número de recaudo único
                        $numeroRecaudoFinal = $contadorAplicacion == 0 ? $numeroRecaudo : $numeroRecaudo . '-' . $contadorAplicacion;
                        $contador = 1;
                        while (Recaudo::where('numero_recaudo', $numeroRecaudoFinal)->exists()) {
                            $numeroRecaudoFinal = $numeroRecaudo . '-' . $contadorAplicacion . '-' . $contador;
                            $contador++;
                        }

                        // Crear recaudo para esta aplicación
                        Recaudo::create([
                            'copropiedad_id' => $propiedad->id,
                            'unidad_id' => $unidad->id,
                            'cuenta_cobro_id' => $cuentaCobro->id,
                            'numero_recaudo' => $numeroRecaudoFinal,
                            'fecha_pago' => $fechaPagoObj,
                            'tipo_pago' => strtolower($tipoPago),
                            'medio_pago' => strtolower($medioPago),
                            'referencia_pago' => $referenciaPago,
                            'descripcion' => $descripcion . ($contadorAplicacion > 0 ? ' - Aplicado a cuenta ' . $cuentaCobro->periodo : ''),
                            'valor_pagado' => $valorAplicar,
                            'estado' => 'aplicado',
                            'registrado_por' => Auth::id(),
                            'activo' => true,
                        ]);

                        $contadorAplicacion++;

                        // Verificar si la cuenta queda en cero
                        $nuevoSaldo = $cuentaCobro->calcularSaldoPendiente();
                        if ($nuevoSaldo <= 0) {
                            $cuentaCobro->update(['estado' => 'pagada']);
                        }

                        $saldoRestante -= $valorAplicar;
                    }

                    // Si queda saldo restante, crear recaudo sin cuenta de cobro (abono general)
                    if ($saldoRestante > 0) {
                        $numeroRecaudoFinal = $numeroRecaudo . '-ABONO';
                        $contador = 1;
                        while (Recaudo::where('numero_recaudo', $numeroRecaudoFinal)->exists()) {
                            $numeroRecaudoFinal = $numeroRecaudo . '-ABONO-' . $contador;
                            $contador++;
                        }

                        Recaudo::create([
                            'copropiedad_id' => $propiedad->id,
                            'unidad_id' => $unidad->id,
                            'cuenta_cobro_id' => null,
                            'numero_recaudo' => $numeroRecaudoFinal,
                            'fecha_pago' => $fechaPagoObj,
                            'tipo_pago' => 'anticipo',
                            'medio_pago' => strtolower($medioPago),
                            'referencia_pago' => $referenciaPago,
                            'descripcion' => $descripcion . ' - Abono general (sin cuenta específica)',
                            'valor_pagado' => $saldoRestante,
                            'estado' => 'aplicado',
                            'registrado_por' => Auth::id(),
                            'activo' => true,
                        ]);
                    }

                    // Actualizar saldo de cartera
                    $this->actualizarCartera($propiedad->id, $unidad->id);

                    $procesados++;

                } catch (\Exception $e) {
                    $errores[] = "Fila " . ($i + 1) . ": Error - " . $e->getMessage();
                    \Log::error('Error al procesar recaudo en fila ' . ($i + 1) . ': ' . $e->getMessage());
                }
            }

            if (!empty($errores)) {
                DB::rollBack();
                return back()->with('error', 'Se encontraron errores al procesar el archivo.')
                    ->with('errores', $errores);
            }

            DB::commit();

            return redirect()->route('admin.recaudos.index')
                ->with('success', "Se procesaron correctamente {$procesados} recaudo(s).");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al importar recaudos: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar el saldo de cartera para una unidad
     */
    private function actualizarCartera($propiedadId, $unidadId)
    {
        // Calcular saldos de cuentas de cobro
        $cuentasPendientes = CuentaCobro::where('copropiedad_id', $propiedadId)
            ->where('unidad_id', $unidadId)
            ->where('estado', 'pendiente')
            ->get();

        $saldoTotal = 0;
        $saldoMora = 0;
        $saldoCorriente = 0;
        $mesActual = Carbon::now()->format('Y-m');

        foreach ($cuentasPendientes as $cuenta) {
            $saldoPendiente = $cuenta->calcularSaldoPendiente();
            $saldoTotal += $saldoPendiente;

            if ($cuenta->periodo < $mesActual) {
                $saldoMora += $saldoPendiente;
            } else {
                $saldoCorriente += $saldoPendiente;
            }
        }

        // Actualizar o crear registro de cartera
        Cartera::updateOrCreate(
            [
                'copropiedad_id' => $propiedadId,
                'unidad_id' => $unidadId,
            ],
            [
                'saldo_total' => $saldoTotal,
                'saldo_mora' => $saldoMora,
                'saldo_corriente' => $saldoCorriente,
                'ultima_actualizacion' => Carbon::now(),
                'activo' => true,
            ]
        );
    }
}
