<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Carbon\Carbon;

class EcommerceController extends Controller
{
    /**
     * Obtener todas las publicaciones publicadas
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Obtener residente principal
        $residente = DB::table('residentes')
            ->where('user_id', $user->id)
            ->where('es_principal', true)
            ->whereNull('deleted_at')
            ->first();

        if (!$residente) {
            $residente = DB::table('residentes')
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->first();
        }

        if (!$residente) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de residente'
            ], 404);
        }

        // Obtener propiedad
        $unidad = DB::table('unidades')
            ->where('id', $residente->unidad_id)
            ->first();

        if (!$unidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de unidad'
            ], 404);
        }

        // Verificar si la propiedad tiene ecommerce activado
        $activarTienda = DB::table('configuraciones_propiedad')
            ->where('propiedad_id', $unidad->propiedad_id)
            ->where('clave', 'activar_tienda')
            ->where('valor', 'true')
            ->exists();

        if (!$activarTienda) {
            return response()->json([
                'success' => false,
                'message' => 'El ecommerce no está activado para esta propiedad'
            ], 403);
        }

        // Obtener publicaciones publicadas
        $publicaciones = DB::table('ecommerce_publicaciones')
            ->where('copropiedad_id', $unidad->propiedad_id)
            ->where('estado', 'publicado')
            ->where('activo', true)
            ->orderBy('fecha_publicacion', 'desc')
            ->get()
            ->map(function ($publicacion) {
                return $this->formatearPublicacion($publicacion);
            });

        return response()->json([
            'success' => true,
            'data' => $publicaciones
        ], 200);
    }

    /**
     * Obtener mis publicaciones (del residente autenticado)
     */
    public function misPublicaciones(Request $request)
    {
        $user = $request->user();
        
        // Obtener residente
        $residente = DB::table('residentes')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$residente) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de residente'
            ], 404);
        }

        // Obtener todas las publicaciones del residente (todos los estados)
        $publicaciones = DB::table('ecommerce_publicaciones')
            ->where('residente_id', $residente->id)
            ->whereNull('deleted_at')
            ->orderBy('fecha_publicacion', 'desc')
            ->get()
            ->map(function ($publicacion) {
                return $this->formatearPublicacion($publicacion);
            });

        return response()->json([
            'success' => true,
            'data' => $publicaciones
        ], 200);
    }

    /**
     * Crear una nueva publicación
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        // Obtener residente
        $residente = DB::table('residentes')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$residente) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de residente'
            ], 404);
        }

        // Obtener propiedad
        $unidad = DB::table('unidades')
            ->where('id', $residente->unidad_id)
            ->first();

        if (!$unidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de unidad'
            ], 404);
        }

        // Verificar si la propiedad tiene ecommerce activado
        $activarTienda = DB::table('configuraciones_propiedad')
            ->where('propiedad_id', $unidad->propiedad_id)
            ->where('clave', 'activar_tienda')
            ->where('valor', 'true')
            ->exists();

        if (!$activarTienda) {
            return response()->json([
                'success' => false,
                'message' => 'El ecommerce no está activado para esta propiedad'
            ], 403);
        }

        // Validar datos
        $validator = Validator::make($request->all(), [
            'categoria_id' => 'required|exists:ecommerce_categorias,id',
            'tipo_publicacion' => 'required|in:venta,arriendo,servicio,otro',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'nullable|numeric|min:0',
            'moneda' => 'nullable|string|max:3',
            'es_negociable' => 'boolean',
            'nombre_contacto' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'whatsapp' => 'boolean',
            'email' => 'nullable|email|max:255',
            'observaciones_contacto' => 'nullable|string',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar configuración de aprobación
        $requiereAprobacion = DB::table('configuraciones_propiedad')
            ->where('propiedad_id', $unidad->propiedad_id)
            ->where('clave', 'ecommerce_requiere_aprobacion')
            ->where('valor', 'true')
            ->exists();

        $estado = $requiereAprobacion ? 'en_revision' : 'publicado';

        DB::beginTransaction();
        try {
            // Crear publicación
            $publicacionId = DB::table('ecommerce_publicaciones')->insertGetId([
                'copropiedad_id' => $unidad->propiedad_id,
                'residente_id' => $residente->id,
                'categoria_id' => $request->categoria_id,
                'tipo_publicacion' => $request->tipo_publicacion,
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'precio' => $request->precio,
                'moneda' => $request->moneda ?? 'COP',
                'es_negociable' => $request->has('es_negociable') ? (bool) $request->es_negociable : false,
                'estado' => $estado,
                'fecha_publicacion' => now(),
                'fecha_cierre' => null,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear contacto
            DB::table('ecommerce_publicacion_contactos')->insert([
                'publicacion_id' => $publicacionId,
                'nombre_contacto' => $request->nombre_contacto,
                'telefono' => $request->telefono,
                'whatsapp' => $request->has('whatsapp') ? (bool) $request->whatsapp : false,
                'email' => $request->email,
                'observaciones' => $request->observaciones_contacto,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

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
                            \Log::error('Error al subir imagen de ecommerce a Cloudinary: ' . $e->getMessage());
                        }
                    }
                }
            }

            DB::commit();

            // Obtener la publicación creada
            $publicacion = DB::table('ecommerce_publicaciones')
                ->where('id', $publicacionId)
                ->first();

            return response()->json([
                'success' => true,
                'message' => $requiereAprobacion 
                    ? 'Publicación creada. Está en revisión y será publicada después de la aprobación del administrador.'
                    : 'Publicación creada exitosamente.',
                'data' => $this->formatearPublicacion($publicacion)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear publicación de ecommerce: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la publicación'
            ], 500);
        }
    }

    /**
     * Actualizar una publicación (solo del residente autenticado)
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        
        // Obtener residente
        $residente = DB::table('residentes')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$residente) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de residente'
            ], 404);
        }

        // Verificar que la publicación pertenezca al residente
        $publicacion = DB::table('ecommerce_publicaciones')
            ->where('id', $id)
            ->where('residente_id', $residente->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$publicacion) {
            return response()->json([
                'success' => false,
                'message' => 'Publicación no encontrada o no tienes permisos para editarla'
            ], 404);
        }

        // Validar datos
        $validator = Validator::make($request->all(), [
            'categoria_id' => 'sometimes|required|exists:ecommerce_categorias,id',
            'tipo_publicacion' => 'sometimes|required|in:venta,arriendo,servicio,otro',
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'nullable|numeric|min:0',
            'moneda' => 'nullable|string|max:3',
            'es_negociable' => 'boolean',
            'estado' => 'sometimes|required|in:publicado,pausado,finalizado,en_revision',
            'nombre_contacto' => 'sometimes|required|string|max:255',
            'telefono' => 'sometimes|required|string|max:20',
            'whatsapp' => 'boolean',
            'email' => 'nullable|email|max:255',
            'observaciones_contacto' => 'nullable|string',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // IMPORTANTE: Con FormData en PUT, Laravel no parsea automáticamente el body
            // Necesitamos parsear manualmente el multipart/form-data
            if ($request->isMethod('put') || $request->isMethod('patch')) {
                $content = $request->getContent();
                $parsedData = [];
                
                if (!empty($content)) {
                    // Obtener el boundary del Content-Type
                    $contentType = $request->header('Content-Type', '');
                    $boundary = null;
                    if (preg_match('/boundary=([^;]+)/', $contentType, $matches)) {
                        $boundary = '--' . trim($matches[1]);
                    }
                    
                    if ($boundary) {
                        // Dividir por el boundary
                        $parts = explode($boundary, $content);
                        foreach ($parts as $part) {
                            // Buscar campos con name="..."
                            if (preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)"\s*\r?\n\r?\n(.*?)(?=\r?\n--|$)/s', $part, $matches)) {
                                $fieldName = $matches[1];
                                $fieldValue = trim($matches[2]);
                                // Limpiar el valor (remover saltos de línea finales y el boundary final)
                                $fieldValue = rtrim($fieldValue, "\r\n-");
                                if (!empty($fieldValue)) {
                                    $parsedData[$fieldName] = $fieldValue;
                                }
                            }
                        }
                    }
                }
                
                // Si se parseó correctamente, mergear con el request
                if (!empty($parsedData)) {
                    \Log::info('Datos parseados del FormData:', $parsedData);
                    $request->merge($parsedData);
                }
            }
            
            // Debug: Log de todos los datos recibidos
            \Log::info('Actualizando publicación ID: ' . $id);
            \Log::info('Datos recibidos:', $request->all());
            \Log::info('Estado recibido: ' . ($request->input('estado') ?? 'NO ENVIADO'));
            \Log::info('Estado actual en BD: ' . $publicacion->estado);
            
            // Actualizar publicación
            $updateData = [];
            if ($request->has('categoria_id')) $updateData['categoria_id'] = $request->categoria_id;
            if ($request->has('tipo_publicacion')) $updateData['tipo_publicacion'] = $request->tipo_publicacion;
            if ($request->has('titulo')) $updateData['titulo'] = $request->titulo;
            if ($request->has('descripcion')) $updateData['descripcion'] = $request->descripcion;
            if ($request->has('precio')) $updateData['precio'] = $request->precio;
            if ($request->has('moneda')) $updateData['moneda'] = $request->moneda;
            if ($request->has('es_negociable')) $updateData['es_negociable'] = (bool) $request->es_negociable;
            
            // Obtener el estado (ahora debería estar disponible después del merge)
            $estadoNuevo = $request->input('estado');
            
            \Log::info('Estado recibido (input): ' . ($estadoNuevo ?? 'NULL'));
            
            if ($estadoNuevo !== null && $estadoNuevo !== '') {
                $updateData['estado'] = $estadoNuevo;
                \Log::info('Estado a actualizar en BD: ' . $estadoNuevo);
                
                // Registrar cambio de estado en historial
                if ($publicacion->estado !== $estadoNuevo) {
                    DB::table('ecommerce_publicacion_estados_historial')->insert([
                        'publicacion_id' => $id,
                        'estado_anterior' => $publicacion->estado,
                        'estado_nuevo' => $estadoNuevo,
                        'cambiado_por' => $residente->id,
                        'comentario' => 'Cambio de estado realizado por el residente',
                        'fecha_cambio' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    \Log::info('Historial de estado registrado: ' . $publicacion->estado . ' -> ' . $estadoNuevo);
                }
            } else {
                \Log::warning('No se recibió el campo estado en la petición o está vacío');
            }
            
            $updateData['updated_at'] = now();

            \Log::info('Datos a actualizar en BD:', $updateData);
            
            $actualizado = DB::table('ecommerce_publicaciones')
                ->where('id', $id)
                ->update($updateData);
            
            \Log::info('Filas actualizadas: ' . $actualizado);
            
            // Verificar el estado actualizado
            $publicacionActualizada = DB::table('ecommerce_publicaciones')
                ->where('id', $id)
                ->first();
            \Log::info('Estado después de actualizar: ' . $publicacionActualizada->estado);

            // Actualizar contacto si se proporciona
            if ($request->has('nombre_contacto') || $request->has('telefono')) {
                $contactoUpdate = [];
                if ($request->has('nombre_contacto')) $contactoUpdate['nombre_contacto'] = $request->nombre_contacto;
                if ($request->has('telefono')) $contactoUpdate['telefono'] = $request->telefono;
                if ($request->has('whatsapp')) $contactoUpdate['whatsapp'] = (bool) $request->whatsapp;
                if ($request->has('email')) $contactoUpdate['email'] = $request->email;
                if ($request->has('observaciones_contacto')) $contactoUpdate['observaciones'] = $request->observaciones_contacto;
                $contactoUpdate['updated_at'] = now();

                DB::table('ecommerce_publicacion_contactos')
                    ->where('publicacion_id', $id)
                    ->update($contactoUpdate);
            }

            // Agregar nuevas imágenes si se proporcionan
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
                            \Log::error('Error al subir imagen de ecommerce a Cloudinary: ' . $e->getMessage());
                        }
                    }
                }
            }

            DB::commit();

            // Obtener la publicación actualizada (después del commit)
            $publicacionActualizada = DB::table('ecommerce_publicaciones')
                ->where('id', $id)
                ->first();
            
            \Log::info('Publicación actualizada - Estado final en BD: ' . $publicacionActualizada->estado);

            return response()->json([
                'success' => true,
                'message' => 'Publicación actualizada exitosamente',
                'data' => $this->formatearPublicacion($publicacionActualizada)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar publicación de ecommerce: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la publicación'
            ], 500);
        }
    }

    /**
     * Obtener detalle de una publicación
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        // Obtener residente
        $residente = DB::table('residentes')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$residente) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de residente'
            ], 404);
        }

        // Obtener propiedad
        $unidad = DB::table('unidades')
            ->where('id', $residente->unidad_id)
            ->first();

        if (!$unidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de unidad'
            ], 404);
        }

        // Obtener publicación (solo publicadas para ver, o del residente para editar)
        $publicacion = DB::table('ecommerce_publicaciones')
            ->where('id', $id)
            ->where('copropiedad_id', $unidad->propiedad_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$publicacion) {
            return response()->json([
                'success' => false,
                'message' => 'Publicación no encontrada'
            ], 404);
        }

        // Solo mostrar publicadas, excepto si es del residente autenticado
        if ($publicacion->estado !== 'publicado' && $publicacion->residente_id !== $residente->id) {
            return response()->json([
                'success' => false,
                'message' => 'Publicación no disponible'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatearPublicacion($publicacion),
            'puede_editar' => $publicacion->residente_id === $residente->id
        ], 200);
    }

    /**
     * Obtener categorías activas
     */
    public function categorias(Request $request)
    {
        $categorias = DB::table('ecommerce_categorias')
            ->where('activo', true)
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(function ($categoria) {
                return [
                    'id' => $categoria->id,
                    'nombre' => $categoria->nombre,
                    'slug' => $categoria->slug,
                    'descripcion' => $categoria->descripcion,
                    'icono' => $categoria->icono,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categorias
        ], 200);
    }

    /**
     * Formatear publicación con imágenes y contactos
     */
    private function formatearPublicacion($publicacion)
    {
        // Obtener imágenes
        $imagenes = DB::table('ecommerce_publicacion_imagenes')
            ->where('publicacion_id', $publicacion->id)
            ->orderBy('orden', 'asc')
            ->get()
            ->map(function ($imagen) {
                return [
                    'id' => $imagen->id,
                    'ruta_imagen' => $imagen->ruta_imagen,
                    'orden' => $imagen->orden,
                ];
            });

        // Obtener contactos
        $contactos = DB::table('ecommerce_publicacion_contactos')
            ->where('publicacion_id', $publicacion->id)
            ->get()
            ->map(function ($contacto) {
                return [
                    'id' => $contacto->id,
                    'nombre_contacto' => $contacto->nombre_contacto,
                    'telefono' => $contacto->telefono,
                    'whatsapp' => (bool) $contacto->whatsapp,
                    'email' => $contacto->email,
                    'observaciones' => $contacto->observaciones,
                ];
            });

        // Obtener información del residente
        $residentePublicacion = DB::table('residentes')
            ->join('users', 'residentes.user_id', '=', 'users.id')
            ->where('residentes.id', $publicacion->residente_id)
            ->select('users.nombre as residente_nombre', 'users.avatar as residente_avatar')
            ->first();

        // Obtener categoría
        $categoria = DB::table('ecommerce_categorias')
            ->where('id', $publicacion->categoria_id)
            ->first();

        return [
            'id' => $publicacion->id,
            'titulo' => $publicacion->titulo,
            'descripcion' => $publicacion->descripcion,
            'tipo_publicacion' => $publicacion->tipo_publicacion,
            'categoria' => $categoria ? [
                'id' => $categoria->id,
                'nombre' => $categoria->nombre,
                'slug' => $categoria->slug,
                'icono' => $categoria->icono,
            ] : null,
            'precio' => $publicacion->precio ? (float) $publicacion->precio : null,
            'moneda' => $publicacion->moneda,
            'es_negociable' => (bool) $publicacion->es_negociable,
            'estado' => $publicacion->estado,
            'fecha_publicacion' => Carbon::parse($publicacion->fecha_publicacion)->format('Y-m-d H:i:s'),
            'residente' => $residentePublicacion ? [
                'nombre' => $residentePublicacion->residente_nombre,
                'avatar' => $residentePublicacion->residente_avatar,
            ] : null,
            'imagenes' => $imagenes,
            'contactos' => $contactos,
        ];
    }
}
