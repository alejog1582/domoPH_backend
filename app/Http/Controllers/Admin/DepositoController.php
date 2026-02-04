<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposito;
use App\Models\Unidad;
use App\Models\Residente;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DepositoController extends Controller
{
    /**
     * Mostrar la lista de depósitos con filtros
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = Deposito::with(['unidad', 'residenteResponsable.user'])
            ->where('copropiedad_id', $propiedad->id);

        // Búsqueda general (código, nivel)
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('codigo', 'like', "%{$buscar}%")
                  ->orWhere('nivel', 'like', "%{$buscar}%");
            });
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

        $depositos = $query->orderBy('codigo')
            ->paginate(20)
            ->appends($request->query());

        // Obtener unidades para filtros
        $unidades = Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get();

        return view('admin.depositos.index', compact('depositos', 'propiedad', 'unidades'));
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

        return view('admin.depositos.create', compact('propiedad', 'unidades', 'residentes'));
    }

    /**
     * Guardar un nuevo depósito
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
            'nivel' => 'nullable|string|max:50',
            'estado' => 'required|in:disponible,asignado,en_mantenimiento,inhabilitado',
            'area_m2' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
            'unidad_id' => 'nullable|exists:unidades,id',
            'residente_responsable_id' => 'nullable|exists:residentes,id',
            'fecha_asignacion' => 'nullable|date',
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.max' => 'El código no puede exceder 50 caracteres.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'area_m2.numeric' => 'El área debe ser un número.',
            'area_m2.min' => 'El área no puede ser negativa.',
            'unidad_id.exists' => 'La unidad seleccionada no existe.',
            'residente_responsable_id.exists' => 'El residente seleccionado no existe.',
            'fecha_asignacion.date' => 'La fecha de asignación debe ser una fecha válida.',
        ]);

        // Verificar que el código sea único por copropiedad
        $existe = Deposito::where('copropiedad_id', $propiedad->id)
            ->where('codigo', $validated['codigo'])
            ->exists();

        if ($existe) {
            return back()->with('error', 'Ya existe un depósito con este código en la copropiedad.')
                ->withInput();
        }

        try {
            Deposito::create([
                'copropiedad_id' => $propiedad->id,
                'codigo' => $validated['codigo'],
                'nivel' => $validated['nivel'] ?? null,
                'estado' => $validated['estado'],
                'area_m2' => $validated['area_m2'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'unidad_id' => $validated['unidad_id'] ?? null,
                'residente_responsable_id' => $validated['residente_responsable_id'] ?? null,
                'fecha_asignacion' => $validated['fecha_asignacion'] ?? null,
                'creado_por' => Auth::id(),
                'activo' => true,
            ]);

            return redirect()->route('admin.depositos.index')
                ->with('success', 'Depósito creado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al crear depósito: ' . $e->getMessage());
            return back()->with('error', 'Error al crear el depósito: ' . $e->getMessage())
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

        $deposito = Deposito::where('copropiedad_id', $propiedad->id)
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

        return view('admin.depositos.edit', compact('deposito', 'propiedad', 'unidades', 'residentes'));
    }

    /**
     * Actualizar un depósito
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $deposito = Deposito::where('copropiedad_id', $propiedad->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'codigo' => 'required|string|max:50',
            'nivel' => 'nullable|string|max:50',
            'estado' => 'required|in:disponible,asignado,en_mantenimiento,inhabilitado',
            'area_m2' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
            'unidad_id' => 'nullable|exists:unidades,id',
            'residente_responsable_id' => 'nullable|exists:residentes,id',
            'fecha_asignacion' => 'nullable|date',
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.max' => 'El código no puede exceder 50 caracteres.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'area_m2.numeric' => 'El área debe ser un número.',
            'area_m2.min' => 'El área no puede ser negativa.',
            'unidad_id.exists' => 'La unidad seleccionada no existe.',
            'residente_responsable_id.exists' => 'El residente seleccionado no existe.',
            'fecha_asignacion.date' => 'La fecha de asignación debe ser una fecha válida.',
        ]);

        // Verificar que el código sea único por copropiedad (excepto el actual)
        $existe = Deposito::where('copropiedad_id', $propiedad->id)
            ->where('codigo', $validated['codigo'])
            ->where('id', '!=', $id)
            ->exists();

        if ($existe) {
            return back()->with('error', 'Ya existe un depósito con este código en la copropiedad.')
                ->withInput();
        }

        try {
            $deposito->update([
                'codigo' => $validated['codigo'],
                'nivel' => $validated['nivel'] ?? null,
                'estado' => $validated['estado'],
                'area_m2' => $validated['area_m2'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'unidad_id' => $validated['unidad_id'] ?? null,
                'residente_responsable_id' => $validated['residente_responsable_id'] ?? null,
                'fecha_asignacion' => $validated['fecha_asignacion'] ?? null,
            ]);

            return redirect()->route('admin.depositos.index')
                ->with('success', 'Depósito actualizado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al actualizar depósito: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar el depósito: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Eliminar un depósito (soft delete)
     */
    public function destroy($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $deposito = Deposito::where('copropiedad_id', $propiedad->id)
            ->findOrFail($id);

        try {
            $deposito->delete();

            return redirect()->route('admin.depositos.index')
                ->with('success', 'Depósito eliminado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al eliminar depósito: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el depósito: ' . $e->getMessage());
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
        $headers = ['codigo', 'nivel', 'estado', 'area_m2', 'observaciones', 'unidad_numero', 'torre', 'bloque', 'residente_documento', 'fecha_asignacion'];
        $sheet->fromArray($headers, null, 'A1');

        // Estilo de encabezados
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle('A1:J1')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Ejemplos de datos
        $examples = [
            ['D-01', 'Sótano 1', 'disponible', '5.50', 'Depósito pequeño', '101', 'A', '1', '', ''],
            ['D-02', 'Piso 1', 'asignado', '8.00', '', '102', 'B', '2', '1234567890', '2026-01-15'],
            ['D-03', 'Sótano 2', 'disponible', '10.00', 'Depósito grande', '', '', '', '', ''],
        ];
        $sheet->fromArray($examples, null, 'A2');

        // Ajustar ancho de columnas
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Crear archivo temporal
        $writer = new Xlsx($spreadsheet);
        $filename = 'plantilla_depositos.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Importar depósitos desde archivo Excel/CSV
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

            foreach ($rows as $row) {
                if (!isset($columnIndexes['codigo']) || empty($row[$columnIndexes['codigo']])) {
                    $ignorados++;
                    continue;
                }

                $codigo = trim($row[$columnIndexes['codigo']]);
                
                // Buscar depósito existente
                $deposito = Deposito::where('copropiedad_id', $propiedad->id)
                    ->where('codigo', $codigo)
                    ->first();

                $data = [
                    'copropiedad_id' => $propiedad->id,
                    'codigo' => $codigo,
                    'nivel' => isset($columnIndexes['nivel']) ? trim($row[$columnIndexes['nivel']]) : null,
                    'estado' => isset($columnIndexes['estado']) && in_array(trim($row[$columnIndexes['estado']]), ['disponible', 'asignado', 'en_mantenimiento', 'inhabilitado'])
                        ? trim($row[$columnIndexes['estado']])
                        : 'disponible',
                    'area_m2' => isset($columnIndexes['area_m2']) && is_numeric($row[$columnIndexes['area_m2']])
                        ? (float)$row[$columnIndexes['area_m2']]
                        : null,
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
                    // Buscar primero el usuario por documento_identidad
                    $user = \App\Models\User::where('documento_identidad', $documento)->first();
                    if ($user) {
                        // Buscar el residente asociado a ese usuario y que pertenezca a una unidad de la propiedad
                        $residente = Residente::whereHas('unidad', function($q) use ($propiedad) {
                            $q->where('propiedad_id', $propiedad->id);
                        })
                        ->where('user_id', $user->id)
                        ->first();
                        if ($residente) {
                            $data['residente_responsable_id'] = $residente->id;
                        }
                    }
                }

                // Fecha de asignación
                if (isset($columnIndexes['fecha_asignacion']) && !empty($row[$columnIndexes['fecha_asignacion']])) {
                    $data['fecha_asignacion'] = trim($row[$columnIndexes['fecha_asignacion']]);
                }

                if ($deposito) {
                    $deposito->update($data);
                    $actualizados++;
                } else {
                    Deposito::create($data);
                    $creados++;
                }
            }

            DB::commit();

            $mensaje = "Importación exitosa. Se han creado {$creados} registro(s), se han actualizado {$actualizados} registro(s) y se han ignorado {$ignorados} registro(s) sin código.";

            return redirect()->route('admin.depositos.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al importar depósitos: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }
}
