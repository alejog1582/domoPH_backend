<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Comunicado;

class ConsejoComunicacionController extends Controller
{
    /**
     * Display a listing of comunicaciones (most recent first).
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = DB::table('consejo_comunicaciones')
            ->where('copropiedad_id', $propiedad->id)
            ->orderBy('fecha_publicacion', 'desc')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('visible_para')) {
            $query->where('visible_para', $request->visible_para);
        }

        $comunicaciones = $query->paginate(15)->appends($request->query());

        return view('admin.consejo-comunicaciones.index', compact('comunicaciones', 'propiedad'));
    }

    /**
     * Show the form for creating a new comunicacion.
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        return view('admin.consejo-comunicaciones.create', compact('propiedad'));
    }

    /**
     * Store a newly created comunicacion.
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
            'contenido' => 'required|string',
            'tipo' => 'required|in:informativa,urgente,circular,recordatorio',
            'visible_para' => 'required|in:consejo,propietarios,residentes,todos',
            'archivos' => 'nullable|array',
            'archivos.*' => 'file|max:10240', // 10MB max
        ]);

        DB::beginTransaction();
        try {
            // Crear comunicacion
            $comunicacionId = DB::table('consejo_comunicaciones')->insertGetId([
                'copropiedad_id' => $propiedad->id,
                'titulo' => $validated['titulo'],
                'contenido' => $validated['contenido'],
                'tipo' => $validated['tipo'],
                'visible_para' => $validated['visible_para'],
                'estado' => 'borrador',
                'fecha_publicacion' => null,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Subir archivos a Cloudinary
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    try {
                        $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                            'folder' => 'domoph/consejo/comunicaciones',
                            'resource_type' => 'auto',
                        ]);

                        DB::table('consejo_comunicacion_archivos')->insert([
                            'comunicacion_id' => $comunicacionId,
                            'nombre_archivo' => $archivo->getClientOriginalName(),
                            'ruta_archivo' => $result['secure_url'] ?? $result['url'] ?? null,
                            'tipo_archivo' => $archivo->getMimeType(),
                            'tamaño' => $archivo->getSize(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error al subir archivo de comunicación a Cloudinary: ' . $e->getMessage());
                    }
                }
            }

            // Si visible_para es residentes, propietarios o todos, sincronizar con comunicados
            if (in_array($validated['visible_para'], ['residentes', 'propietarios', 'todos'])) {
                $this->sincronizarConComunicados($comunicacionId, $propiedad->id, $validated);
            }

            DB::commit();

            return redirect()->route('admin.consejo-comunicaciones.index')
                ->with('success', 'Comunicación creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear comunicación: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la comunicación.');
        }
    }

    /**
     * Display the specified comunicacion.
     */
    public function show($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $comunicacion = DB::table('consejo_comunicaciones')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$comunicacion) {
            return redirect()->route('admin.consejo-comunicaciones.index')
                ->with('error', 'Comunicación no encontrada.');
        }

        // Obtener archivos
        $archivos = DB::table('consejo_comunicacion_archivos')
            ->where('comunicacion_id', $id)
            ->get();

        // Verificar si puede editar/eliminar
        $puede_editar = $comunicacion->estado === 'borrador';
        $puede_eliminar = $comunicacion->estado === 'borrador';

        return view('admin.consejo-comunicaciones.show', compact('comunicacion', 'archivos', 'puede_editar', 'puede_eliminar', 'propiedad'));
    }

    /**
     * Show the form for editing the specified comunicacion.
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $comunicacion = DB::table('consejo_comunicaciones')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$comunicacion) {
            return redirect()->route('admin.consejo-comunicaciones.index')
                ->with('error', 'Comunicación no encontrada.');
        }

        // Solo se pueden editar comunicaciones en borrador
        if ($comunicacion->estado !== 'borrador') {
            return redirect()->route('admin.consejo-comunicaciones.index')
                ->with('error', 'Solo se pueden editar comunicaciones en estado borrador.');
        }

        // Obtener archivos existentes
        $archivos = DB::table('consejo_comunicacion_archivos')
            ->where('comunicacion_id', $id)
            ->get();

        return view('admin.consejo-comunicaciones.edit', compact('comunicacion', 'archivos', 'propiedad'));
    }

    /**
     * Update the specified comunicacion.
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $comunicacion = DB::table('consejo_comunicaciones')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$comunicacion) {
            return redirect()->route('admin.consejo-comunicaciones.index')
                ->with('error', 'Comunicación no encontrada.');
        }

        // Solo se pueden editar comunicaciones en borrador
        if ($comunicacion->estado !== 'borrador') {
            return redirect()->route('admin.consejo-comunicaciones.index')
                ->with('error', 'Solo se pueden editar comunicaciones en estado borrador.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
            'tipo' => 'required|in:informativa,urgente,circular,recordatorio',
            'visible_para' => 'required|in:consejo,propietarios,residentes,todos',
            'archivos' => 'nullable|array',
            'archivos.*' => 'file|max:10240',
            'archivos_eliminar' => 'nullable|array',
            'archivos_eliminar.*' => 'integer|exists:consejo_comunicacion_archivos,id',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar comunicacion
            DB::table('consejo_comunicaciones')
                ->where('id', $id)
                ->update([
                    'titulo' => $validated['titulo'],
                    'contenido' => $validated['contenido'],
                    'tipo' => $validated['tipo'],
                    'visible_para' => $validated['visible_para'],
                    'updated_at' => now(),
                ]);

            // Eliminar archivos marcados
            if (!empty($validated['archivos_eliminar'])) {
                $archivosEliminar = DB::table('consejo_comunicacion_archivos')
                    ->whereIn('id', $validated['archivos_eliminar'])
                    ->where('comunicacion_id', $id)
                    ->get();

                foreach ($archivosEliminar as $archivo) {
                    try {
                        $urlParts = explode('/', $archivo->ruta_archivo);
                        $publicId = pathinfo(end($urlParts), PATHINFO_FILENAME);
                        Cloudinary::uploadApi()->destroy('domoph/consejo/comunicaciones/' . $publicId);
                    } catch (\Exception $e) {
                        \Log::warning('No se pudo eliminar archivo de Cloudinary: ' . $e->getMessage());
                    }
                }

                DB::table('consejo_comunicacion_archivos')
                    ->whereIn('id', $validated['archivos_eliminar'])
                    ->where('comunicacion_id', $id)
                    ->delete();
            }

            // Subir nuevos archivos
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    try {
                        $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                            'folder' => 'domoph/consejo/comunicaciones',
                            'resource_type' => 'auto',
                        ]);

                        DB::table('consejo_comunicacion_archivos')->insert([
                            'comunicacion_id' => $id,
                            'nombre_archivo' => $archivo->getClientOriginalName(),
                            'ruta_archivo' => $result['secure_url'] ?? $result['url'] ?? null,
                            'tipo_archivo' => $archivo->getMimeType(),
                            'tamaño' => $archivo->getSize(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error al subir archivo de comunicación a Cloudinary: ' . $e->getMessage());
                    }
                }
            }

            // Obtener comunicación actualizada para sincronizar
            $comunicacionActualizada = DB::table('consejo_comunicaciones')
                ->where('id', $id)
                ->first();

            // Si visible_para es residentes, propietarios o todos, sincronizar con comunicados
            if (in_array($validated['visible_para'], ['residentes', 'propietarios', 'todos'])) {
                $this->sincronizarConComunicados($id, $propiedad->id, [
                    'titulo' => $validated['titulo'],
                    'contenido' => $validated['contenido'],
                    'tipo' => $validated['tipo'],
                ]);
            } else {
                // Si cambió a "consejo", eliminar de comunicados si existe
                $this->eliminarDeComunicados($id, $propiedad->id);
            }

            DB::commit();

            return redirect()->route('admin.consejo-comunicaciones.index')
                ->with('success', 'Comunicación actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar comunicación: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la comunicación.');
        }
    }

    /**
     * Remove the specified comunicacion.
     */
    public function destroy($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $comunicacion = DB::table('consejo_comunicaciones')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$comunicacion) {
            return redirect()->route('admin.consejo-comunicaciones.index')
                ->with('error', 'Comunicación no encontrada.');
        }

        // Solo se pueden eliminar comunicaciones en borrador
        if ($comunicacion->estado !== 'borrador') {
            return redirect()->route('admin.consejo-comunicaciones.index')
                ->with('error', 'Solo se pueden eliminar comunicaciones en estado borrador.');
        }

        DB::beginTransaction();
        try {
            // Eliminar archivos de Cloudinary
            $archivos = DB::table('consejo_comunicacion_archivos')
                ->where('comunicacion_id', $id)
                ->get();

            foreach ($archivos as $archivo) {
                try {
                    $urlParts = explode('/', $archivo->ruta_archivo);
                    $publicId = pathinfo(end($urlParts), PATHINFO_FILENAME);
                    Cloudinary::uploadApi()->destroy('domoph/consejo/comunicaciones/' . $publicId);
                } catch (\Exception $e) {
                    \Log::warning('No se pudo eliminar archivo de Cloudinary: ' . $e->getMessage());
                }
            }

            // Eliminar archivos
            DB::table('consejo_comunicacion_archivos')->where('comunicacion_id', $id)->delete();

            // Eliminar comunicación
            DB::table('consejo_comunicaciones')->where('id', $id)->delete();

            // Si estaba duplicada en comunicados, eliminar también
            if (in_array($comunicacion->visible_para, ['residentes', 'propietarios', 'todos'])) {
                Comunicado::where('titulo', $comunicacion->titulo)
                    ->where('copropiedad_id', $propiedad->id)
                    ->delete();
            }

            DB::commit();

            return redirect()->route('admin.consejo-comunicaciones.index')
                ->with('success', 'Comunicación eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar comunicación: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al eliminar la comunicación.');
        }
    }

    /**
     * Publicar comunicacion.
     */
    public function publicar($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $comunicacion = DB::table('consejo_comunicaciones')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$comunicacion) {
            return redirect()->route('admin.consejo-comunicaciones.index')
                ->with('error', 'Comunicación no encontrada.');
        }

        DB::beginTransaction();
        try {
            // Actualizar estado a publicada
            DB::table('consejo_comunicaciones')
                ->where('id', $id)
                ->update([
                    'estado' => 'publicada',
                    'fecha_publicacion' => now(),
                    'updated_at' => now(),
                ]);

            // Si visible_para es residentes, propietarios o todos, sincronizar con comunicados
            if (in_array($comunicacion->visible_para, ['residentes', 'propietarios', 'todos'])) {
                $this->sincronizarConComunicados($id, $propiedad->id, [
                    'titulo' => $comunicacion->titulo,
                    'contenido' => $comunicacion->contenido,
                    'tipo' => $comunicacion->tipo,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.consejo-comunicaciones.index')
                ->with('success', 'Comunicación publicada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al publicar comunicación: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al publicar la comunicación.');
        }
    }

    /**
     * Sincronizar comunicación del consejo con la tabla comunicados.
     */
    private function sincronizarConComunicados($comunicacionId, $copropiedadId, $datos)
    {
        try {
            // Obtener archivos de la comunicación
            $archivos = DB::table('consejo_comunicacion_archivos')
                ->where('comunicacion_id', $comunicacionId)
                ->get();

            $imagenPortada = null;
            if ($archivos->isNotEmpty()) {
                // Buscar primera imagen
                foreach ($archivos as $archivo) {
                    if (str_starts_with($archivo->tipo_archivo, 'image/')) {
                        $imagenPortada = $archivo->ruta_archivo;
                        break;
                    }
                }
            }

            // Buscar comunicado existente por título y copropiedad
            // Usamos el título como identificador principal
            $comunicadoExistente = Comunicado::where('copropiedad_id', $copropiedadId)
                ->where('titulo', $datos['titulo'])
                ->where('tipo', 'general')
                ->first();

            if ($comunicadoExistente) {
                // Actualizar comunicado existente
                $comunicadoExistente->update([
                    'titulo' => $datos['titulo'],
                    'contenido' => $datos['contenido'],
                    'imagen_portada' => $imagenPortada ?? $comunicadoExistente->imagen_portada,
                    'destacado' => $datos['tipo'] === 'urgente',
                    'fecha_publicacion' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Crear nuevo comunicado
                Comunicado::create([
                    'copropiedad_id' => $copropiedadId,
                    'titulo' => $datos['titulo'],
                    'contenido' => $datos['contenido'],
                    'tipo' => 'general',
                    'imagen_portada' => $imagenPortada,
                    'fecha_publicacion' => now(),
                    'fecha_expiracion' => null,
                    'destacado' => $datos['tipo'] === 'urgente',
                    'activo' => true,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al sincronizar comunicación con comunicados: ' . $e->getMessage());
            // No lanzar excepción para no interrumpir el flujo principal
        }
    }

    /**
     * Eliminar comunicación de la tabla comunicados si existe.
     */
    private function eliminarDeComunicados($comunicacionId, $copropiedadId)
    {
        try {
            // Obtener la comunicación del consejo para obtener el título y contenido
            $comunicacion = DB::table('consejo_comunicaciones')
                ->where('id', $comunicacionId)
                ->where('copropiedad_id', $copropiedadId)
                ->first();

            if ($comunicacion) {
                // Buscar comunicado asociado por título y tipo
                $comunicado = Comunicado::where('copropiedad_id', $copropiedadId)
                    ->where('titulo', $comunicacion->titulo)
                    ->where('tipo', 'general')
                    ->first();

                if ($comunicado) {
                    $comunicado->delete();
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error al eliminar comunicación de comunicados: ' . $e->getMessage());
            // No lanzar excepción para no interrumpir el flujo principal
        }
    }
}
