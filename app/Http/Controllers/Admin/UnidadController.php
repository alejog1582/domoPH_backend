<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unidad;
use App\Helpers\AdminHelper;
use App\Services\PlantillaUnidadesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UnidadController extends Controller
{
    /**
     * Mostrar la lista de unidades con opción de carga masiva
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = Unidad::where('propiedad_id', $propiedad->id);

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('numero', 'like', "%{$buscar}%")
                  ->orWhere('torre', 'like', "%{$buscar}%")
                  ->orWhere('bloque', 'like', "%{$buscar}%");
            });
        }

        $unidades = $query->orderBy('torre')
            ->orderBy('bloque')
            ->orderBy('numero')
            ->paginate(15);

        return view('admin.unidades.index', compact('unidades', 'propiedad'));
    }

    /**
     * Descargar plantilla de ejemplo para carga masiva
     */
    public function downloadTemplate()
    {
        $rutaArchivo = public_path('plantillas/plantilla_unidades.xlsx');
        
        // Crear el archivo si no existe
        if (!file_exists($rutaArchivo)) {
            $creado = PlantillaUnidadesService::crearPlantilla($rutaArchivo);
            
            if (!$creado) {
                return back()->with('error', 'No se pudo crear la plantilla. Por favor, contacte al administrador.');
            }
        }
        
        // Verificar que el archivo existe
        if (!file_exists($rutaArchivo)) {
            return back()->with('error', 'La plantilla no está disponible.');
        }
        
        // Descargar el archivo
        return response()->download($rutaArchivo, 'plantilla_unidades.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Procesar la carga masiva de unidades desde Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls,csv,txt|max:10240', // 10MB máximo
        ]);

        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return back()->with('error', 'No hay propiedad asignada.');
        }

        try {
            $file = $request->file('archivo');
            $extension = $file->getClientOriginalExtension();
            
            // Leer el archivo (CSV o Excel)
            $rows = [];
            if (in_array($extension, ['csv'])) {
                // Leer CSV
                $handle = fopen($file->getRealPath(), 'r');
                while (($row = fgetcsv($handle)) !== false) {
                    $rows[] = $row;
                }
                fclose($handle);
            } else {
                // Para Excel, necesitaríamos el paquete maatwebsite/excel
                // Por ahora, intentamos leer como CSV también
                $handle = fopen($file->getRealPath(), 'r');
                while (($row = fgetcsv($handle)) !== false) {
                    $rows[] = $row;
                }
                fclose($handle);
            }
            
            if (empty($rows)) {
                return back()->with('error', 'El archivo está vacío o no tiene el formato correcto.');
            }

            $headers = array_shift($rows); // Primera fila son los encabezados

            // Mapear encabezados a campos de la base de datos
            $headerMap = [
                'numero' => ['numero', 'número', 'num', 'nro'],
                'torre' => ['torre'],
                'bloque' => ['bloque', 'block'],
                'tipo' => ['tipo', 'tipo_unidad'],
                'area_m2' => ['area_m2', 'area', 'área', 'metros'],
                'coeficiente' => ['coeficiente', 'coef'],
                'habitaciones' => ['habitaciones', 'habitacion', 'habitación'],
                'banos' => ['banos', 'baños', 'baño', 'bathrooms'],
                'estado' => ['estado', 'status'],
                'observaciones' => ['observaciones', 'observacion', 'observación', 'notas', 'nota'],
            ];

            // Encontrar índices de columnas
            $columnIndexes = [];
            foreach ($headerMap as $field => $possibleHeaders) {
                foreach ($possibleHeaders as $header) {
                    $index = array_search(strtolower(trim($header)), array_map('strtolower', array_map('trim', $headers)));
                    if ($index !== false) {
                        $columnIndexes[$field] = $index;
                        break;
                    }
                }
            }

            if (empty($columnIndexes)) {
                return back()->with('error', 'No se encontraron columnas válidas en el archivo.');
            }

            DB::beginTransaction();

            $updated = 0;
            $errors = [];

            foreach ($rows as $rowIndex => $row) {
                // Validar que tenga al menos el número de unidad
                if (empty($row[$columnIndexes['numero']] ?? null)) {
                    continue; // Saltar filas vacías
                }

                $numero = trim($row[$columnIndexes['numero']]);
                
                // Buscar unidad existente por número, torre y bloque
                $torre = isset($columnIndexes['torre']) ? trim($row[$columnIndexes['torre']] ?? '') : null;
                $bloque = isset($columnIndexes['bloque']) ? trim($row[$columnIndexes['bloque']] ?? '') : null;

                $unidad = Unidad::where('propiedad_id', $propiedad->id)
                    ->where('numero', $numero);

                if ($torre) {
                    $unidad->where('torre', $torre);
                }
                if ($bloque) {
                    $unidad->where('bloque', $bloque);
                }

                $unidad = $unidad->first();

                // Solo actualizar si la unidad existe
                if ($unidad) {
                    $updateData = [
                        'propiedad_id' => $propiedad->id,
                    ];

                    if (isset($columnIndexes['tipo'])) {
                        $updateData['tipo'] = trim($row[$columnIndexes['tipo']] ?? '');
                    }
                    if (isset($columnIndexes['area_m2'])) {
                        $updateData['area_m2'] = is_numeric($row[$columnIndexes['area_m2']] ?? null) 
                            ? (float)$row[$columnIndexes['area_m2']] 
                            : null;
                    }
                    if (isset($columnIndexes['coeficiente'])) {
                        $updateData['coeficiente'] = is_numeric($row[$columnIndexes['coeficiente']] ?? null) 
                            ? (int)$row[$columnIndexes['coeficiente']] 
                            : null;
                    }
                    if (isset($columnIndexes['habitaciones'])) {
                        $updateData['habitaciones'] = is_numeric($row[$columnIndexes['habitaciones']] ?? null) 
                            ? (int)$row[$columnIndexes['habitaciones']] 
                            : null;
                    }
                    if (isset($columnIndexes['banos'])) {
                        $updateData['banos'] = is_numeric($row[$columnIndexes['banos']] ?? null) 
                            ? (int)$row[$columnIndexes['banos']] 
                            : null;
                    }
                    if (isset($columnIndexes['estado'])) {
                        $updateData['estado'] = trim($row[$columnIndexes['estado']] ?? '');
                    }
                    if (isset($columnIndexes['observaciones'])) {
                        $updateData['observaciones'] = trim($row[$columnIndexes['observaciones']] ?? '');
                    }

                    $unidad->update($updateData);
                    $updated++;
                } else {
                    $errors[] = "Fila " . ($rowIndex + 2) . ": Unidad {$numero}" . ($torre ? " Torre {$torre}" : "") . ($bloque ? " Bloque {$bloque}" : "") . " no encontrada. Solo se actualizan unidades existentes.";
                }
            }

            DB::commit();

            $message = "Se actualizaron {$updated} unidades correctamente.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " unidades no se encontraron y no se actualizaron.";
            }

            return back()->with('success', $message)
                ->with('errors', $errors);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }
}
