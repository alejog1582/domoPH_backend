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

            // Obtener cuentas de cobro en mora (para mostrar en la modal de acuerdo de pago)
            $cuentasEnMora = CuentaCobro::where('copropiedad_id', $propiedadId)
                ->where('unidad_id', $unidadId)
                ->whereIn('estado', ['pendiente', 'vencida'])
                ->with(['detalles'])
                ->orderBy('periodo', 'desc')
                ->orderBy('fecha_emision', 'desc')
                ->get()
                ->map(function ($cuenta) {
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
                        'valor_total' => (float) $cuenta->valor_total,
                        'saldo_pendiente' => $saldoPendiente,
                        'estado' => $cuenta->estado,
                    ];
                });

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
                    'cuentas_en_mora' => $cuentasEnMora, // Para la modal de acuerdo de pago
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
     * Solicitar un acuerdo de pago
     * Crea registros en acuerdos_pagos para que el administrador los revise
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
            'valor_mensual_propuesto' => 'required|numeric|min:1',
            'seleccionar_todas' => 'required|boolean',
            'cuentas_cobro_ids' => 'required_if:seleccionar_todas,false|array',
            'cuentas_cobro_ids.*' => 'exists:cuenta_cobros,id',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',
            'valor_mensual_propuesto.required' => 'El valor mensual propuesto es obligatorio.',
            'valor_mensual_propuesto.numeric' => 'El valor mensual debe ser un número.',
            'valor_mensual_propuesto.min' => 'El valor mensual debe ser mayor a 0.',
            'seleccionar_todas.required' => 'Debes indicar si deseas seleccionar todas las cuentas.',
            'cuentas_cobro_ids.required_if' => 'Debes seleccionar al menos una cuenta de cobro.',
            'cuentas_cobro_ids.array' => 'Las cuentas de cobro deben ser un array.',
            'cuentas_cobro_ids.*.exists' => 'Una o más cuentas de cobro seleccionadas no existen.',
        ]);

        try {
            DB::beginTransaction();

            // Obtener cuentas de cobro en mora de la unidad
            $cuentasEnMora = CuentaCobro::where('copropiedad_id', $propiedadId)
                ->where('unidad_id', $unidadId)
                ->whereIn('estado', ['pendiente', 'vencida'])
                ->get();

            if ($cuentasEnMora->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes cuentas de cobro en mora.',
                    'error' => 'NO_CUENTAS_MORA'
                ], 400);
            }

            // Determinar qué cuentas incluir en el acuerdo
            $cuentasParaAcuerdo = [];
            
            if ($validated['seleccionar_todas']) {
                // Si selecciona todas, usar todas las cuentas en mora
                $cuentasParaAcuerdo = $cuentasEnMora->pluck('id')->toArray();
            } else {
                // Validar que las cuentas seleccionadas pertenezcan a la unidad y estén en mora
                $cuentasSeleccionadas = CuentaCobro::where('copropiedad_id', $propiedadId)
                    ->where('unidad_id', $unidadId)
                    ->whereIn('id', $validated['cuentas_cobro_ids'])
                    ->whereIn('estado', ['pendiente', 'vencida'])
                    ->get();

                if ($cuentasSeleccionadas->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Las cuentas de cobro seleccionadas no son válidas o no están en mora.',
                        'error' => 'CUENTAS_INVALIDAS'
                    ], 400);
                }

                $cuentasParaAcuerdo = $cuentasSeleccionadas->pluck('id')->toArray();
            }

            // Generar número de acuerdo único
            $year = date('Y');
            $ultimoAcuerdo = AcuerdoPago::where('copropiedad_id', $propiedadId)
                ->whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();
            
            $numeroSecuencial = $ultimoAcuerdo ? $ultimoAcuerdo->id + 1 : 1;
            $numeroAcuerdo = 'ACU-' . str_pad($numeroSecuencial, 6, '0', STR_PAD_LEFT);

            // Calcular valores totales
            $totalSaldoMora = $cuentasEnMora->sum(function($cuenta) {
                return $cuenta->calcularSaldoPendiente();
            });

            // Si se seleccionan todas las cuentas, crear un solo registro con cuenta_cobro_id = null
            if ($validated['seleccionar_todas']) {
                $acuerdo = AcuerdoPago::create([
                    'copropiedad_id' => $propiedadId,
                    'unidad_id' => $unidadId,
                    'cartera_id' => $cartera->id,
                    'cuenta_cobro_id' => null, // null porque incluye todas
                    'numero_acuerdo' => $numeroAcuerdo,
                    'fecha_acuerdo' => Carbon::now(),
                    'fecha_inicio' => Carbon::now(),
                    'fecha_fin' => null, // Se definirá cuando el admin apruebe
                    'descripcion' => $validated['descripcion'],
                    'saldo_original' => $totalSaldoMora,
                    'valor_acordado' => $totalSaldoMora, // Se ajustará cuando el admin apruebe
                    'valor_inicial' => 0,
                    'saldo_pendiente' => $totalSaldoMora,
                    'numero_cuotas' => 1, // Se calculará cuando el admin apruebe
                    'valor_cuota' => $validated['valor_mensual_propuesto'],
                    'valor_mensual_propuesto' => $validated['valor_mensual_propuesto'],
                    'interes_acuerdo' => 0,
                    'valor_intereses' => 0,
                    'estado' => 'pendiente', // Pendiente de aprobación del admin
                    'activo' => true,
                    'usuario_id' => $user->id,
                ]);
            } else {
                // Si se seleccionan cuentas específicas, crear un registro por cada cuenta
                foreach ($cuentasParaAcuerdo as $cuentaCobroId) {
                    $cuentaCobro = CuentaCobro::find($cuentaCobroId);
                    $saldoPendiente = $cuentaCobro->calcularSaldoPendiente();

                    AcuerdoPago::create([
                        'copropiedad_id' => $propiedadId,
                        'unidad_id' => $unidadId,
                        'cartera_id' => $cartera->id,
                        'cuenta_cobro_id' => $cuentaCobroId,
                        'numero_acuerdo' => $numeroAcuerdo . '-' . $cuentaCobroId, // Número único por cuenta
                        'fecha_acuerdo' => Carbon::now(),
                        'fecha_inicio' => Carbon::now(),
                        'fecha_fin' => null,
                        'descripcion' => $validated['descripcion'],
                        'saldo_original' => $saldoPendiente,
                        'valor_acordado' => $saldoPendiente,
                        'valor_inicial' => 0,
                        'saldo_pendiente' => $saldoPendiente,
                        'numero_cuotas' => 1,
                        'valor_cuota' => $validated['valor_mensual_propuesto'],
                        'valor_mensual_propuesto' => $validated['valor_mensual_propuesto'],
                        'interes_acuerdo' => 0,
                        'valor_intereses' => 0,
                        'estado' => 'pendiente',
                        'activo' => true,
                        'usuario_id' => $user->id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de acuerdo de pago enviada correctamente. El administrador la revisará.',
                'data' => [
                    'numero_acuerdo' => $numeroAcuerdo,
                    'cuentas_incluidas' => count($cuentasParaAcuerdo),
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al solicitar acuerdo de pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la solicitud: ' . $e->getMessage(),
                'error' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
}
