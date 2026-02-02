<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cartera;
use App\Models\CuentaCobro;
use App\Models\AcuerdoPago;
use App\Models\Residente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CarteraController extends Controller
{
    /**
     * Obtener información completa de la cartera del residente
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
            // Obtener la cartera de la unidad
            $cartera = Cartera::where('copropiedad_id', $propiedadId)
                ->where('unidad_id', $unidadId)
                ->where('activo', true)
                ->first();

            if (!$cartera) {
                // Si no existe cartera, crear una con saldos en cero
                $cartera = Cartera::create([
                    'copropiedad_id' => $propiedadId,
                    'unidad_id' => $unidadId,
                    'saldo_total' => 0,
                    'saldo_mora' => 0,
                    'saldo_corriente' => 0,
                    'ultima_actualizacion' => Carbon::now(),
                    'activo' => true,
                ]);
            }

            // Obtener todas las cuentas de cobro de la unidad
            // Ordenar por periodo (más reciente primero), luego por fecha de emisión, y finalmente por fecha de creación
            $cuentasCobro = CuentaCobro::where('copropiedad_id', $propiedadId)
                ->where('unidad_id', $unidadId)
                ->where('estado', '!=', 'anulada')
                ->with(['detalles'])
                ->orderBy('periodo', 'desc')
                ->orderBy('fecha_emision', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($cuenta) {
                    // Calcular saldo pendiente
                    $saldoPendiente = $cuenta->calcularSaldoPendiente();
                    
                    return [
                        'id' => $cuenta->id,
                        'periodo' => $cuenta->periodo,
                        'fecha_emision' => $cuenta->fecha_emision 
                            ? $cuenta->fecha_emision->format('Y-m-d')
                            : null,
                        'fecha_vencimiento' => $cuenta->fecha_vencimiento 
                            ? $cuenta->fecha_vencimiento->format('Y-m-d')
                            : null,
                        'valor_cuotas' => (float) $cuenta->valor_cuotas,
                        'valor_intereses' => (float) $cuenta->valor_intereses,
                        'valor_descuentos' => (float) $cuenta->valor_descuentos,
                        'valor_recargos' => (float) $cuenta->valor_recargos,
                        'valor_total' => (float) $cuenta->valor_total,
                        'saldo_pendiente' => $saldoPendiente,
                        'estado' => $cuenta->estado,
                        'observaciones' => $cuenta->observaciones,
                        'conceptos' => $cuenta->detalles->map(function ($detalle) {
                            return [
                                'id' => $detalle->id,
                                'concepto' => $detalle->concepto,
                                'valor' => (float) $detalle->valor,
                            ];
                        }),
                    ];
                });

            // Obtener acuerdos de pago activos
            $acuerdosPago = AcuerdoPago::where('copropiedad_id', $propiedadId)
                ->where('unidad_id', $unidadId)
                ->where('activo', true)
                ->whereIn('estado', ['pendiente', 'activo'])
                ->orderBy('fecha_acuerdo', 'desc')
                ->get()
                ->map(function ($acuerdo) {
                    return [
                        'id' => $acuerdo->id,
                        'numero_acuerdo' => $acuerdo->numero_acuerdo,
                        'fecha_acuerdo' => $acuerdo->fecha_acuerdo 
                            ? $acuerdo->fecha_acuerdo->format('Y-m-d')
                            : null,
                        'fecha_inicio' => $acuerdo->fecha_inicio 
                            ? $acuerdo->fecha_inicio->format('Y-m-d')
                            : null,
                        'fecha_fin' => $acuerdo->fecha_fin 
                            ? $acuerdo->fecha_fin->format('Y-m-d')
                            : null,
                        'valor_acordado' => (float) $acuerdo->valor_acordado,
                        'saldo_pendiente' => (float) $acuerdo->saldo_pendiente,
                        'numero_cuotas' => $acuerdo->numero_cuotas,
                        'valor_cuota' => (float) $acuerdo->valor_cuota,
                        'estado' => $acuerdo->estado,
                    ];
                });

            // Determinar si está en mora
            $estaEnMora = $cartera->saldo_mora > 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'cartera' => [
                        'id' => $cartera->id,
                        'saldo_total' => (float) $cartera->saldo_total,
                        'saldo_mora' => (float) $cartera->saldo_mora,
                        'saldo_corriente' => (float) $cartera->saldo_corriente,
                        'ultima_actualizacion' => $cartera->ultima_actualizacion 
                            ? $cartera->ultima_actualizacion->format('Y-m-d H:i:s')
                            : null,
                        'esta_en_mora' => $estaEnMora,
                    ],
                    'cuentas_cobro' => $cuentasCobro,
                    'acuerdos_pago' => $acuerdosPago,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al obtener cartera: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la información de cartera: ' . $e->getMessage(),
                'error' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Solicitar un acuerdo de pago (crea una PQRS de tipo peticion)
     */
    public function solicitarAcuerdoPago(Request $request)
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

        // Validar que tenga saldo en mora
        $cartera = Cartera::where('copropiedad_id', $propiedadId)
            ->where('unidad_id', $unidadId)
            ->where('activo', true)
            ->first();

        if (!$cartera || $cartera->saldo_mora <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes saldo en mora para solicitar un acuerdo de pago.',
                'error' => 'NO_MORA'
            ], 400);
        }

        // Validar datos
        $validated = $request->validate([
            'descripcion' => 'required|string|max:1000',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',
        ]);

        try {
            // Generar número de radicado único
            $year = date('Y');
            $month = date('m');
            
            $ultimoRadicado = DB::table('pqrs')
                ->where('numero_radicado', 'like', "PQRS-{$year}-{$month}-%")
                ->orderBy('numero_radicado', 'desc')
                ->value('numero_radicado');
            
            if ($ultimoRadicado) {
                $partes = explode('-', $ultimoRadicado);
                $secuencial = intval(end($partes)) + 1;
            } else {
                $secuencial = 1;
            }
            
            $numeroRadicado = sprintf('PQRS-%s-%s-%05d', $year, $month, $secuencial);

            // Crear la PQRS
            $pqrsId = DB::table('pqrs')->insertGetId([
                'copropiedad_id' => $propiedadId,
                'unidad_id' => $unidadId,
                'residente_id' => $residente->id,
                'tipo' => 'peticion',
                'categoria' => 'administracion',
                'asunto' => 'Solicitud de acuerdo de pago',
                'descripcion' => $validated['descripcion'],
                'prioridad' => 'alta',
                'estado' => 'radicada',
                'canal' => 'app',
                'numero_radicado' => $numeroRadicado,
                'fecha_radicacion' => Carbon::now(),
                'activo' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Crear registro inicial en el historial
            DB::table('pqrs_historial')->insert([
                'pqrs_id' => $pqrsId,
                'estado_anterior' => null,
                'estado_nuevo' => 'radicada',
                'comentario' => 'Solicitud de acuerdo de pago radicada',
                'soporte_url' => null,
                'cambiado_por' => $user->id,
                'fecha_cambio' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de acuerdo de pago enviada correctamente.',
                'data' => [
                    'pqrs_id' => $pqrsId,
                    'numero_radicado' => $numeroRadicado,
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error al solicitar acuerdo de pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la solicitud: ' . $e->getMessage(),
                'error' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
}
