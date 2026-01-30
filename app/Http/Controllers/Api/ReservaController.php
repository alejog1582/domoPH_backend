<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ZonaSocial;
use App\Models\Reserva;
use App\Models\ReservaInvitado;
use App\Models\Residente;
use App\Models\Cartera;
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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
        } else {
            // Si no hay residente, no podemos verificar mora
            $residente = null;
        }

        // Verificar si el usuario está en mora
        $estaEnMora = false;
        $saldoMora = 0;
        if ($residente && $residente->unidad) {
            $cartera = Cartera::where('unidad_id', $residente->unidad->id)->first();
            if ($cartera && $cartera->saldo_mora > 0) {
                $estaEnMora = true;
                $saldoMora = (float) $cartera->saldo_mora;
            }
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
                'esta_en_mora' => $estaEnMora,
                'saldo_mora' => $saldoMora,
                'zonas_sociales' => $zonasSociales,
                'reservas' => $reservas,
            ]
        ], 200);
    }

    /**
     * Buscar invitados (residentes de la unidad o invitados de reservas anteriores)
     */
    public function buscarInvitados(Request $request)
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

        // Validar parámetros
        $request->validate([
            'query' => 'required|string|min:2',
            'copropiedad_id' => 'required|integer',
        ]);

        $query = $request->input('query');
        $copropiedadId = $request->input('copropiedad_id');

        // Obtener la unidad del usuario
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad'])
            ->first();

        if (!$residente || !$residente->unidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar la unidad del usuario.',
                'error' => 'UNIT_NOT_FOUND'
            ], 404);
        }

        $unidadId = $residente->unidad->id;

        $resultados = [];

        // 1. Buscar en residentes de la misma copropiedad (excluyendo al usuario actual)
        $residentes = Residente::whereHas('unidad', function($q) use ($copropiedadId) {
                $q->where('propiedad_id', $copropiedadId);
            })
            ->where('user_id', '!=', $user->id) // Excluir al usuario actual
            ->whereHas('user', function($q) use ($query) {
                $q->where(function($subQ) use ($query) {
                    $subQ->where('nombre', 'like', "%{$query}%")
                         ->orWhere('documento_identidad', 'like', "%{$query}%");
                });
            })
            ->activos()
            ->with(['user', 'unidad'])
            ->limit(10)
            ->get();

        foreach ($residentes as $res) {
            $resultados[] = [
                'id' => 'residente_' . $res->id,
                'tipo' => 'residente',
                'nombre' => $res->user->nombre,
                'documento' => $res->user->documento_identidad,
                'telefono' => $res->user->telefono,
                'unidad' => [
                    'numero' => $res->unidad->numero,
                    'torre' => $res->unidad->torre,
                    'bloque' => $res->unidad->bloque,
                ],
            ];
        }

        // 2. Buscar en invitados de reservas anteriores de la misma copropiedad
        $invitadosAnteriores = ReservaInvitado::where('copropiedad_id', $copropiedadId)
            ->where(function($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                  ->orWhere('documento', 'like', "%{$query}%");
            })
            ->whereNotNull('nombre')
            ->where('nombre', '!=', '')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->unique(function($item) {
                // Agrupar por nombre y documento para evitar duplicados
                return strtolower($item->nombre . '_' . ($item->documento ?? ''));
            });

        foreach ($invitadosAnteriores as $inv) {
            // Verificar que no esté ya en los resultados
            $existe = collect($resultados)->first(function($r) use ($inv) {
                return $r['nombre'] === $inv->nombre && 
                       ($r['documento'] ?? '') === ($inv->documento ?? '');
            });

            if (!$existe) {
                $resultados[] = [
                    'id' => 'invitado_' . $inv->id,
                    'tipo' => 'invitado_anterior',
                    'nombre' => $inv->nombre,
                    'documento' => $inv->documento,
                    'telefono' => $inv->telefono,
                    'tipo_acceso' => $inv->tipo,
                    'placa' => $inv->placa,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => array_slice($resultados, 0, 10) // Limitar a 10 resultados totales
        ], 200);
    }

    /**
     * Crear una nueva reserva
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

        // Validar datos de entrada
        $request->validate([
            'zona_social_id' => 'required|exists:zonas_sociales,id',
            'fecha_reserva' => 'required|date|after_or_equal:today',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'descripcion' => 'nullable|string',
            'soporte_pago' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB máximo
            'invitados' => 'nullable|array',
            'invitados.*.nombre' => 'required_with:invitados|string|max:150',
            'invitados.*.documento' => 'nullable|string|max:50',
            'invitados.*.telefono' => 'nullable|string|max:50',
            'invitados.*.tipo' => 'required_with:invitados|in:peatonal,vehicular',
            'invitados.*.placa' => 'nullable|string|max:20',
            'invitados.*.id_busqueda' => 'nullable|string', // Para identificar si es residente
        ], [
            'zona_social_id.required' => 'La zona social es obligatoria.',
            'zona_social_id.exists' => 'La zona social seleccionada no existe.',
            'fecha_reserva.required' => 'La fecha de reserva es obligatoria.',
            'fecha_reserva.after_or_equal' => 'La fecha de reserva no puede ser anterior a hoy.',
            'hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'hora_fin.required' => 'La hora de fin es obligatoria.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        // Obtener información del residente
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

        $copropiedadId = $residente->unidad->propiedad->id;
        $unidadId = $residente->unidad->id;

        // Obtener la zona social
        $zonaSocial = ZonaSocial::findOrFail($request->zona_social_id);

        // Validar que la zona social pertenezca a la misma copropiedad
        if ($zonaSocial->propiedad_id != $copropiedadId) {
            return response()->json([
                'success' => false,
                'message' => 'La zona social no pertenece a tu copropiedad.',
                'error' => 'ZONA_INVALID'
            ], 400);
        }

        // Calcular duración en minutos
        $horaInicio = Carbon::createFromFormat('H:i', $request->hora_inicio);
        $horaFin = Carbon::createFromFormat('H:i', $request->hora_fin);
        $duracionMinutos = $horaInicio->diffInMinutes($horaFin);

        // Calcular costo (si aplica)
        $costoReserva = 0;
        if ($zonaSocial->valor_alquiler) {
            $horas = $duracionMinutos / 60;
            $costoReserva = $zonaSocial->valor_alquiler * $horas;
        }

        // Procesar invitados si existen (puede venir como string JSON desde FormData)
        $invitadosData = $request->invitados;
        if (is_string($request->invitados)) {
            $invitadosData = json_decode($request->invitados, true);
        }
        if (!is_array($invitadosData)) {
            $invitadosData = [];
        }

        // Procesar soporte de pago si se envió
        $adjuntos = null;
        if ($request->hasFile('soporte_pago')) {
            try {
                $archivo = $request->file('soporte_pago');
                $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                    'folder' => 'reservas/soportes_pago',
                    'resource_type' => 'auto',
                ]);
                
                $adjuntos = [
                    'soporte_pago' => [
                        'url' => $result['secure_url'],
                        'public_id' => $result['public_id'],
                        'format' => $result['format'] ?? null,
                        'uploaded_at' => now()->toISOString(),
                    ]
                ];
            } catch (\Exception $e) {
                \Log::error('Error al subir soporte de pago a Cloudinary: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error al subir el soporte de pago. Por favor, intenta nuevamente.',
                    'error' => 'UPLOAD_ERROR'
                ], 500);
            }
        }

        // Crear la reserva
        $reserva = Reserva::create([
            'copropiedad_id' => $copropiedadId,
            'unidad_id' => $unidadId,
            'residente_id' => $residente->id,
            'zona_social_id' => $request->zona_social_id,
            'nombre_solicitante' => $user->nombre,
            'telefono_solicitante' => $user->telefono,
            'email_solicitante' => $user->email,
            'fecha_reserva' => $request->fecha_reserva,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'duracion_minutos' => $duracionMinutos,
            'cantidad_invitados' => count($invitadosData),
            'descripcion' => $request->descripcion,
            'costo_reserva' => $costoReserva,
            'deposito_garantia' => $zonaSocial->valor_deposito ?? 0,
            'requiere_pago' => $costoReserva > 0 || ($zonaSocial->valor_deposito ?? 0) > 0,
            'estado_pago' => ($costoReserva > 0 || ($zonaSocial->valor_deposito ?? 0) > 0) ? 'pendiente' : 'exento',
            'estado' => $zonaSocial->requiere_aprobacion ? 'solicitada' : 'aprobada',
            'es_exclusiva' => $zonaSocial->reservas_simultaneas == 1,
            'permite_invitados' => $zonaSocial->acepta_invitados,
            'adjuntos' => $adjuntos,
            'activo' => true,
        ]);

        // Si requiere aprobación, asignar aprobador automáticamente si no requiere
        if (!$zonaSocial->requiere_aprobacion) {
            $reserva->update([
                'aprobada_por' => $user->id, // O el ID del administrador si hay uno por defecto
                'fecha_aprobacion' => now(),
            ]);
        }

        // Crear invitados si existen
        if (count($invitadosData) > 0) {
            foreach ($invitadosData as $invitadoData) {
                $invitadoReserva = [
                    'reserva_id' => $reserva->id,
                    'copropiedad_id' => $copropiedadId,
                    'nombre' => $invitadoData['nombre'],
                    'documento' => $invitadoData['documento'] ?? null,
                    'telefono' => $invitadoData['telefono'] ?? null,
                    'tipo' => $invitadoData['tipo'],
                    'placa' => $invitadoData['placa'] ?? null,
                    'estado' => 'registrado',
                ];

                // Si el invitado es un residente (viene con id_busqueda que empieza con 'residente_')
                if (isset($invitadoData['id_busqueda']) && str_starts_with($invitadoData['id_busqueda'], 'residente_')) {
                    $residenteId = (int) str_replace('residente_', '', $invitadoData['id_busqueda']);
                    $residenteInvitado = Residente::with('unidad')->find($residenteId);
                    
                    if ($residenteInvitado && $residenteInvitado->unidad) {
                        $invitadoReserva['residente_id'] = $residenteInvitado->id;
                        $invitadoReserva['unidad_id'] = $residenteInvitado->unidad->id;
                    }
                }

                ReservaInvitado::create($invitadoReserva);
            }
        }

        // Registrar en historial
        \App\Models\ReservaHistorial::create([
            'reserva_id' => $reserva->id,
            'estado_anterior' => null,
            'estado_nuevo' => $reserva->estado,
            'comentario' => 'Reserva creada',
            'cambiado_por' => $user->id,
            'fecha_cambio' => now(),
        ]);

        // Cargar relaciones para la respuesta
        $reserva->load(['unidad', 'residente', 'zonaSocial', 'invitados']);

        return response()->json([
            'success' => true,
            'message' => $zonaSocial->requiere_aprobacion 
                ? 'Reserva solicitada exitosamente. Está pendiente de aprobación.' 
                : 'Reserva confirmada exitosamente.',
            'data' => [
                'id' => $reserva->id,
                'estado' => $reserva->estado,
                'fecha_reserva' => $reserva->fecha_reserva->format('Y-m-d'),
                'hora_inicio' => $reserva->hora_inicio,
                'hora_fin' => $reserva->hora_fin,
                'zona_social' => [
                    'id' => $reserva->zonaSocial->id,
                    'nombre' => $reserva->zonaSocial->nombre,
                ],
            ]
        ], 201);
    }

    /**
     * Actualizar soporte de pago de una reserva existente
     */
    public function actualizarSoportePago(Request $request, $id)
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

        // Validar datos de entrada
        $request->validate([
            'soporte_pago' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB máximo
        ], [
            'soporte_pago.required' => 'El soporte de pago es obligatorio.',
            'soporte_pago.file' => 'El soporte de pago debe ser un archivo válido.',
            'soporte_pago.mimes' => 'El soporte de pago debe ser un archivo PDF, JPG, JPEG o PNG.',
            'soporte_pago.max' => 'El soporte de pago no puede ser mayor a 5MB.',
        ]);

        // Obtener la reserva
        $reserva = Reserva::findOrFail($id);

        // Verificar que la reserva pertenezca al usuario
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->first();

        if (!$residente || $reserva->unidad_id !== $residente->unidad_id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar esta reserva.',
                'error' => 'UNAUTHORIZED'
            ], 403);
        }

        // Verificar que la reserva esté en estado solicitada
        if ($reserva->estado !== 'solicitada') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede actualizar el soporte de pago en reservas solicitadas.',
                'error' => 'INVALID_STATE'
            ], 400);
        }

        // Procesar soporte de pago
        try {
            $archivo = $request->file('soporte_pago');
            $result = Cloudinary::uploadApi()->upload($archivo->getRealPath(), [
                'folder' => 'reservas/soportes_pago',
                'resource_type' => 'auto',
            ]);
            
            // Obtener adjuntos existentes o crear nuevo objeto
            $adjuntos = $reserva->adjuntos ?? [];
            $adjuntos['soporte_pago'] = [
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'format' => $result['format'] ?? null,
                'uploaded_at' => now()->toISOString(),
            ];

            // Actualizar la reserva
            $reserva->update([
                'adjuntos' => $adjuntos,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Soporte de pago actualizado exitosamente.',
                'data' => [
                    'id' => $reserva->id,
                    'adjuntos' => $adjuntos,
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error al subir soporte de pago a Cloudinary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el soporte de pago. Por favor, intenta nuevamente.',
                'error' => 'UPLOAD_ERROR'
            ], 500);
        }
    }
}
