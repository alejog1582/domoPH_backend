<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pqrs;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PqrsController extends Controller
{
    /**
     * Mostrar la lista de PQRS
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Query base: PQRS con sus relaciones
        $query = Pqrs::with(['unidad', 'residente', 'respondidoPor'])
            ->where('copropiedad_id', $propiedad->id)
            ->where('activo', true);

        // Por defecto: mostrar solo radicada y en_proceso
        if (!$request->filled('estado')) {
            $query->whereIn('estado', ['radicada', 'en_proceso']);
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

        // Filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        // Filtro por prioridad
        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
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

        // Filtro por búsqueda en asunto o número de radicado
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('asunto', 'like', "%{$buscar}%")
                  ->orWhere('numero_radicado', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        // Filtro por fecha desde
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_radicacion', '>=', $request->fecha_desde);
        }

        // Filtro por fecha hasta
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_radicacion', '<=', $request->fecha_hasta);
        }

        // Ordenar por fecha de radicación descendente
        $pqrs = $query->orderBy('fecha_radicacion', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Obtener unidades para el filtro
        $unidades = \App\Models\Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get(['id', 'numero', 'torre', 'bloque']);

        return view('admin.pqrs.index', compact(
            'pqrs', 
            'propiedad', 
            'unidades'
        ));
    }

    /**
     * Mostrar el formulario de edición de una PQRS (solo gestión)
     */
    public function edit(Pqrs $pqrs)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que la PQRS pertenezca a la propiedad activa
        if ($pqrs->copropiedad_id !== $propiedad->id) {
            return redirect()->route('admin.pqrs.index')
                ->with('error', 'No tiene acceso a esta PQRS.');
        }

        return view('admin.pqrs.edit', compact('pqrs', 'propiedad'));
    }

    /**
     * Actualizar una PQRS (solo campos de gestión)
     */
    public function update(Request $request, Pqrs $pqrs)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Verificar que la PQRS pertenezca a la propiedad activa
        if ($pqrs->copropiedad_id !== $propiedad->id) {
            return redirect()->route('admin.pqrs.index')
                ->with('error', 'No tiene acceso a esta PQRS.');
        }

        // Solo validar campos de gestión (no los campos principales del residente)
        $validated = $request->validate([
            'prioridad' => 'required|in:baja,media,alta,critica',
            'estado' => 'required|in:radicada,en_proceso,respondida,cerrada,rechazada',
            'respuesta' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'calificacion_servicio' => 'nullable|integer|min:1|max:5',
        ], [
            'prioridad.required' => 'La prioridad es obligatoria.',
            'prioridad.in' => 'La prioridad seleccionada no es válida.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'calificacion_servicio.integer' => 'La calificación debe ser un número entero.',
            'calificacion_servicio.min' => 'La calificación mínima es 1.',
            'calificacion_servicio.max' => 'La calificación máxima es 5.',
        ]);

        try {
            // Si el estado cambia a respondida, cerrar o rechazada, actualizar fecha_respuesta
            $fechaRespuesta = $pqrs->fecha_respuesta;
            if (in_array($validated['estado'], ['respondida', 'cerrada', 'rechazada']) && !$pqrs->fecha_respuesta) {
                $fechaRespuesta = Carbon::now();
            }

            // Si hay respuesta, actualizar respondido_por
            $respondidoPor = $pqrs->respondido_por;
            if (!empty($validated['respuesta'])) {
                $respondidoPor = Auth::id();
            }

            $pqrs->update([
                'prioridad' => $validated['prioridad'],
                'estado' => $validated['estado'],
                'respuesta' => $validated['respuesta'] ?? null,
                'fecha_respuesta' => $fechaRespuesta,
                'respondido_por' => $respondidoPor,
                'observaciones' => $validated['observaciones'] ?? null,
                'calificacion_servicio' => $validated['calificacion_servicio'] ?? null,
            ]);

            return redirect()->route('admin.pqrs.index')
                ->with('success', 'PQRS actualizada correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al actualizar PQRS: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar la PQRS: ' . $e->getMessage())
                ->withInput();
        }
    }
}
