<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pqrs;
use App\Models\User;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // Obtener el historial con diferenciación de roles
        $historial = DB::table('pqrs_historial')
            ->where('pqrs_id', $pqrs->id)
            ->orderBy('fecha_cambio', 'desc')
            ->get()
            ->map(function ($registro) use ($propiedad) {
                // Obtener el usuario que hizo el cambio
                $usuarioCambio = User::find($registro->cambiado_por);
                
                // Determinar si el registro fue hecho por un residente o por la administración
                // Verificar si el usuario tiene el rol "residente" para esta propiedad
                $esResidente = false;
                if ($usuarioCambio) {
                    $esResidente = $usuarioCambio->hasRole('residente', $propiedad->id);
                }
                
                return (object) [
                    'id' => $registro->id,
                    'estado_anterior' => $registro->estado_anterior,
                    'estado_nuevo' => $registro->estado_nuevo,
                    'comentario' => $registro->comentario,
                    'soporte_url' => $registro->soporte_url,
                    'cambiado_por' => $registro->cambiado_por,
                    'fecha_cambio' => $registro->fecha_cambio,
                    'es_residente' => $esResidente,
                    'usuario' => $usuarioCambio ? $usuarioCambio->nombre : 'Usuario desconocido',
                ];
            });

        return view('admin.pqrs.edit', compact('pqrs', 'propiedad', 'historial'));
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
            DB::beginTransaction();

            $estadoAnterior = $pqrs->estado;
            $estadoNuevo = $validated['estado'];
            
            // Si el estado cambia a respondida, cerrar o rechazada, actualizar fecha_respuesta
            $fechaRespuesta = $pqrs->fecha_respuesta;
            if (in_array($estadoNuevo, ['respondida', 'cerrada', 'rechazada']) && !$pqrs->fecha_respuesta) {
                $fechaRespuesta = Carbon::now();
            }

            // Si hay respuesta, actualizar respondido_por
            $respondidoPor = $pqrs->respondido_por;
            if (!empty($validated['respuesta'])) {
                $respondidoPor = Auth::id();
            }

            // Actualizar la PQRS
            $pqrs->update([
                'prioridad' => $validated['prioridad'],
                'estado' => $estadoNuevo,
                'respuesta' => $validated['respuesta'] ?? null,
                'fecha_respuesta' => $fechaRespuesta,
                'respondido_por' => $respondidoPor,
                'observaciones' => $validated['observaciones'] ?? null,
                'calificacion_servicio' => $validated['calificacion_servicio'] ?? null,
            ]);

            // Crear registro en el historial si:
            // 1. Hay una respuesta (cada respuesta se registra por separado, incluso si es la misma)
            // 2. Hay cambio de estado
            // 3. Hay observaciones nuevas (solo si no hay respuesta ni cambio de estado)
            
            $debeRegistrarHistorial = false;
            $comentarioHistorial = null;

            // Si hay respuesta, registrar en historial (cada respuesta se registra)
            if (!empty($validated['respuesta'])) {
                $debeRegistrarHistorial = true;
                $comentarioHistorial = $validated['respuesta'];
            }
            // Si hay cambio de estado, también registrar
            elseif ($estadoAnterior !== $estadoNuevo) {
                $debeRegistrarHistorial = true;
                $comentarioHistorial = "Cambio de estado: " . ucfirst(str_replace('_', ' ', $estadoAnterior)) . " → " . ucfirst(str_replace('_', ' ', $estadoNuevo));
            }
            // Si hay observaciones y no hay respuesta ni cambio de estado, registrar observaciones
            elseif (!empty($validated['observaciones']) && $validated['observaciones'] !== $pqrs->observaciones) {
                $debeRegistrarHistorial = true;
                $comentarioHistorial = "Observaciones: " . $validated['observaciones'];
            }

            if ($debeRegistrarHistorial) {
                DB::table('pqrs_historial')->insert([
                    'pqrs_id' => $pqrs->id,
                    'estado_anterior' => $estadoAnterior !== $estadoNuevo ? $estadoAnterior : null,
                    'estado_nuevo' => $estadoNuevo,
                    'comentario' => $comentarioHistorial,
                    'soporte_url' => null, // Se puede agregar soporte en el futuro si es necesario
                    'cambiado_por' => Auth::id(),
                    'fecha_cambio' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.pqrs.edit', $pqrs->id)
                ->with('success', 'PQRS actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar PQRS: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar la PQRS: ' . $e->getMessage())
                ->withInput();
        }
    }
}
