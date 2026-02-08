<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Residente;
use App\Models\Cartera;
use App\Models\CuentaCobro;
use App\Models\Pqrs;
use App\Models\Comunicado;
use App\Models\SorteoParqueadero;
use App\Models\Parqueadero;
use App\Models\Deposito;
use App\Models\ManualConvivencia;
use App\Models\Encuesta;
use App\Models\Votacion;
use App\Models\EncuestaRespuesta;
use App\Models\Voto;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ResidenteAuthController extends Controller
{
    /**
     * Login de residente mediante API
     */
    public function login(Request $request)
    {
        // Validar datos de entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // Buscar usuario por email
        $user = User::where('email', $request->email)->first();

        // Validar credenciales
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Las credenciales proporcionadas no son correctas.',
                'error' => 'INVALID_CREDENTIALS'
            ], 401);
        }

        // Validar que el usuario esté activo
        if (!$user->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta está desactivada. Contacta con la administración.',
                'error' => 'ACCOUNT_INACTIVE'
            ], 403);
        }

        // Validar que el usuario tenga rol de residente
        if (!$user->hasRole('residente')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para acceder como residente. Tu cuenta no tiene el rol de residente.',
                'error' => 'INVALID_ROLE'
            ], 403);
        }

        // Obtener información del residente (principal)
        $residente = Residente::where('user_id', $user->id)
            ->where('es_principal', true)
            ->activos()
            ->with(['unidad.propiedad'])
            ->first();

        // Si no tiene principal, obtener el primero activo
        if (!$residente) {
            $residente = Residente::where('user_id', $user->id)
                ->activos()
                ->with(['unidad.propiedad'])
                ->first();
        }

        if (!$residente) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes unidades asignadas como residente.',
                'error' => 'NO_UNIT_ASSIGNED'
            ], 403);
        }

        // Verificar que la unidad y propiedad existan
        if (!$residente->unidad) {
            return response()->json([
                'success' => false,
                'message' => 'Tu unidad no está disponible. Contacta con la administración.',
                'error' => 'UNIT_NOT_FOUND'
            ], 403);
        }

        if (!$residente->unidad->propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'La propiedad no está disponible. Contacta con la administración.',
                'error' => 'PROPERTY_NOT_FOUND'
            ], 403);
        }

        // Verificar que la propiedad tenga suscripción activa (si aplica)
        if ($residente->unidad->propiedad->estado === 'inactiva' || $residente->unidad->propiedad->estado === 'suspendida') {
            return response()->json([
                'success' => false,
                'message' => 'La propiedad está inactiva o suspendida. Contacta con la administración.',
                'error' => 'PROPERTY_INACTIVE'
            ], 403);
        }

        // Crear token de autenticación
        $token = $user->createToken('residente-token', ['residente'])->plainTextToken;

        // Preparar datos del usuario para el dashboard
        $userData = [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'email' => $user->email,
            'telefono' => $user->telefono,
            'documento_identidad' => $user->documento_identidad,
            'tipo_documento' => $user->tipo_documento,
            'avatar' => $user->avatar,
        ];

        // Preparar datos del residente
        $residenteData = [
            'id' => $residente->id,
            'tipo_relacion' => $residente->tipo_relacion,
            'fecha_inicio' => $residente->fecha_inicio?->format('Y-m-d'),
            'fecha_fin' => $residente->fecha_fin?->format('Y-m-d'),
            'es_principal' => $residente->es_principal,
            'recibe_notificaciones' => $residente->recibe_notificaciones,
        ];

        // Preparar datos de la unidad
        $unidadData = [
            'id' => $residente->unidad->id,
            'numero' => $residente->unidad->numero,
            'torre' => $residente->unidad->torre,
            'bloque' => $residente->unidad->bloque,
            'coeficiente' => $residente->unidad->coeficiente,
            'area_m2' => $residente->unidad->area_m2,
            'tipo' => $residente->unidad->tipo,
            'habitaciones' => $residente->unidad->habitaciones,
            'banos' => $residente->unidad->banos,
            'estado' => $residente->unidad->estado,
        ];

        // Preparar datos de la propiedad
        $propiedadData = [
            'id' => $residente->unidad->propiedad->id,
            'nombre' => $residente->unidad->propiedad->nombre,
            'nit' => $residente->unidad->propiedad->nit,
            'direccion' => $residente->unidad->propiedad->direccion,
            'telefono' => $residente->unidad->propiedad->telefono,
            'email' => $residente->unidad->propiedad->email,
        ];

        // Obtener todas las unidades del residente (por si tiene múltiples)
        $todasUnidades = Residente::where('user_id', $user->id)
            ->activos()
            ->with(['unidad.propiedad'])
            ->get()
            ->map(function ($res) {
                return [
                    'id' => $res->unidad->id,
                    'numero' => $res->unidad->numero,
                    'torre' => $res->unidad->torre,
                    'bloque' => $res->unidad->bloque,
                    'propiedad' => [
                        'id' => $res->unidad->propiedad->id,
                        'nombre' => $res->unidad->propiedad->nombre,
                    ],
                    'es_principal' => $res->es_principal,
                ];
            });

        // Obtener información de cartera
        $cartera = Cartera::where('unidad_id', $residente->unidad->id)
            ->where('activo', true)
            ->first();

        $carteraData = null;
        $proximoVencimiento = null;
        if ($cartera) {
            $carteraData = [
                'saldo_total' => (float) $cartera->saldo_total,
                'saldo_mora' => (float) $cartera->saldo_mora,
                'saldo_corriente' => (float) $cartera->saldo_corriente,
            ];

            // Obtener próxima cuenta de cobro vencida o por vencer
            $proximaCuenta = CuentaCobro::where('unidad_id', $residente->unidad->id)
                ->where('estado', 'pendiente')
                ->where('fecha_vencimiento', '>=', Carbon::now())
                ->orderBy('fecha_vencimiento', 'asc')
                ->first();

            if ($proximaCuenta) {
                $proximoVencimiento = [
                    'fecha' => $proximaCuenta->fecha_vencimiento->format('Y-m-d'),
                    'fecha_formateada' => $proximaCuenta->fecha_vencimiento->format('d M'),
                    'valor' => (float) $proximaCuenta->valor_total,
                ];
            }
        }

        // Obtener PQRS abiertas (radicada, en_proceso)
        $pqrsAbiertas = Pqrs::where('copropiedad_id', $propiedadData['id'])
            ->where(function($query) use ($residente) {
                $query->where('unidad_id', $residente->unidad->id)
                      ->orWhere('residente_id', $residente->id);
            })
            ->whereIn('estado', ['radicada', 'en_proceso'])
            ->where('activo', true)
            ->orderBy('fecha_radicacion', 'desc')
            ->get()
            ->map(function ($pqr) {
                return [
                    'id' => $pqr->id,
                    'numero_radicado' => $pqr->numero_radicado,
                    'asunto' => $pqr->asunto,
                    'tipo' => $pqr->tipo,
                    'estado' => $pqr->estado,
                    'prioridad' => $pqr->prioridad,
                    'fecha_radicacion' => $pqr->fecha_radicacion->format('Y-m-d H:i:s'),
                ];
            });

        // Obtener comunicados nuevos (no leídos)
        $comunicadosQuery = Comunicado::where('copropiedad_id', $propiedadData['id'])
            ->where('publicado', true)
            ->where('activo', true)
            ->where(function($query) use ($residente) {
                // Comunicados visibles para todos
                $query->where('visible_para', 'todos')
                      // O visibles para propietarios (si el residente es propietario)
                      ->orWhere(function($q) use ($residente) {
                          if ($residente->tipo_relacion === 'propietario') {
                              $q->where('visible_para', 'propietarios');
                          }
                      })
                      // O comunicados específicos para esta unidad
                      ->orWhereHas('unidades', function($q) use ($residente) {
                          $q->where('unidades.id', $residente->unidad->id);
                      })
                      // O comunicados específicos para este residente
                      ->orWhereHas('residentes', function($q) use ($residente) {
                          $q->where('residentes.id', $residente->id);
                      });
            })
            ->with(['residentes' => function($query) use ($residente) {
                $query->where('residentes.id', $residente->id);
            }])
            ->orderBy('fecha_publicacion', 'desc')
            ->limit(10)
            ->get();

        $comunicadosNuevos = $comunicadosQuery->map(function ($comunicado) use ($residente) {
            // Verificar si está leído
            $leido = false;
            $pivotResidente = $comunicado->residentes->first();
            if ($pivotResidente && $pivotResidente->pivot) {
                $leido = (bool) $pivotResidente->pivot->leido;
            }

            return [
                'id' => $comunicado->id,
                'titulo' => $comunicado->titulo,
                'resumen' => $comunicado->resumen,
                'tipo' => $comunicado->tipo,
                'fecha_publicacion' => $comunicado->fecha_publicacion?->format('Y-m-d H:i:s'),
                'leido' => $leido,
            ];
        })
        ->filter(function ($comunicado) {
            return !$comunicado['leido']; // Solo los no leídos
        })
        ->values();

        // Obtener reservas activas (si existe el modelo)
        // Por ahora retornamos un array vacío, se puede implementar cuando exista el modelo
        $reservasActivas = [];
        $proximaReserva = null;

        // Validar si existe un sorteo de parqueadero activo
        $sorteoActivo = SorteoParqueadero::where('copropiedad_id', $propiedadData['id'])
            ->where('estado', 'activo')
            ->where('activo', true)
            ->whereDate('fecha_inicio_recoleccion', '<=', Carbon::now())
            ->whereDate('fecha_fin_recoleccion', '>=', Carbon::now())
            ->first();

        $sorteoData = null;
        if ($sorteoActivo) {
            $sorteoData = [
                'id' => $sorteoActivo->id,
                'titulo' => $sorteoActivo->titulo,
                'descripcion' => $sorteoActivo->descripcion,
                'fecha_inicio_recoleccion' => $sorteoActivo->fecha_inicio_recoleccion->format('Y-m-d'),
                'fecha_fin_recoleccion' => $sorteoActivo->fecha_fin_recoleccion->format('Y-m-d'),
                'fecha_sorteo' => $sorteoActivo->fecha_sorteo->format('Y-m-d'),
                'fecha_inicio_uso' => $sorteoActivo->fecha_inicio_uso ? $sorteoActivo->fecha_inicio_uso->format('Y-m-d') : null,
                'duracion_meses' => $sorteoActivo->duracion_meses,
                'capacidad_autos' => $sorteoActivo->capacidad_autos,
                'capacidad_motos' => $sorteoActivo->capacidad_motos,
            ];
        }

        // Obtener parqueadero asignado a la unidad
        $parqueadero = Parqueadero::where('unidad_id', $residente->unidad->id)
            ->where('activo', true)
            ->where('estado', 'asignado')
            ->first();

        $parqueaderoData = null;
        if ($parqueadero) {
            $parqueaderoData = [
                'id' => $parqueadero->id,
                'codigo' => $parqueadero->codigo,
                'tipo' => $parqueadero->tipo,
                'tipo_vehiculo' => $parqueadero->tipo_vehiculo,
                'nivel' => $parqueadero->nivel,
                'fecha_asignacion' => $parqueadero->fecha_asignacion ? $parqueadero->fecha_asignacion->format('Y-m-d') : null,
            ];
        }

        // Obtener depósito asignado a la unidad
        $deposito = Deposito::where('unidad_id', $residente->unidad->id)
            ->where('activo', true)
            ->where('estado', 'asignado')
            ->first();

        $depositoData = null;
        if ($deposito) {
            $depositoData = [
                'id' => $deposito->id,
                'codigo' => $deposito->codigo,
                'nivel' => $deposito->nivel,
                'area_m2' => $deposito->area_m2 ? (float) $deposito->area_m2 : null,
                'fecha_asignacion' => $deposito->fecha_asignacion ? $deposito->fecha_asignacion->format('Y-m-d') : null,
            ];
        }

        // Obtener manual de convivencia activo
        $manualConvivencia = ManualConvivencia::where('copropiedad_id', $propiedadData['id'])
            ->where('activo', true)
            ->first();

        $manualConvivenciaData = null;
        if ($manualConvivencia) {
            $manualConvivenciaData = [
                'id' => $manualConvivencia->id,
                'manual_url' => $manualConvivencia->manual_url,
                'principales_deberes' => $manualConvivencia->principales_deberes,
                'principales_obligaciones' => $manualConvivencia->principales_obligaciones,
            ];
        }

        // Obtener encuestas activas
        $encuestasActivas = Encuesta::where('copropiedad_id', $propiedadData['id'])
            ->where('estado', 'activa')
            ->where('activo', true)
            ->whereDate('fecha_inicio', '<=', Carbon::now())
            ->whereDate('fecha_fin', '>=', Carbon::now())
            ->get()
            ->map(function ($encuesta) use ($residente) {
                // Verificar si el residente ya respondió
                $yaRespondio = EncuestaRespuesta::where('encuesta_id', $encuesta->id)
                    ->where('residente_id', $residente->id)
                    ->exists();

                return [
                    'id' => $encuesta->id,
                    'titulo' => $encuesta->titulo,
                    'descripcion' => $encuesta->descripcion,
                    'tipo_respuesta' => $encuesta->tipo_respuesta,
                    'fecha_inicio' => $encuesta->fecha_inicio->format('Y-m-d'),
                    'fecha_fin' => $encuesta->fecha_fin->format('Y-m-d'),
                    'ya_respondio' => $yaRespondio,
                ];
            });

        // Obtener votaciones activas
        $votacionesActivas = Votacion::where('copropiedad_id', $propiedadData['id'])
            ->where('estado', 'activa')
            ->where('activo', true)
            ->whereDate('fecha_inicio', '<=', Carbon::now())
            ->whereDate('fecha_fin', '>=', Carbon::now())
            ->with('opciones')
            ->get()
            ->map(function ($votacion) use ($residente) {
                // Verificar si el residente ya votó
                $yaVoto = Voto::where('votacion_id', $votacion->id)
                    ->where('residente_id', $residente->id)
                    ->exists();

                return [
                    'id' => $votacion->id,
                    'titulo' => $votacion->titulo,
                    'descripcion' => $votacion->descripcion,
                    'fecha_inicio' => $votacion->fecha_inicio->format('Y-m-d'),
                    'fecha_fin' => $votacion->fecha_fin->format('Y-m-d'),
                    'opciones' => $votacion->opciones->map(function ($opcion) {
                        return [
                            'id' => $opcion->id,
                            'texto_opcion' => $opcion->texto_opcion,
                        ];
                    }),
                    'ya_voto' => $yaVoto,
                ];
            });

        // Obtener asambleas en estado programada o en_curso
        $asambleas = DB::table('asambleas')
            ->where('copropiedad_id', $propiedadData['id'])
            ->whereIn('estado', ['programada', 'en_curso'])
            ->where('activo', true)
            ->orderBy('fecha_inicio', 'desc')
            ->get()
            ->map(function ($asamblea) use ($residente) {
                // Obtener votaciones abiertas de esta asamblea
                $votaciones = DB::table('asamblea_votaciones')
                    ->where('asamblea_id', $asamblea->id)
                    ->where('estado', 'abierta')
                    ->orderBy('fecha_inicio', 'desc')
                    ->get()
                    ->map(function ($votacion) use ($residente) {
                        // Obtener opciones de la votación
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

                return [
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
                    'votaciones' => $votaciones,
                ];
            });

        // Respuesta exitosa
        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'user' => $userData,
                'residente' => $residenteData,
                'unidad' => $unidadData,
                'propiedad' => $propiedadData,
                'todas_unidades' => $todasUnidades,
                'cartera' => $carteraData,
                'proximo_vencimiento' => $proximoVencimiento,
                'pqrs_abiertas' => $pqrsAbiertas,
                'comunicados_nuevos' => $comunicadosNuevos,
                'reservas_activas' => $reservasActivas,
                'proxima_reserva' => $proximaReserva,
                'sorteo_parqueadero_activo' => $sorteoData,
                'parqueadero' => $parqueaderoData,
                'deposito' => $depositoData,
                'manual_convivencia' => $manualConvivenciaData,
                'encuestas_activas' => $encuestasActivas->count() > 0 ? $encuestasActivas : null,
                'votaciones_activas' => $votacionesActivas->count() > 0 ? $votacionesActivas : null,
                'asambleas' => $asambleas->count() > 0 ? $asambleas : null,
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 200);
    }

    /**
     * Logout de residente
     */
    public function logout(Request $request)
    {
        // Revocar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ], 200);
    }

    /**
     * Obtener información del usuario autenticado (refrescar datos)
     */
    public function me(Request $request)
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

        if (!$residente) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de residente'
            ], 404);
        }

        $userData = [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'email' => $user->email,
            'telefono' => $user->telefono,
            'documento_identidad' => $user->documento_identidad,
            'tipo_documento' => $user->tipo_documento,
            'avatar' => $user->avatar,
        ];

        $residenteData = [
            'id' => $residente->id,
            'tipo_relacion' => $residente->tipo_relacion,
            'fecha_inicio' => $residente->fecha_inicio?->format('Y-m-d'),
            'fecha_fin' => $residente->fecha_fin?->format('Y-m-d'),
            'es_principal' => $residente->es_principal,
            'recibe_notificaciones' => $residente->recibe_notificaciones,
        ];

        $unidadData = [
            'id' => $residente->unidad->id,
            'numero' => $residente->unidad->numero,
            'torre' => $residente->unidad->torre,
            'bloque' => $residente->unidad->bloque,
            'coeficiente' => $residente->unidad->coeficiente,
            'area_m2' => $residente->unidad->area_m2,
            'tipo' => $residente->unidad->tipo,
            'habitaciones' => $residente->unidad->habitaciones,
            'banos' => $residente->unidad->banos,
            'estado' => $residente->unidad->estado,
        ];

        $propiedadData = [
            'id' => $residente->unidad->propiedad->id,
            'nombre' => $residente->unidad->propiedad->nombre,
            'nit' => $residente->unidad->propiedad->nit,
            'direccion' => $residente->unidad->propiedad->direccion,
            'telefono' => $residente->unidad->propiedad->telefono,
            'email' => $residente->unidad->propiedad->email,
        ];

        // Obtener todas las unidades del residente
        $todasUnidades = Residente::where('user_id', $user->id)
            ->activos()
            ->with(['unidad.propiedad'])
            ->get()
            ->map(function ($res) {
                return [
                    'id' => $res->unidad->id,
                    'numero' => $res->unidad->numero,
                    'torre' => $res->unidad->torre,
                    'bloque' => $res->unidad->bloque,
                    'propiedad' => [
                        'id' => $res->unidad->propiedad->id,
                        'nombre' => $res->unidad->propiedad->nombre,
                    ],
                    'es_principal' => $res->es_principal,
                ];
            });

        // Obtener información de cartera
        $cartera = Cartera::where('unidad_id', $residente->unidad->id)
            ->where('activo', true)
            ->first();

        $carteraData = null;
        $proximoVencimiento = null;
        if ($cartera) {
            $carteraData = [
                'saldo_total' => (float) $cartera->saldo_total,
                'saldo_mora' => (float) $cartera->saldo_mora,
                'saldo_corriente' => (float) $cartera->saldo_corriente,
            ];

            // Obtener próxima cuenta de cobro vencida o por vencer
            $proximaCuenta = CuentaCobro::where('unidad_id', $residente->unidad->id)
                ->where('estado', 'pendiente')
                ->where('fecha_vencimiento', '>=', Carbon::now())
                ->orderBy('fecha_vencimiento', 'asc')
                ->first();

            if ($proximaCuenta) {
                $proximoVencimiento = [
                    'fecha' => $proximaCuenta->fecha_vencimiento->format('Y-m-d'),
                    'fecha_formateada' => $proximaCuenta->fecha_vencimiento->format('d M'),
                    'valor' => (float) $proximaCuenta->valor_total,
                ];
            }
        }

        // Obtener PQRS abiertas
        $pqrsAbiertas = Pqrs::where('copropiedad_id', $propiedadData['id'])
            ->where(function($query) use ($residente) {
                $query->where('unidad_id', $residente->unidad->id)
                      ->orWhere('residente_id', $residente->id);
            })
            ->whereIn('estado', ['radicada', 'en_proceso'])
            ->where('activo', true)
            ->orderBy('fecha_radicacion', 'desc')
            ->get()
            ->map(function ($pqr) {
                return [
                    'id' => $pqr->id,
                    'numero_radicado' => $pqr->numero_radicado,
                    'asunto' => $pqr->asunto,
                    'tipo' => $pqr->tipo,
                    'estado' => $pqr->estado,
                    'prioridad' => $pqr->prioridad,
                    'fecha_radicacion' => $pqr->fecha_radicacion->format('Y-m-d H:i:s'),
                ];
            });

        // Obtener comunicados nuevos
        $comunicadosQuery = Comunicado::where('copropiedad_id', $propiedadData['id'])
            ->where('publicado', true)
            ->where('activo', true)
            ->where(function($query) use ($residente) {
                $query->where('visible_para', 'todos')
                      ->orWhere(function($q) use ($residente) {
                          if ($residente->tipo_relacion === 'propietario') {
                              $q->where('visible_para', 'propietarios');
                          }
                      })
                      ->orWhereHas('unidades', function($q) use ($residente) {
                          $q->where('unidades.id', $residente->unidad->id);
                      })
                      ->orWhereHas('residentes', function($q) use ($residente) {
                          $q->where('residentes.id', $residente->id);
                      });
            })
            ->with(['residentes' => function($query) use ($residente) {
                $query->where('residentes.id', $residente->id);
            }])
            ->orderBy('fecha_publicacion', 'desc')
            ->limit(10)
            ->get();

        $comunicadosNuevos = $comunicadosQuery->map(function ($comunicado) use ($residente) {
            $leido = false;
            $pivotResidente = $comunicado->residentes->first();
            if ($pivotResidente && $pivotResidente->pivot) {
                $leido = (bool) $pivotResidente->pivot->leido;
            }

            return [
                'id' => $comunicado->id,
                'titulo' => $comunicado->titulo,
                'resumen' => $comunicado->resumen,
                'tipo' => $comunicado->tipo,
                'fecha_publicacion' => $comunicado->fecha_publicacion?->format('Y-m-d H:i:s'),
                'leido' => $leido,
            ];
        })
        ->filter(function ($comunicado) {
            return !$comunicado['leido'];
        })
        ->values();

        // Obtener reservas activas
        $reservasActivas = [];
        $proximaReserva = null;

        // Validar si existe un sorteo de parqueadero activo
        $sorteoActivo = SorteoParqueadero::where('copropiedad_id', $propiedadData['id'])
            ->where('estado', 'activo')
            #->where('activo', true)
            ->whereDate('fecha_inicio_recoleccion', '<=', Carbon::now())
            ->whereDate('fecha_inicio_uso', '>=', Carbon::now())
            ->first();

        $sorteoData = null;
        if ($sorteoActivo) {
            $sorteoData = [
                'id' => $sorteoActivo->id,
                'titulo' => $sorteoActivo->titulo,
                'descripcion' => $sorteoActivo->descripcion,
                'fecha_inicio_recoleccion' => $sorteoActivo->fecha_inicio_recoleccion->format('Y-m-d'),
                'fecha_fin_recoleccion' => $sorteoActivo->fecha_fin_recoleccion->format('Y-m-d'),
                'fecha_sorteo' => $sorteoActivo->fecha_sorteo->format('Y-m-d'),
                'fecha_inicio_uso' => $sorteoActivo->fecha_inicio_uso ? $sorteoActivo->fecha_inicio_uso->format('Y-m-d') : null,
                'duracion_meses' => $sorteoActivo->duracion_meses,
                'capacidad_autos' => $sorteoActivo->capacidad_autos,
                'capacidad_motos' => $sorteoActivo->capacidad_motos,
            ];
        }

        // Obtener parqueadero asignado a la unidad
        $parqueadero = Parqueadero::where('unidad_id', $residente->unidad->id)
            ->where('activo', true)
            ->where('estado', 'asignado')
            ->first();

        $parqueaderoData = null;
        if ($parqueadero) {
            $parqueaderoData = [
                'id' => $parqueadero->id,
                'codigo' => $parqueadero->codigo,
                'tipo' => $parqueadero->tipo,
                'tipo_vehiculo' => $parqueadero->tipo_vehiculo,
                'nivel' => $parqueadero->nivel,
                'fecha_asignacion' => $parqueadero->fecha_asignacion ? $parqueadero->fecha_asignacion->format('Y-m-d') : null,
            ];
        }

        // Obtener depósito asignado a la unidad
        $deposito = Deposito::where('unidad_id', $residente->unidad->id)
            ->where('activo', true)
            ->where('estado', 'asignado')
            ->first();

        $depositoData = null;
        if ($deposito) {
            $depositoData = [
                'id' => $deposito->id,
                'codigo' => $deposito->codigo,
                'nivel' => $deposito->nivel,
                'area_m2' => $deposito->area_m2 ? (float) $deposito->area_m2 : null,
                'fecha_asignacion' => $deposito->fecha_asignacion ? $deposito->fecha_asignacion->format('Y-m-d') : null,
            ];
        }

        // Obtener manual de convivencia activo
        $manualConvivencia = ManualConvivencia::where('copropiedad_id', $propiedadData['id'])
            ->where('activo', true)
            ->first();

        $manualConvivenciaData = null;
        if ($manualConvivencia) {
            $manualConvivenciaData = [
                'id' => $manualConvivencia->id,
                'manual_url' => $manualConvivencia->manual_url,
                'principales_deberes' => $manualConvivencia->principales_deberes,
                'principales_obligaciones' => $manualConvivencia->principales_obligaciones,
            ];
        }

        // Obtener encuestas activas
        $encuestasActivas = Encuesta::where('copropiedad_id', $propiedadData['id'])
            ->where('estado', 'activa')
            ->where('activo', true)
            ->whereDate('fecha_inicio', '<=', Carbon::now())
            ->whereDate('fecha_fin', '>=', Carbon::now())
            ->get()
            ->map(function ($encuesta) use ($residente) {
                // Verificar si el residente ya respondió
                $yaRespondio = EncuestaRespuesta::where('encuesta_id', $encuesta->id)
                    ->where('residente_id', $residente->id)
                    ->exists();

                return [
                    'id' => $encuesta->id,
                    'titulo' => $encuesta->titulo,
                    'descripcion' => $encuesta->descripcion,
                    'tipo_respuesta' => $encuesta->tipo_respuesta,
                    'fecha_inicio' => $encuesta->fecha_inicio->format('Y-m-d'),
                    'fecha_fin' => $encuesta->fecha_fin->format('Y-m-d'),
                    'ya_respondio' => $yaRespondio,
                ];
            });

        // Obtener votaciones activas
        $votacionesActivas = Votacion::where('copropiedad_id', $propiedadData['id'])
            ->where('estado', 'activa')
            ->where('activo', true)
            ->whereDate('fecha_inicio', '<=', Carbon::now())
            ->whereDate('fecha_fin', '>=', Carbon::now())
            ->with('opciones')
            ->get()
            ->map(function ($votacion) use ($residente) {
                // Verificar si el residente ya votó
                $yaVoto = Voto::where('votacion_id', $votacion->id)
                    ->where('residente_id', $residente->id)
                    ->exists();

                return [
                    'id' => $votacion->id,
                    'titulo' => $votacion->titulo,
                    'descripcion' => $votacion->descripcion,
                    'fecha_inicio' => $votacion->fecha_inicio->format('Y-m-d'),
                    'fecha_fin' => $votacion->fecha_fin->format('Y-m-d'),
                    'opciones' => $votacion->opciones->map(function ($opcion) {
                        return [
                            'id' => $opcion->id,
                            'texto_opcion' => $opcion->texto_opcion,
                        ];
                    }),
                    'ya_voto' => $yaVoto,
                ];
            });

        // Obtener asambleas en estado programada o en_curso
        $asambleas = \DB::table('asambleas')
            ->where('copropiedad_id', $propiedadData['id'])
            ->whereIn('estado', ['programada', 'en_curso'])
            ->where('activo', true)
            ->orderBy('fecha_inicio', 'desc')
            ->get()
            ->map(function ($asamblea) use ($residente) {
                // Obtener votaciones abiertas de esta asamblea
                $votaciones = \DB::table('asamblea_votaciones')
                    ->where('asamblea_id', $asamblea->id)
                    ->where('estado', 'abierta')
                    ->orderBy('fecha_inicio', 'desc')
                    ->get()
                    ->map(function ($votacion) use ($residente) {
                        // Obtener opciones de la votación
                        $opciones = \DB::table('asamblea_votacion_opciones')
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
                        $yaVoto = \DB::table('asamblea_votos')
                            ->where('votacion_id', $votacion->id)
                            ->where('residente_id', $residente->id)
                            ->exists();

                        return [
                            'id' => $votacion->id,
                            'titulo' => $votacion->titulo,
                            'descripcion' => $votacion->descripcion,
                            'tipo' => $votacion->tipo,
                            'estado' => $votacion->estado,
                            'fecha_inicio' => $votacion->fecha_inicio ? \Carbon\Carbon::parse($votacion->fecha_inicio)->format('Y-m-d H:i:s') : null,
                            'fecha_fin' => $votacion->fecha_fin ? \Carbon\Carbon::parse($votacion->fecha_fin)->format('Y-m-d H:i:s') : null,
                            'opciones' => $opciones,
                            'ya_voto' => $yaVoto,
                        ];
                    });

                return [
                    'id' => $asamblea->id,
                    'titulo' => $asamblea->titulo,
                    'descripcion' => $asamblea->descripcion,
                    'tipo' => $asamblea->tipo,
                    'modalidad' => $asamblea->modalidad,
                    'estado' => $asamblea->estado,
                    'fecha_inicio' => $asamblea->fecha_inicio ? \Carbon\Carbon::parse($asamblea->fecha_inicio)->format('Y-m-d H:i:s') : null,
                    'fecha_fin' => $asamblea->fecha_fin ? \Carbon\Carbon::parse($asamblea->fecha_fin)->format('Y-m-d H:i:s') : null,
                    'quorum_minimo' => $asamblea->quorum_minimo,
                    'quorum_actual' => $asamblea->quorum_actual,
                    'url_transmision' => $asamblea->url_transmision,
                    'votaciones' => $votaciones,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $userData,
                'residente' => $residenteData,
                'unidad' => $unidadData,
                'propiedad' => $propiedadData,
                'todas_unidades' => $todasUnidades,
                'cartera' => $carteraData,
                'proximo_vencimiento' => $proximoVencimiento,
                'pqrs_abiertas' => $pqrsAbiertas,
                'comunicados_nuevos' => $comunicadosNuevos,
                'reservas_activas' => $reservasActivas,
                'proxima_reserva' => $proximaReserva,
                'sorteo_parqueadero_activo' => $sorteoData,
                'parqueadero' => $parqueaderoData,
                'deposito' => $depositoData,
                'manual_convivencia' => $manualConvivenciaData,
                'encuestas_activas' => $encuestasActivas->count() > 0 ? $encuestasActivas : null,
                'votaciones_activas' => $votacionesActivas->count() > 0 ? $votacionesActivas : null,
                'asambleas' => $asambleas->count() > 0 ? $asambleas : null,
            ]
        ], 200);
    }
}
