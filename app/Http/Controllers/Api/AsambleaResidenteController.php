<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Residente;
use Carbon\Carbon;

class AsambleaResidenteController extends Controller
{
    /**
     * Obtener detalles de una asamblea
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        // Obtener información del residente
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad.propiedad'])
            ->first();

        if (!$residente) {
            $residente = Residente::where('user_id', $user->id)
                ->activos()
                ->with(['unidad.propiedad'])
                ->first();
        }

        if (!$residente || !$residente->unidad || !$residente->unidad->propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de residente'
            ], 404);
        }

        $propiedadId = $residente->unidad->propiedad->id;

        // Obtener asamblea
        $asamblea = DB::table('asambleas')
            ->where('id', $id)
            ->where('copropiedad_id', $propiedadId)
            ->whereIn('estado', ['programada', 'en_curso'])
            ->where('activo', true)
            ->first();

        if (!$asamblea) {
            return response()->json([
                'success' => false,
                'message' => 'Asamblea no encontrada'
            ], 404);
        }

        // Obtener documentos
        $documentos = DB::table('asamblea_documentos')
            ->where('asamblea_id', $id)
            ->where('activo', true)
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'nombre' => $doc->nombre,
                    'tipo' => $doc->tipo,
                    'archivo_url' => $doc->archivo_url,
                    'visible_para' => $doc->visible_para,
                ];
            });

        // Obtener votaciones abiertas
        $votaciones = DB::table('asamblea_votaciones')
            ->where('asamblea_id', $id)
            ->where('estado', 'abierta')
            ->orderBy('fecha_inicio', 'desc')
            ->get()
            ->map(function ($votacion) use ($residente) {
                // Obtener opciones
                $opciones = DB::table('asamblea_votacion_opciones')
                    ->where('votacion_id', $votacion->id)
                    ->orderBy('orden', 'asc')
                    ->get()
                    ->map(function ($opcion) {
                        return [
                            'id' => $opcion->id,
                            'opcion' => $opcion->opcion,
                            'orden' => $opcion->orden,
                        ];
                    });

                // Verificar si el residente ya votó
                $yaVoto = DB::table('asamblea_votos')
                    ->where('votacion_id', $votacion->id)
                    ->where('residente_id', $residente->id)
                    ->exists();

                return [
                    'id' => $votacion->id,
                    'titulo' => $votacion->titulo,
                    'descripcion' => $votacion->descripcion,
                    'tipo' => $votacion->tipo,
                    'estado' => $votacion->estado,
                    'fecha_inicio' => $votacion->fecha_inicio ? Carbon::parse($votacion->fecha_inicio)->format('Y-m-d H:i:s') : null,
                    'fecha_fin' => $votacion->fecha_fin ? Carbon::parse($votacion->fecha_fin)->format('Y-m-d H:i:s') : null,
                    'opciones' => $opciones,
                    'ya_voto' => $yaVoto,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $asamblea->id,
                'titulo' => $asamblea->titulo,
                'descripcion' => $asamblea->descripcion,
                'tipo' => $asamblea->tipo,
                'modalidad' => $asamblea->modalidad,
                'estado' => $asamblea->estado,
                'fecha_inicio' => $asamblea->fecha_inicio ? Carbon::parse($asamblea->fecha_inicio)->format('Y-m-d H:i:s') : null,
                'fecha_fin' => $asamblea->fecha_fin ? Carbon::parse($asamblea->fecha_fin)->format('Y-m-d H:i:s') : null,
                'quorum_minimo' => $asamblea->quorum_minimo,
                'quorum_actual' => $asamblea->quorum_actual,
                'url_transmision' => $asamblea->url_transmision,
                'documentos' => $documentos,
                'votaciones' => $votaciones,
            ]
        ], 200);
    }

    /**
     * Votar en una votación de asamblea
     */
    public function votar(Request $request, $asambleaId, $votacionId)
    {
        $user = $request->user();
        
        $request->validate([
            'opcion_id' => 'required|integer|exists:asamblea_votacion_opciones,id',
        ]);

        // Obtener información del residente
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad.propiedad'])
            ->first();

        if (!$residente) {
            $residente = Residente::where('user_id', $user->id)
                ->activos()
                ->with(['unidad.propiedad'])
                ->first();
        }

        if (!$residente || !$residente->unidad || !$residente->unidad->propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de residente'
            ], 404);
        }

        $propiedadId = $residente->unidad->propiedad->id;

        // Verificar que la asamblea existe y está activa
        $asamblea = DB::table('asambleas')
            ->where('id', $asambleaId)
            ->where('copropiedad_id', $propiedadId)
            ->whereIn('estado', ['programada', 'en_curso'])
            ->where('activo', true)
            ->first();

        if (!$asamblea) {
            return response()->json([
                'success' => false,
                'message' => 'Asamblea no encontrada'
            ], 404);
        }

        // Verificar que la votación existe y está abierta
        $votacion = DB::table('asamblea_votaciones')
            ->where('id', $votacionId)
            ->where('asamblea_id', $asambleaId)
            ->where('estado', 'abierta')
            ->first();

        if (!$votacion) {
            return response()->json([
                'success' => false,
                'message' => 'Votación no encontrada o cerrada'
            ], 404);
        }

        // Verificar que la opción pertenece a esta votación
        $opcion = DB::table('asamblea_votacion_opciones')
            ->where('id', $request->opcion_id)
            ->where('votacion_id', $votacionId)
            ->first();

        if (!$opcion) {
            return response()->json([
                'success' => false,
                'message' => 'Opción no válida para esta votación'
            ], 400);
        }

        // Verificar que el residente no haya votado antes
        $yaVoto = DB::table('asamblea_votos')
            ->where('votacion_id', $votacionId)
            ->where('residente_id', $residente->id)
            ->exists();

        if ($yaVoto) {
            return response()->json([
                'success' => false,
                'message' => 'Ya has votado en esta votación'
            ], 400);
        }

        // Obtener coeficiente del residente
        $coeficiente = $residente->unidad->coeficiente ?? 0;

        DB::beginTransaction();
        try {
            // Registrar el voto
            DB::table('asamblea_votos')->insert([
                'votacion_id' => $votacionId,
                'residente_id' => $residente->id,
                'opcion_id' => $request->opcion_id,
                'coeficiente_aplicado' => $coeficiente,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Voto registrado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al registrar voto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el voto'
            ], 500);
        }
    }

    /**
     * Ver resultados de una votación
     */
    public function verResultados(Request $request, $asambleaId, $votacionId)
    {
        $user = $request->user();
        
        // Obtener información del residente
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad.propiedad'])
            ->first();

        if (!$residente) {
            $residente = Residente::where('user_id', $user->id)
                ->activos()
                ->with(['unidad.propiedad'])
                ->first();
        }

        if (!$residente || !$residente->unidad || !$residente->unidad->propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de residente'
            ], 404);
        }

        $propiedadId = $residente->unidad->propiedad->id;

        // Verificar que la asamblea existe
        $asamblea = DB::table('asambleas')
            ->where('id', $asambleaId)
            ->where('copropiedad_id', $propiedadId)
            ->where('activo', true)
            ->first();

        if (!$asamblea) {
            return response()->json([
                'success' => false,
                'message' => 'Asamblea no encontrada'
            ], 404);
        }

        // Obtener votación
        $votacion = DB::table('asamblea_votaciones')
            ->where('id', $votacionId)
            ->where('asamblea_id', $asambleaId)
            ->first();

        if (!$votacion) {
            return response()->json([
                'success' => false,
                'message' => 'Votación no encontrada'
            ], 404);
        }

        // Obtener opciones con resultados
        $opciones = DB::table('asamblea_votacion_opciones')
            ->where('votacion_id', $votacionId)
            ->orderBy('orden', 'asc')
            ->get()
            ->map(function ($opcion) use ($votacionId) {
                // Contar votos para esta opción
                $votos = DB::table('asamblea_votos')
                    ->where('votacion_id', $votacionId)
                    ->where('opcion_id', $opcion->id)
                    ->count();

                // Sumar coeficientes
                $coeficienteTotal = DB::table('asamblea_votos')
                    ->where('votacion_id', $votacionId)
                    ->where('opcion_id', $opcion->id)
                    ->sum('coeficiente_aplicado');

                return [
                    'opcion_id' => $opcion->id,
                    'opcion' => $opcion->opcion,
                    'votos' => $votos,
                    'coeficiente_total' => (float) $coeficienteTotal,
                ];
            });

        // Total de votos
        $totalVotos = DB::table('asamblea_votos')
            ->where('votacion_id', $votacionId)
            ->count();

        // Calcular porcentajes
        $resultados = $opciones->map(function ($opcion) use ($totalVotos) {
            $porcentaje = $totalVotos > 0 ? ($opcion['votos'] / $totalVotos) * 100 : 0;
            return [
                'opcion_id' => $opcion['opcion_id'],
                'opcion' => $opcion['opcion'],
                'votos' => $opcion['votos'],
                'porcentaje' => round($porcentaje, 2),
                'coeficiente_total' => $opcion['coeficiente_total'],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'votacion' => [
                    'id' => $votacion->id,
                    'titulo' => $votacion->titulo,
                    'descripcion' => $votacion->descripcion,
                    'tipo' => $votacion->tipo,
                    'estado' => $votacion->estado,
                    'fecha_inicio' => $votacion->fecha_inicio ? Carbon::parse($votacion->fecha_inicio)->format('Y-m-d H:i:s') : null,
                    'fecha_fin' => $votacion->fecha_fin ? Carbon::parse($votacion->fecha_fin)->format('Y-m-d H:i:s') : null,
                ],
                'resultados' => $resultados,
                'total_votos' => $totalVotos,
            ]
        ], 200);
    }
}
