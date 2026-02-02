<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Autorizacion;
use App\Models\Residente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutorizacionController extends Controller
{
    /**
     * Obtener autorizaciones del residente
     */
    public function index()
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

        try {
            // Obtener autorizaciones activas
            $autorizacionesActivas = Autorizacion::where('copropiedad_id', $propiedadId)
                ->where('unidad_id', $unidadId)
                ->where('residente_id', $residente->id)
                ->where('estado', 'activa')
                ->where('activo', true)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($autorizacion) {
                    return $this->mapAutorizacion($autorizacion);
                });

            // Obtener autorizaciones en historial (vencidas y suspendidas)
            $autorizacionesHistorial = Autorizacion::where('copropiedad_id', $propiedadId)
                ->where('unidad_id', $unidadId)
                ->where('residente_id', $residente->id)
                ->whereIn('estado', ['vencida', 'suspendida'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($autorizacion) {
                    return $this->mapAutorizacion($autorizacion);
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'activas' => $autorizacionesActivas,
                    'historial' => $autorizacionesHistorial,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al obtener autorizaciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las autorizaciones: ' . $e->getMessage(),
                'error' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Crear una nueva autorización
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
            'nombre_autorizado' => 'required|string|max:150',
            'documento_autorizado' => 'nullable|string|max:50',
            'tipo_autorizado' => 'required|in:familiar,empleado,aseo,mantenimiento,proveedor,otro',
            'tipo_acceso' => 'required|in:peatonal,vehicular,ambos',
            'placa_vehiculo' => 'nullable|string|max:20|required_if:tipo_acceso,vehicular,ambos',
            'dias_autorizados' => 'nullable|array',
            'dias_autorizados.*' => 'string|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'hora_desde' => 'nullable|date_format:H:i',
            'hora_hasta' => 'nullable|date_format:H:i|after:hora_desde',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'observaciones' => 'nullable|string|max:1000',
        ], [
            'nombre_autorizado.required' => 'El nombre de la persona autorizada es obligatorio.',
            'nombre_autorizado.max' => 'El nombre no puede exceder 150 caracteres.',
            'tipo_autorizado.required' => 'El tipo de autorizado es obligatorio.',
            'tipo_autorizado.in' => 'El tipo de autorizado no es válido.',
            'tipo_acceso.required' => 'El tipo de acceso es obligatorio.',
            'tipo_acceso.in' => 'El tipo de acceso no es válido.',
            'placa_vehiculo.required_if' => 'La placa del vehículo es obligatoria cuando el tipo de acceso es vehicular o ambos.',
            'hora_hasta.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ]);

        try {
            // Crear la autorización
            $autorizacion = Autorizacion::create([
                'copropiedad_id' => $propiedadId,
                'unidad_id' => $unidadId,
                'residente_id' => $residente->id,
                'nombre_autorizado' => $validated['nombre_autorizado'],
                'documento_autorizado' => $validated['documento_autorizado'] ?? null,
                'tipo_autorizado' => $validated['tipo_autorizado'],
                'tipo_acceso' => $validated['tipo_acceso'],
                'placa_vehiculo' => $validated['placa_vehiculo'] ?? null,
                'dias_autorizados' => $validated['dias_autorizados'] ?? null,
                'hora_desde' => $validated['hora_desde'] ?? null,
                'hora_hasta' => $validated['hora_hasta'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'] ?? null,
                'fecha_fin' => $validated['fecha_fin'] ?? null,
                'estado' => 'activa',
                'observaciones' => $validated['observaciones'] ?? null,
                'activo' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Autorización creada correctamente.',
                'data' => [
                    'autorizacion' => $this->mapAutorizacion($autorizacion)
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error al crear autorización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la autorización: ' . $e->getMessage(),
                'error' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Actualizar una autorización
     */
    public function update(Request $request, $id)
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

        // Verificar que la autorización pertenezca al residente
        $autorizacion = Autorizacion::where('copropiedad_id', $propiedadId)
            ->where('unidad_id', $unidadId)
            ->where('residente_id', $residente->id)
            ->where('id', $id)
            ->first();

        if (!$autorizacion) {
            return response()->json([
                'success' => false,
                'message' => 'Autorización no encontrada.',
                'error' => 'NOT_FOUND'
            ], 404);
        }

        // Validar datos
        $validated = $request->validate([
            'nombre_autorizado' => 'required|string|max:150',
            'documento_autorizado' => 'nullable|string|max:50',
            'tipo_autorizado' => 'required|in:familiar,empleado,aseo,mantenimiento,proveedor,otro',
            'tipo_acceso' => 'required|in:peatonal,vehicular,ambos',
            'placa_vehiculo' => 'nullable|string|max:20|required_if:tipo_acceso,vehicular,ambos',
            'dias_autorizados' => 'nullable|array',
            'dias_autorizados.*' => 'string|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'hora_desde' => 'nullable|date_format:H:i',
            'hora_hasta' => 'nullable|date_format:H:i|after:hora_desde',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado' => 'required|in:activa,vencida,suspendida',
            'observaciones' => 'nullable|string|max:1000',
        ], [
            'nombre_autorizado.required' => 'El nombre de la persona autorizada es obligatorio.',
            'nombre_autorizado.max' => 'El nombre no puede exceder 150 caracteres.',
            'tipo_autorizado.required' => 'El tipo de autorizado es obligatorio.',
            'tipo_autorizado.in' => 'El tipo de autorizado no es válido.',
            'tipo_acceso.required' => 'El tipo de acceso es obligatorio.',
            'tipo_acceso.in' => 'El tipo de acceso no es válido.',
            'placa_vehiculo.required_if' => 'La placa del vehículo es obligatoria cuando el tipo de acceso es vehicular o ambos.',
            'hora_hasta.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado no es válido.',
        ]);

        try {
            // Actualizar la autorización
            $autorizacion->update([
                'nombre_autorizado' => $validated['nombre_autorizado'],
                'documento_autorizado' => $validated['documento_autorizado'] ?? null,
                'tipo_autorizado' => $validated['tipo_autorizado'],
                'tipo_acceso' => $validated['tipo_acceso'],
                'placa_vehiculo' => $validated['placa_vehiculo'] ?? null,
                'dias_autorizados' => $validated['dias_autorizados'] ?? null,
                'hora_desde' => $validated['hora_desde'] ?? null,
                'hora_hasta' => $validated['hora_hasta'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'] ?? null,
                'fecha_fin' => $validated['fecha_fin'] ?? null,
                'estado' => $validated['estado'],
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            // Recargar la autorización actualizada
            $autorizacion->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Autorización actualizada correctamente.',
                'data' => [
                    'autorizacion' => $this->mapAutorizacion($autorizacion)
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al actualizar autorización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la autorización: ' . $e->getMessage(),
                'error' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Mapear autorización a formato de respuesta
     */
    private function mapAutorizacion(Autorizacion $autorizacion): array
    {
        return [
            'id' => $autorizacion->id,
            'nombre_autorizado' => $autorizacion->nombre_autorizado,
            'documento_autorizado' => $autorizacion->documento_autorizado,
            'tipo_autorizado' => $autorizacion->tipo_autorizado,
            'tipo_acceso' => $autorizacion->tipo_acceso,
            'placa_vehiculo' => $autorizacion->placa_vehiculo,
            'dias_autorizados' => $autorizacion->dias_autorizados ?? [],
            'hora_desde' => $autorizacion->hora_desde,
            'hora_hasta' => $autorizacion->hora_hasta,
            'fecha_inicio' => $autorizacion->fecha_inicio 
                ? $autorizacion->fecha_inicio->format('Y-m-d')
                : null,
            'fecha_fin' => $autorizacion->fecha_fin 
                ? $autorizacion->fecha_fin->format('Y-m-d')
                : null,
            'estado' => $autorizacion->estado,
            'observaciones' => $autorizacion->observaciones,
            'created_at' => $autorizacion->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $autorizacion->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
