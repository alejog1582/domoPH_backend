<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Encuesta;
use App\Models\Votacion;
use App\Models\EncuestaRespuesta;
use App\Models\Voto;
use App\Models\Residente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EncuestaVotacionResidenteController extends Controller
{
    /**
     * Responder una encuesta
     */
    public function responderEncuesta(Request $request, $id)
    {
        $user = $request->user();
        
        // Obtener el residente principal
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->first();

        if (!$residente) {
            $residente = Residente::where('user_id', $user->id)
                ->activos()
                ->first();
        }

        if (!$residente) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de residente'
            ], 404);
        }

        // Validar que la encuesta exista y esté activa
        $encuesta = Encuesta::where('id', $id)
            ->where('estado', 'activa')
            ->where('activo', true)
            ->whereDate('fecha_inicio', '<=', Carbon::now())
            ->whereDate('fecha_fin', '>=', Carbon::now())
            ->first();

        if (!$encuesta) {
            return response()->json([
                'success' => false,
                'message' => 'La encuesta no está disponible o ha expirado'
            ], 404);
        }

        // Verificar si ya respondió
        $yaRespondio = EncuestaRespuesta::where('encuesta_id', $encuesta->id)
            ->where('residente_id', $residente->id)
            ->exists();

        if ($yaRespondio) {
            return response()->json([
                'success' => false,
                'message' => 'Ya has respondido esta encuesta'
            ], 400);
        }

        // Validar datos según el tipo de respuesta
        if ($encuesta->tipo_respuesta === 'respuesta_abierta') {
            $validated = $request->validate([
                'respuesta_abierta' => 'required|string|max:5000',
            ]);

            DB::beginTransaction();
            try {
                EncuestaRespuesta::create([
                    'encuesta_id' => $encuesta->id,
                    'residente_id' => $residente->id,
                    'respuesta_abierta' => $validated['respuesta_abierta'],
                    'opcion_id' => null,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Respuesta registrada exitosamente'
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar la respuesta: ' . $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de encuesta no válido'
            ], 400);
        }
    }

    /**
     * Votar en una votación
     */
    public function votar(Request $request, $id)
    {
        $user = $request->user();
        
        // Obtener el residente principal
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->first();

        if (!$residente) {
            $residente = Residente::where('user_id', $user->id)
                ->activos()
                ->first();
        }

        if (!$residente) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de residente'
            ], 404);
        }

        // Validar que la votación exista y esté activa
        $votacion = Votacion::where('id', $id)
            ->where('estado', 'activa')
            ->where('activo', true)
            ->whereDate('fecha_inicio', '<=', Carbon::now())
            ->whereDate('fecha_fin', '>=', Carbon::now())
            ->with('opciones')
            ->first();

        if (!$votacion) {
            return response()->json([
                'success' => false,
                'message' => 'La votación no está disponible o ha expirado'
            ], 404);
        }

        // Verificar si ya votó
        $yaVoto = Voto::where('votacion_id', $votacion->id)
            ->where('residente_id', $residente->id)
            ->exists();

        if ($yaVoto) {
            return response()->json([
                'success' => false,
                'message' => 'Ya has votado en esta votación'
            ], 400);
        }

        // Validar opción seleccionada
        $validated = $request->validate([
            'opcion_id' => 'required|integer|exists:votacion_opciones,id',
        ]);

        // Verificar que la opción pertenezca a esta votación
        $opcionValida = $votacion->opciones->contains('id', $validated['opcion_id']);
        if (!$opcionValida) {
            return response()->json([
                'success' => false,
                'message' => 'La opción seleccionada no es válida para esta votación'
            ], 400);
        }

        DB::beginTransaction();
        try {
            Voto::create([
                'votacion_id' => $votacion->id,
                'residente_id' => $residente->id,
                'opcion_id' => $validated['opcion_id'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Voto registrado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el voto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver resultados de una votación
     */
    public function verResultados($id)
    {
        // Validar que la votación exista
        $votacion = Votacion::where('id', $id)
            ->where('activo', true)
            ->with(['opciones', 'votos'])
            ->first();

        if (!$votacion) {
            return response()->json([
                'success' => false,
                'message' => 'La votación no existe'
            ], 404);
        }

        $totalVotos = $votacion->votos->count();

        $resultados = $votacion->opciones->map(function ($opcion) use ($totalVotos) {
            $votosOpcion = $opcion->votos->count();
            $porcentaje = $totalVotos > 0 ? ($votosOpcion / $totalVotos) * 100 : 0;

            return [
                'id' => $opcion->id,
                'texto_opcion' => $opcion->texto_opcion,
                'votos' => $votosOpcion,
                'porcentaje' => round($porcentaje, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'votacion' => [
                    'id' => $votacion->id,
                    'titulo' => $votacion->titulo,
                    'descripcion' => $votacion->descripcion,
                    'fecha_inicio' => $votacion->fecha_inicio->format('Y-m-d'),
                    'fecha_fin' => $votacion->fecha_fin->format('Y-m-d'),
                    'total_votos' => $totalVotos,
                ],
                'resultados' => $resultados,
            ]
        ], 200);
    }
}
