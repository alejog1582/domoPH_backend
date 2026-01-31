<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visita;
use App\Models\Residente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VisitaController extends Controller
{
    /**
     * Obtener visitas de los últimos 6 meses de la unidad del residente
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

        // Calcular fecha de hace 6 meses
        $fechaDesde = Carbon::now()->subMonths(6)->startOfDay();

        // Query: visitas de los últimos 6 meses de la unidad (sin importar residente_id)
        $visitas = Visita::where('copropiedad_id', $propiedadId)
            ->where('unidad_id', $unidadId)
            ->where('activo', true)
            ->where('fecha_ingreso', '>=', $fechaDesde)
            ->orderBy('fecha_ingreso', 'desc') // Más reciente a más antigua
            ->get()
            ->map(function ($visita) {
                return [
                    'id' => $visita->id,
                    'nombre_visitante' => $visita->nombre_visitante,
                    'documento_visitante' => $visita->documento_visitante,
                    'tipo_visita' => $visita->tipo_visita,
                    'placa_vehiculo' => $visita->placa_vehiculo,
                    'motivo' => $visita->motivo,
                    'fecha_ingreso' => $visita->fecha_ingreso 
                        ? $visita->fecha_ingreso->format('Y-m-d H:i:s')
                        : null,
                    'fecha_ingreso_formateada' => $visita->fecha_ingreso 
                        ? $visita->fecha_ingreso->format('d M Y')
                        : null,
                    'hora_ingreso' => $visita->fecha_ingreso 
                        ? $visita->fecha_ingreso->format('h:i A')
                        : null,
                    'fecha_salida' => $visita->fecha_salida 
                        ? $visita->fecha_salida->format('Y-m-d H:i:s')
                        : null,
                    'fecha_salida_formateada' => $visita->fecha_salida 
                        ? $visita->fecha_salida->format('d M Y')
                        : null,
                    'hora_salida' => $visita->fecha_salida 
                        ? $visita->fecha_salida->format('h:i A')
                        : null,
                    'estado' => $visita->estado,
                    'observaciones' => $visita->observaciones,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'visitas' => $visitas,
            ]
        ], 200);
    }

    /**
     * Crear una nueva visita
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

        // Validar datos
        $validated = $request->validate([
            'nombre_visitante' => 'required|string|max:150',
            'documento_visitante' => 'nullable|string|max:50',
            'tipo_visita' => 'required|in:peatonal,vehicular',
            'placa_vehiculo' => 'nullable|string|max:20',
            'motivo' => 'nullable|string|max:200',
            'fecha_ingreso' => 'required|date',
        ]);

        // Crear la visita
        $visita = Visita::create([
            'copropiedad_id' => $residente->unidad->propiedad->id,
            'unidad_id' => $residente->unidad->id,
            'residente_id' => $residente->id,
            'nombre_visitante' => $validated['nombre_visitante'],
            'documento_visitante' => $validated['documento_visitante'] ?? null,
            'tipo_visita' => $validated['tipo_visita'],
            'placa_vehiculo' => $validated['placa_vehiculo'] ?? null,
            'motivo' => $validated['motivo'] ?? null,
            'fecha_ingreso' => Carbon::parse($validated['fecha_ingreso']),
            'estado' => 'programada', // Estado inicial para visitas programadas
            'registrada_por' => $user->id,
            'activo' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visita autorizada correctamente.',
            'data' => [
                'visita' => [
                    'id' => $visita->id,
                    'nombre_visitante' => $visita->nombre_visitante,
                    'documento_visitante' => $visita->documento_visitante,
                    'tipo_visita' => $visita->tipo_visita,
                    'placa_vehiculo' => $visita->placa_vehiculo,
                    'motivo' => $visita->motivo,
                    'fecha_ingreso' => $visita->fecha_ingreso->format('Y-m-d H:i:s'),
                    'estado' => $visita->estado,
                ]
            ]
        ], 201);
    }
}
