<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cartera;
use App\Models\Unidad;
use App\Models\CuentaCobro;
use App\Models\CuentaCobroDetalle;
use App\Helpers\AdminHelper;
use App\Services\PlantillaCarteraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class CarteraController extends Controller
{
    /**
     * Mostrar la lista de carteras de unidades
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Dashboard: Estadísticas de mora
        $unidadesEnMora = Cartera::where('copropiedad_id', $propiedad->id)
            ->where('activo', true)
            ->where('saldo_mora', '>', 0)
            ->count();

        $valorMoraTotal = Cartera::where('copropiedad_id', $propiedad->id)
            ->where('activo', true)
            ->sum('saldo_mora');

        // Query base: carteras con sus unidades
        $query = Cartera::with(['unidad'])
            ->where('carteras.copropiedad_id', $propiedad->id)
            ->where('carteras.activo', true);

        // Filtro por unidad (búsqueda en número, torre, bloque)
        if ($request->filled('buscar_unidad')) {
            $buscar = $request->buscar_unidad;
            $query->whereHas('unidad', function($q) use ($buscar) {
                $q->where('numero', 'like', "%{$buscar}%")
                  ->orWhere('torre', 'like', "%{$buscar}%")
                  ->orWhere('bloque', 'like', "%{$buscar}%");
            });
        }

        // Filtro por estado de la unidad
        if ($request->filled('estado_unidad')) {
            $query->whereHas('unidad', function($q) use ($request) {
                $q->where('estado', $request->estado_unidad);
            });
        }

        // Filtro por unidades en mora
        if ($request->filled('en_mora') && $request->en_mora == '1') {
            $query->where('saldo_mora', '>', 0);
        }

        $carteras = $query->orderBy('saldo_mora', 'desc')
            ->orderBy('saldo_total', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Obtener estados únicos de unidades para el filtro
        $estadosUnidades = Unidad::where('propiedad_id', $propiedad->id)
            ->whereNotNull('estado')
            ->distinct()
            ->pluck('estado')
            ->filter()
            ->values();

        return view('admin.cartera.index', compact(
            'carteras',
            'propiedad',
            'unidadesEnMora',
            'valorMoraTotal',
            'estadosUnidades'
        ));
    }

    /**
     * Mostrar la vista de carga de saldos iniciales
     */
    public function showCargarSaldos()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $totalUnidades = Unidad::where('propiedad_id', $propiedad->id)->count();

        return view('admin.cartera.cargar-saldos', compact('propiedad', 'totalUnidades'));
    }

    /**
     * Descargar plantilla de saldos iniciales
     */
    public function downloadTemplate()
    {
        $rutaArchivo = public_path('plantillas/plantilla_cartera_saldos.xlsx');
        
        // Crear el archivo si no existe
        if (!file_exists($rutaArchivo)) {
            $creado = PlantillaCarteraService::crearPlantilla($rutaArchivo);
            
            if (!$creado) {
                return back()->with('error', 'No se pudo crear la plantilla. Por favor, contacte al administrador.');
            }
        } else {
            // Regenerar la plantilla para asegurar que tenga todas las unidades actuales
            PlantillaCarteraService::crearPlantilla($rutaArchivo);
        }
        
        // Verificar que el archivo existe
        if (!file_exists($rutaArchivo)) {
            return back()->with('error', 'La plantilla no está disponible.');
        }
        
        // Descargar el archivo
        return response()->download($rutaArchivo, 'plantilla_cartera_saldos.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Procesar la carga masiva de saldos iniciales
     */
    public function importSaldos(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB máximo
        ], [
            'archivo.required' => 'El campo archivo es obligatorio.',
            'archivo.mimes' => 'El archivo debe ser de tipo: xlsx, xls o csv.',
            'archivo.max' => 'El archivo no debe ser mayor a 10MB.',
        ]);

        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return back()->with('error', 'No hay propiedad asignada.');
        }

        try {
            // Obtener total de unidades para validación
            $totalUnidades = Unidad::where('propiedad_id', $propiedad->id)->count();

            $file = $request->file('archivo');
            $extension = $file->getClientOriginalExtension();
            
            // Leer el archivo (Excel o CSV)
            $rows = [];
            $headers = [];
            
            if (in_array($extension, ['xlsx', 'xls'])) {
                // Leer archivo Excel usando PhpSpreadsheet
                $spreadsheet = IOFactory::load($file->getRealPath());
                $worksheet = $spreadsheet->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                
                // Leer encabezados (primera fila)
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $cellValue = $worksheet->getCell($columnLetter . '1')->getValue();
                    $headers[] = $cellValue ? trim($cellValue) : '';
                }
                
                // Leer datos (desde la fila 2)
                for ($row = 2; $row <= $highestRow; $row++) {
                    $rowData = [];
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $cellValue = $worksheet->getCell($columnLetter . $row)->getValue();
                        $rowData[] = $cellValue ? trim($cellValue) : '';
                    }
                    $rows[] = $rowData;
                }
            } else {
                // Leer CSV
                $handle = fopen($file->getRealPath(), 'r');
                $headers = fgetcsv($handle); // Primera fila son los encabezados
                if ($headers) {
                    $headers = array_map('trim', $headers);
                }
                while (($row = fgetcsv($handle)) !== false) {
                    $rows[] = array_map('trim', $row);
                }
                fclose($handle);
            }
            
            if (empty($rows)) {
                return back()->with('error', 'El archivo está vacío o no tiene el formato correcto.');
            }

            // Validar que el número de registros coincida con el total de unidades
            if (count($rows) != $totalUnidades) {
                return back()->with('error', "El archivo debe contener exactamente {$totalUnidades} registros (una por cada unidad). El archivo contiene " . count($rows) . " registros.");
            }

            // Mapear encabezados a campos de la base de datos
            $headerMap = [
                'numero' => ['numero', 'número', 'num', 'nro'],
                'torre' => ['torre'],
                'bloque' => ['bloque', 'block'],
                'saldo_corriente' => ['saldo_corriente', 'saldo corriente', 'corriente'],
                'saldo_mora' => ['saldo_mora', 'saldo mora', 'mora'],
                'saldo_total' => ['saldo_total', 'saldo total', 'total'],
            ];

            // Encontrar índices de columnas
            $columnIndexes = [];
            foreach ($headerMap as $field => $possibleHeaders) {
                foreach ($possibleHeaders as $header) {
                    $index = array_search(strtolower($header), array_map('strtolower', $headers));
                    if ($index !== false) {
                        $columnIndexes[$field] = $index;
                        break;
                    }
                }
            }

            // Validar que existan las columnas necesarias
            if (!isset($columnIndexes['numero'])) {
                return back()->with('error', 'No se encontró la columna "numero" en el archivo. Esta columna es obligatoria.');
            }

            if (!isset($columnIndexes['saldo_corriente']) || !isset($columnIndexes['saldo_mora']) || !isset($columnIndexes['saldo_total'])) {
                return back()->with('error', 'El archivo debe contener las columnas: saldo_corriente, saldo_mora y saldo_total.');
            }

            DB::beginTransaction();

            $actualizados = 0;
            $errores = [];

            foreach ($rows as $rowIndex => $row) {
                try {
                    // Obtener número de unidad
                    $numero = isset($row[$columnIndexes['numero']]) ? trim($row[$columnIndexes['numero']]) : '';
                    
                    if (empty($numero)) {
                        $errores[] = "Fila " . ($rowIndex + 2) . ": El número de unidad está vacío.";
                        continue;
                    }
                    
                    // Obtener valores de torre y bloque
                    $torre = null;
                    $bloque = null;
                    
                    if (isset($columnIndexes['torre']) && isset($row[$columnIndexes['torre']])) {
                        $torreValue = trim($row[$columnIndexes['torre']]);
                        $torre = $torreValue !== '' ? $torreValue : null;
                    }
                    
                    if (isset($columnIndexes['bloque']) && isset($row[$columnIndexes['bloque']])) {
                        $bloqueValue = trim($row[$columnIndexes['bloque']]);
                        $bloque = $bloqueValue !== '' ? $bloqueValue : null;
                    }

                    // Buscar unidad
                    $query = Unidad::where('propiedad_id', $propiedad->id)
                        ->where('numero', $numero);

                    if ($torre !== null) {
                        $query->where('torre', $torre);
                    } else {
                        $query->whereNull('torre');
                    }

                    if ($bloque !== null) {
                        $query->where('bloque', $bloque);
                    } else {
                        $query->whereNull('bloque');
                    }

                    $unidad = $query->first();

                    if (!$unidad) {
                        $errores[] = "Fila " . ($rowIndex + 2) . ": No se encontró la unidad {$numero}" . ($torre ? " - Torre {$torre}" : "") . ($bloque ? " - Bloque {$bloque}" : "");
                        continue;
                    }

                    // Obtener valores de saldos
                    $saldoCorriente = isset($row[$columnIndexes['saldo_corriente']]) 
                        ? (is_numeric($row[$columnIndexes['saldo_corriente']]) ? (float)$row[$columnIndexes['saldo_corriente']] : 0)
                        : 0;
                    
                    $saldoMora = isset($row[$columnIndexes['saldo_mora']]) 
                        ? (is_numeric($row[$columnIndexes['saldo_mora']]) ? (float)$row[$columnIndexes['saldo_mora']] : 0)
                        : 0;
                    
                    $saldoTotal = isset($row[$columnIndexes['saldo_total']]) 
                        ? (is_numeric($row[$columnIndexes['saldo_total']]) ? (float)$row[$columnIndexes['saldo_total']] : 0)
                        : 0;

                    // Buscar o crear cartera
                    $cartera = Cartera::where('copropiedad_id', $propiedad->id)
                        ->where('unidad_id', $unidad->id)
                        ->first();

                    if ($cartera) {
                        // Actualizar cartera existente
                        $cartera->update([
                            'saldo_corriente' => $saldoCorriente,
                            'saldo_mora' => $saldoMora + $cartera->saldo_mora,
                            'saldo_total' => $saldoTotal + $cartera->saldo_total,
                            'ultima_actualizacion' => Carbon::now(),
                        ]);
                    } else {
                        // Crear nueva cartera
                        $cartera = Cartera::create([
                            'copropiedad_id' => $propiedad->id,
                            'unidad_id' => $unidad->id,
                            'saldo_corriente' => $saldoCorriente,
                            'saldo_mora' => $saldoMora,
                            'saldo_total' => $saldoTotal,
                            'ultima_actualizacion' => Carbon::now(),
                            'activo' => true,
                        ]);
                    }

                    // Crear o buscar cuenta de cobro y detalle para trazabilidad
                    // Usar periodo 2000-02 (febrero 2000) para diferenciarlo de cuentas normales y de creación inicial
                    $periodo = '2000-02';
                    $fechaEmision = Carbon::now()->toDateString();

                    // Verificar si ya existe una cuenta de cobro para este periodo y unidad
                    $cuentaCobro = CuentaCobro::where('copropiedad_id', $propiedad->id)
                        ->where('unidad_id', $unidad->id)
                        ->where('periodo', $periodo)
                        ->first();

                    if (!$cuentaCobro) {
                        // Crear nueva cuenta de cobro solo si no existe
                        $cuentaCobro = CuentaCobro::create([
                            'copropiedad_id' => $propiedad->id,
                            'unidad_id' => $unidad->id,
                            'periodo' => $periodo,
                            'fecha_emision' => $fechaEmision,
                            'fecha_vencimiento' => null,
                            'valor_cuotas' => $saldoTotal,
                            'valor_intereses' => 0,
                            'valor_descuentos' => 0,
                            'valor_recargos' => 0,
                            'valor_total' => $saldoTotal,
                            'estado' => 'pagada',
                            'observaciones' => 'Cargue inicial de saldos de cartera.',
                        ]);
                    }

                    // Verificar si ya existe un detalle con este concepto para esta cuenta de cobro
                    $detalleExistente = CuentaCobroDetalle::where('cuenta_cobro_id', $cuentaCobro->id)
                        ->where('concepto', 'Cargue inicial de saldos cartera')
                        ->first();

                    if (!$detalleExistente) {
                        // Crear detalle con concepto "cargue inicial de saldos cartera" solo si no existe
                        CuentaCobroDetalle::create([
                            'cuenta_cobro_id' => $cuentaCobro->id,
                            'concepto' => 'Cargue inicial de saldos cartera',
                            'cuota_administracion_id' => null,
                            'valor' => $saldoTotal,
                        ]);
                    }

                    $actualizados++;
                } catch (\Exception $e) {
                    $errores[] = "Fila " . ($rowIndex + 2) . ": " . $e->getMessage();
                }
            }

            if (!empty($errores)) {
                DB::rollBack();
                return back()->with('error', 'Se encontraron errores al procesar el archivo: ' . implode(' | ', array_slice($errores, 0, 5)) . (count($errores) > 5 ? ' ...' : ''));
            }

            DB::commit();

            return redirect()->route('admin.cartera.index')
                ->with('success', "Carga exitosa. Se han actualizado {$actualizados} registro(s) de cartera.");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al importar saldos de cartera: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar los detalles de cuenta de cobro asociados a una cartera
     */
    public function detalles(Cartera $cartera)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que la cartera pertenezca a la propiedad activa
        if ($cartera->copropiedad_id != $propiedad->id) {
            return redirect()->route('admin.cartera.index')
                ->with('error', 'No tiene permisos para ver esta cartera.');
        }

        // Cargar la unidad relacionada
        $cartera->load('unidad');

        // Obtener todas las cuentas de cobro de la unidad asociada a esta cartera
        $cuentasCobro = CuentaCobro::where('copropiedad_id', $propiedad->id)
            ->where('unidad_id', $cartera->unidad_id)
            ->orderBy('fecha_emision', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Obtener todos los detalles de cuenta de cobro ordenados por fecha
        $detalles = CuentaCobroDetalle::whereHas('cuentaCobro', function($query) use ($propiedad, $cartera) {
                $query->where('copropiedad_id', $propiedad->id)
                      ->where('unidad_id', $cartera->unidad_id);
            })
            ->with(['cuentaCobro' => function($query) {
                $query->select('id', 'periodo', 'fecha_emision', 'estado', 'valor_total');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.cartera.detalles', compact('cartera', 'detalles', 'propiedad'));
    }
}
