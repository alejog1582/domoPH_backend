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

        return view('admin.sorteos-parqueadero.create', compact('propiedad'));
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
            'capacidad_autos' => 'required|integer|min:0',
            'capacidad_motos' => 'required|integer|min:0',
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
            'capacidad_autos.required' => 'La capacidad de autos es obligatoria.',
            'capacidad_autos.integer' => 'La capacidad de autos debe ser un número entero.',
            'capacidad_autos.min' => 'La capacidad de autos no puede ser negativa.',
            'capacidad_motos.required' => 'La capacidad de motos es obligatoria.',
            'capacidad_motos.integer' => 'La capacidad de motos debe ser un número entero.',
            'capacidad_motos.min' => 'La capacidad de motos no puede ser negativa.',
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
            'capacidad_autos' => $validated['capacidad_autos'],
            'capacidad_motos' => $validated['capacidad_motos'],
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
            'capacidad_autos' => 'required|integer|min:0',
            'capacidad_motos' => 'required|integer|min:0',
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
            'capacidad_autos.required' => 'La capacidad de autos es obligatoria.',
            'capacidad_autos.integer' => 'La capacidad de autos debe ser un número entero.',
            'capacidad_autos.min' => 'La capacidad de autos no puede ser negativa.',
            'capacidad_motos.required' => 'La capacidad de motos es obligatoria.',
            'capacidad_motos.integer' => 'La capacidad de motos debe ser un número entero.',
            'capacidad_motos.min' => 'La capacidad de motos no puede ser negativa.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado no es válido.',
        ]);

        $sorteo->update([
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'fecha_inicio_recoleccion' => $validated['fecha_inicio_recoleccion'],
            'fecha_fin_recoleccion' => $validated['fecha_fin_recoleccion'],
            'fecha_sorteo' => $validated['fecha_sorteo'],
            'capacidad_autos' => $validated['capacidad_autos'],
            'capacidad_motos' => $validated['capacidad_motos'],
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
        $query = ParticipanteSorteoParqueadero::with(['unidad', 'residente.user'])
            ->where('sorteo_parqueadero_id', $id)
            ->where('copropiedad_id', $propiedad->id);

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
}
