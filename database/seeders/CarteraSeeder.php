<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Propiedad;
use App\Models\Unidad;
use App\Models\CuotaAdministracion;
use App\Models\Cartera;
use App\Models\CuentaCobro;
use App\Models\CuentaCobroDetalle;
use App\Models\Recaudo;
use App\Models\RecaudoDetalle;
use App\Models\User;
use App\Models\Residente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CarteraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('💰 Iniciando creación de datos de Cartera DEMO...');

        // Obtener la propiedad demo
        $propiedad = Propiedad::where('email', 'demo@domoph.com')->first();

        if (!$propiedad) {
            $this->command->error('   ✗ No se encontró la propiedad demo. Ejecuta primero el DemoSeeder.');
            return;
        }

        // Obtener todas las unidades de la propiedad
        $unidades = Unidad::where('propiedad_id', $propiedad->id)->get();

        if ($unidades->isEmpty()) {
            $this->command->error('   ✗ No se encontraron unidades para la propiedad demo.');
            return;
        }

        // Obtener usuario administrador para registrar recaudos
        $adminUser = User::where('email', 'demo@domoph.com')->first();

        // 1. Crear cuotas de administración
        $this->crearCuotasAdministracion($propiedad, $unidades);

        // 2. Crear carteras para todas las unidades
        $this->crearCarteras($propiedad, $unidades);

        // 3. Crear cuentas de cobro (2 por unidad)
        $cuentasCobro = $this->crearCuentasCobro($propiedad, $unidades);

        // 4. Crear detalles de cuentas de cobro
        $this->crearDetallesCuentasCobro($cuentasCobro);

        // 5. Crear recaudos (70% de las cuentas)
        $this->crearRecaudos($propiedad, $cuentasCobro, $adminUser);

        // 6. Actualizar carteras con saldos reales
        $this->actualizarCarteras($propiedad, $unidades);

        $this->command->info('✅ Datos de Cartera DEMO creados exitosamente!');
    }

    /**
     * Crear cuotas de administración basadas en coeficientes
     */
    private function crearCuotasAdministracion(Propiedad $propiedad, $unidades): void
    {
        $this->command->info('📋 Creando cuotas de administración...');

        // Valor base mínimo (por encima de $340.000)
        $valorBaseMinimo = 350000;
        
        // Obtener coeficientes únicos de todas las unidades
        $coeficientesUnicos = $unidades->pluck('coeficiente')->unique()->sort()->values();
        $coeficienteMin = $coeficientesUnicos->first();

        // Crear cuota ordinaria activa (indefinida) por cada coeficiente único
        $mesActual = Carbon::now()->startOfMonth();
        $cuotasCreadas = 0;
        
        foreach ($coeficientesUnicos as $coeficiente) {
            // Calcular valor basado en coeficiente
            // Fórmula: valor = valorBaseMinimo * (coeficiente / coeficienteMin)
            // Esto asegura que unidades con menor coeficiente paguen menos
            $factor = $coeficiente / $coeficienteMin;
            $valorCuota = round($valorBaseMinimo * $factor, 2);

            // Agregar variación aleatoria pequeña para hacerlo más realista (±5%)
            $variacion = rand(-5, 5) / 100;
            $valorCuota = round($valorCuota * (1 + $variacion), 2);

            CuotaAdministracion::updateOrCreate(
                [
                    'propiedad_id' => $propiedad->id,
                    'coeficiente' => (int) $coeficiente, // Asegurar que sea entero
                    'concepto' => CuotaAdministracion::CONCEPTO_CUOTA_ORDINARIA,
                ],
                [
                    'valor' => $valorCuota,
                    'mes_desde' => $mesActual->copy()->subMonths(6), // Desde hace 6 meses
                    'mes_hasta' => null, // Indefinida
                    'activo' => true,
                ]
            );
            
            $cuotasCreadas++;
        }

        $this->command->info('   ✓ ' . $cuotasCreadas . ' cuotas de administración creadas (una por coeficiente único)');
    }

    /**
     * Crear carteras para todas las unidades
     */
    private function crearCarteras(Propiedad $propiedad, $unidades): void
    {
        $this->command->info('💼 Creando carteras...');

        foreach ($unidades as $unidad) {
            Cartera::updateOrCreate(
                [
                    'copropiedad_id' => $propiedad->id,
                    'unidad_id' => $unidad->id,
                ],
                [
                    'saldo_total' => 0,
                    'saldo_mora' => 0,
                    'saldo_corriente' => 0,
                    'ultima_actualizacion' => now(),
                    'activo' => true,
                ]
            );
        }

        $this->command->info('   ✓ ' . $unidades->count() . ' carteras creadas');
    }

    /**
     * Crear cuentas de cobro (2 por unidad)
     */
    private function crearCuentasCobro(Propiedad $propiedad, $unidades): array
    {
        $this->command->info('📄 Creando cuentas de cobro...');

        $cuentasCobro = [];
        $mesActual = Carbon::now();
        
        foreach ($unidades as $unidad) {
            // Primera cuenta: mes anterior
            $periodo1 = $mesActual->copy()->subMonth()->format('Y-m');
            $fechaEmision1 = $mesActual->copy()->subMonth()->startOfMonth()->addDays(5);
            $fechaVencimiento1 = $fechaEmision1->copy()->addDays(15);

            $cuenta1 = CuentaCobro::updateOrCreate(
                [
                    'copropiedad_id' => $propiedad->id,
                    'unidad_id' => $unidad->id,
                    'periodo' => $periodo1,
                ],
                [
                    'fecha_emision' => $fechaEmision1,
                    'fecha_vencimiento' => $fechaVencimiento1,
                    'valor_cuotas' => 0, // Se calculará con los detalles
                    'valor_intereses' => 0,
                    'valor_descuentos' => 0,
                    'valor_recargos' => 0,
                    'valor_total' => 0, // Se calculará con los detalles
                    'estado' => $fechaVencimiento1->isPast() ? 'vencida' : 'pendiente',
                    'observaciones' => null,
                ]
            );

            $cuentasCobro[] = $cuenta1;

            // Segunda cuenta: mes actual
            $periodo2 = $mesActual->format('Y-m');
            $fechaEmision2 = $mesActual->copy()->startOfMonth()->addDays(5);
            $fechaVencimiento2 = $fechaEmision2->copy()->addDays(15);

            $cuenta2 = CuentaCobro::updateOrCreate(
                [
                    'copropiedad_id' => $propiedad->id,
                    'unidad_id' => $unidad->id,
                    'periodo' => $periodo2,
                ],
                [
                    'fecha_emision' => $fechaEmision2,
                    'fecha_vencimiento' => $fechaVencimiento2,
                    'valor_cuotas' => 0,
                    'valor_intereses' => 0,
                    'valor_descuentos' => 0,
                    'valor_recargos' => 0,
                    'valor_total' => 0,
                    'estado' => 'pendiente',
                    'observaciones' => null,
                ]
            );

            $cuentasCobro[] = $cuenta2;
        }

        $this->command->info('   ✓ ' . count($cuentasCobro) . ' cuentas de cobro creadas');
        return $cuentasCobro;
    }

    /**
     * Crear detalles de cuentas de cobro
     */
    private function crearDetallesCuentasCobro(array $cuentasCobro): void
    {
        $this->command->info('🧾 Creando detalles de cuentas de cobro...');

        $totalDetalles = 0;

        foreach ($cuentasCobro as $cuentaCobro) {
            // Obtener la unidad y su cuota de administración
            $unidad = $cuentaCobro->unidad;
            $cuotaAdmin = CuotaAdministracion::where('propiedad_id', $cuentaCobro->copropiedad_id)
                ->where('coeficiente', $unidad->coeficiente)
                ->where('concepto', CuotaAdministracion::CONCEPTO_CUOTA_ORDINARIA)
                ->where('activo', true)
                ->first();

            if (!$cuotaAdmin) {
                $this->command->warn("   ⚠ No se encontró cuota de administración para unidad {$unidad->id}");
                continue;
            }

            // Crear detalle de cuota ordinaria
            $detalle = CuentaCobroDetalle::updateOrCreate(
                [
                    'cuenta_cobro_id' => $cuentaCobro->id,
                    'concepto' => 'Cuota de Administración',
                    'cuota_administracion_id' => $cuotaAdmin->id,
                ],
                [
                    'valor' => $cuotaAdmin->valor,
                ]
            );

            $totalDetalles++;

            // Actualizar valores de la cuenta de cobro
            $valorCuotas = $detalle->valor;
            $valorTotal = $valorCuotas; // Sin intereses, descuentos ni recargos por ahora

            $cuentaCobro->update([
                'valor_cuotas' => $valorCuotas,
                'valor_total' => $valorTotal,
            ]);
        }

        $this->command->info('   ✓ ' . $totalDetalles . ' detalles de cuentas de cobro creados');
    }

    /**
     * Crear recaudos asegurando que solo 5 unidades queden en mora
     * Los recaudos se aplican primero a las cuentas más antiguas (FIFO)
     */
    private function crearRecaudos(Propiedad $propiedad, array $cuentasCobro, ?User $adminUser): void
    {
        $this->command->info('💰 Creando recaudos...');

        if (!$adminUser) {
            $this->command->warn('   ⚠ No se encontró usuario administrador para registrar recaudos');
            return;
        }

        // Agrupar cuentas por unidad y ordenarlas por fecha de vencimiento (más antiguas primero)
        $cuentasPorUnidad = [];
        foreach ($cuentasCobro as $cuenta) {
            $cuentasPorUnidad[$cuenta->unidad_id][] = $cuenta;
        }

        // Ordenar cuentas de cada unidad por fecha de vencimiento (más antiguas primero)
        foreach ($cuentasPorUnidad as $unidadId => $cuentas) {
            usort($cuentasPorUnidad[$unidadId], function($a, $b) {
                $fechaA = Carbon::parse($a->fecha_vencimiento);
                $fechaB = Carbon::parse($b->fecha_vencimiento);
                return $fechaA->lt($fechaB) ? -1 : ($fechaA->gt($fechaB) ? 1 : 0);
            });
        }

        // Seleccionar 5 unidades que quedarán en mora
        $unidadesIds = array_keys($cuentasPorUnidad);

        // Identificar la unidad de la residente Diana Zamudio (creada en DemoSeeder)
        $unidadDianaId = null;
        $userDiana = User::where('email', 'diana.zamudio@domoph.pro')->first();
        if ($userDiana) {
            $residenteDiana = Residente::where('user_id', $userDiana->id)
                ->where('es_principal', true)
                ->first();

            if ($residenteDiana) {
                $unidadDianaId = $residenteDiana->unidad_id;
            }
        }

        $unidadesEnMora = collect($unidadesIds)->random(min(5, count($unidadesIds)))->toArray();

        // Asegurar que la unidad de Diana Zamudio siempre quede en mora
        if ($unidadDianaId !== null && in_array($unidadDianaId, $unidadesIds, true)) {
            if (!in_array($unidadDianaId, $unidadesEnMora, true)) {
                if (count($unidadesEnMora) >= 5) {
                    // Reemplazar la última unidad seleccionada por la de Diana
                    $unidadesEnMora[count($unidadesEnMora) - 1] = $unidadDianaId;
                } else {
                    // Agregarla a la lista
                    $unidadesEnMora[] = $unidadDianaId;
                }

                // Normalizar eliminando duplicados
                $unidadesEnMora = array_values(array_unique($unidadesEnMora));
            }
        }
        $unidadesSinMora = array_diff($unidadesIds, $unidadesEnMora);

        $this->command->info('   📌 Unidades que quedarán en mora: ' . count($unidadesEnMora));
        $this->command->info('   ✅ Unidades sin mora: ' . count($unidadesSinMora));

        $totalRecaudos = 0;
        $numeroRecaudo = 1;
        $mediosPago = ['efectivo', 'transferencia', 'consignacion', 'tarjeta', 'pse'];

        // Procesar unidades SIN mora: pagar todas o casi todas sus cuentas (aplicando FIFO)
        foreach ($unidadesSinMora as $unidadId) {
            $cuentasUnidad = $cuentasPorUnidad[$unidadId];
            
            // Calcular el monto total a pagar para esta unidad
            $totalCuentas = collect($cuentasUnidad)->sum('valor_total');
            
            // 90% de probabilidad de pagar completamente, 10% de pagar parcialmente
            $pagarCompleto = rand(1, 100) <= 90;
            
            if ($pagarCompleto) {
                $montoTotalPago = $totalCuentas;
            } else {
                // Pago parcial entre 80% y 95% del total
                $porcentaje = rand(80, 95) / 100;
                $montoTotalPago = round($totalCuentas * $porcentaje, 2);
            }

            $medioPago = $mediosPago[array_rand($mediosPago)];
            $montoRestante = $montoTotalPago;

            // Aplicar el pago a las cuentas más antiguas primero (FIFO)
            foreach ($cuentasUnidad as $cuentaCobro) {
                if ($montoRestante <= 0.01) {
                    break; // Ya no hay más dinero para aplicar
                }

                $saldoPendiente = $cuentaCobro->valor_total;
                
                // Calcular cuánto se puede pagar de esta cuenta
                $valorPagado = min($saldoPendiente, $montoRestante);
                $montoRestante -= $valorPagado;

                $tipoPago = ($valorPagado >= $saldoPendiente - 0.01) ? 'total' : 'parcial';

                // Fecha de pago: antes o en la fecha de vencimiento (para evitar mora)
                $fechaVencimiento = Carbon::parse($cuentaCobro->fecha_vencimiento);
                if ($fechaVencimiento->isPast()) {
                    // Si ya venció, pagar antes de la fecha de vencimiento (simular pago tardío pero no en mora)
                    $fechaPago = $fechaVencimiento->copy()->subDays(rand(1, 5));
                } else {
                    // Si no ha vencido, pagar entre emisión y vencimiento
                    $fechaEmision = Carbon::parse($cuentaCobro->fecha_emision);
                    $diasEntre = $fechaEmision->diffInDays($fechaVencimiento);
                    $fechaPago = $fechaEmision->copy()->addDays(rand(0, max(1, $diasEntre - 1)));
                }

                $numeroRecaudoStr = 'REC-' . str_pad($numeroRecaudo++, 6, '0', STR_PAD_LEFT);

                $recaudo = Recaudo::updateOrCreate(
                    [
                        'numero_recaudo' => $numeroRecaudoStr,
                    ],
                    [
                        'copropiedad_id' => $propiedad->id,
                        'unidad_id' => $cuentaCobro->unidad_id,
                        'cuenta_cobro_id' => $cuentaCobro->id,
                        'fecha_pago' => $fechaPago,
                        'tipo_pago' => $tipoPago,
                        'medio_pago' => $medioPago,
                        'referencia_pago' => 'REF-' . rand(100000, 999999),
                        'descripcion' => "Pago de cuenta de cobro período {$cuentaCobro->periodo}",
                        'valor_pagado' => round($valorPagado, 2),
                        'estado' => 'aplicado',
                        'registrado_por' => $adminUser->id,
                        'activo' => true,
                    ]
                );

                // Crear detalle del recaudo
                $detalleCuenta = $cuentaCobro->detalles->first();
                if ($detalleCuenta) {
                    RecaudoDetalle::updateOrCreate(
                        [
                            'recaudo_id' => $recaudo->id,
                            'cuenta_cobro_detalle_id' => $detalleCuenta->id,
                        ],
                        [
                            'concepto' => $detalleCuenta->concepto,
                            'valor_aplicado' => round($valorPagado, 2),
                        ]
                    );
                }

                // Actualizar estado de la cuenta de cobro
                $saldoPendienteFinal = $saldoPendiente - $valorPagado;
                if ($saldoPendienteFinal <= 0.01) {
                    $cuentaCobro->update(['estado' => 'pagada']);
                } else {
                    $cuentaCobro->update(['estado' => 'pendiente']);
                }

                $totalRecaudos++;
            }
        }

        // Procesar unidades EN MORA: pagar pocas o ninguna cuenta, o pagar parcialmente (aplicando FIFO)
        foreach ($unidadesEnMora as $unidadId) {
            $cuentasUnidad = $cuentasPorUnidad[$unidadId];
            
            // Calcular el monto total de las cuentas
            $totalCuentas = collect($cuentasUnidad)->sum('valor_total');
            
            // Solo pagar el 30% del total de las cuentas (aplicado a las más antiguas)
            $montoTotalPago = round($totalCuentas * 0.3, 2);
            $montoRestante = $montoTotalPago;
            
            $medioPago = $mediosPago[array_rand($mediosPago)];

            // Aplicar el pago a las cuentas más antiguas primero (FIFO)
            foreach ($cuentasUnidad as $cuentaCobro) {
                if ($montoRestante <= 0.01) {
                    break; // Ya no hay más dinero para aplicar
                }

                $saldoPendiente = $cuentaCobro->valor_total;
                
                // Calcular cuánto se puede pagar de esta cuenta
                $valorPagado = min($saldoPendiente, $montoRestante);
                $montoRestante -= $valorPagado;

                $tipoPago = 'parcial'; // En mora siempre será parcial

                // Fecha de pago: después del vencimiento (simular mora)
                $fechaVencimiento = Carbon::parse($cuentaCobro->fecha_vencimiento);
                $fechaPago = $fechaVencimiento->copy()->addDays(rand(1, 30));

                $numeroRecaudoStr = 'REC-' . str_pad($numeroRecaudo++, 6, '0', STR_PAD_LEFT);

                $recaudo = Recaudo::updateOrCreate(
                    [
                        'numero_recaudo' => $numeroRecaudoStr,
                    ],
                    [
                        'copropiedad_id' => $propiedad->id,
                        'unidad_id' => $cuentaCobro->unidad_id,
                        'cuenta_cobro_id' => $cuentaCobro->id,
                        'fecha_pago' => $fechaPago,
                        'tipo_pago' => $tipoPago,
                        'medio_pago' => $medioPago,
                        'referencia_pago' => 'REF-' . rand(100000, 999999),
                        'descripcion' => "Pago parcial de cuenta de cobro período {$cuentaCobro->periodo}",
                        'valor_pagado' => round($valorPagado, 2),
                        'estado' => 'aplicado',
                        'registrado_por' => $adminUser->id,
                        'activo' => true,
                    ]
                );

                // Crear detalle del recaudo
                $detalleCuenta = $cuentaCobro->detalles->first();
                if ($detalleCuenta) {
                    RecaudoDetalle::updateOrCreate(
                        [
                            'recaudo_id' => $recaudo->id,
                            'cuenta_cobro_detalle_id' => $detalleCuenta->id,
                        ],
                        [
                            'concepto' => $detalleCuenta->concepto,
                            'valor_aplicado' => round($valorPagado, 2),
                        ]
                    );
                }

                // Actualizar estado: siempre quedará como vencida o pendiente con saldo
                $saldoPendienteFinal = $saldoPendiente - $valorPagado;
                if ($fechaVencimiento->isPast()) {
                    $cuentaCobro->update(['estado' => 'vencida']);
                } else {
                    $cuentaCobro->update(['estado' => 'pendiente']);
                }

                $totalRecaudos++;
            }
        }

        $this->command->info('   ✓ ' . $totalRecaudos . ' recaudos creados');
        $this->command->info('   ⚠ ' . count($unidadesEnMora) . ' unidades quedarán en mora');
    }

    /**
     * Actualizar carteras con saldos reales
     */
    private function actualizarCarteras(Propiedad $propiedad, $unidades): void
    {
        $this->command->info('🔄 Actualizando carteras con saldos reales...');

        foreach ($unidades as $unidad) {
            // Obtener todas las cuentas de cobro de la unidad
            $cuentasCobro = CuentaCobro::where('copropiedad_id', $propiedad->id)
                ->where('unidad_id', $unidad->id)
                ->where('estado', '!=', 'anulada')
                ->get();

            $saldoTotal = 0;
            $saldoMora = 0;
            $saldoCorriente = 0;

            foreach ($cuentasCobro as $cuenta) {
                $saldoPendiente = $cuenta->calcularSaldoPendiente();
                $saldoTotal += $saldoPendiente;

                // Si está vencida, va a mora, si no, a corriente
                if ($cuenta->estado === 'vencida' || 
                    ($cuenta->fecha_vencimiento && Carbon::parse($cuenta->fecha_vencimiento)->isPast() && $saldoPendiente > 0)) {
                    $saldoMora += $saldoPendiente;
                } else {
                    $saldoCorriente += $saldoPendiente;
                }
            }

            // Actualizar cartera
            Cartera::where('copropiedad_id', $propiedad->id)
                ->where('unidad_id', $unidad->id)
                ->update([
                    'saldo_total' => round($saldoTotal, 2),
                    'saldo_mora' => round($saldoMora, 2),
                    'saldo_corriente' => round($saldoCorriente, 2),
                    'ultima_actualizacion' => now(),
                ]);
        }

        $this->command->info('   ✓ Carteras actualizadas con saldos reales');
    }
}
