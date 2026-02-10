<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\MascotaRegistrada;
use App\Models\Mascota;
use App\Models\Unidad;
use App\Models\Residente;
use App\Helpers\AdminHelper;
use App\Services\PlantillaMascotasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class MascotaController extends Controller
{
    /**
     * Mostrar la lista de mascotas con opción de carga masiva
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener todas las unidades de la propiedad para filtrar mascotas
        $unidadesIds = Unidad::where('propiedad_id', $propiedad->id)->pluck('id');
        
        $query = Mascota::whereIn('unidad_id', $unidadesIds)
            ->with(['unidad', 'residente.user']);

        // Búsqueda general (nombre de mascota, raza, número de chip)
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('raza', 'like', "%{$buscar}%")
                  ->orWhere('numero_chip', 'like', "%{$buscar}%");
            });
        }

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por unidad
        if ($request->filled('unidad_id')) {
            $query->where('unidad_id', $request->unidad_id);
        }

        // Filtro por residente
        if ($request->filled('residente_id')) {
            $query->where('residente_id', $request->residente_id);
        }

        // Filtro por vacunado
        if ($request->filled('vacunado')) {
            $query->where('vacunado', $request->vacunado == '1');
        }

        // Filtro por activo
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo == '1');
        }

        $mascotas = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Obtener unidades para el filtro
        $unidades = Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('torre')
            ->orderBy('bloque')
            ->orderBy('numero')
            ->get();

        // Obtener tipos únicos
        $tiposUnicos = Mascota::whereIn('unidad_id', $unidadesIds)
            ->groupBy('tipo')
            ->pluck('tipo')
            ->unique()
            ->values();

        return view('admin.mascotas.index', compact('mascotas', 'unidades', 'tiposUnicos', 'propiedad'));
    }

    /**
     * Mostrar el formulario de creación de una mascota
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $unidades = Unidad::where('propiedad_id', $propiedad->id)
            ->with(['residentes.user'])
            ->orderBy('torre')
            ->orderBy('bloque')
            ->orderBy('numero')
            ->get();

        return view('admin.mascotas.create', compact('unidades', 'propiedad'));
    }

    /**
     * Guardar una nueva mascota
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $request->validate([
            'unidad_id' => 'required|exists:unidades,id',
            'residente_id' => 'required|exists:residentes,id',
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:perro,gato,ave,reptil,roedor,otro',
            'raza' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'sexo' => 'required|in:macho,hembra,desconocido',
            'fecha_nacimiento' => 'nullable|date',
            'edad_aproximada' => 'nullable|integer|min:0',
            'peso_kg' => 'nullable|numeric|min:0',
            'tamanio' => 'nullable|in:pequeño,mediano,grande',
            'numero_chip' => 'nullable|string|max:100',
            'vacunado' => 'boolean',
            'esterilizado' => 'boolean',
            'estado_salud' => 'nullable|in:saludable,en_tratamiento,crónico,desconocido',
            'fecha_vigencia_vacunas' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
            'foto_mascota' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_vacunacion' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], [
            'unidad_id.required' => 'El campo unidad es obligatorio.',
            'unidad_id.exists' => 'La unidad seleccionada no es válida.',
            'residente_id.required' => 'El campo residente es obligatorio.',
            'residente_id.exists' => 'El residente seleccionado no es válido.',
            'nombre.required' => 'El campo nombre es obligatorio.',
            'tipo.required' => 'El campo tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
            'sexo.required' => 'El campo sexo es obligatorio.',
            'sexo.in' => 'El sexo seleccionado no es válido.',
            'foto_mascota.image' => 'El archivo de foto de mascota debe ser una imagen.',
            'foto_mascota.mimes' => 'La foto de mascota debe ser jpeg, png, jpg o gif.',
            'foto_mascota.max' => 'La foto de mascota no debe ser mayor a 5MB.',
            'foto_vacunacion.image' => 'El archivo de foto de vacunación debe ser una imagen.',
            'foto_vacunacion.mimes' => 'La foto de vacunación debe ser jpeg, png, jpg o gif.',
            'foto_vacunacion.max' => 'La foto de vacunación no debe ser mayor a 5MB.',
        ]);

        // Verificar que la unidad y el residente pertenezcan a la propiedad
        $unidad = Unidad::find($request->unidad_id);
        $residente = Residente::find($request->residente_id);

        if (!$unidad || $unidad->propiedad_id != $propiedad->id) {
            return back()->with('error', 'La unidad seleccionada no pertenece a su propiedad.')
                ->withInput();
        }

        if (!$residente || $residente->unidad_id != $unidad->id) {
            return back()->with('error', 'El residente seleccionado no pertenece a la unidad seleccionada.')
                ->withInput();
        }

        try {
            $dataCreate = [
                'unidad_id' => $request->unidad_id,
                'residente_id' => $request->residente_id,
                'nombre' => $request->nombre,
                'tipo' => $request->tipo,
                'raza' => $request->raza,
                'color' => $request->color,
                'sexo' => $request->sexo,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'edad_aproximada' => $request->edad_aproximada,
                'peso_kg' => $request->peso_kg,
                'tamanio' => $request->tamanio,
                'numero_chip' => $request->numero_chip,
                'vacunado' => $request->has('vacunado'),
                'esterilizado' => $request->has('esterilizado'),
                'estado_salud' => $request->estado_salud,
                'fecha_vigencia_vacunas' => $request->fecha_vigencia_vacunas,
                'observaciones' => $request->observaciones,
                'activo' => $request->has('activo'),
            ];

            // Subir foto de la mascota si se proporciona
            if ($request->hasFile('foto_mascota')) {
                try {
                    $result = Cloudinary::uploadApi()->upload($request->file('foto_mascota')->getRealPath(), [
                        'folder' => 'domoph/mascotas',
                        'public_id' => 'foto_mascota_' . time() . '_' . uniqid(),
                    ]);
                    
                    $dataCreate['foto_url'] = $result['secure_url'] ?? $result['url'] ?? null;
                    
                    if (!$dataCreate['foto_url']) {
                        throw new \Exception('No se pudo obtener la URL de la imagen subida.');
                    }
                } catch (\Exception $e) {
                    \Log::error('Error al subir foto de mascota a Cloudinary: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                    return back()->with('error', 'Error al subir la foto de la mascota: ' . $e->getMessage())->withInput();
                }
            }

            // Subir foto de vacunación si se proporciona
            if ($request->hasFile('foto_vacunacion')) {
                try {
                    $result = Cloudinary::uploadApi()->upload($request->file('foto_vacunacion')->getRealPath(), [
                        'folder' => 'domoph/mascotas',
                        'public_id' => 'foto_vacunas_' . time() . '_' . uniqid(),
                    ]);
                    
                    $dataCreate['foto_url_vacunas'] = $result['secure_url'] ?? $result['url'] ?? null;
                    
                    if (!$dataCreate['foto_url_vacunas']) {
                        throw new \Exception('No se pudo obtener la URL de la imagen subida.');
                    }
                } catch (\Exception $e) {
                    \Log::error('Error al subir foto de vacunación a Cloudinary: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                    return back()->with('error', 'Error al subir la foto de vacunación: ' . $e->getMessage())->withInput();
                }
            }

            $mascota = Mascota::create($dataCreate);

            // Enviar correo al residente asignado
            try {
                $residente = $mascota->residente;
                if ($residente && $residente->user) {
                    $email = $residente->user->email;
                    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        Mail::to($email)->send(new MascotaRegistrada($mascota));
                    } else {
                        \Log::warning("No se pudo enviar correo de mascota registrada: email inválido para residente {$residente->id}");
                    }
                }
            } catch (\Exception $emailException) {
                // Log del error pero no fallar la creación de la mascota
                \Log::error('Error al enviar email de mascota registrada: ' . $emailException->getMessage());
            }

            return redirect()->route('admin.mascotas.index')
                ->with('success', 'Mascota creada correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al crear mascota: ' . $e->getMessage());
            return back()->with('error', 'Error al crear la mascota: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Descargar plantilla Excel para importar mascotas
     */
    public function downloadTemplate()
    {
        $rutaPlantilla = public_path('plantillas/plantilla_mascotas.xlsx');
        
        // Si no existe, crearla
        if (!file_exists($rutaPlantilla)) {
            PlantillaMascotasService::crearPlantilla($rutaPlantilla);
        }
        
        return response()->download($rutaPlantilla, 'plantilla_mascotas.xlsx');
    }

    /**
     * Importar mascotas desde archivo Excel/CSV
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
                'identificacion_residente' => ['identificacion_residente', 'identificacion', 'documento', 'documento_identidad', 'cedula', 'cédula', 'dni', 'residente_identificacion'],
                'nombre' => ['nombre', 'nombre_mascota', 'mascota_nombre'],
                'tipo' => ['tipo', 'tipo_mascota'],
                'raza' => ['raza'],
                'color' => ['color'],
                'sexo' => ['sexo'],
                'fecha_nacimiento' => ['fecha_nacimiento', 'nacimiento', 'fecha_nac'],
                'edad_aproximada' => ['edad_aproximada', 'edad', 'edad_aprox'],
                'peso_kg' => ['peso_kg', 'peso', 'peso_kg'],
                'tamanio' => ['tamanio', 'tamaño', 'size'],
                'numero_chip' => ['numero_chip', 'chip', 'numero_chip'],
                'vacunado' => ['vacunado', 'vacunas'],
                'esterilizado' => ['esterilizado', 'castrado'],
                'estado_salud' => ['estado_salud', 'salud', 'estado'],
                'fecha_vigencia_vacunas' => ['fecha_vigencia_vacunas', 'vigencia_vacunas', 'vigencia'],
                'observaciones' => ['observaciones', 'observacion', 'notas'],
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

            if (!isset($columnIndexes['nombre'])) {
                return back()->with('error', 'No se encontró la columna "nombre" en el archivo. Esta columna es obligatoria.');
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
                    continue;
                }

                // Buscar residente por identificación
                $identificacionResidente = isset($columnIndexes['identificacion_residente']) && isset($row[$columnIndexes['identificacion_residente']]) 
                    ? trim($row[$columnIndexes['identificacion_residente']]) : null;

                $residente = null;
                
                // Si viene identificación, buscar por documento_identidad en users
                if ($identificacionResidente && !empty($identificacionResidente)) {
                    $user = \App\Models\User::where('documento_identidad', $identificacionResidente)->first();
                    if ($user) {
                        $residente = Residente::where('user_id', $user->id)
                            ->where('unidad_id', $unidad->id)
                            ->first();
                    }
                }
                
                // Si no se encontró residente, buscar el primer residente de la unidad
                if (!$residente) {
                    $residente = Residente::where('unidad_id', $unidad->id)->first();
                }

                if (!$residente) {
                    $ignorados++;
                    continue;
                }

                // Validar nombre de mascota (obligatorio)
                $nombreMascota = isset($row[$columnIndexes['nombre']]) ? trim($row[$columnIndexes['nombre']]) : '';
                if (empty($nombreMascota)) {
                    $ignorados++;
                    continue;
                }

                // Preparar datos de la mascota
                $tipo = isset($columnIndexes['tipo']) && isset($row[$columnIndexes['tipo']]) 
                    ? strtolower(trim($row[$columnIndexes['tipo']])) : 'otro';
                
                $allowedTipos = Mascota::getTipos();
                if (!in_array($tipo, $allowedTipos)) {
                    $tipo = 'otro';
                }

                $sexo = isset($columnIndexes['sexo']) && isset($row[$columnIndexes['sexo']]) 
                    ? strtolower(trim($row[$columnIndexes['sexo']])) : 'desconocido';
                
                $allowedSexos = Mascota::getSexos();
                if (!in_array($sexo, $allowedSexos)) {
                    $sexo = 'desconocido';
                }

                $fechaNacimiento = null;
                if (isset($columnIndexes['fecha_nacimiento']) && isset($row[$columnIndexes['fecha_nacimiento']])) {
                    $fechaNacValue = trim($row[$columnIndexes['fecha_nacimiento']]);
                    if ($fechaNacValue) {
                        try {
                            $fechaNacimiento = \Carbon\Carbon::parse($fechaNacValue)->format('Y-m-d');
                        } catch (\Exception $e) {
                            // Ignorar fecha inválida
                        }
                    }
                }

                $fechaVigenciaVacunas = null;
                if (isset($columnIndexes['fecha_vigencia_vacunas']) && isset($row[$columnIndexes['fecha_vigencia_vacunas']])) {
                    $fechaVigValue = trim($row[$columnIndexes['fecha_vigencia_vacunas']]);
                    if ($fechaVigValue) {
                        try {
                            $fechaVigenciaVacunas = \Carbon\Carbon::parse($fechaVigValue)->format('Y-m-d');
                        } catch (\Exception $e) {
                            // Ignorar fecha inválida
                        }
                    }
                }

                $vacunado = false;
                if (isset($columnIndexes['vacunado']) && isset($row[$columnIndexes['vacunado']])) {
                    $vacunadoValue = trim($row[$columnIndexes['vacunado']]);
                    $vacunado = in_array(strtolower($vacunadoValue), ['1', 'si', 'sí', 'yes', 'true', 'verdadero']);
                }

                $esterilizado = false;
                if (isset($columnIndexes['esterilizado']) && isset($row[$columnIndexes['esterilizado']])) {
                    $esterilizadoValue = trim($row[$columnIndexes['esterilizado']]);
                    $esterilizado = in_array(strtolower($esterilizadoValue), ['1', 'si', 'sí', 'yes', 'true', 'verdadero']);
                }

                $tamanio = null;
                if (isset($columnIndexes['tamanio']) && isset($row[$columnIndexes['tamanio']])) {
                    $tamanioValue = strtolower(trim($row[$columnIndexes['tamanio']]));
                    $allowedTamanios = Mascota::getTamanios();
                    if (in_array($tamanioValue, $allowedTamanios)) {
                        $tamanio = $tamanioValue;
                    }
                }

                $estadoSalud = null;
                if (isset($columnIndexes['estado_salud']) && isset($row[$columnIndexes['estado_salud']])) {
                    $estadoSaludValue = strtolower(trim($row[$columnIndexes['estado_salud']]));
                    $allowedEstados = Mascota::getEstadosSalud();
                    if (in_array($estadoSaludValue, $allowedEstados)) {
                        $estadoSalud = $estadoSaludValue;
                    }
                }

                // Buscar mascota existente (mismo nombre, misma unidad y mismo residente)
                $mascota = Mascota::where('unidad_id', $unidad->id)
                    ->where('residente_id', $residente->id)
                    ->where('nombre', $nombreMascota)
                    ->first();

                $dataMascota = [
                    'unidad_id' => $unidad->id,
                    'residente_id' => $residente->id,
                    'nombre' => $nombreMascota,
                    'tipo' => $tipo,
                    'raza' => isset($columnIndexes['raza']) && isset($row[$columnIndexes['raza']]) 
                        ? trim($row[$columnIndexes['raza']]) : null,
                    'color' => isset($columnIndexes['color']) && isset($row[$columnIndexes['color']]) 
                        ? trim($row[$columnIndexes['color']]) : null,
                    'sexo' => $sexo,
                    'fecha_nacimiento' => $fechaNacimiento,
                    'edad_aproximada' => isset($columnIndexes['edad_aproximada']) && isset($row[$columnIndexes['edad_aproximada']]) 
                        ? (int)trim($row[$columnIndexes['edad_aproximada']]) : null,
                    'peso_kg' => isset($columnIndexes['peso_kg']) && isset($row[$columnIndexes['peso_kg']]) 
                        ? (float)trim($row[$columnIndexes['peso_kg']]) : null,
                    'tamanio' => $tamanio,
                    'numero_chip' => isset($columnIndexes['numero_chip']) && isset($row[$columnIndexes['numero_chip']]) 
                        ? trim($row[$columnIndexes['numero_chip']]) : null,
                    'vacunado' => $vacunado,
                    'esterilizado' => $esterilizado,
                    'estado_salud' => $estadoSalud,
                    'fecha_vigencia_vacunas' => $fechaVigenciaVacunas,
                    'observaciones' => isset($columnIndexes['observaciones']) && isset($row[$columnIndexes['observaciones']]) 
                        ? trim($row[$columnIndexes['observaciones']]) : null,
                    'activo' => true,
                ];

                if ($mascota) {
                    $mascota->update($dataMascota);
                    $actualizados++;
                } else {
                    $mascota = Mascota::create($dataMascota);
                    $creados++;
                    
                    // Enviar correo al residente asignado solo si es una nueva mascota
                    try {
                        $residente = $mascota->residente;
                        if ($residente && $residente->user) {
                            $email = $residente->user->email;
                            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                Mail::to($email)->send(new MascotaRegistrada($mascota));
                            }
                        }
                    } catch (\Exception $emailException) {
                        // Log del error pero no fallar la importación
                        \Log::error('Error al enviar email de mascota registrada durante importación: ' . $emailException->getMessage());
                    }
                }
            }

            DB::commit();

            $mensaje = "Importación exitosa. Se han creado {$creados} registro(s), se han actualizado {$actualizados} registro(s)";
            if ($ignorados > 0) {
                $mensaje .= " y se han ignorado {$ignorados} registro(s) sin número de unidad, nombre de mascota o residente válido.";
            }

            return redirect()->route('admin.mascotas.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al importar mascotas: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar el formulario de edición de una mascota
     */
    public function edit(Mascota $mascota)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que la mascota pertenezca a una unidad de la propiedad activa
        $unidad = $mascota->unidad;
        if (!$unidad || $unidad->propiedad_id != $propiedad->id) {
            return redirect()->route('admin.mascotas.index')
                ->with('error', 'No tiene permisos para editar esta mascota.');
        }

        $unidades = Unidad::where('propiedad_id', $propiedad->id)
            ->with('residentes.user')
            ->orderBy('torre')
            ->orderBy('bloque')
            ->orderBy('numero')
            ->get();

        return view('admin.mascotas.edit', compact('mascota', 'unidades', 'propiedad'));
    }

    /**
     * Actualizar una mascota
     */
    public function update(Request $request, Mascota $mascota)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que la mascota pertenezca a una unidad de la propiedad activa
        $unidad = $mascota->unidad;
        if (!$unidad || $unidad->propiedad_id != $propiedad->id) {
            return redirect()->route('admin.mascotas.index')
                ->with('error', 'No tiene permisos para editar esta mascota.');
        }

        $request->validate([
            'unidad_id' => 'required|exists:unidades,id',
            'residente_id' => 'required|exists:residentes,id',
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:perro,gato,ave,reptil,roedor,otro',
            'raza' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'sexo' => 'required|in:macho,hembra,desconocido',
            'fecha_nacimiento' => 'nullable|date',
            'edad_aproximada' => 'nullable|integer|min:0',
            'peso_kg' => 'nullable|numeric|min:0',
            'tamanio' => 'nullable|in:pequeño,mediano,grande',
            'numero_chip' => 'nullable|string|max:100',
            'vacunado' => 'boolean',
            'esterilizado' => 'boolean',
            'estado_salud' => 'nullable|in:saludable,en_tratamiento,crónico,desconocido',
            'fecha_vigencia_vacunas' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
            'foto_mascota' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_vacunacion' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], [
            'unidad_id.required' => 'El campo unidad es obligatorio.',
            'unidad_id.exists' => 'La unidad seleccionada no es válida.',
            'residente_id.required' => 'El campo residente es obligatorio.',
            'residente_id.exists' => 'El residente seleccionado no es válido.',
            'nombre.required' => 'El campo nombre es obligatorio.',
            'tipo.required' => 'El campo tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
            'sexo.required' => 'El campo sexo es obligatorio.',
            'sexo.in' => 'El sexo seleccionado no es válido.',
            'foto_mascota.image' => 'El archivo de foto de mascota debe ser una imagen.',
            'foto_mascota.mimes' => 'La foto de mascota debe ser jpeg, png, jpg o gif.',
            'foto_mascota.max' => 'La foto de mascota no debe ser mayor a 5MB.',
            'foto_vacunacion.image' => 'El archivo de foto de vacunación debe ser una imagen.',
            'foto_vacunacion.mimes' => 'La foto de vacunación debe ser jpeg, png, jpg o gif.',
            'foto_vacunacion.max' => 'La foto de vacunación no debe ser mayor a 5MB.',
        ]);

        // Verificar que la unidad y el residente pertenezcan a la propiedad
        $unidadNueva = Unidad::find($request->unidad_id);
        $residenteNuevo = Residente::find($request->residente_id);

        if (!$unidadNueva || $unidadNueva->propiedad_id != $propiedad->id) {
            return back()->with('error', 'La unidad seleccionada no pertenece a su propiedad.')
                ->withInput();
        }

        if (!$residenteNuevo || $residenteNuevo->unidad_id != $unidadNueva->id) {
            return back()->with('error', 'El residente seleccionado no pertenece a la unidad seleccionada.')
                ->withInput();
        }

        try {
            $dataUpdate = [
                'unidad_id' => $request->unidad_id,
                'residente_id' => $request->residente_id,
                'nombre' => $request->nombre,
                'tipo' => $request->tipo,
                'raza' => $request->raza,
                'color' => $request->color,
                'sexo' => $request->sexo,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'edad_aproximada' => $request->edad_aproximada,
                'peso_kg' => $request->peso_kg,
                'tamanio' => $request->tamanio,
                'numero_chip' => $request->numero_chip,
                'vacunado' => $request->has('vacunado'),
                'esterilizado' => $request->has('esterilizado'),
                'estado_salud' => $request->estado_salud,
                'fecha_vigencia_vacunas' => $request->fecha_vigencia_vacunas,
                'observaciones' => $request->observaciones,
                'activo' => $request->has('activo'),
            ];

            // Subir foto de la mascota si se proporciona
            if ($request->hasFile('foto_mascota')) {
                try {
                    $result = Cloudinary::uploadApi()->upload($request->file('foto_mascota')->getRealPath(), [
                        'folder' => 'domoph/mascotas',
                        'public_id' => 'foto_mascota_' . $mascota->id . '_' . time(),
                    ]);
                    
                    $dataUpdate['foto_url'] = $result['secure_url'] ?? $result['url'] ?? null;
                    
                    if (!$dataUpdate['foto_url']) {
                        throw new \Exception('No se pudo obtener la URL de la imagen subida.');
                    }
                } catch (\Exception $e) {
                    \Log::error('Error al subir foto de mascota a Cloudinary: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                    return back()->with('error', 'Error al subir la foto de la mascota: ' . $e->getMessage())->withInput();
                }
            }

            // Subir foto de vacunación si se proporciona
            if ($request->hasFile('foto_vacunacion')) {
                try {
                    $result = Cloudinary::uploadApi()->upload($request->file('foto_vacunacion')->getRealPath(), [
                        'folder' => 'domoph/mascotas',
                        'public_id' => 'foto_vacunas_' . $mascota->id . '_' . time(),
                    ]);
                    
                    $dataUpdate['foto_url_vacunas'] = $result['secure_url'] ?? $result['url'] ?? null;
                    
                    if (!$dataUpdate['foto_url_vacunas']) {
                        throw new \Exception('No se pudo obtener la URL de la imagen subida.');
                    }
                } catch (\Exception $e) {
                    \Log::error('Error al subir foto de vacunación a Cloudinary: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                    return back()->with('error', 'Error al subir la foto de vacunación: ' . $e->getMessage())->withInput();
                }
            }

            $mascota->update($dataUpdate);

            return redirect()->route('admin.mascotas.index')
                ->with('success', 'Mascota actualizada correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al actualizar mascota: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar la mascota: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Eliminar una mascota (soft delete)
     */
    public function destroy(Mascota $mascota)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que la mascota pertenezca a una unidad de la propiedad activa
        $unidad = $mascota->unidad;
        if (!$unidad || $unidad->propiedad_id != $propiedad->id) {
            return redirect()->route('admin.mascotas.index')
                ->with('error', 'No tiene permisos para eliminar esta mascota.');
        }

        try {
            $mascota->delete();

            return redirect()->route('admin.mascotas.index')
                ->with('success', 'Mascota eliminada correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al eliminar mascota: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la mascota: ' . $e->getMessage());
        }
    }
}
