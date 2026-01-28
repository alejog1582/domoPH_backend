<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Models\Unidad;
use App\Models\User;
use App\Helpers\AdminHelper;
use App\Services\PlantillaResidentesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ResidenteController extends Controller
{
    /**
     * Mostrar la lista de residentes con opción de carga masiva
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener todas las unidades de la propiedad para filtrar residentes
        $unidadesIds = Unidad::where('propiedad_id', $propiedad->id)->pluck('id');
        
        $query = Residente::whereIn('unidad_id', $unidadesIds)
            ->with(['user', 'unidad', 'mascotas']);

        // Búsqueda general (nombre, email, documento)
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('user', function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('documento_identidad', 'like', "%{$buscar}%");
            });
        }

        // Filtro por tipo de relación
        if ($request->filled('tipo_relacion')) {
            $query->where('tipo_relacion', $request->tipo_relacion);
        }

        // Filtro por unidad
        if ($request->filled('unidad_id')) {
            $query->where('unidad_id', $request->unidad_id);
        }

        // Filtro por es_principal
        if ($request->filled('es_principal')) {
            $query->where('es_principal', $request->es_principal == '1');
        }

        $residentes = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Obtener unidades para el filtro
        $unidades = Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('torre')
            ->orderBy('bloque')
            ->orderBy('numero')
            ->get();

        // Obtener tipos de relación únicos
        $tiposRelacionUnicos = Residente::whereIn('unidad_id', $unidadesIds)
            ->groupBy('tipo_relacion')
            ->pluck('tipo_relacion')
            ->unique()
            ->values();

        return view('admin.residentes.index', compact('residentes', 'unidades', 'tiposRelacionUnicos', 'propiedad'));
    }

    /**
     * Descargar plantilla Excel para importar residentes
     */
    public function downloadTemplate()
    {
        $rutaPlantilla = public_path('plantillas/plantilla_residentes.xlsx');
        
        // Si no existe, crearla
        if (!file_exists($rutaPlantilla)) {
            PlantillaResidentesService::crearPlantilla($rutaPlantilla);
        }
        
        return response()->download($rutaPlantilla, 'plantilla_residentes.xlsx');
    }

    /**
     * Importar residentes desde archivo Excel/CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'archivo.required' => 'Debe seleccionar un archivo para importar.',
            'archivo.file' => 'El archivo no es válido.',
            'archivo.mimes' => 'El archivo debe ser de tipo Excel (.xlsx, .xls) o CSV.',
            'archivo.max' => 'El archivo no debe ser mayor a 10MB.',
        ]);

        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        try {
            $archivo = $request->file('archivo');
            $extension = $archivo->getClientOriginalExtension();
            
            // Leer archivo según extensión
            if (in_array($extension, ['xlsx', 'xls'])) {
                $spreadsheet = IOFactory::load($archivo->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();
            } else {
                // CSV
                $rows = [];
                if (($handle = fopen($archivo->getRealPath(), 'r')) !== false) {
                    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                        $rows[] = $data;
                    }
                    fclose($handle);
                }
            }

            if (empty($rows) || count($rows) < 2) {
                return back()->with('error', 'El archivo está vacío o no tiene el formato correcto.');
            }

            // Primera fila son los encabezados
            $headers = array_map('trim', array_map('strtolower', $rows[0]));
            $rows = array_slice($rows, 1);

            // Mapear encabezados a campos
            $headerMap = [
                'numero_unidad' => ['numero_unidad', 'numero', 'número', 'num', 'nro', 'unidad_numero'],
                'torre_unidad' => ['torre_unidad', 'torre', 'torre_unidad'],
                'bloque_unidad' => ['bloque_unidad', 'bloque', 'block', 'bloque_unidad'],
                'nombre' => ['nombre', 'name', 'nombres'],
                'email' => ['email', 'correo', 'correo_electronico'],
                'documento' => ['documento', 'documento_identidad', 'cedula', 'cédula', 'dni'],
                'telefono' => ['telefono', 'teléfono', 'phone', 'celular'],
                'tipo_relacion' => ['tipo_relacion', 'tipo', 'relacion', 'relación'],
                'fecha_inicio' => ['fecha_inicio', 'inicio', 'fecha_inicial'],
                'fecha_fin' => ['fecha_fin', 'fin', 'fecha_final'],
                'es_principal' => ['es_principal', 'principal', 'es principal'],
                'recibe_notificaciones' => ['recibe_notificaciones', 'notificaciones', 'recibe notificaciones'],
                'observaciones' => ['observaciones', 'observacion', 'observación', 'notas', 'nota'],
            ];

            // Encontrar índices de columnas
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
            if (!isset($columnIndexes['numero_unidad'])) {
                return back()->with('error', 'No se encontró la columna "numero_unidad" en el archivo. Esta columna es obligatoria.');
            }

            if (!isset($columnIndexes['email']) && !isset($columnIndexes['documento'])) {
                return back()->with('error', 'Debe incluir al menos una de las columnas: "email" o "documento".');
            }

            DB::beginTransaction();

            $creados = 0;
            $actualizados = 0;
            $ignorados = 0;

            foreach ($rows as $rowIndex => $row) {
                // Validar número de unidad (obligatorio)
                $numeroUnidad = isset($row[$columnIndexes['numero_unidad']]) ? trim($row[$columnIndexes['numero_unidad']]) : '';
                
                if (empty($numeroUnidad)) {
                    $ignorados++;
                    continue;
                }

                // Buscar unidad
                $torreUnidad = isset($columnIndexes['torre_unidad']) && isset($row[$columnIndexes['torre_unidad']]) 
                    ? trim($row[$columnIndexes['torre_unidad']]) : null;
                $bloqueUnidad = isset($columnIndexes['bloque_unidad']) && isset($row[$columnIndexes['bloque_unidad']]) 
                    ? trim($row[$columnIndexes['bloque_unidad']]) : null;

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
                    $ignorados++;
                    continue; // Saltar si no se encuentra la unidad
                }

                // Buscar o crear usuario
                $email = isset($columnIndexes['email']) && isset($row[$columnIndexes['email']]) 
                    ? trim($row[$columnIndexes['email']]) : null;
                $documento = isset($columnIndexes['documento']) && isset($row[$columnIndexes['documento']]) 
                    ? trim($row[$columnIndexes['documento']]) : null;

                $user = null;
                if ($email) {
                    $user = User::where('email', $email)->first();
                }
                
                if (!$user && $documento) {
                    $user = User::where('documento_identidad', $documento)->first();
                }

                // Si no existe, crear usuario
                if (!$user) {
                    $nombre = isset($columnIndexes['nombre']) && isset($row[$columnIndexes['nombre']]) 
                        ? trim($row[$columnIndexes['nombre']]) : 'Usuario sin nombre';
                    $telefono = isset($columnIndexes['telefono']) && isset($row[$columnIndexes['telefono']]) 
                        ? trim($row[$columnIndexes['telefono']]) : null;

                    if (!$email && !$documento) {
                        $ignorados++;
                        continue; // No se puede crear usuario sin email ni documento
                    }

                    $user = User::create([
                        'nombre' => $nombre,
                        'email' => $email ?: 'usuario_' . time() . '@temp.com',
                        'password' => Hash::make('password123'), // Password temporal
                        'documento_identidad' => $documento,
                        'telefono' => $telefono,
                        'activo' => true,
                    ]);
                }

                // Preparar datos del residente
                $tipoRelacion = isset($columnIndexes['tipo_relacion']) && isset($row[$columnIndexes['tipo_relacion']]) 
                    ? strtolower(trim($row[$columnIndexes['tipo_relacion']])) : 'propietario';
                
                $allowedTipos = ['propietario', 'arrendatario', 'residente_temporal', 'otro'];
                if (!in_array($tipoRelacion, $allowedTipos)) {
                    $tipoRelacion = 'propietario';
                }

                $fechaInicio = null;
                if (isset($columnIndexes['fecha_inicio']) && isset($row[$columnIndexes['fecha_inicio']])) {
                    $fechaInicioValue = trim($row[$columnIndexes['fecha_inicio']]);
                    if ($fechaInicioValue) {
                        try {
                            $fechaInicio = \Carbon\Carbon::parse($fechaInicioValue)->format('Y-m-d');
                        } catch (\Exception $e) {
                            // Ignorar fecha inválida
                        }
                    }
                }

                $fechaFin = null;
                if (isset($columnIndexes['fecha_fin']) && isset($row[$columnIndexes['fecha_fin']])) {
                    $fechaFinValue = trim($row[$columnIndexes['fecha_fin']]);
                    if ($fechaFinValue) {
                        try {
                            $fechaFin = \Carbon\Carbon::parse($fechaFinValue)->format('Y-m-d');
                        } catch (\Exception $e) {
                            // Ignorar fecha inválida
                        }
                    }
                }

                $esPrincipal = false;
                if (isset($columnIndexes['es_principal']) && isset($row[$columnIndexes['es_principal']])) {
                    $esPrincipalValue = trim($row[$columnIndexes['es_principal']]);
                    $esPrincipal = in_array(strtolower($esPrincipalValue), ['1', 'si', 'sí', 'yes', 'true', 'verdadero']);
                }

                $recibeNotificaciones = true;
                if (isset($columnIndexes['recibe_notificaciones']) && isset($row[$columnIndexes['recibe_notificaciones']])) {
                    $recibeNotValue = trim($row[$columnIndexes['recibe_notificaciones']]);
                    $recibeNotificaciones = !in_array(strtolower($recibeNotValue), ['0', 'no', 'false', 'falso']);
                }

                $observaciones = isset($columnIndexes['observaciones']) && isset($row[$columnIndexes['observaciones']]) 
                    ? trim($row[$columnIndexes['observaciones']]) : null;

                // Buscar residente existente (mismo usuario y misma unidad)
                $residente = Residente::where('user_id', $user->id)
                    ->where('unidad_id', $unidad->id)
                    ->first();

                $dataResidente = [
                    'user_id' => $user->id,
                    'unidad_id' => $unidad->id,
                    'tipo_relacion' => $tipoRelacion,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'es_principal' => $esPrincipal,
                    'recibe_notificaciones' => $recibeNotificaciones,
                    'observaciones' => $observaciones,
                ];

                if ($residente) {
                    $residente->update($dataResidente);
                    $actualizados++;
                } else {
                    Residente::create($dataResidente);
                    $creados++;
                }
            }

            DB::commit();

            $mensaje = "Importación exitosa. Se han creado {$creados} registro(s), se han actualizado {$actualizados} registro(s)";
            if ($ignorados > 0) {
                $mensaje .= " y se han ignorado {$ignorados} registro(s) sin número de unidad o sin email/documento.";
            }

            return redirect()->route('admin.residentes.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al importar residentes: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar el formulario de creación de un residente
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $unidades = Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('torre')
            ->orderBy('bloque')
            ->orderBy('numero')
            ->get();

        return view('admin.residentes.create', compact('unidades', 'propiedad'));
    }

    /**
     * Guardar un nuevo residente
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'documento_identidad' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'unidad_id' => 'required|exists:unidades,id',
            'tipo_relacion' => 'required|in:propietario,arrendatario,residente_temporal,otro',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'es_principal' => 'boolean',
            'recibe_notificaciones' => 'boolean',
            'observaciones' => 'nullable|string',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'email.required' => 'El campo email es obligatorio.',
            'email.email' => 'El email debe ser una dirección de correo válida.',
            'email.unique' => 'Este email ya está registrado por otro usuario.',
            'unidad_id.required' => 'El campo unidad es obligatorio.',
            'unidad_id.exists' => 'La unidad seleccionada no es válida.',
            'tipo_relacion.required' => 'El campo tipo de relación es obligatorio.',
            'tipo_relacion.in' => 'El tipo de relación seleccionado no es válido.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ]);

        // Verificar que la unidad pertenezca a la propiedad
        $unidad = Unidad::find($request->unidad_id);
        if (!$unidad || $unidad->propiedad_id != $propiedad->id) {
            return back()->with('error', 'La unidad seleccionada no pertenece a su propiedad.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Crear usuario
            $user = User::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'password' => Hash::make('password123'), // Password temporal
                'documento_identidad' => $request->documento_identidad,
                'telefono' => $request->telefono,
                'activo' => true,
            ]);

            // Crear residente
            $residente = Residente::create([
                'user_id' => $user->id,
                'unidad_id' => $request->unidad_id,
                'tipo_relacion' => $request->tipo_relacion,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'es_principal' => $request->has('es_principal'),
                'recibe_notificaciones' => $request->has('recibe_notificaciones'),
                'observaciones' => $request->observaciones,
            ]);

            DB::commit();

            return redirect()->route('admin.residentes.index')
                ->with('success', 'Residente creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear residente: ' . $e->getMessage());
            return back()->with('error', 'Error al crear el residente: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar el formulario de edición de un residente
     */
    public function edit(Residente $residente)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que el residente pertenezca a una unidad de la propiedad activa
        $unidad = $residente->unidad;
        if (!$unidad || $unidad->propiedad_id != $propiedad->id) {
            return redirect()->route('admin.residentes.index')
                ->with('error', 'No tiene permisos para editar este residente.');
        }

        $unidades = Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('torre')
            ->orderBy('bloque')
            ->orderBy('numero')
            ->get();

        return view('admin.residentes.edit', compact('residente', 'unidades', 'propiedad'));
    }

    /**
     * Actualizar un residente
     */
    public function update(Request $request, Residente $residente)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que el residente pertenezca a una unidad de la propiedad activa
        $unidad = $residente->unidad;
        if (!$unidad || $unidad->propiedad_id != $propiedad->id) {
            return redirect()->route('admin.residentes.index')
                ->with('error', 'No tiene permisos para editar este residente.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $residente->user_id,
            'documento_identidad' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'unidad_id' => 'required|exists:unidades,id',
            'tipo_relacion' => 'required|in:propietario,arrendatario,residente_temporal,otro',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'es_principal' => 'boolean',
            'recibe_notificaciones' => 'boolean',
            'observaciones' => 'nullable|string',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'email.required' => 'El campo email es obligatorio.',
            'email.email' => 'El email debe ser una dirección de correo válida.',
            'email.unique' => 'Este email ya está registrado por otro usuario.',
            'unidad_id.required' => 'El campo unidad es obligatorio.',
            'unidad_id.exists' => 'La unidad seleccionada no es válida.',
            'tipo_relacion.required' => 'El campo tipo de relación es obligatorio.',
            'tipo_relacion.in' => 'El tipo de relación seleccionado no es válido.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ]);

        // Verificar que la unidad pertenezca a la propiedad
        $unidadNueva = Unidad::find($request->unidad_id);
        if (!$unidadNueva || $unidadNueva->propiedad_id != $propiedad->id) {
            return back()->with('error', 'La unidad seleccionada no pertenece a su propiedad.')
                ->withInput();
        }

        try {
            // Actualizar información del usuario
            $user = $residente->user;
            if ($user) {
                $user->update([
                    'nombre' => $request->nombre,
                    'email' => $request->email,
                    'documento_identidad' => $request->documento_identidad,
                    'telefono' => $request->telefono,
                ]);
            }

            // Actualizar información del residente
            $residente->update([
                'unidad_id' => $request->unidad_id,
                'tipo_relacion' => $request->tipo_relacion,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'es_principal' => $request->has('es_principal'),
                'recibe_notificaciones' => $request->has('recibe_notificaciones'),
                'observaciones' => $request->observaciones,
            ]);

            return redirect()->route('admin.residentes.index')
                ->with('success', 'Residente actualizado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al actualizar residente: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar el residente: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Eliminar un residente (soft delete)
     */
    public function destroy(Residente $residente)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que el residente pertenezca a una unidad de la propiedad activa
        $unidad = $residente->unidad;
        if (!$unidad || $unidad->propiedad_id != $propiedad->id) {
            return redirect()->route('admin.residentes.index')
                ->with('error', 'No tiene permisos para eliminar este residente.');
        }

        try {
            $residente->delete();

            return redirect()->route('admin.residentes.index')
                ->with('success', 'Residente eliminado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al eliminar residente: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el residente: ' . $e->getMessage());
        }
    }
}
