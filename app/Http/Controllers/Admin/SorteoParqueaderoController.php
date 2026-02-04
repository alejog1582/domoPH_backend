<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SorteoParqueadero;
use App\Models\ParticipanteSorteoParqueadero;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SorteoParqueaderoController extends Controller
{
    /**
     * Mostrar la lista de sorteos
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Query base: sorteos con sus relaciones
        $query = SorteoParqueadero::with(['copropiedad', 'creadoPor', 'participantes'])
            ->where('copropiedad_id', $propiedad->id);

        // Filtro por estado
        if ($request->filled('estado')) {
            if ($request->estado !== 'todos') {
                $query->where('estado', $request->estado);
            }
        }

        // Filtro por búsqueda de título
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where('titulo', 'like', "%{$buscar}%");
        }

        // Filtro por fecha de sorteo desde
        if ($request->filled('fecha_sorteo_desde')) {
            $query->whereDate('fecha_sorteo', '>=', $request->fecha_sorteo_desde);
        }

        // Filtro por fecha de sorteo hasta
        if ($request->filled('fecha_sorteo_hasta')) {
            $query->whereDate('fecha_sorteo', '<=', $request->fecha_sorteo_hasta);
        }

        // Filtro por activo
        if ($request->filled('activo')) {
            if ($request->activo !== 'todos') {
                $query->where('activo', $request->activo == 'si');
            }
        }

        // Ordenar por fecha de creación (más recientes primero)
        $query->orderBy('created_at', 'desc');

        // Paginación
        $sorteos = $query->paginate(20)->withQueryString();

        return view('admin.sorteos-parqueadero.index', compact('sorteos'));
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

        // Contar parqueaderos disponibles por tipo de vehículo
        $cantidadParqueaderosCarro = \App\Models\Parqueadero::where('copropiedad_id', $propiedad->id)
            ->where('tipo_vehiculo', 'carro')
            ->where('activo', true)
            ->count();

        $cantidadParqueaderosMoto = \App\Models\Parqueadero::where('copropiedad_id', $propiedad->id)
            ->where('tipo_vehiculo', 'moto')
            ->where('activo', true)
            ->count();

        return view('admin.sorteos-parqueadero.create', compact(
            'propiedad',
            'cantidadParqueaderosCarro',
            'cantidadParqueaderosMoto'
        ));
    }

    /**
     * Guardar un nuevo sorteo
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'fecha_inicio_recoleccion' => 'required|date',
            'fecha_fin_recoleccion' => 'required|date|after_or_equal:fecha_inicio_recoleccion',
            'fecha_sorteo' => 'required|date|after_or_equal:fecha_fin_recoleccion',
            'hora_sorteo' => 'nullable|date_format:H:i',
            'fecha_inicio_uso' => 'nullable|date|after_or_equal:fecha_sorteo',
            'duracion_meses' => 'nullable|integer|min:1',
            'capacidad_autos' => 'required|integer|min:0',
            'capacidad_motos' => 'required|integer|min:0',
            'balotas_blancas_carro' => 'nullable|integer|min:0',
            'balotas_blancas_moto' => 'nullable|integer|min:0',
            'estado' => 'required|in:borrador,activo,cerrado,anulado',
            'activo' => 'boolean',
        ], [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no puede exceder 150 caracteres.',
            'fecha_inicio_recoleccion.required' => 'La fecha de inicio de recolección es obligatoria.',
            'fecha_fin_recoleccion.required' => 'La fecha de fin de recolección es obligatoria.',
            'fecha_fin_recoleccion.after_or_equal' => 'La fecha de fin de recolección debe ser posterior o igual a la fecha de inicio.',
            'fecha_sorteo.required' => 'La fecha del sorteo es obligatoria.',
            'fecha_sorteo.after_or_equal' => 'La fecha del sorteo debe ser posterior o igual a la fecha de fin de recolección.',
            'hora_sorteo.date_format' => 'La hora del sorteo debe tener el formato HH:MM.',
            'fecha_inicio_uso.after_or_equal' => 'La fecha de inicio de uso debe ser posterior o igual a la fecha del sorteo.',
            'duracion_meses.integer' => 'La duración en meses debe ser un número entero.',
            'duracion_meses.min' => 'La duración en meses debe ser al menos 1 mes.',
            'capacidad_autos.required' => 'La capacidad de autos es obligatoria.',
            'capacidad_autos.integer' => 'La capacidad de autos debe ser un número entero.',
            'capacidad_autos.min' => 'La capacidad de autos no puede ser negativa.',
            'capacidad_motos.required' => 'La capacidad de motos es obligatoria.',
            'capacidad_motos.integer' => 'La capacidad de motos debe ser un número entero.',
            'capacidad_motos.min' => 'La capacidad de motos no puede ser negativa.',
            'balotas_blancas_carro.integer' => 'Las balotas blancas para carros debe ser un número entero.',
            'balotas_blancas_carro.min' => 'Las balotas blancas para carros no puede ser negativa.',
            'balotas_blancas_moto.integer' => 'Las balotas blancas para motos debe ser un número entero.',
            'balotas_blancas_moto.min' => 'Las balotas blancas para motos no puede ser negativa.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado no es válido.',
        ]);

        $sorteo = SorteoParqueadero::create([
            'copropiedad_id' => $propiedad->id,
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'fecha_inicio_recoleccion' => $validated['fecha_inicio_recoleccion'],
            'fecha_fin_recoleccion' => $validated['fecha_fin_recoleccion'],
            'fecha_sorteo' => $validated['fecha_sorteo'],
            'hora_sorteo' => $validated['hora_sorteo'] ?? null,
            'fecha_inicio_uso' => $validated['fecha_inicio_uso'] ?? null,
            'duracion_meses' => $validated['duracion_meses'] ?? null,
            'capacidad_autos' => $validated['capacidad_autos'],
            'capacidad_motos' => $validated['capacidad_motos'],
            'balotas_blancas_carro' => $validated['balotas_blancas_carro'] ?? 0,
            'balotas_blancas_moto' => $validated['balotas_blancas_moto'] ?? 0,
            'estado' => $validated['estado'],
            'creado_por' => Auth::id(),
            'activo' => $request->has('activo') ? (bool)$request->activo : true,
        ]);

        return redirect()->route('admin.sorteos-parqueadero.index')
            ->with('success', 'Sorteo creado correctamente.');
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

        $sorteo = SorteoParqueadero::where('copropiedad_id', $propiedad->id)
            ->findOrFail($id);

        return view('admin.sorteos-parqueadero.edit', compact('sorteo', 'propiedad'));
    }

    /**
     * Actualizar un sorteo
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $sorteo = SorteoParqueadero::where('copropiedad_id', $propiedad->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'fecha_inicio_recoleccion' => 'required|date',
            'fecha_fin_recoleccion' => 'required|date|after_or_equal:fecha_inicio_recoleccion',
            'fecha_sorteo' => 'required|date|after_or_equal:fecha_fin_recoleccion',
            'hora_sorteo' => 'nullable|date_format:H:i',
            'fecha_inicio_uso' => 'nullable|date|after_or_equal:fecha_sorteo',
            'duracion_meses' => 'nullable|integer|min:1',
            'capacidad_autos' => 'required|integer|min:0',
            'capacidad_motos' => 'required|integer|min:0',
            'balotas_blancas_carro' => 'nullable|integer|min:0',
            'balotas_blancas_moto' => 'nullable|integer|min:0',
            'estado' => 'required|in:borrador,activo,cerrado,anulado',
            'activo' => 'boolean',
        ], [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no puede exceder 150 caracteres.',
            'fecha_inicio_recoleccion.required' => 'La fecha de inicio de recolección es obligatoria.',
            'fecha_fin_recoleccion.required' => 'La fecha de fin de recolección es obligatoria.',
            'fecha_fin_recoleccion.after_or_equal' => 'La fecha de fin de recolección debe ser posterior o igual a la fecha de inicio.',
            'fecha_sorteo.required' => 'La fecha del sorteo es obligatoria.',
            'fecha_sorteo.after_or_equal' => 'La fecha del sorteo debe ser posterior o igual a la fecha de fin de recolección.',
            'hora_sorteo.date_format' => 'La hora del sorteo debe tener el formato HH:MM.',
            'fecha_inicio_uso.after_or_equal' => 'La fecha de inicio de uso debe ser posterior o igual a la fecha del sorteo.',
            'duracion_meses.integer' => 'La duración en meses debe ser un número entero.',
            'duracion_meses.min' => 'La duración en meses debe ser al menos 1 mes.',
            'capacidad_autos.required' => 'La capacidad de autos es obligatoria.',
            'capacidad_autos.integer' => 'La capacidad de autos debe ser un número entero.',
            'capacidad_autos.min' => 'La capacidad de autos no puede ser negativa.',
            'capacidad_motos.required' => 'La capacidad de motos es obligatoria.',
            'capacidad_motos.integer' => 'La capacidad de motos debe ser un número entero.',
            'capacidad_motos.min' => 'La capacidad de motos no puede ser negativa.',
            'balotas_blancas_carro.integer' => 'Las balotas blancas para carros debe ser un número entero.',
            'balotas_blancas_carro.min' => 'Las balotas blancas para carros no puede ser negativa.',
            'balotas_blancas_moto.integer' => 'Las balotas blancas para motos debe ser un número entero.',
            'balotas_blancas_moto.min' => 'Las balotas blancas para motos no puede ser negativa.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado no es válido.',
        ]);

        $sorteo->update([
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'fecha_inicio_recoleccion' => $validated['fecha_inicio_recoleccion'],
            'fecha_fin_recoleccion' => $validated['fecha_fin_recoleccion'],
            'fecha_sorteo' => $validated['fecha_sorteo'],
            'hora_sorteo' => $validated['hora_sorteo'] ?? null,
            'fecha_inicio_uso' => $validated['fecha_inicio_uso'] ?? null,
            'duracion_meses' => $validated['duracion_meses'] ?? null,
            'capacidad_autos' => $validated['capacidad_autos'],
            'capacidad_motos' => $validated['capacidad_motos'],
            'balotas_blancas_carro' => $validated['balotas_blancas_carro'] ?? 0,
            'balotas_blancas_moto' => $validated['balotas_blancas_moto'] ?? 0,
            'estado' => $validated['estado'],
            'activo' => $request->has('activo') ? (bool)$request->activo : true,
        ]);

        return redirect()->route('admin.sorteos-parqueadero.index')
            ->with('success', 'Sorteo actualizado correctamente.');
    }

    /**
     * Mostrar los participantes de un sorteo
     */
    public function participantes(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $sorteo = SorteoParqueadero::where('copropiedad_id', $propiedad->id)
            ->with(['copropiedad', 'creadoPor'])
            ->findOrFail($id);

        // Query base: participantes del sorteo
        // Nota: No se filtra por copropiedad_id ya que pueden haber participantes de diferentes copropiedades
        $query = ParticipanteSorteoParqueadero::with(['unidad', 'residente.user'])
            ->where('sorteo_parqueadero_id', $id);

        // Filtro por tipo de vehículo
        if ($request->filled('tipo_vehiculo')) {
            if ($request->tipo_vehiculo !== 'todos') {
                $query->where('tipo_vehiculo', $request->tipo_vehiculo);
            }
        }

        // Filtro por favorecido
        if ($request->filled('fue_favorecido')) {
            if ($request->fue_favorecido !== 'todos') {
                $query->where('fue_favorecido', $request->fue_favorecido == 'si');
            }
        }

        // Filtro por búsqueda de placa
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where('placa', 'like', "%{$buscar}%");
        }

        // Ordenar por fecha de inscripción (más recientes primero)
        $query->orderBy('fecha_inscripcion', 'desc');

        // Paginación
        $participantes = $query->paginate(20)->withQueryString();

        return view('admin.sorteos-parqueadero.participantes', compact('sorteo', 'participantes'));
    }

    /**
     * Obtener datos del sorteo para el modal
     */
    public function datosSorteo($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return response()->json(['error' => 'No hay propiedad asignada.'], 404);
        }

        $sorteo = SorteoParqueadero::where('copropiedad_id', $propiedad->id)
            ->with('participantes')
            ->findOrFail($id);

        $participantesAutos = $sorteo->participantes->where('tipo_vehiculo', 'carro')->count();
        $participantesMotos = $sorteo->participantes->where('tipo_vehiculo', 'moto')->count();

        return response()->json([
            'capacidad_autos' => $sorteo->capacidad_autos ?? 0,
            'capacidad_motos' => $sorteo->capacidad_motos ?? 0,
            'participantes_autos' => $participantesAutos,
            'participantes_motos' => $participantesMotos,
            'balotas_blancas_carro' => $sorteo->balotas_blancas_carro ?? 0,
            'balotas_blancas_moto' => $sorteo->balotas_blancas_moto ?? 0,
        ]);
    }

    /**
     * Iniciar sorteo - guardar balotas y redirigir
     */
    public function iniciarSorteo(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.sorteos-parqueadero.index')
                ->with('error', 'No hay propiedad asignada.');
        }

        $sorteo = SorteoParqueadero::where('copropiedad_id', $propiedad->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'tipo_sorteo' => 'required|in:manual,automatico',
            'balotas_blancas_carro' => 'nullable|integer|min:0',
            'balotas_blancas_moto' => 'nullable|integer|min:0',
        ]);

        // Actualizar balotas blancas
        $sorteo->update([
            'balotas_blancas_carro' => $validated['balotas_blancas_carro'] ?? 0,
            'balotas_blancas_moto' => $validated['balotas_blancas_moto'] ?? 0,
        ]);

        // Redirigir según el tipo de sorteo
        if ($validated['tipo_sorteo'] === 'manual') {
            return redirect()->route('admin.sorteos-parqueadero.sorteo-manual', $id)
                ->with('success', 'Sorteo manual iniciado correctamente.');
        } else {
            return redirect()->route('admin.sorteos-parqueadero.sorteo-automatico', $id)
                ->with('success', 'Sorteo automático iniciado correctamente.');
        }
    }

    /**
     * Vista de sorteo manual
     */
    public function sorteoManual($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.sorteos-parqueadero.index')
                ->with('error', 'No hay propiedad asignada.');
        }

        $sorteo = SorteoParqueadero::where('copropiedad_id', $propiedad->id)
            ->with(['participantes.unidad', 'participantes.residente.user'])
            ->findOrFail($id);

        // Obtener participantes ordenados por fecha de inscripción
        $participantesCarro = $sorteo->participantes
            ->where('tipo_vehiculo', 'carro')
            ->sortBy('fecha_inscripcion')
            ->values();
        
        $participantesMoto = $sorteo->participantes
            ->where('tipo_vehiculo', 'moto')
            ->sortBy('fecha_inscripcion')
            ->values();

        // Obtener parqueaderos disponibles por tipo de vehículo
        $parqueaderosCarro = \App\Models\Parqueadero::where('copropiedad_id', $propiedad->id)
            ->where('tipo_vehiculo', 'carro')
            ->where('activo', true)
            ->orderBy('codigo')
            ->get();

        $parqueaderosMoto = \App\Models\Parqueadero::where('copropiedad_id', $propiedad->id)
            ->where('tipo_vehiculo', 'moto')
            ->where('activo', true)
            ->orderBy('codigo')
            ->get();

        // Obtener parqueaderos ya asignados en este sorteo
        $parqueaderosAsignados = $sorteo->participantes
            ->whereNotNull('parqueadero_asignado')
            ->where('parqueadero_asignado', '!=', 'Balota blanca')
            ->pluck('parqueadero_asignado')
            ->toArray();

        return view('admin.sorteos-parqueadero.sorteo-manual', compact(
            'sorteo',
            'participantesCarro',
            'participantesMoto',
            'parqueaderosCarro',
            'parqueaderosMoto',
            'parqueaderosAsignados'
        ));
    }

    /**
     * Vista de sorteo automático
     */
    public function sorteoAutomatico($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.sorteos-parqueadero.index')
                ->with('error', 'No hay propiedad asignada.');
        }

        $sorteo = SorteoParqueadero::where('copropiedad_id', $propiedad->id)
            ->with(['participantes.unidad', 'participantes.residente.user'])
            ->findOrFail($id);

        // Obtener participantes ordenados por fecha de inscripción
        $participantesCarro = $sorteo->participantes
            ->where('tipo_vehiculo', 'carro')
            ->sortBy('fecha_inscripcion')
            ->values();
        
        $participantesMoto = $sorteo->participantes
            ->where('tipo_vehiculo', 'moto')
            ->sortBy('fecha_inscripcion')
            ->values();

        return view('admin.sorteos-parqueadero.sorteo-automatico', compact(
            'sorteo',
            'participantesCarro',
            'participantesMoto'
        ));
    }

    /**
     * Asignar parqueadero manualmente
     */
    public function asignarParqueadero(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return response()->json(['error' => 'No hay propiedad asignada.'], 404);
        }

        $sorteo = SorteoParqueadero::where('copropiedad_id', $propiedad->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'participante_id' => 'required|exists:participantes_sorteos_parqueadero,id',
            'parqueadero_codigo' => 'required|string',
        ]);

        $participante = ParticipanteSorteoParqueadero::where('sorteo_parqueadero_id', $id)
            ->findOrFail($validated['participante_id']);

        // Verificar que el parqueadero no esté ya asignado
        $parqueaderoYaAsignado = ParticipanteSorteoParqueadero::where('sorteo_parqueadero_id', $id)
            ->where('parqueadero_asignado', $validated['parqueadero_codigo'])
            ->where('id', '!=', $participante->id)
            ->exists();

        if ($parqueaderoYaAsignado) {
            return response()->json([
                'error' => 'Este parqueadero ya está asignado a otro participante.'
            ], 400);
        }

        // Verificar que el participante no tenga balota blanca (bloqueado)
        if ($participante->parqueadero_asignado === 'Balota blanca') {
            return response()->json([
                'error' => 'Este participante tiene balota blanca y no puede ser modificado.'
            ], 400);
        }

        $participante->update([
            'parqueadero_asignado' => $validated['parqueadero_codigo'],
            'fue_favorecido' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Parqueadero asignado correctamente.'
        ]);
    }

    /**
     * Asignar balota blanca
     */
    public function asignarBalotaBlanca(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return response()->json(['error' => 'No hay propiedad asignada.'], 404);
        }

        $sorteo = SorteoParqueadero::where('copropiedad_id', $propiedad->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'participante_id' => 'required|exists:participantes_sorteos_parqueadero,id',
        ]);

        $participante = ParticipanteSorteoParqueadero::where('sorteo_parqueadero_id', $id)
            ->findOrFail($validated['participante_id']);

        // Verificar que el participante no esté ya bloqueado
        if ($participante->parqueadero_asignado === 'Balota blanca') {
            return response()->json([
                'error' => 'Este participante ya tiene balota blanca.'
            ], 400);
        }

        $participante->update([
            'parqueadero_asignado' => 'Balota blanca',
            'fue_favorecido' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Balota blanca asignada correctamente.'
        ]);
    }

    /**
     * Ejecutar sorteo automático
     */
    public function ejecutarSorteoAutomatico(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return response()->json(['error' => 'No hay propiedad asignada.'], 404);
        }

        $sorteo = SorteoParqueadero::where('copropiedad_id', $propiedad->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'participante_id' => 'required|exists:participantes_sorteos_parqueadero,id',
        ]);

        $participante = ParticipanteSorteoParqueadero::where('sorteo_parqueadero_id', $id)
            ->findOrFail($validated['participante_id']);

        // Verificar que el participante no esté ya asignado
        if ($participante->parqueadero_asignado) {
            return response()->json([
                'error' => 'Este participante ya tiene un resultado asignado.'
            ], 400);
        }

        $tipoVehiculo = $participante->tipo_vehiculo;

        // Obtener parqueaderos disponibles para este tipo de vehículo
        $parqueaderosDisponibles = \App\Models\Parqueadero::where('copropiedad_id', $propiedad->id)
            ->where('tipo_vehiculo', $tipoVehiculo)
            ->where('activo', true)
            ->orderBy('codigo')
            ->get();

        // Obtener parqueaderos ya asignados en este sorteo
        $parqueaderosAsignados = ParticipanteSorteoParqueadero::where('sorteo_parqueadero_id', $id)
            ->whereNotNull('parqueadero_asignado')
            ->where('parqueadero_asignado', '!=', 'Balota blanca')
            ->pluck('parqueadero_asignado')
            ->toArray();

        // Filtrar parqueaderos disponibles (no asignados)
        $parqueaderosDisponibles = $parqueaderosDisponibles->reject(function ($parqueadero) use ($parqueaderosAsignados) {
            return in_array($parqueadero->codigo, $parqueaderosAsignados);
        });

        // Contar balotas blancas ya usadas
        $balotasBlancasUsadas = ParticipanteSorteoParqueadero::where('sorteo_parqueadero_id', $id)
            ->where('parqueadero_asignado', 'Balota blanca')
            ->where('tipo_vehiculo', $tipoVehiculo)
            ->count();

        $balotasBlancasDisponibles = ($tipoVehiculo === 'carro' 
            ? ($sorteo->balotas_blancas_carro ?? 0) 
            : ($sorteo->balotas_blancas_moto ?? 0)) - $balotasBlancasUsadas;

        // Calcular total de opciones (parqueaderos + balotas blancas)
        $totalOpciones = $parqueaderosDisponibles->count() + max(0, $balotasBlancasDisponibles);

        if ($totalOpciones === 0) {
            return response()->json([
                'error' => 'No hay opciones disponibles para el sorteo.'
            ], 400);
        }

        // Generar número aleatorio
        $numeroAleatorio = rand(1, $totalOpciones);

        if ($numeroAleatorio <= $parqueaderosDisponibles->count()) {
            // Asignar parqueadero
            $parqueaderoAsignado = $parqueaderosDisponibles->random();
            $participante->update([
                'parqueadero_asignado' => $parqueaderoAsignado->codigo,
                'fue_favorecido' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Parqueadero asignado correctamente.',
                'resultado' => [
                    'tipo' => 'parqueadero',
                    'codigo' => $parqueaderoAsignado->codigo,
                    'fue_favorecido' => true,
                ]
            ]);
        } else {
            // Asignar balota blanca
            $participante->update([
                'parqueadero_asignado' => 'Balota blanca',
                'fue_favorecido' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Balota blanca asignada.',
                'resultado' => [
                    'tipo' => 'balota_blanca',
                    'fue_favorecido' => false,
                ]
            ]);
        }
    }
}
