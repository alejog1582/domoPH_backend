<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AsambleaController extends Controller
{
    /**
     * Display a listing of asambleas (last 3 years).
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        // Obtener asambleas de los últimos 3 años
        $fechaInicio = Carbon::now()->subYears(3);
        
        $query = DB::table('asambleas')
            ->where('copropiedad_id', $propiedad->id)
            ->where('fecha_inicio', '>=', $fechaInicio)
            ->orderBy('fecha_inicio', 'desc');

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('modalidad')) {
            $query->where('modalidad', $request->modalidad);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_inicio', '<=', $request->fecha_hasta);
        }

        $asambleas = $query->paginate(15)->appends($request->query());

        return view('admin.asambleas.index', compact('asambleas', 'propiedad'));
    }

    /**
     * Show the form for creating a new asamblea.
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        return view('admin.asambleas.create', compact('propiedad'));
    }

    /**
     * Store a newly created asamblea.
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:ordinaria,extraordinaria',
            'modalidad' => 'required|in:presencial,virtual,mixta',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'quorum_minimo' => 'required|numeric|min:0|max:100',
            'url_transmision' => 'nullable|url|max:500',
            'proveedor_transmision' => 'nullable|in:daily,livekit,agora,twilio',
            'token_transmision' => 'nullable|string',
            'archivos' => 'nullable|array',
            'archivos.*' => 'file|max:10240', // 10MB max
            'tipos_archivo' => 'nullable|array',
            'tipos_archivo.*' => 'in:orden_dia,acta,presupuesto,soporte,otro',
            'visible_para_archivos' => 'nullable|array',
            'visible_para_archivos.*' => 'in:todos,propietarios,administracion',
        ]);

        DB::beginTransaction();
        try {
            // Crear asamblea
            $asambleaId = DB::table('asambleas')->insertGetId([
                'copropiedad_id' => $propiedad->id,
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'tipo' => $validated['tipo'],
                'modalidad' => $validated['modalidad'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'estado' => 'programada',
                'quorum_minimo' => $validated['quorum_minimo'],
                'quorum_actual' => null,
                'url_transmision' => $validated['url_transmision'] ?? null,
                'proveedor_transmision' => $validated['proveedor_transmision'] ?? null,
                'token_transmision' => $validated['token_transmision'] ?? null,
                'creado_por' => auth()->id(),
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Subir archivos a Cloudinary
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $index => $archivo) {
                    try {
                        $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                            'folder' => 'domoph/asambleas/documentos',
                            'resource_type' => 'auto',
                        ]);

                        $tipoArchivo = $request->tipos_archivo[$index] ?? 'otro';
                        $visiblePara = $request->visible_para_archivos[$index] ?? 'todos';

                        DB::table('asamblea_documentos')->insert([
                            'asamblea_id' => $asambleaId,
                            'nombre' => $archivo->getClientOriginalName(),
                            'tipo' => $tipoArchivo,
                            'archivo_url' => $result['secure_url'] ?? $result['url'] ?? null,
                            'visible_para' => $visiblePara,
                            'subido_por' => auth()->id(),
                            'activo' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error al subir archivo de asamblea a Cloudinary: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.asambleas.index')
                ->with('success', 'Asamblea creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear asamblea: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la asamblea.');
        }
    }

    /**
     * Display the specified asamblea.
     */
    public function show($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $asamblea = DB::table('asambleas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$asamblea) {
            return redirect()->route('admin.asambleas.index')
                ->with('error', 'Asamblea no encontrada.');
        }

        // Obtener documentos
        $documentos = DB::table('asamblea_documentos')
            ->where('asamblea_id', $id)
            ->where('activo', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Obtener asistencias
        $asistencias = DB::table('asamblea_asistencias')
            ->leftJoin('residentes', 'asamblea_asistencias.residente_id', '=', 'residentes.id')
            ->leftJoin('users', 'asamblea_asistencias.user_id', '=', 'users.id')
            ->where('asamblea_asistencias.asamblea_id', $id)
            ->select(
                'asamblea_asistencias.*',
                'residentes.nombre as residente_nombre',
                'users.nombre as user_nombre'
            )
            ->orderBy('asamblea_asistencias.created_at', 'desc')
            ->get();

        // Obtener votaciones
        $votaciones = DB::table('asamblea_votaciones')
            ->where('asamblea_id', $id)
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        // Para cada votación, obtener opciones y resultados
        foreach ($votaciones as $votacion) {
            $votacion->opciones = DB::table('asamblea_votacion_opciones')
                ->where('votacion_id', $votacion->id)
                ->orderBy('orden')
                ->get();

            // Obtener resultados
            foreach ($votacion->opciones as $opcion) {
                $opcion->votos = DB::table('asamblea_votos')
                    ->where('votacion_id', $votacion->id)
                    ->where('opcion_id', $opcion->id)
                    ->count();

                $opcion->coeficiente_total = DB::table('asamblea_votos')
                    ->where('votacion_id', $votacion->id)
                    ->where('opcion_id', $opcion->id)
                    ->sum('coeficiente_aplicado');
            }

            $votacion->total_votos = DB::table('asamblea_votos')
                ->where('votacion_id', $votacion->id)
                ->count();
        }

        return view('admin.asambleas.show', compact('asamblea', 'documentos', 'asistencias', 'votaciones', 'propiedad'));
    }

    /**
     * Show the form for editing the specified asamblea.
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $asamblea = DB::table('asambleas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$asamblea) {
            return redirect()->route('admin.asambleas.index')
                ->with('error', 'Asamblea no encontrada.');
        }

        // Solo se pueden editar asambleas programadas
        if ($asamblea->estado !== 'programada') {
            return redirect()->route('admin.asambleas.index')
                ->with('error', 'Solo se pueden editar asambleas en estado programada.');
        }

        // Obtener documentos existentes
        $documentos = DB::table('asamblea_documentos')
            ->where('asamblea_id', $id)
            ->where('activo', true)
            ->get();

        return view('admin.asambleas.edit', compact('asamblea', 'documentos', 'propiedad'));
    }

    /**
     * Update the specified asamblea.
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $asamblea = DB::table('asambleas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$asamblea) {
            return redirect()->route('admin.asambleas.index')
                ->with('error', 'Asamblea no encontrada.');
        }

        // Solo se pueden editar asambleas programadas
        if ($asamblea->estado !== 'programada') {
            return redirect()->route('admin.asambleas.index')
                ->with('error', 'Solo se pueden editar asambleas en estado programada.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:ordinaria,extraordinaria',
            'modalidad' => 'required|in:presencial,virtual,mixta',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'quorum_minimo' => 'required|numeric|min:0|max:100',
            'url_transmision' => 'nullable|url|max:500',
            'proveedor_transmision' => 'nullable|in:daily,livekit,agora,twilio',
            'token_transmision' => 'nullable|string',
            'archivos' => 'nullable|array',
            'archivos.*' => 'file|max:10240',
            'tipos_archivo' => 'nullable|array',
            'tipos_archivo.*' => 'in:orden_dia,acta,presupuesto,soporte,otro',
            'visible_para_archivos' => 'nullable|array',
            'visible_para_archivos.*' => 'in:todos,propietarios,administracion',
            'archivos_eliminar' => 'nullable|array',
            'archivos_eliminar.*' => 'integer|exists:asamblea_documentos,id',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar asamblea
            DB::table('asambleas')
                ->where('id', $id)
                ->update([
                    'titulo' => $validated['titulo'],
                    'descripcion' => $validated['descripcion'] ?? null,
                    'tipo' => $validated['tipo'],
                    'modalidad' => $validated['modalidad'],
                    'fecha_inicio' => $validated['fecha_inicio'],
                    'fecha_fin' => $validated['fecha_fin'],
                    'quorum_minimo' => $validated['quorum_minimo'],
                    'url_transmision' => $validated['url_transmision'] ?? null,
                    'proveedor_transmision' => $validated['proveedor_transmision'] ?? null,
                    'token_transmision' => $validated['token_transmision'] ?? null,
                    'updated_at' => now(),
                ]);

            // Eliminar archivos marcados
            if (!empty($validated['archivos_eliminar'])) {
                $archivosEliminar = DB::table('asamblea_documentos')
                    ->whereIn('id', $validated['archivos_eliminar'])
                    ->where('asamblea_id', $id)
                    ->get();

                foreach ($archivosEliminar as $archivo) {
                    try {
                        $urlParts = explode('/', $archivo->archivo_url);
                        $publicId = pathinfo(end($urlParts), PATHINFO_FILENAME);
                        Cloudinary::uploadApi()->destroy('domoph/asambleas/documentos/' . $publicId);
                    } catch (\Exception $e) {
                        \Log::warning('No se pudo eliminar archivo de Cloudinary: ' . $e->getMessage());
                    }
                }

                DB::table('asamblea_documentos')
                    ->whereIn('id', $validated['archivos_eliminar'])
                    ->where('asamblea_id', $id)
                    ->update(['activo' => false, 'deleted_at' => now()]);
            }

            // Subir nuevos archivos
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $index => $archivo) {
                    try {
                        $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                            'folder' => 'domoph/asambleas/documentos',
                            'resource_type' => 'auto',
                        ]);

                        $tipoArchivo = $request->tipos_archivo[$index] ?? 'otro';
                        $visiblePara = $request->visible_para_archivos[$index] ?? 'todos';

                        DB::table('asamblea_documentos')->insert([
                            'asamblea_id' => $id,
                            'nombre' => $archivo->getClientOriginalName(),
                            'tipo' => $tipoArchivo,
                            'archivo_url' => $result['secure_url'] ?? $result['url'] ?? null,
                            'visible_para' => $visiblePara,
                            'subido_por' => auth()->id(),
                            'activo' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error al subir archivo de asamblea a Cloudinary: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.asambleas.index')
                ->with('success', 'Asamblea actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar asamblea: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la asamblea.');
        }
    }

    /**
     * Remove the specified asamblea.
     */
    public function destroy($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $asamblea = DB::table('asambleas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$asamblea) {
            return redirect()->route('admin.asambleas.index')
                ->with('error', 'Asamblea no encontrada.');
        }

        // Solo se pueden eliminar asambleas programadas
        if ($asamblea->estado !== 'programada') {
            return redirect()->route('admin.asambleas.index')
                ->with('error', 'Solo se pueden eliminar asambleas en estado programada.');
        }

        DB::beginTransaction();
        try {
            // Eliminar documentos de Cloudinary
            $documentos = DB::table('asamblea_documentos')
                ->where('asamblea_id', $id)
                ->get();

            foreach ($documentos as $archivo) {
                try {
                    $urlParts = explode('/', $archivo->archivo_url);
                    $publicId = pathinfo(end($urlParts), PATHINFO_FILENAME);
                    Cloudinary::uploadApi()->destroy('domoph/asambleas/documentos/' . $publicId);
                } catch (\Exception $e) {
                    \Log::warning('No se pudo eliminar archivo de Cloudinary: ' . $e->getMessage());
                }
            }

            // Eliminar asamblea (cascade eliminará documentos, asistencias, votaciones, etc.)
            DB::table('asambleas')->where('id', $id)->delete();

            DB::commit();

            return redirect()->route('admin.asambleas.index')
                ->with('success', 'Asamblea eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar asamblea: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al eliminar la asamblea.');
        }
    }

    /**
     * Store a new votacion for the asamblea.
     */
    public function storeVotacion(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $asamblea = DB::table('asambleas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$asamblea) {
            return redirect()->route('admin.asambleas.index')
                ->with('error', 'Asamblea no encontrada.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:si_no,opcion_multiple',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'opciones' => 'required|array|min:2',
            'opciones.*' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Crear votación
            $votacionId = DB::table('asamblea_votaciones')->insertGetId([
                'asamblea_id' => $id,
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'tipo' => $validated['tipo'],
                'estado' => 'abierta',
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear opciones
            foreach ($validated['opciones'] as $index => $opcion) {
                DB::table('asamblea_votacion_opciones')->insert([
                    'votacion_id' => $votacionId,
                    'opcion' => $opcion,
                    'orden' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.asambleas.show', $id)
                ->with('success', 'Votación creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear votación: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la votación.');
        }
    }
}
