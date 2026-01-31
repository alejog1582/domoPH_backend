<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pqrs;
use App\Models\PqrsHistorial;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Carbon\Carbon;

class PqrsController extends Controller
{
    /**
     * Obtener PQRS de la unidad del residente
     */
    public function index(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Obtener el residente principal
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad.propiedad'])
            ->first();

        if (!$residente || !$residente->unidad || !$residente->unidad->propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar tu unidad o propiedad.',
                'error' => 'UNIT_NOT_FOUND'
            ], 404);
        }

        $propiedadId = $residente->unidad->propiedad->id;
        $unidadId = $residente->unidad->id;
        
        // Obtener todas las PQRS de la unidad
        $pqrs = Pqrs::where('copropiedad_id', $propiedadId)
            ->where('unidad_id', $unidadId)
            ->where('activo', true)
            ->orderBy('fecha_radicacion', 'desc')
            ->get()
            ->map(function ($pqrsItem) {
                return [
                    'id' => $pqrsItem->id,
                    'tipo' => $pqrsItem->tipo,
                    'categoria' => $pqrsItem->categoria,
                    'asunto' => $pqrsItem->asunto,
                    'descripcion' => $pqrsItem->descripcion,
                    'prioridad' => $pqrsItem->prioridad,
                    'estado' => $pqrsItem->estado,
                    'numero_radicado' => $pqrsItem->numero_radicado,
                    'fecha_radicacion' => $pqrsItem->fecha_radicacion 
                        ? $pqrsItem->fecha_radicacion->format('Y-m-d H:i:s')
                        : null,
                    'fecha_radicacion_formateada' => $pqrsItem->fecha_radicacion 
                        ? $pqrsItem->fecha_radicacion->format('d M Y')
                        : null,
                    'fecha_respuesta' => $pqrsItem->fecha_respuesta 
                        ? $pqrsItem->fecha_respuesta->format('Y-m-d H:i:s')
                        : null,
                    'respuesta' => $pqrsItem->respuesta,
                    'calificacion_servicio' => $pqrsItem->calificacion_servicio,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'pqrs' => $pqrs,
            ]
        ], 200);
    }

    /**
     * Obtener el detalle de una PQRS con su historial
     */
    public function show(Request $request, $id)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Obtener el residente principal
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad.propiedad'])
            ->first();

        if (!$residente || !$residente->unidad || !$residente->unidad->propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar tu unidad o propiedad.',
                'error' => 'UNIT_NOT_FOUND'
            ], 404);
        }

        $propiedadId = $residente->unidad->propiedad->id;
        $unidadId = $residente->unidad->id;

        // Verificar que la PQRS pertenezca a la unidad
        $pqrs = Pqrs::where('copropiedad_id', $propiedadId)
            ->where('unidad_id', $unidadId)
            ->where('id', $id)
            ->where('activo', true)
            ->first();

        if (!$pqrs) {
            return response()->json([
                'success' => false,
                'message' => 'PQRS no encontrada.',
                'error' => 'NOT_FOUND'
            ], 404);
        }

        // Obtener el historial
        $historial = DB::table('pqrs_historial')
            ->where('pqrs_id', $id)
            ->orderBy('fecha_cambio', 'desc')
            ->get()
            ->map(function ($registro) use ($propiedadId) {
                // Obtener el usuario que hizo el cambio
                $usuarioCambio = User::find($registro->cambiado_por);
                
                // Determinar si el registro fue hecho por un residente o por la administración
                // Verificar si el usuario tiene el rol "residente" para esta propiedad
                $esResidente = false;
                if ($usuarioCambio) {
                    $esResidente = $usuarioCambio->hasRole('residente', $propiedadId);
                }
                
                return [
                    'id' => $registro->id,
                    'estado_anterior' => $registro->estado_anterior,
                    'estado_nuevo' => $registro->estado_nuevo,
                    'comentario' => $registro->comentario,
                    'soporte_url' => $registro->soporte_url,
                    'fecha_cambio' => $registro->fecha_cambio 
                        ? Carbon::parse($registro->fecha_cambio)->format('Y-m-d H:i:s')
                        : null,
                    'fecha_cambio_formateada' => $registro->fecha_cambio 
                        ? Carbon::parse($registro->fecha_cambio)->format('d M Y H:i')
                        : null,
                    'cambiado_por' => $registro->cambiado_por,
                    'es_residente' => $esResidente,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'pqrs' => [
                    'id' => $pqrs->id,
                    'tipo' => $pqrs->tipo,
                    'categoria' => $pqrs->categoria,
                    'asunto' => $pqrs->asunto,
                    'descripcion' => $pqrs->descripcion,
                    'prioridad' => $pqrs->prioridad,
                    'estado' => $pqrs->estado,
                    'numero_radicado' => $pqrs->numero_radicado,
                    'fecha_radicacion' => $pqrs->fecha_radicacion 
                        ? $pqrs->fecha_radicacion->format('Y-m-d H:i:s')
                        : null,
                    'fecha_radicacion_formateada' => $pqrs->fecha_radicacion 
                        ? $pqrs->fecha_radicacion->format('d M Y')
                        : null,
                    'fecha_respuesta' => $pqrs->fecha_respuesta 
                        ? $pqrs->fecha_respuesta->format('Y-m-d H:i:s')
                        : null,
                    'respuesta' => $pqrs->respuesta,
                    'calificacion_servicio' => $pqrs->calificacion_servicio,
                    'adjuntos' => $pqrs->adjuntos,
                ],
                'historial' => $historial,
            ]
        ], 200);
    }

    /**
     * Crear una nueva PQRS
     */
    public function store(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Obtener el residente principal
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad.propiedad'])
            ->first();

        if (!$residente || !$residente->unidad || !$residente->unidad->propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar tu unidad o propiedad.',
                'error' => 'UNIT_NOT_FOUND'
            ], 404);
        }

        $propiedadId = $residente->unidad->propiedad->id;
        $unidadId = $residente->unidad->id;

        // Validar datos
        $validated = $request->validate([
            'tipo' => 'required|in:peticion,queja,reclamo,sugerencia',
            'categoria' => 'required|in:administracion,mantenimiento,seguridad,convivencia,servicios,otro',
            'asunto' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'prioridad' => 'nullable|in:baja,media,alta,critica',
            'soporte' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB máximo
        ], [
            'tipo.required' => 'El tipo de PQRS es obligatorio.',
            'tipo.in' => 'El tipo de PQRS no es válido.',
            'categoria.required' => 'La categoría es obligatoria.',
            'categoria.in' => 'La categoría no es válida.',
            'asunto.required' => 'El asunto es obligatorio.',
            'asunto.max' => 'El asunto no puede exceder 200 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'prioridad.in' => 'La prioridad no es válida.',
            'soporte.image' => 'El archivo debe ser una imagen.',
            'soporte.mimes' => 'La imagen debe ser jpeg, png, jpg o gif.',
            'soporte.max' => 'La imagen no puede exceder 5MB.',
        ]);

        try {
            DB::beginTransaction();

            // Generar número de radicado único
            $numeroRadicado = $this->generarNumeroRadicado($propiedadId);

            // Subir imagen a Cloudinary si se proporciona
            $soporteUrl = null;
            if ($request->hasFile('soporte')) {
                $uploadedFile = $request->file('soporte');
                $result = Cloudinary::uploadApi()->upload($uploadedFile->getRealPath(), [
                    'folder' => 'pqrs',
                    'resource_type' => 'image',
                ]);
                $soporteUrl = $result['secure_url'];
            }

            // Crear la PQRS
            $pqrs = Pqrs::create([
                'copropiedad_id' => $propiedadId,
                'unidad_id' => $unidadId,
                'residente_id' => $residente->id,
                'tipo' => $validated['tipo'],
                'categoria' => $validated['categoria'],
                'asunto' => $validated['asunto'],
                'descripcion' => $validated['descripcion'],
                'prioridad' => $validated['prioridad'] ?? 'media',
                'estado' => 'radicada',
                'canal' => 'app',
                'numero_radicado' => $numeroRadicado,
                'fecha_radicacion' => now(),
                'activo' => true,
            ]);

            // Crear registro inicial en el historial
            DB::table('pqrs_historial')->insert([
                'pqrs_id' => $pqrs->id,
                'estado_anterior' => null,
                'estado_nuevo' => 'radicada',
                'comentario' => 'PQRS radicada',
                'soporte_url' => $soporteUrl,
                'cambiado_por' => $user->id,
                'fecha_cambio' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'PQRS creada correctamente.',
                'data' => [
                    'pqrs' => [
                        'id' => $pqrs->id,
                        'numero_radicado' => $pqrs->numero_radicado,
                        'estado' => $pqrs->estado,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear PQRS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la PQRS: ' . $e->getMessage(),
                'error' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Agregar respuesta al historial de una PQRS
     */
    public function agregarRespuesta(Request $request, $id)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }

        // Obtener el residente principal
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad.propiedad'])
            ->first();

        if (!$residente || !$residente->unidad || !$residente->unidad->propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar tu unidad o propiedad.',
                'error' => 'UNIT_NOT_FOUND'
            ], 404);
        }

        $propiedadId = $residente->unidad->propiedad->id;
        $unidadId = $residente->unidad->id;

        // Verificar que la PQRS pertenezca a la unidad
        $pqrs = Pqrs::where('copropiedad_id', $propiedadId)
            ->where('unidad_id', $unidadId)
            ->where('id', $id)
            ->where('activo', true)
            ->first();

        if (!$pqrs) {
            return response()->json([
                'success' => false,
                'message' => 'PQRS no encontrada.',
                'error' => 'NOT_FOUND'
            ], 404);
        }

        // Validar que la PQRS no esté cerrada o rechazada
        if (in_array($pqrs->estado, ['cerrada', 'rechazada'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden agregar respuestas a una PQRS cerrada o rechazada.',
                'error' => 'PQRS_CLOSED'
            ], 400);
        }

        // Validar datos
        $validated = $request->validate([
            'comentario' => 'required|string|max:1000',
            'soporte' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB máximo
        ], [
            'comentario.required' => 'El comentario es obligatorio.',
            'comentario.max' => 'El comentario no puede exceder 1000 caracteres.',
            'soporte.image' => 'El archivo debe ser una imagen.',
            'soporte.mimes' => 'La imagen debe ser jpeg, png, jpg o gif.',
            'soporte.max' => 'La imagen no puede exceder 5MB.',
        ]);

        try {
            $soporteUrl = null;

            // Subir imagen a Cloudinary si se proporciona
            if ($request->hasFile('soporte')) {
                $uploadedFile = $request->file('soporte');
                $result = Cloudinary::uploadApi()->upload($uploadedFile->getRealPath(), [
                    'folder' => 'pqrs',
                    'resource_type' => 'image',
                ]);
                $soporteUrl = $result['secure_url'];
            }

            // Obtener el estado actual de la PQRS
            $estadoActual = $pqrs->estado;

            // Insertar en el historial
            DB::table('pqrs_historial')->insert([
                'pqrs_id' => $pqrs->id,
                'estado_anterior' => $estadoActual,
                'estado_nuevo' => $estadoActual, // El estado no cambia, solo se agrega comentario
                'comentario' => $validated['comentario'],
                'soporte_url' => $soporteUrl,
                'cambiado_por' => $user->id,
                'fecha_cambio' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Respuesta agregada correctamente.',
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error al agregar respuesta a PQRS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar la respuesta: ' . $e->getMessage(),
                'error' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Generar número de radicado único
     */
    private function generarNumeroRadicado($propiedadId)
    {
        $year = date('Y');
        $month = date('m');
        
        // Obtener el último número de radicado del año
        $ultimoRadicado = Pqrs::where('numero_radicado', 'like', "PQRS-{$year}-{$month}-%")
            ->orderBy('numero_radicado', 'desc')
            ->value('numero_radicado');
        
        if ($ultimoRadicado) {
            // Extraer el número secuencial
            $partes = explode('-', $ultimoRadicado);
            $secuencial = intval(end($partes)) + 1;
        } else {
            $secuencial = 1;
        }
        
        // Formato: PQRS-YYYY-MM-XXXXX
        return sprintf('PQRS-%s-%s-%05d', $year, $month, $secuencial);
    }
}
