<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsejoTareaController extends Controller
{
    /**
     * Display a listing of tareas (pendiente and en_progreso by default).
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = DB::table('consejo_tareas')
            ->where('copropiedad_id', $propiedad->id)
            ->orderBy('fecha_vencimiento', 'asc')
            ->orderBy('created_at', 'desc');

        // Por defecto mostrar pendiente y en_progreso
        if (!$request->filled('estado')) {
            $query->whereIn('estado', ['pendiente', 'en_progreso']);
        } else {
            if ($request->estado !== 'todos') {
                $query->where('estado', $request->estado);
            }
        }

        // Filtros
        if ($request->filled('acta_id')) {
            $query->where('acta_id', $request->acta_id);
        }

        if ($request->filled('decision_id')) {
            $query->where('decision_id', $request->decision_id);
        }

        if ($request->filled('responsable_id')) {
            $query->where('responsable_id', $request->responsable_id);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        $tareas = $query->paginate(15)->appends($request->query());

        // Verificar si cada tarea puede editarse/eliminarse
        foreach ($tareas as $tarea) {
            if ($tarea->acta_id) {
                $firmasCount = DB::table('consejo_acta_firmas')
                    ->where('acta_id', $tarea->acta_id)
                    ->count();
                $tarea->puede_editar = $firmasCount == 0;
                $tarea->puede_eliminar = $firmasCount == 0;
            } else {
                $tarea->puede_editar = true;
                $tarea->puede_eliminar = true;
            }
        }

        // Obtener datos para filtros
        $actas = DB::table('consejo_actas')
            ->where('copropiedad_id', $propiedad->id)
            ->orderBy('fecha_acta', 'desc')
            ->get();

        $integrantes = DB::table('consejo_integrantes')
            ->where('copropiedad_id', $propiedad->id)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('admin.consejo-tareas.index', compact('tareas', 'actas', 'integrantes', 'propiedad'));
    }

    /**
     * Show the form for creating a new tarea.
     */
    public function create(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener actas de los últimos 3 meses
        $actas = DB::table('consejo_actas')
            ->where('copropiedad_id', $propiedad->id)
            ->where('fecha_acta', '>=', Carbon::now()->subMonths(3))
            ->orderBy('fecha_acta', 'desc')
            ->get();

        // Obtener integrantes activos
        $integrantes = DB::table('consejo_integrantes')
            ->where('copropiedad_id', $propiedad->id)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        // Si viene de una decisión específica
        $decisionId = $request->get('decision_id');
        $actaId = $request->get('acta_id');

        // Si hay acta seleccionada, obtener decisiones
        $decisiones = [];
        if ($actaId) {
            $decisiones = DB::table('consejo_decisiones')
                ->where('acta_id', $actaId)
                ->get();
        }

        return view('admin.consejo-tareas.create', compact('actas', 'integrantes', 'decisiones', 'decisionId', 'actaId', 'propiedad'));
    }

    /**
     * Store a newly created tarea.
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'acta_id' => 'nullable|exists:consejo_actas,id',
            'decision_id' => 'nullable|exists:consejo_decisiones,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'responsable_id' => 'nullable|exists:consejo_integrantes,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_inicio',
            'prioridad' => 'required|in:baja,media,alta',
        ]);

        // Si tiene acta_id, verificar que no tenga firmas
        if ($validated['acta_id']) {
            $firmasCount = DB::table('consejo_acta_firmas')
                ->where('acta_id', $validated['acta_id'])
                ->count();

            if ($firmasCount > 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No se pueden crear tareas asociadas a un acta que ya tiene firmas.');
            }
        }

        DB::beginTransaction();
        try {
            DB::table('consejo_tareas')->insert([
                'copropiedad_id' => $propiedad->id,
                'acta_id' => $validated['acta_id'] ?? null,
                'decision_id' => $validated['decision_id'] ?? null,
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'],
                'responsable_id' => $validated['responsable_id'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'] ?? null,
                'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
                'prioridad' => $validated['prioridad'],
                'estado' => 'pendiente',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.consejo-tareas.index')
                ->with('success', 'Tarea creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear tarea: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la tarea.');
        }
    }

    /**
     * Display the specified tarea.
     */
    public function show($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tarea = DB::table('consejo_tareas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$tarea) {
            return redirect()->route('admin.consejo-tareas.index')
                ->with('error', 'Tarea no encontrada.');
        }

        // Obtener seguimientos
        $seguimientos = DB::table('consejo_tarea_seguimientos')
            ->join('users', 'consejo_tarea_seguimientos.created_by', '=', 'users.id')
            ->where('consejo_tarea_seguimientos.tarea_id', $id)
            ->select('consejo_tarea_seguimientos.*', 'users.nombre as creador_nombre')
            ->orderBy('consejo_tarea_seguimientos.created_at', 'desc')
            ->get();

        // Obtener archivos
        $archivos = DB::table('consejo_tarea_archivos')
            ->where('tarea_id', $id)
            ->get();

        // Verificar si puede editar/eliminar
        $puede_editar = true;
        $puede_eliminar = true;
        if ($tarea->acta_id) {
            $firmasCount = DB::table('consejo_acta_firmas')
                ->where('acta_id', $tarea->acta_id)
                ->count();
            $puede_editar = $firmasCount == 0;
            $puede_eliminar = $firmasCount == 0;
        }

        return view('admin.consejo-tareas.show', compact('tarea', 'seguimientos', 'archivos', 'puede_editar', 'puede_eliminar', 'propiedad'));
    }

    /**
     * Show gestionar view for tarea.
     */
    public function gestionar($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tarea = DB::table('consejo_tareas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$tarea) {
            return redirect()->route('admin.consejo-tareas.index')
                ->with('error', 'Tarea no encontrada.');
        }

        // Obtener seguimientos
        $seguimientos = DB::table('consejo_tarea_seguimientos')
            ->join('users', 'consejo_tarea_seguimientos.created_by', '=', 'users.id')
            ->where('consejo_tarea_seguimientos.tarea_id', $id)
            ->select('consejo_tarea_seguimientos.*', 'users.nombre as creador_nombre')
            ->orderBy('consejo_tarea_seguimientos.created_at', 'desc')
            ->get();

        // Obtener archivos
        $archivos = DB::table('consejo_tarea_archivos')
            ->where('tarea_id', $id)
            ->get();

        return view('admin.consejo-tareas.gestionar', compact('tarea', 'seguimientos', 'archivos', 'propiedad'));
    }

    /**
     * Add seguimiento to tarea.
     */
    public function agregarSeguimiento(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tarea = DB::table('consejo_tareas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$tarea) {
            return redirect()->route('admin.consejo-tareas.index')
                ->with('error', 'Tarea no encontrada.');
        }

        $validated = $request->validate([
            'comentario' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $estadoAnterior = $tarea->estado;
            $estadoNuevo = $request->get('estado_nuevo', $tarea->estado);

            // Solo presidente puede cambiar estado
            $integrante = DB::table('consejo_integrantes')
                ->where('user_id', auth()->id())
                ->where('copropiedad_id', $propiedad->id)
                ->where('estado', 'activo')
                ->first();

            $esPresidente = $integrante && $integrante->es_presidente == true;

            if ($estadoNuevo !== $estadoAnterior && !$esPresidente) {
                return redirect()->back()
                    ->with('error', 'Solo el presidente puede cambiar el estado de las tareas.');
            }

            // Crear seguimiento
            DB::table('consejo_tarea_seguimientos')->insert([
                'tarea_id' => $id,
                'comentario' => $validated['comentario'],
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoNuevo,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Actualizar estado de la tarea si cambió
            if ($estadoNuevo !== $estadoAnterior) {
                DB::table('consejo_tareas')
                    ->where('id', $id)
                    ->update([
                        'estado' => $estadoNuevo,
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            return redirect()->route('admin.consejo-tareas.gestionar', $id)
                ->with('success', 'Seguimiento agregado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al agregar seguimiento: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al agregar el seguimiento.');
        }
    }

    /**
     * Upload archivo to tarea.
     */
    public function subirArchivo(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tarea = DB::table('consejo_tareas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$tarea) {
            return redirect()->route('admin.consejo-tareas.index')
                ->with('error', 'Tarea no encontrada.');
        }

        $validated = $request->validate([
            'archivo' => 'required|file|max:10240', // 10MB max
        ]);

        try {
            $archivo = $request->file('archivo');
            $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                'folder' => 'domoph/consejo/tareas',
                'resource_type' => 'auto',
            ]);

            DB::table('consejo_tarea_archivos')->insert([
                'tarea_id' => $id,
                'nombre_archivo' => $archivo->getClientOriginalName(),
                'ruta_archivo' => $result['secure_url'] ?? $result['url'] ?? null,
                'tipo_archivo' => $archivo->getMimeType(),
                'tamaño' => $archivo->getSize(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('admin.consejo-tareas.gestionar', $id)
                ->with('success', 'Archivo subido exitosamente.');
        } catch (\Exception $e) {
            \Log::error('Error al subir archivo de tarea: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al subir el archivo.');
        }
    }

    /**
     * Show the form for editing the specified tarea.
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tarea = DB::table('consejo_tareas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$tarea) {
            return redirect()->route('admin.consejo-tareas.index')
                ->with('error', 'Tarea no encontrada.');
        }

        // Verificar si puede editar
        if ($tarea->acta_id) {
            $firmasCount = DB::table('consejo_acta_firmas')
                ->where('acta_id', $tarea->acta_id)
                ->count();

            if ($firmasCount > 0) {
                return redirect()->route('admin.consejo-tareas.index')
                    ->with('error', 'No se puede editar una tarea asociada a un acta que ya tiene firmas.');
            }
        }

        // Obtener integrantes activos
        $integrantes = DB::table('consejo_integrantes')
            ->where('copropiedad_id', $propiedad->id)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('admin.consejo-tareas.edit', compact('tarea', 'integrantes', 'propiedad'));
    }

    /**
     * Update the specified tarea.
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tarea = DB::table('consejo_tareas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$tarea) {
            return redirect()->route('admin.consejo-tareas.index')
                ->with('error', 'Tarea no encontrada.');
        }

        // Verificar si puede editar
        if ($tarea->acta_id) {
            $firmasCount = DB::table('consejo_acta_firmas')
                ->where('acta_id', $tarea->acta_id)
                ->count();

            if ($firmasCount > 0) {
                return redirect()->route('admin.consejo-tareas.index')
                    ->with('error', 'No se puede editar una tarea asociada a un acta que ya tiene firmas.');
            }
        }

        // Verificar si el usuario es presidente (solo presidente puede cambiar estado)
        $integrante = DB::table('consejo_integrantes')
            ->where('user_id', auth()->id())
            ->where('copropiedad_id', $propiedad->id)
            ->where('estado', 'activo')
            ->first();

        $esPresidente = $integrante && $integrante->es_presidente == true;

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'responsable_id' => 'nullable|exists:consejo_integrantes,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_inicio',
            'prioridad' => 'required|in:baja,media,alta',
            'estado' => 'required|in:pendiente,en_progreso,bloqueada,finalizada',
        ]);

        // Solo presidente puede cambiar estado
        if (!$esPresidente && $validated['estado'] !== $tarea->estado) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Solo el presidente puede cambiar el estado de las tareas.');
        }

        DB::beginTransaction();
        try {
            DB::table('consejo_tareas')
                ->where('id', $id)
                ->update([
                    'titulo' => $validated['titulo'],
                    'descripcion' => $validated['descripcion'],
                    'responsable_id' => $validated['responsable_id'] ?? null,
                    'fecha_inicio' => $validated['fecha_inicio'] ?? null,
                    'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
                    'prioridad' => $validated['prioridad'],
                    'estado' => $validated['estado'],
                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()->route('admin.consejo-tareas.index')
                ->with('success', 'Tarea actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar tarea: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la tarea.');
        }
    }

    /**
     * Remove the specified tarea.
     */
    public function destroy($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $tarea = DB::table('consejo_tareas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$tarea) {
            return redirect()->route('admin.consejo-tareas.index')
                ->with('error', 'Tarea no encontrada.');
        }

        // Verificar si puede eliminar
        if ($tarea->acta_id) {
            $firmasCount = DB::table('consejo_acta_firmas')
                ->where('acta_id', $tarea->acta_id)
                ->count();

            if ($firmasCount > 0) {
                return redirect()->route('admin.consejo-tareas.index')
                    ->with('error', 'No se puede eliminar una tarea asociada a un acta que ya tiene firmas.');
            }
        }

        DB::beginTransaction();
        try {
            // Eliminar archivos de Cloudinary
            $archivos = DB::table('consejo_tarea_archivos')
                ->where('tarea_id', $id)
                ->get();

            foreach ($archivos as $archivo) {
                try {
                    $urlParts = explode('/', $archivo->ruta_archivo);
                    $publicId = pathinfo(end($urlParts), PATHINFO_FILENAME);
                    Cloudinary::uploadApi()->destroy('domoph/consejo/tareas/' . $publicId);
                } catch (\Exception $e) {
                    \Log::warning('No se pudo eliminar archivo de Cloudinary: ' . $e->getMessage());
                }
            }

            // Eliminar seguimientos
            DB::table('consejo_tarea_seguimientos')->where('tarea_id', $id)->delete();

            // Eliminar archivos
            DB::table('consejo_tarea_archivos')->where('tarea_id', $id)->delete();

            // Eliminar tarea
            DB::table('consejo_tareas')->where('id', $id)->delete();

            DB::commit();

            return redirect()->route('admin.consejo-tareas.index')
                ->with('success', 'Tarea eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar tarea: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al eliminar la tarea.');
        }
    }

    /**
     * Obtener decisiones de un acta (AJAX).
     */
    public function getDecisiones(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return response()->json(['decisiones' => []], 400);
        }

        $actaId = $request->get('acta_id');
        
        if (!$actaId) {
            return response()->json(['decisiones' => []], 400);
        }

        // Verificar que el acta pertenece a la propiedad
        $acta = DB::table('consejo_actas')
            ->where('id', $actaId)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$acta) {
            return response()->json(['decisiones' => []], 404);
        }

        // Obtener decisiones del acta
        $decisiones = DB::table('consejo_decisiones')
            ->where('acta_id', $actaId)
            ->select('id', 'descripcion')
            ->get();

        return response()->json(['decisiones' => $decisiones]);
    }
}
