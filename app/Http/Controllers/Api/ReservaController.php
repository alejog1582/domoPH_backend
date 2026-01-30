<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ZonaSocial;
use App\Models\Reserva;
use App\Models\ReservaInvitado;
use Carbon\Carbon;

class ReservaController extends Controller
{
    /**
     * Obtener zonas sociales con horarios, imágenes y reglas, 
     * además de reservas futuras con sus invitados
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

        // Obtener la propiedad del usuario desde el token o request
        $propiedadId = $request->input('propiedad_id');
        
        // Si no viene en el request, intentar obtenerla del usuario
        if (!$propiedadId) {
            // Obtener la propiedad desde el residente principal
            $residente = \App\Models\Residente::where('user_id', $user->id)
                ->where('es_principal', true)
                ->activos()
                ->with(['unidad.propiedad'])
                ->first();
            
            if (!$residente || !$residente->unidad || !$residente->unidad->propiedad) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar la propiedad del usuario.',
                    'error' => 'PROPERTY_NOT_FOUND'
                ], 404);
            }
            
            $propiedadId = $residente->unidad->propiedad->id;
        }

        // Obtener zonas sociales con sus relaciones
        $zonasSociales = ZonaSocial::where('propiedad_id', $propiedadId)
            ->where('activo', true)
            ->where('estado', 'activa')
            ->with([
                'horarios' => function($query) {
                    $query->where('activo', true)
                          ->orderByRaw("FIELD(dia_semana, 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo')")
                          ->orderBy('hora_inicio');
                },
                'imagenes' => function($query) {
                    $query->where('activo', true)
                          ->orderBy('orden');
                },
                'reglas'
            ])
            ->get()
            ->map(function ($zona) {
                return [
                    'id' => $zona->id,
                    'nombre' => $zona->nombre,
                    'descripcion' => $zona->descripcion,
                    'ubicacion' => $zona->ubicacion,
                    'capacidad_maxima' => $zona->capacidad_maxima,
                    'max_invitados_por_reserva' => $zona->max_invitados_por_reserva,
                    'tiempo_minimo_uso_horas' => $zona->tiempo_minimo_uso_horas,
                    'tiempo_maximo_uso_horas' => $zona->tiempo_maximo_uso_horas,
                    'reservas_simultaneas' => $zona->reservas_simultaneas,
                    'valor_alquiler' => (float) $zona->valor_alquiler,
                    'valor_deposito' => (float) $zona->valor_deposito,
                    'requiere_aprobacion' => $zona->requiere_aprobacion,
                    'permite_reservas_en_mora' => $zona->permite_reservas_en_mora,
                    'acepta_invitados' => $zona->acepta_invitados,
                    'reglamento_url' => $zona->reglamento_url,
                    'estado' => $zona->estado,
                    'horarios' => $zona->horarios->map(function ($horario) {
                        return [
                            'id' => $horario->id,
                            'dia_semana' => $horario->dia_semana,
                            'hora_inicio' => $horario->hora_inicio,
                            'hora_fin' => $horario->hora_fin,
                            'activo' => $horario->activo,
                        ];
                    }),
                    'imagenes' => $zona->imagenes->map(function ($imagen) {
                        return [
                            'id' => $imagen->id,
                            'url_imagen' => $imagen->url_imagen,
                            'orden' => $imagen->orden,
                        ];
                    }),
                    'reglas' => $zona->reglas->map(function ($regla) {
                        return [
                            'id' => $regla->id,
                            'clave' => $regla->clave,
                            'valor' => $regla->valor,
                            'descripcion' => $regla->descripcion,
                        ];
                    }),
                ];
            });

        // Obtener reservas futuras (fecha >= hoy)
        $fechaActual = Carbon::now()->startOfDay();
        
        $reservas = Reserva::where('copropiedad_id', $propiedadId)
            ->where('fecha_reserva', '>=', $fechaActual->format('Y-m-d'))
            ->where('activo', true)
            ->with(['unidad', 'residente', 'zonaSocial', 'invitados'])
            ->orderBy('fecha_reserva', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get()
            ->map(function ($reserva) {
                return [
                    'id' => $reserva->id,
                    'copropiedad_id' => $reserva->copropiedad_id,
                    'unidad_id' => $reserva->unidad_id,
                    'residente_id' => $reserva->residente_id,
                    'zona_social_id' => $reserva->zona_social_id,
                    'nombre_solicitante' => $reserva->nombre_solicitante,
                    'telefono_solicitante' => $reserva->telefono_solicitante,
                    'email_solicitante' => $reserva->email_solicitante,
                    'fecha_reserva' => $reserva->fecha_reserva->format('Y-m-d'),
                    'hora_inicio' => $reserva->hora_inicio,
                    'hora_fin' => $reserva->hora_fin,
                    'duracion_minutos' => $reserva->duracion_minutos,
                    'cantidad_invitados' => $reserva->cantidad_invitados,
                    'descripcion' => $reserva->descripcion,
                    'costo_reserva' => (float) $reserva->costo_reserva,
                    'deposito_garantia' => (float) $reserva->deposito_garantia,
                    'requiere_pago' => $reserva->requiere_pago,
                    'estado_pago' => $reserva->estado_pago,
                    'estado' => $reserva->estado,
                    'aprobada_por' => $reserva->aprobada_por,
                    'fecha_aprobacion' => $reserva->fecha_aprobacion?->format('Y-m-d H:i:s'),
                    'motivo_rechazo' => $reserva->motivo_rechazo,
                    'motivo_cancelacion' => $reserva->motivo_cancelacion,
                    'es_exclusiva' => $reserva->es_exclusiva,
                    'permite_invitados' => $reserva->permite_invitados,
                    'incumplimiento' => $reserva->incumplimiento,
                    'observaciones_admin' => $reserva->observaciones_admin,
                    'adjuntos' => $reserva->adjuntos,
                    'unidad' => $reserva->unidad ? [
                        'id' => $reserva->unidad->id,
                        'numero' => $reserva->unidad->numero,
                        'torre' => $reserva->unidad->torre,
                        'bloque' => $reserva->unidad->bloque,
                    ] : null,
                    'residente' => $reserva->residente ? [
                        'id' => $reserva->residente->id,
                        'tipo_relacion' => $reserva->residente->tipo_relacion,
                    ] : null,
                    'zona_social' => $reserva->zonaSocial ? [
                        'id' => $reserva->zonaSocial->id,
                        'nombre' => $reserva->zonaSocial->nombre,
                    ] : null,
                    'invitados' => $reserva->invitados->map(function ($invitado) {
                        return [
                            'id' => $invitado->id,
                            'reserva_id' => $invitado->reserva_id,
                            'residente_id' => $invitado->residente_id,
                            'nombre' => $invitado->nombre,
                            'documento' => $invitado->documento,
                            'telefono' => $invitado->telefono,
                            'tipo' => $invitado->tipo,
                            'placa' => $invitado->placa,
                            'estado' => $invitado->estado,
                            'fecha_ingreso' => $invitado->fecha_ingreso?->format('Y-m-d H:i:s'),
                            'fecha_salida' => $invitado->fecha_salida?->format('Y-m-d H:i:s'),
                            'observaciones' => $invitado->observaciones,
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'zonas_sociales' => $zonasSociales,
                'reservas' => $reservas,
            ]
        ], 200);
    }
}
