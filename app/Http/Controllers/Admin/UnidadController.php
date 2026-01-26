<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unidad;
use App\Helpers\AdminHelper;
use App\Services\PlantillaUnidadesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
                    $index = array_search(strtolower($header), array_map('strtolower', $headers));
                    if ($index !== false) {
                        $columnIndexes[$field] = $index;
                        break;
                    }
                }
            }

            // Validar que exista la columna número (obligatoria)
            if (!isset($columnIndexes['numero'])) {
                return back()->with('error', 'No se encontró la columna "numero" en el archivo. Esta columna es obligatoria.');
            }

            DB::beginTransaction();

            $creados = 0;
            $actualizados = 0;
            $ignorados = 0;

            foreach ($rows as $rowIndex => $row) {
                // Validar que tenga el campo número (obligatorio)
                $numero = isset($row[$columnIndexes['numero']]) ? trim($row[$columnIndexes['numero']]) : '';
                
                if (empty($numero)) {
                    $ignorados++;
                    continue; // Saltar registros sin número
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

                // Buscar unidad existente por numero, torre y bloque
                $query = Unidad::where('propiedad_id', $propiedad->id)
                    ->where('numero', $numero);

                // Si torre está definida en el archivo, buscar por torre (puede ser null o valor)
                if (isset($columnIndexes['torre'])) {
                    if ($torre !== null) {
                        $query->where('torre', $torre);
                    } else {
                        $query->whereNull('torre');
                    }
                }

                // Si bloque está definido en el archivo, buscar por bloque (puede ser null o valor)
                if (isset($columnIndexes['bloque'])) {
                    if ($bloque !== null) {
                        $query->where('bloque', $bloque);
                    } else {
                        $query->whereNull('bloque');
                    }
                }

                $unidad = $query->first();

                // Preparar datos para crear/actualizar
                $data = [
                    'propiedad_id' => $propiedad->id,
                    'numero' => $numero,
                ];

                if (isset($columnIndexes['torre']) && isset($row[$columnIndexes['torre']])) {
                    $data['torre'] = trim($row[$columnIndexes['torre']]) ?: null;
                }

                if (isset($columnIndexes['bloque']) && isset($row[$columnIndexes['bloque']])) {
                    $data['bloque'] = trim($row[$columnIndexes['bloque']]) ?: null;
                }

                if (isset($columnIndexes['tipo']) && isset($row[$columnIndexes['tipo']])) {
                    $tipo = trim($row[$columnIndexes['tipo']]);
                    if (in_array($tipo, ['apartamento', 'casa', 'local', 'parqueadero', 'bodega', 'otro'])) {
                        $data['tipo'] = $tipo;
                    }
                }

                if (isset($columnIndexes['area_m2']) && isset($row[$columnIndexes['area_m2']])) {
                    $data['area_m2'] = is_numeric($row[$columnIndexes['area_m2']]) 
                        ? (float)$row[$columnIndexes['area_m2']] 
                        : null;
                }

                if (isset($columnIndexes['coeficiente']) && isset($row[$columnIndexes['coeficiente']])) {
                    $data['coeficiente'] = is_numeric($row[$columnIndexes['coeficiente']]) 
                        ? (int)$row[$columnIndexes['coeficiente']] 
                        : 0;
                }

                if (isset($columnIndexes['habitaciones']) && isset($row[$columnIndexes['habitaciones']])) {
                    $data['habitaciones'] = is_numeric($row[$columnIndexes['habitaciones']]) 
                        ? (int)$row[$columnIndexes['habitaciones']] 
                        : null;
                }

                if (isset($columnIndexes['banos']) && isset($row[$columnIndexes['banos']])) {
                    $data['banos'] = is_numeric($row[$columnIndexes['banos']]) 
                        ? (int)$row[$columnIndexes['banos']] 
                        : null;
                }

                if (isset($columnIndexes['estado']) && isset($row[$columnIndexes['estado']])) {
                    $estado = trim($row[$columnIndexes['estado']]);
                    if (in_array($estado, ['ocupada', 'desocupada', 'en_construccion', 'mantenimiento'])) {
                        $data['estado'] = $estado;
                    }
                }

                if (isset($columnIndexes['observaciones']) && isset($row[$columnIndexes['observaciones']])) {
                    $data['observaciones'] = trim($row[$columnIndexes['observaciones']]) ?: null;
                }

                if ($unidad) {
                    // Actualizar unidad existente
                    $unidad->update($data);
                    $actualizados++;
                } else {
                    // Crear nueva unidad
                    Unidad::create($data);
                    $creados++;
                }
            }

            DB::commit();

            // Mensaje de éxito con estadísticas
            $mensaje = "Importación exitosa. ";
            $mensaje .= "Se han creado {$creados} registro(s), ";
            $mensaje .= "se han actualizado {$actualizados} registro(s) ";
            $mensaje .= "y se han ignorado {$ignorados} registro(s) sin número.";

            return redirect()->route('admin.unidades.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al importar unidades: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }
}
