<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class EcommerceController extends Controller
{
    /**
     * Mostrar la lista de publicaciones (productos)
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $query = DB::table('ecommerce_publicaciones')
            ->leftJoin('residentes', 'ecommerce_publicaciones.residente_id', '=', 'residentes.id')
            ->leftJoin('users', 'residentes.user_id', '=', 'users.id')
            ->leftJoin('ecommerce_categorias', 'ecommerce_publicaciones.categoria_id', '=', 'ecommerce_categorias.id')
            ->where('ecommerce_publicaciones.copropiedad_id', $propiedad->id)
            ->select(
                'ecommerce_publicaciones.*',
                'users.nombre as residente_nombre',
                'ecommerce_categorias.nombre as categoria_nombre'
            );

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('ecommerce_publicaciones.estado', $request->estado);
        }

        // Filtro por tipo
        if ($request->filled('tipo_publicacion')) {
            $query->where('ecommerce_publicaciones.tipo_publicacion', $request->tipo_publicacion);
        }

        // Filtro por categoría
        if ($request->filled('categoria_id')) {
            $query->where('ecommerce_publicaciones.categoria_id', $request->categoria_id);
        }

        // Filtro por activo
        if ($request->filled('activo')) {
            $query->where('ecommerce_publicaciones.activo', $request->activo == '1');
        } else {
            // Por defecto mostrar solo activas
            $query->where('ecommerce_publicaciones.activo', true);
        }

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('ecommerce_publicaciones.titulo', 'like', "%{$buscar}%")
                  ->orWhere('ecommerce_publicaciones.descripcion', 'like', "%{$buscar}%");
            });
        }

        $publicaciones = $query->orderBy('ecommerce_publicaciones.fecha_publicacion', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Obtener categorías para el filtro
        $categorias = DB::table('ecommerce_categorias')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('admin.ecommerce.index', compact('publicaciones', 'categorias', 'propiedad'));
    }

    /**
     * Mostrar el formulario de creación de una publicación
     */
    public function create()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $categorias = DB::table('ecommerce_categorias')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $residentes = DB::table('residentes')
            ->join('unidades', 'residentes.unidad_id', '=', 'unidades.id')
            ->join('users', 'residentes.user_id', '=', 'users.id')
            ->where('unidades.propiedad_id', $propiedad->id)
            ->select('residentes.id', 'users.nombre')
            ->orderBy('users.nombre')
            ->get();

        return view('admin.ecommerce.create', compact('categorias', 'residentes', 'propiedad'));
    }

    /**
     * Guardar una nueva publicación
     */
    public function store(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $request->validate([
            'residente_id' => 'required|exists:residentes,id',
            'categoria_id' => 'required|exists:ecommerce_categorias,id',
            'tipo_publicacion' => 'required|in:venta,arriendo,servicio,otro',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'nullable|numeric|min:0',
            'moneda' => 'nullable|string|max:3',
            'es_negociable' => 'boolean',
            'estado' => 'required|in:publicado,pausado,finalizado,en_revision',
            'fecha_publicacion' => 'required|date',
            'activo' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $publicacionId = DB::table('ecommerce_publicaciones')->insertGetId([
                'copropiedad_id' => $propiedad->id,
                'residente_id' => $request->residente_id,
                'categoria_id' => $request->categoria_id,
                'tipo_publicacion' => $request->tipo_publicacion,
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'precio' => $request->precio,
                'moneda' => $request->moneda ?? 'COP',
                'es_negociable' => $request->has('es_negociable') ? true : false,
                'estado' => $request->estado,
                'fecha_publicacion' => $request->fecha_publicacion,
                'fecha_cierre' => null,
                'activo' => $request->has('activo') ? true : false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear contacto si se proporciona
            if ($request->filled('nombre_contacto') && $request->filled('telefono')) {
                DB::table('ecommerce_publicacion_contactos')->insert([
                    'publicacion_id' => $publicacionId,
                    'nombre_contacto' => $request->nombre_contacto,
                    'telefono' => $request->telefono,
                    'whatsapp' => $request->has('whatsapp') ? true : false,
                    'email' => $request->email,
                    'observaciones' => $request->observaciones_contacto,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Subir imágenes si se proporcionan
            if ($request->hasFile('imagenes')) {
                $orden = 0;
                foreach ($request->file('imagenes') as $imagen) {
                    if ($imagen && $imagen->isValid()) {
                        try {
                            $result = Cloudinary::uploadApi()->upload($imagen->getRealPath(), [
                                'folder' => 'domoph/ecommerce',
                                'public_id' => 'ecommerce_' . $publicacionId . '_' . time() . '_' . $orden,
                            ]);
                            
                            $rutaImagen = $result['secure_url'] ?? $result['url'] ?? null;
                            
                            if ($rutaImagen) {
                                DB::table('ecommerce_publicacion_imagenes')->insert([
                                    'publicacion_id' => $publicacionId,
                                    'ruta_imagen' => $rutaImagen,
                                    'orden' => $orden,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                                $orden++;
                            }
                        } catch (\Exception $e) {
                            \Log::error('Error al subir imagen de ecommerce a Cloudinary: ' . $e->getMessage(), [
                                'trace' => $e->getTraceAsString()
                            ]);
                            // Continuar con las demás imágenes aunque una falle
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.ecommerce.index')
                ->with('success', 'Publicación creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear publicación: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la publicación.');
        }
    }

    /**
     * Mostrar una publicación específica
     */
    public function show($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $publicacion = DB::table('ecommerce_publicaciones')
            ->leftJoin('residentes', 'ecommerce_publicaciones.residente_id', '=', 'residentes.id')
            ->leftJoin('users', 'residentes.user_id', '=', 'users.id')
            ->leftJoin('ecommerce_categorias', 'ecommerce_publicaciones.categoria_id', '=', 'ecommerce_categorias.id')
            ->where('ecommerce_publicaciones.id', $id)
            ->where('ecommerce_publicaciones.copropiedad_id', $propiedad->id)
            ->select(
                'ecommerce_publicaciones.*',
                'users.nombre as residente_nombre',
                'ecommerce_categorias.nombre as categoria_nombre'
            )
            ->first();

        if (!$publicacion) {
            return redirect()->route('admin.ecommerce.index')
                ->with('error', 'Publicación no encontrada.');
        }

        // Obtener contactos
        $contactos = DB::table('ecommerce_publicacion_contactos')
            ->where('publicacion_id', $id)
            ->get();

        // Obtener imágenes
        $imagenes = DB::table('ecommerce_publicacion_imagenes')
            ->where('publicacion_id', $id)
            ->orderBy('orden')
            ->get();

        return view('admin.ecommerce.show', compact('publicacion', 'contactos', 'imagenes', 'propiedad'));
    }

    /**
     * Mostrar el formulario de edición de una publicación
     */
    public function edit($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $publicacion = DB::table('ecommerce_publicaciones')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$publicacion) {
            return redirect()->route('admin.ecommerce.index')
                ->with('error', 'Publicación no encontrada.');
        }

        $categorias = DB::table('ecommerce_categorias')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $residentes = DB::table('residentes')
            ->join('unidades', 'residentes.unidad_id', '=', 'unidades.id')
            ->join('users', 'residentes.user_id', '=', 'users.id')
            ->where('unidades.propiedad_id', $propiedad->id)
            ->select('residentes.id', 'users.nombre')
            ->orderBy('users.nombre')
            ->get();

        // Obtener contactos
        $contactos = DB::table('ecommerce_publicacion_contactos')
            ->where('publicacion_id', $id)
            ->get();

        return view('admin.ecommerce.edit', compact('publicacion', 'categorias', 'residentes', 'contactos', 'propiedad'));
    }

    /**
     * Actualizar una publicación
     */
    public function update(Request $request, $id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $publicacion = DB::table('ecommerce_publicaciones')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$publicacion) {
            return redirect()->route('admin.ecommerce.index')
                ->with('error', 'Publicación no encontrada.');
        }

        $request->validate([
            'residente_id' => 'required|exists:residentes,id',
            'categoria_id' => 'required|exists:ecommerce_categorias,id',
            'tipo_publicacion' => 'required|in:venta,arriendo,servicio,otro',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'nullable|numeric|min:0',
            'moneda' => 'nullable|string|max:3',
            'es_negociable' => 'boolean',
            'estado' => 'required|in:publicado,pausado,finalizado,en_revision',
            'fecha_publicacion' => 'required|date',
            'activo' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $estadoAnterior = $publicacion->estado;
            $estadoNuevo = $request->estado;

            DB::table('ecommerce_publicaciones')
                ->where('id', $id)
                ->update([
                    'residente_id' => $request->residente_id,
                    'categoria_id' => $request->categoria_id,
                    'tipo_publicacion' => $request->tipo_publicacion,
                    'titulo' => $request->titulo,
                    'descripcion' => $request->descripcion,
                    'precio' => $request->precio,
                    'moneda' => $request->moneda ?? 'COP',
                    'es_negociable' => $request->has('es_negociable') ? true : false,
                    'estado' => $request->estado,
                    'fecha_publicacion' => $request->fecha_publicacion,
                    'fecha_cierre' => $request->estado === 'finalizado' ? now() : null,
                    'activo' => $request->has('activo') ? true : false,
                    'updated_at' => now(),
                ]);

            // Registrar cambio de estado en historial si cambió
            if ($estadoAnterior !== $estadoNuevo) {
                DB::table('ecommerce_publicacion_estados_historial')->insert([
                    'publicacion_id' => $id,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $estadoNuevo,
                    'cambiado_por' => auth()->user()->propiedad_id ? 
                        DB::table('residentes')
                            ->where('user_id', auth()->id())
                            ->value('id') : null,
                    'comentario' => 'Actualizado por administrador',
                    'fecha_cambio' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Subir nuevas imágenes si se proporcionan
            if ($request->hasFile('imagenes')) {
                // Obtener el orden máximo actual
                $maxOrden = DB::table('ecommerce_publicacion_imagenes')
                    ->where('publicacion_id', $id)
                    ->max('orden') ?? -1;
                
                $orden = $maxOrden + 1;
                
                foreach ($request->file('imagenes') as $imagen) {
                    if ($imagen && $imagen->isValid()) {
                        try {
                            $result = Cloudinary::uploadApi()->upload($imagen->getRealPath(), [
                                'folder' => 'domoph/ecommerce',
                                'public_id' => 'ecommerce_' . $id . '_' . time() . '_' . $orden,
                            ]);
                            
                            $rutaImagen = $result['secure_url'] ?? $result['url'] ?? null;
                            
                            if ($rutaImagen) {
                                DB::table('ecommerce_publicacion_imagenes')->insert([
                                    'publicacion_id' => $id,
                                    'ruta_imagen' => $rutaImagen,
                                    'orden' => $orden,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                                $orden++;
                            }
                        } catch (\Exception $e) {
                            \Log::error('Error al subir imagen de ecommerce a Cloudinary: ' . $e->getMessage(), [
                                'trace' => $e->getTraceAsString()
                            ]);
                            // Continuar con las demás imágenes aunque una falle
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.ecommerce.index')
                ->with('success', 'Publicación actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar publicación: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la publicación.');
        }
    }

    /**
     * Eliminar una publicación
     */
    public function destroy($id)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $publicacion = DB::table('ecommerce_publicaciones')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$publicacion) {
            return redirect()->route('admin.ecommerce.index')
                ->with('error', 'Publicación no encontrada.');
        }

        DB::beginTransaction();
        try {
            // Eliminar contactos (cascade)
            DB::table('ecommerce_publicacion_contactos')
                ->where('publicacion_id', $id)
                ->delete();

            // Eliminar imágenes (cascade)
            DB::table('ecommerce_publicacion_imagenes')
                ->where('publicacion_id', $id)
                ->delete();

            // Eliminar historial de estados (cascade)
            DB::table('ecommerce_publicacion_estados_historial')
                ->where('publicacion_id', $id)
                ->delete();

            // Eliminar publicación
            DB::table('ecommerce_publicaciones')
                ->where('id', $id)
                ->delete();

            DB::commit();

            return redirect()->route('admin.ecommerce.index')
                ->with('success', 'Publicación eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar publicación: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al eliminar la publicación.');
        }
    }

    /**
     * Aprobar una publicación
     */
    public function aprobar($id)
    {
        return $this->cambiarEstado($id, 'publicado', 'Publicación aprobada exitosamente.');
    }

    /**
     * Pausar una publicación
     */
    public function pausar($id)
    {
        return $this->cambiarEstado($id, 'pausado', 'Publicación pausada exitosamente.');
    }

    /**
     * Finalizar una publicación
     */
    public function finalizar($id)
    {
        return $this->cambiarEstado($id, 'finalizado', 'Publicación finalizada exitosamente.');
    }

    /**
     * Cambiar el estado de una publicación
     */
    private function cambiarEstado($id, $nuevoEstado, $mensaje)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No hay propiedad asignada.'], 400);
            }
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $publicacion = DB::table('ecommerce_publicaciones')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedad->id)
            ->first();

        if (!$publicacion) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Publicación no encontrada.'], 404);
            }
            return redirect()->route('admin.ecommerce.index')
                ->with('error', 'Publicación no encontrada.');
        }

        DB::beginTransaction();
        try {
            $estadoAnterior = $publicacion->estado;

            DB::table('ecommerce_publicaciones')
                ->where('id', $id)
                ->update([
                    'estado' => $nuevoEstado,
                    'fecha_cierre' => $nuevoEstado === 'finalizado' ? now() : null,
                    'updated_at' => now(),
                ]);

            // Registrar cambio de estado en historial
            DB::table('ecommerce_publicacion_estados_historial')->insert([
                'publicacion_id' => $id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
                'cambiado_por' => auth()->user()->propiedad_id ? 
                    DB::table('residentes')
                        ->where('user_id', auth()->id())
                        ->value('id') : null,
                'comentario' => 'Cambio de estado por administrador',
                'fecha_cambio' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => $mensaje], 200);
            }

            return redirect()->route('admin.ecommerce.index')
                ->with('success', $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al cambiar estado de publicación: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Error al cambiar el estado.'], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al cambiar el estado de la publicación.');
        }
    }
}
