<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LlamadoAtencion;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LlamadoAtencionController extends Controller
{
    /**
     * Mostrar la lista de llamados de atención
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Query base: llamados de atención con sus relaciones
        $query = LlamadoAtencion::with(['unidad', 'residente', 'registradoPor'])
            ->where('copropiedad_id', $propiedad->id)
            ->where('activo', true);

        // Por defecto: mostrar solo abierto y en_proceso
        if (!$request->filled('estado')) {
            $query->whereIn('estado', ['abierto', 'en_proceso']);
        } else {
            // Si hay filtro de estado, aplicarlo
            if ($request->estado !== 'todos') {
                $query->where('estado', $request->estado);
            }
        }

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por nivel
        if ($request->filled('nivel')) {
            $query->where('nivel', $request->nivel);
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

        // Filtro por búsqueda en motivo
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('motivo', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        // Filtro por fecha desde
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_evento', '>=', $request->fecha_desde);
        }

        // Filtro por fecha hasta
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_evento', '<=', $request->fecha_hasta);
        }

        // Filtro por reincidencia
        if ($request->filled('es_reincidencia')) {
            $query->where('es_reincidencia', $request->es_reincidencia == '1');
        }

        // Ordenar por fecha de evento descendente
        $llamados = $query->orderBy('fecha_evento', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Obtener unidades para el filtro
        $unidades = \App\Models\Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get(['id', 'numero', 'torre', 'bloque']);

        return view('admin.llamados-atencion.index', compact(
            'llamados', 
            'propiedad', 
            'unidades'
        ));
    }

    /**
     * Mostrar el formulario de creación de un llamado de atención
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener unidades para el select
        $unidades = \App\Models\Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get(['id', 'numero', 'torre', 'bloque']);

        return view('admin.llamados-atencion.create', compact('propiedad', 'unidades'));
    }

    /**
     * Guardar un nuevo llamado de atención
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'unidad_id' => 'nullable|exists:unidades,id',
            'residente_id' => 'nullable|exists:residentes,id',
            'tipo' => 'required|in:convivencia,ruido,mascotas,parqueadero,seguridad,otro',
            'motivo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'nivel' => 'required|in:leve,moderado,grave',
            'estado' => 'required|in:abierto,en_proceso,cerrado,anulado',
            'fecha_evento' => 'required|date',
            'evidencia' => 'nullable|array',
            'observaciones' => 'nullable|string',
            'es_reincidencia' => 'boolean',
        ], [
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
            'motivo.required' => 'El motivo es obligatorio.',
            'motivo.max' => 'El motivo no puede exceder 200 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'nivel.required' => 'El nivel es obligatorio.',
            'nivel.in' => 'El nivel seleccionado no es válido.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'fecha_evento.required' => 'La fecha del evento es obligatoria.',
        ]);

        try {
            // Verificar que la unidad pertenezca a la propiedad activa (si se proporciona)
            if ($validated['unidad_id']) {
                $unidad = \App\Models\Unidad::where('propiedad_id', $propiedad->id)
                    ->where('id', $validated['unidad_id'])
                    ->first();

                if (!$unidad) {
                    return back()->with('error', 'La unidad no pertenece a la propiedad activa.')
                        ->withInput();
                }
            }

            $llamado = LlamadoAtencion::create([
                'copropiedad_id' => $propiedad->id,
                'unidad_id' => $validated['unidad_id'] ?? null,
                'residente_id' => $validated['residente_id'] ?? null,
                'tipo' => $validated['tipo'],
                'motivo' => $validated['motivo'],
                'descripcion' => $validated['descripcion'],
                'nivel' => $validated['nivel'],
                'estado' => $validated['estado'],
                'fecha_evento' => Carbon::parse($validated['fecha_evento']),
                'fecha_registro' => Carbon::now(),
                'registrado_por' => Auth::id(),
                'evidencia' => $validated['evidencia'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'es_reincidencia' => $validated['es_reincidencia'] ?? false,
                'activo' => true,
            ]);

            return redirect()->route('admin.llamados-atencion.index')
                ->with('success', 'Llamado de atención creado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al crear llamado de atención: ' . $e->getMessage());
            return back()->with('error', 'Error al crear el llamado de atención: ' . $e->getMessage())
                ->withInput();
        }
    }
}
