<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LlamadoAtencion;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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
            'soporte' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB máximo
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

            // Procesar imagen de soporte si se proporciona
            $evidencia = $validated['evidencia'] ?? null;
            if ($request->hasFile('soporte')) {
                $archivo = $request->file('soporte');
                $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                    'folder' => 'domoph/llamados_atencion',
                    'resource_type' => 'image',
                ]);
                $soporteUrl = $result['secure_url'];
                
                // Si evidencia es null, crear array, si no, agregar la nueva imagen
                if (!$evidencia) {
                    $evidencia = [];
                }
                if (!is_array($evidencia)) {
                    $evidencia = json_decode($evidencia, true) ?? [];
                }
                $evidencia[] = $soporteUrl;
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
                'evidencia' => $evidencia ? json_encode($evidencia) : null,
                'observaciones' => $validated['observaciones'] ?? null,
                'es_reincidencia' => $validated['es_reincidencia'] ?? false,
                'activo' => true,
            ]);

            // Crear registro inicial en el historial
            DB::table('llamados_atencion_historial')->insert([
                'llamado_atencion_id' => $llamado->id,
                'estado_anterior' => null,
                'estado_nuevo' => $llamado->estado,
                'comentario' => 'Llamado de atención creado',
                'soporte_url' => null,
                'cambiado_por' => Auth::id(),
                'fecha_cambio' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->route('admin.llamados-atencion.index')
                ->with('success', 'Llamado de atención creado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al crear llamado de atención: ' . $e->getMessage());
            return back()->with('error', 'Error al crear el llamado de atención: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar el formulario de gestión de un llamado de atención
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $llamado = LlamadoAtencion::with(['unidad', 'residente', 'registradoPor'])
            ->where('copropiedad_id', $propiedad->id)
            ->where('id', $id)
            ->where('activo', true)
            ->firstOrFail();

        // Obtener el historial
        $historial = DB::table('llamados_atencion_historial')
            ->where('llamado_atencion_id', $llamado->id)
            ->orderBy('fecha_cambio', 'desc')
            ->get();

        return view('admin.llamados-atencion.edit', compact('llamado', 'historial', 'propiedad'));
    }

    /**
     * Actualizar un llamado de atención (estado y comentarios)
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $llamado = LlamadoAtencion::where('copropiedad_id', $propiedad->id)
            ->where('id', $id)
            ->where('activo', true)
            ->firstOrFail();

        $validated = $request->validate([
            'estado' => 'required|in:abierto,en_proceso,cerrado,anulado',
            'comentario' => 'nullable|string|max:1000',
            'soporte' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB máximo
        ], [
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'comentario.max' => 'El comentario no puede exceder 1000 caracteres.',
            'soporte.image' => 'El archivo debe ser una imagen.',
            'soporte.mimes' => 'La imagen debe ser jpeg, png, jpg o gif.',
            'soporte.max' => 'La imagen no puede exceder 5MB.',
        ]);

        try {
            $estadoAnterior = $llamado->estado;
            $estadoNuevo = $validated['estado'];
            $soporteUrl = null;

            // Subir imagen de soporte si se proporciona
            if ($request->hasFile('soporte')) {
                $archivo = $request->file('soporte');
                $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                    'folder' => 'domoph/llamados_atencion',
                    'resource_type' => 'image',
                ]);
                $soporteUrl = $result['secure_url'];
            }

            // Actualizar el estado del llamado
            $llamado->estado = $estadoNuevo;
            $llamado->save();

            // Crear registro en el historial si hay cambio de estado o comentario
            if ($estadoAnterior !== $estadoNuevo || $validated['comentario']) {
                DB::table('llamados_atencion_historial')->insert([
                    'llamado_atencion_id' => $llamado->id,
                    'estado_anterior' => $estadoAnterior !== $estadoNuevo ? $estadoAnterior : null,
                    'estado_nuevo' => $estadoNuevo,
                    'comentario' => $validated['comentario'] ?? null,
                    'soporte_url' => $soporteUrl,
                    'cambiado_por' => Auth::id(),
                    'fecha_cambio' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            return redirect()->route('admin.llamados-atencion.edit', $llamado->id)
                ->with('success', 'Llamado de atención actualizado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al actualizar llamado de atención: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar el llamado de atención: ' . $e->getMessage())
                ->withInput();
        }
    }
}
