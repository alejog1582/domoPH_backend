<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parqueadero;
use App\Models\Unidad;
use App\Models\Residente;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ParqueaderoController extends Controller
{
    /**
     * Mostrar la lista de parqueaderos con filtros
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = Parqueadero::with(['unidad', 'residenteResponsable.user'])
            ->where('copropiedad_id', $propiedad->id);

        // Búsqueda general (código, nivel)
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('codigo', 'like', "%{$buscar}%")
                  ->orWhere('nivel', 'like', "%{$buscar}%");
            });
        }

        // Filtro por tipo
        if ($request->filled('tipo')) {
            if ($request->tipo !== 'todos') {
                $query->where('tipo', $request->tipo);
            }
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            if ($request->estado !== 'todos') {
                $query->where('estado', $request->estado);
            }
        }

        // Filtro por unidad
        if ($request->filled('unidad_id')) {
            $query->where('unidad_id', $request->unidad_id);
        }

        // Filtro por es_cubierto
        if ($request->filled('es_cubierto')) {
            if ($request->es_cubierto !== 'todos') {
                $query->where('es_cubierto', $request->es_cubierto == 'si');
            }
        }

        $parqueaderos = $query->orderBy('codigo')
            ->paginate(20)
            ->appends($request->query());

        // Obtener unidades para filtros
        $unidades = Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get();

        return view('admin.parqueaderos.index', compact('parqueaderos', 'propiedad', 'unidades'));
    }

    /**
     * Mostrar el formulario de creación
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $unidades = Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get();

        $residentes = Residente::whereHas('unidad', function($q) use ($propiedad) {
            $q->where('propiedad_id', $propiedad->id);
        })
        ->activos()
        ->with('user')
        ->join('users', 'residentes.user_id', '=', 'users.id')
        ->orderBy('users.nombre')
        ->select('residentes.*')
        ->get();

        return view('admin.parqueaderos.create', compact('propiedad', 'unidades', 'residentes'));
    }

    /**
     * Guardar un nuevo parqueadero
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'codigo' => 'required|string|max:50',
            'tipo' => 'required|in:privado,comunal,visitantes',
            'tipo_vehiculo' => 'nullable|in:carro,moto',
            'nivel' => 'nullable|string|max:50',
            'estado' => 'required|in:disponible,asignado,en_mantenimiento,inhabilitado',
            'es_cubierto' => 'boolean',
            'observaciones' => 'nullable|string',
            'unidad_id' => 'nullable|exists:unidades,id',
            'residente_responsable_id' => 'nullable|exists:residentes,id',
            'fecha_asignacion' => 'nullable|date',
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.max' => 'El código no puede exceder 50 caracteres.',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
            'tipo_vehiculo.in' => 'El tipo de vehículo seleccionado no es válido.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'unidad_id.exists' => 'La unidad seleccionada no existe.',
            'residente_responsable_id.exists' => 'El residente seleccionado no existe.',
            'fecha_asignacion.date' => 'La fecha de asignación debe ser una fecha válida.',
        ]);

        // Verificar que el código sea único por copropiedad
        $existe = Parqueadero::where('copropiedad_id', $propiedad->id)
            ->where('codigo', $validated['codigo'])
            ->exists();

        if ($existe) {
            return back()->with('error', 'Ya existe un parqueadero con este código en la copropiedad.')
                ->withInput();
        }

        try {
            Parqueadero::create([
                'copropiedad_id' => $propiedad->id,
                'codigo' => $validated['codigo'],
                'tipo' => $validated['tipo'],
                'tipo_vehiculo' => $validated['tipo_vehiculo'],
                'nivel' => $validated['nivel'] ?? null,
                'estado' => $validated['estado'],
                'es_cubierto' => $request->has('es_cubierto') ? (bool)$request->es_cubierto : false,
                'observaciones' => $validated['observaciones'] ?? null,
                'unidad_id' => $validated['unidad_id'] ?? null,
                'residente_responsable_id' => $validated['residente_responsable_id'] ?? null,
                'fecha_asignacion' => $validated['fecha_asignacion'] ?? null,
                'creado_por' => Auth::id(),
                'activo' => true,
            ]);

            return redirect()->route('admin.parqueaderos.index')
                ->with('success', 'Parqueadero creado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al crear parqueadero: ' . $e->getMessage());
            return back()->with('error', 'Error al crear el parqueadero: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar el formulario de edición
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $parqueadero = Parqueadero::where('copropiedad_id', $propiedad->id)
            ->findOrFail($id);

        $unidades = Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get();

        $residentes = Residente::whereHas('unidad', function($q) use ($propiedad) {
            $q->where('propiedad_id', $propiedad->id);
        })
        ->activos()
        ->with('user')
        ->join('users', 'residentes.user_id', '=', 'users.id')
        ->orderBy('users.nombre')
        ->select('residentes.*')
        ->get();

        return view('admin.parqueaderos.edit', compact('parqueadero', 'propiedad', 'unidades', 'residentes'));
    }

    /**
     * Actualizar un parqueadero
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $parqueadero = Parqueadero::where('copropiedad_id', $propiedad->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'codigo' => 'required|string|max:50',
            'tipo' => 'required|in:privado,comunal,visitantes',
            'tipo_vehiculo' => 'required|in:carro,moto',
            'nivel' => 'nullable|string|max:50',
            'estado' => 'required|in:disponible,asignado,en_mantenimiento,inhabilitado',
            'es_cubierto' => 'boolean',
            'observaciones' => 'nullable|string',
            'unidad_id' => 'nullable|exists:unidades,id',
            'residente_responsable_id' => 'nullable|exists:residentes,id',
            'fecha_asignacion' => 'nullable|date',
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.max' => 'El código no puede exceder 50 caracteres.',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
            'tipo_vehiculo.required' => 'El tipo de vehículo es obligatorio.',
            'tipo_vehiculo.in' => 'El tipo de vehículo seleccionado no es válido.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'unidad_id.exists' => 'La unidad seleccionada no existe.',
            'residente_responsable_id.exists' => 'El residente seleccionado no existe.',
            'fecha_asignacion.date' => 'La fecha de asignación debe ser una fecha válida.',
        ]);

        // Verificar que el código sea único por copropiedad (excepto el actual)
        $existe = Parqueadero::where('copropiedad_id', $propiedad->id)
            ->where('codigo', $validated['codigo'])
            ->where('id', '!=', $id)
            ->exists();

        if ($existe) {
            return back()->with('error', 'Ya existe un parqueadero con este código en la copropiedad.')
                ->withInput();
        }

        try {
            $parqueadero->update([
                'codigo' => $validated['codigo'],
                'tipo' => $validated['tipo'],
                'tipo_vehiculo' => $validated['tipo_vehiculo'],
                'nivel' => $validated['nivel'] ?? null,
                'estado' => $validated['estado'],
                'es_cubierto' => $request->has('es_cubierto') ? (bool)$request->es_cubierto : false,
                'observaciones' => $validated['observaciones'] ?? null,
                'unidad_id' => $validated['unidad_id'] ?? null,
                'residente_responsable_id' => $validated['residente_responsable_id'] ?? null,
                'fecha_asignacion' => $validated['fecha_asignacion'] ?? null,
            ]);

            return redirect()->route('admin.parqueaderos.index')
                ->with('success', 'Parqueadero actualizado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al actualizar parqueadero: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar el parqueadero: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Eliminar un parqueadero (soft delete)
     */
    public function destroy($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $parqueadero = Parqueadero::where('copropiedad_id', $propiedad->id)
            ->findOrFail($id);

        try {
            $parqueadero->delete();

            return redirect()->route('admin.parqueaderos.index')
                ->with('success', 'Parqueadero eliminado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al eliminar parqueadero: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el parqueadero: ' . $e->getMessage());
        }
    }

    /**
     * Descargar plantilla de ejemplo para carga masiva
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $headers = ['codigo', 'tipo', 'tipo_vehiculo', 'nivel', 'estado', 'es_cubierto', 'observaciones', 'unidad_numero', 'torre', 'bloque', 'residente_documento', 'fecha_asignacion'];
        $sheet->fromArray($headers, null, 'A1');

        // Estilo de encabezados
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $sheet->getStyle('A1:L1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle('A1:L1')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Ejemplos de datos
        $examples = [
            ['P-01', 'privado', 'carro', 'Sótano 1', 'disponible', '1', 'Parqueadero cubierto', '101', 'A', '1', '', ''],
            ['P-02', 'comunal', 'moto', 'Piso 1', 'asignado', '0', '', '102', 'B', '2', '1234567890', '2026-01-15'],
            ['V-01', 'visitantes', 'carro', 'Exterior', 'disponible', '0', 'Para visitantes', '', '', '', '', ''],
        ];
        $sheet->fromArray($examples, null, 'A2');

        // Ajustar ancho de columnas
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Crear archivo temporal
        $writer = new Xlsx($spreadsheet);
        $filename = 'plantilla_parqueaderos.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Importar parqueaderos desde archivo Excel/CSV
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
            DB::beginTransaction();

            $file = $request->file('archivo');
            $extension = $file->getClientOriginalExtension();
            
            // Leer el archivo
            $rows = [];
            $headers = [];
            
            if (in_array($extension, ['xlsx', 'xls'])) {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $worksheet = $spreadsheet->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                
                // Leer encabezados
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $cellValue = $worksheet->getCell($columnLetter . '1')->getValue();
                    $headers[] = $cellValue ? trim(strtolower($cellValue)) : '';
                }
                
                // Leer datos
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
                if ($handle !== false) {
                    $headers = array_map('trim', array_map('strtolower', fgetcsv($handle)));
                    while (($data = fgetcsv($handle)) !== false) {
                        $rows[] = array_map('trim', $data);
                    }
                    fclose($handle);
                }
            }

            // Mapear columnas
            $columnIndexes = [];
            foreach ($headers as $index => $header) {
                $columnIndexes[$header] = $index;
            }

            $creados = 0;
            $actualizados = 0;
            $ignorados = 0;

            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 porque la fila 1 es el encabezado y empezamos desde 0
                
                if (!isset($columnIndexes['codigo']) || empty($row[$columnIndexes['codigo']])) {
                    $ignorados++;
                    continue;
                }

                // Validar tipo_vehiculo obligatorio
                if (!isset($columnIndexes['tipo_vehiculo']) || empty($row[$columnIndexes['tipo_vehiculo']])) {
                    return back()->with('error', "Fila {$rowNumber}: El campo 'tipo_vehiculo' es obligatorio y debe ser 'carro' o 'moto'.")
                        ->withInput();
                }

                $tipoVehiculo = trim($row[$columnIndexes['tipo_vehiculo']]);
                if (!in_array($tipoVehiculo, ['carro', 'moto'])) {
                    return back()->with('error', "Fila {$rowNumber}: El campo 'tipo_vehiculo' debe ser 'carro' o 'moto'. Valor recibido: '{$tipoVehiculo}'.")
                        ->withInput();
                }

                $codigo = trim($row[$columnIndexes['codigo']]);
                
                // Buscar parqueadero existente
                $parqueadero = Parqueadero::where('copropiedad_id', $propiedad->id)
                    ->where('codigo', $codigo)
                    ->first();

                $data = [
                    'copropiedad_id' => $propiedad->id,
                    'codigo' => $codigo,
                    'tipo' => isset($columnIndexes['tipo']) && in_array(trim($row[$columnIndexes['tipo']]), ['privado', 'comunal', 'visitantes'])
                        ? trim($row[$columnIndexes['tipo']])
                        : 'privado',
                    'tipo_vehiculo' => $tipoVehiculo,
                    'nivel' => isset($columnIndexes['nivel']) ? trim($row[$columnIndexes['nivel']]) : null,
                    'estado' => isset($columnIndexes['estado']) && in_array(trim($row[$columnIndexes['estado']]), ['disponible', 'asignado', 'en_mantenimiento', 'inhabilitado'])
                        ? trim($row[$columnIndexes['estado']])
                        : 'disponible',
                    'es_cubierto' => isset($columnIndexes['es_cubierto']) && (trim($row[$columnIndexes['es_cubierto']]) == '1' || strtolower(trim($row[$columnIndexes['es_cubierto']])) == 'si'),
                    'observaciones' => isset($columnIndexes['observaciones']) ? trim($row[$columnIndexes['observaciones']]) : null,
                    'creado_por' => Auth::id(),
                    'activo' => true,
                ];

                // Buscar unidad por número, torre y bloque
                if (isset($columnIndexes['unidad_numero']) && !empty($row[$columnIndexes['unidad_numero']])) {
                    $unidadNumero = trim($row[$columnIndexes['unidad_numero']]);
                    $torre = isset($columnIndexes['torre']) && !empty($row[$columnIndexes['torre']]) 
                        ? trim($row[$columnIndexes['torre']]) 
                        : null;
                    $bloque = isset($columnIndexes['bloque']) && !empty($row[$columnIndexes['bloque']]) 
                        ? trim($row[$columnIndexes['bloque']]) 
                        : null;
                    
                    $query = Unidad::where('propiedad_id', $propiedad->id)
                        ->where('numero', $unidadNumero);
                    
                    if ($torre) {
                        $query->where('torre', $torre);
                    } else {
                        $query->whereNull('torre');
                    }
                    
                    if ($bloque) {
                        $query->where('bloque', $bloque);
                    } else {
                        $query->whereNull('bloque');
                    }
                    
                    $unidad = $query->first();
                    if ($unidad) {
                        $data['unidad_id'] = $unidad->id;
                    }
                }

                // Buscar residente por documento
                if (isset($columnIndexes['residente_documento']) && !empty($row[$columnIndexes['residente_documento']])) {
                    $documento = trim($row[$columnIndexes['residente_documento']]);
                    $residente = Residente::whereHas('unidad', function($q) use ($propiedad) {
                        $q->where('propiedad_id', $propiedad->id);
                    })
                    ->where('documento_identidad', $documento)
                    ->first();
                    if ($residente) {
                        $data['residente_responsable_id'] = $residente->id;
                    }
                }

                // Fecha de asignación
                if (isset($columnIndexes['fecha_asignacion']) && !empty($row[$columnIndexes['fecha_asignacion']])) {
                    $data['fecha_asignacion'] = trim($row[$columnIndexes['fecha_asignacion']]);
                }

                if ($parqueadero) {
                    $parqueadero->update($data);
                    $actualizados++;
                } else {
                    Parqueadero::create($data);
                    $creados++;
                }
            }

            DB::commit();

            $mensaje = "Importación exitosa. Se han creado {$creados} registro(s), se han actualizado {$actualizados} registro(s) y se han ignorado {$ignorados} registro(s) sin código.";

            return redirect()->route('admin.parqueaderos.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al importar parqueaderos: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }
}
