<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LlamadoAtencion;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class LlamadoAtencionController extends Controller
{
    /**
     * Obtener llamados de atención de la unidad del residente
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
        
        // Obtener todos los llamados de atención de la unidad (sin importar residente_id)
        $llamados = LlamadoAtencion::where('copropiedad_id', $propiedadId)
            ->where('unidad_id', $unidadId)
            ->where('activo', true)
            ->orderBy('fecha_registro', 'desc')
            ->get()
            ->map(function ($llamado) {
                return [
                    'id' => $llamado->id,
                    'tipo' => $llamado->tipo,
                    'motivo' => $llamado->motivo,
                    'descripcion' => $llamado->descripcion,
                    'nivel' => $llamado->nivel,
                    'estado' => $llamado->estado,
                    'fecha_evento' => $llamado->fecha_evento 
                        ? $llamado->fecha_evento->format('Y-m-d H:i:s')
                        : null,
                    'fecha_evento_formateada' => $llamado->fecha_evento 
                        ? $llamado->fecha_evento->format('d M Y')
                        : null,
                    'fecha_registro' => $llamado->fecha_registro 
                        ? $llamado->fecha_registro->format('Y-m-d H:i:s')
                        : null,
                    'fecha_registro_formateada' => $llamado->fecha_registro 
                        ? $llamado->fecha_registro->format('d M Y')
                        : null,
                    'es_reincidencia' => $llamado->es_reincidencia,
                    'observaciones' => $llamado->observaciones,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'llamados' => $llamados,
            ]
        ], 200);
    }

    /**
     * Obtener el historial de un llamado de atención
     */
    public function historial(Request $request, $id)
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

        // Verificar que el llamado pertenezca a la unidad
        $llamado = LlamadoAtencion::where('copropiedad_id', $propiedadId)
            ->where('unidad_id', $unidadId)
            ->where('id', $id)
            ->where('activo', true)
            ->first();

        if (!$llamado) {
            return response()->json([
                'success' => false,
                'message' => 'Llamado de atención no encontrado.',
                'error' => 'NOT_FOUND'
            ], 404);
        }

        // Obtener el historial
        $historial = DB::table('llamados_atencion_historial')
            ->where('llamado_atencion_id', $id)
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
                        ? \Carbon\Carbon::parse($registro->fecha_cambio)->format('Y-m-d H:i:s')
                        : null,
                    'fecha_cambio_formateada' => $registro->fecha_cambio 
                        ? \Carbon\Carbon::parse($registro->fecha_cambio)->format('d M Y H:i')
                        : null,
                    'cambiado_por' => $registro->cambiado_por,
                    'es_residente' => $esResidente,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'llamado' => [
                    'id' => $llamado->id,
                    'tipo' => $llamado->tipo,
                    'motivo' => $llamado->motivo,
                    'descripcion' => $llamado->descripcion,
                    'nivel' => $llamado->nivel,
                    'estado' => $llamado->estado,
                ],
                'historial' => $historial,
            ]
        ], 200);
    }

    /**
     * Agregar una respuesta al historial del llamado de atención
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

        // Verificar que el llamado pertenezca a la unidad
        $llamado = LlamadoAtencion::where('copropiedad_id', $propiedadId)
            ->where('unidad_id', $unidadId)
            ->where('id', $id)
            ->where('activo', true)
            ->first();

        if (!$llamado) {
            return response()->json([
                'success' => false,
                'message' => 'Llamado de atención no encontrado.',
                'error' => 'NOT_FOUND'
            ], 404);
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
                    'folder' => 'domoph/llamados_atencion',
                    'resource_type' => 'image',
                ]);
                $soporteUrl = $result['secure_url'];
            }

            // Obtener el estado actual del llamado
            $estadoActual = $llamado->estado;

            // Insertar en el historial
            DB::table('llamados_atencion_historial')->insert([
                'llamado_atencion_id' => $llamado->id,
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
            \Log::error('Error al agregar respuesta al llamado de atención: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar la respuesta: ' . $e->getMessage(),
                'error' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
}
