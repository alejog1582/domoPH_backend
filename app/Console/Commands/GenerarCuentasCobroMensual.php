<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Propiedad;
use App\Models\Unidad;
use App\Models\CuotaAdministracion;
use App\Models\CuentaCobro;
use App\Models\CuentaCobroDetalle;
use App\Models\Cartera;
use App\Models\Recaudo;
use App\Models\ConfiguracionPropiedad;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerarCuentasCobroMensual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cartera:generar-cuentas-mensual {--mes= : Mes a liquidar en formato YYYY-MM (opcional, por defecto mes actual)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera las cuentas de cobro mensuales para todas las propiedades activas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Obtener el mes a liquidar
        $mesLiquidar = $this->option('mes') ? Carbon::createFromFormat('Y-m', $this->option('mes')) : Carbon::now();
        $periodo = $mesLiquidar->format('Y-m');
        $fechaEmision = $mesLiquidar->copy()->startOfMonth();
        
        $this->info("Iniciando generación de cuentas de cobro para el período: {$periodo}");

        // Obtener todas las propiedades activas
        $propiedades = Propiedad::where('estado', 'activa')->get();

        if ($propiedades->isEmpty()) {
            $this->warn('No se encontraron propiedades activas.');
            return Command::FAILURE;
        }

        $this->info("Se encontraron {$propiedades->count()} propiedad(es) activa(s).");

        $totalCuentasCreadas = 0;
        $totalCuentasActualizadas = 0;
        $totalErrores = 0;

        DB::beginTransaction();

        try {
            foreach ($propiedades as $propiedad) {
                $this->info("\nProcesando propiedad: {$propiedad->nombre} (ID: {$propiedad->id})");

                // Obtener configuración de día de vencimiento para esta propiedad
                $configDiaVencimiento = ConfiguracionPropiedad::where('propiedad_id', $propiedad->id)
                    ->where('clave', 'dia_vencimiento_cuenta_cobro')
                    ->first();
                
                // Día de vencimiento por defecto: 10 si no existe configuración
                $diaVencimiento = $configDiaVencimiento ? (int) $configDiaVencimiento->valor : 10;
                
                // Validar que el día esté en rango válido (1-31)
                $diaVencimiento = max(1, min(31, $diaVencimiento));

                // Obtener todas las unidades de la propiedad
                $unidades = Unidad::where('propiedad_id', $propiedad->id)->get();

                if ($unidades->isEmpty()) {
                    $this->warn("  No se encontraron unidades para la propiedad {$propiedad->nombre}.");
                    continue;
                }

                $this->info("  Procesando {$unidades->count()} unidad(es)...");

                foreach ($unidades as $unidad) {
                    try {
                        // Verificar si ya existe una cuenta de cobro para este período y unidad
                        $cuentaCobroExistente = CuentaCobro::where('copropiedad_id', $propiedad->id)
                            ->where('unidad_id', $unidad->id)
                            ->where('periodo', $periodo)
                            ->first();

                        if ($cuentaCobroExistente) {
                            $this->warn("  Unidad {$unidad->numero}: Ya existe cuenta de cobro para el período {$periodo}. Omitiendo...");
                            continue;
                        }

                        $valorCuotas = 0;
                        $detalles = [];

                        // 1. Buscar cuota ordinaria con el coeficiente de la unidad
                        // Las cuotas ordinarias siempre se aplican (no tienen validación de fechas)
                        $cuotaOrdinaria = CuotaAdministracion::where('propiedad_id', $propiedad->id)
                            ->where('concepto', CuotaAdministracion::CONCEPTO_CUOTA_ORDINARIA)
                            ->where('activo', true)
                            ->where('coeficiente', $unidad->coeficiente)
                            ->first();

                        if ($cuotaOrdinaria) {
                            // El valor en la tabla ya está calculado para el coeficiente específico de esta cuota
                            // No se debe multiplicar por el coeficiente de la unidad
                            $valorCuotaOrdinaria = (float) $cuotaOrdinaria->valor;
                            $valorCuotas += $valorCuotaOrdinaria;
                            $detalles[] = [
                                'concepto' => 'Cuota ordinaria',
                                'cuota_administracion_id' => $cuotaOrdinaria->id,
                                'valor' => $valorCuotaOrdinaria,
                            ];
                        }

                        // 2. Buscar cuotas extraordinarias que apliquen
                        $cuotasExtraordinarias = CuotaAdministracion::where('propiedad_id', $propiedad->id)
                            ->where('concepto', CuotaAdministracion::CONCEPTO_CUOTA_EXTRAORDINARIA)
                            ->where('activo', true)
                            ->get();

                        foreach ($cuotasExtraordinarias as $cuotaExtra) {
                            // Validar que aplica para esta unidad
                            $aplica = false;
                            
                            if ($cuotaExtra->coeficiente === null) {
                                // Si coeficiente es null, aplica a todas las unidades
                                $aplica = true;
                            } elseif ($cuotaExtra->coeficiente == $unidad->coeficiente) {
                                // Si tiene coeficiente, solo aplica a unidades con ese coeficiente
                                $aplica = true;
                            }

                            // Validar que aplica en el mes actual
                            if ($aplica && $cuotaExtra->aplicaEnFecha($fechaEmision)) {
                                // Si la cuota extraordinaria tiene coeficiente, el valor ya está calculado para ese coeficiente
                                // Si no tiene coeficiente (null), el valor es fijo para todas las unidades
                                $valorCuotaExtra = (float) $cuotaExtra->valor;
                                $valorCuotas += $valorCuotaExtra;
                                $detalles[] = [
                                    'concepto' => 'Cuota extraordinaria',
                                    'cuota_administracion_id' => $cuotaExtra->id,
                                    'valor' => $valorCuotaExtra,
                                ];
                            }
                        }

                        // Si no hay cuotas que aplicar, continuar con la siguiente unidad
                        if ($valorCuotas == 0 && empty($detalles)) {
                            $this->warn("  Unidad {$unidad->numero}: No se encontraron cuotas aplicables para el período {$periodo}.");
                            continue;
                        }

                        // Buscar recaudo de anticipo para esta unidad (tipo_pago = 'anticipo' y cuenta_cobro_id = null)
                        $recaudoAnticipo = Recaudo::where('copropiedad_id', $propiedad->id)
                            ->where('unidad_id', $unidad->id)
                            ->where('tipo_pago', 'anticipo')
                            ->whereNull('cuenta_cobro_id')
                            ->where('activo', true)
                            ->where('estado', '!=', 'anulado')
                            ->first();

                        // Calcular fecha de vencimiento usando el día configurado para la propiedad
                        // El día de vencimiento se establece en el mes correspondiente al período
                        $fechaVencimiento = $mesLiquidar->copy()->day($diaVencimiento);
                        
                        // Si el día configurado no existe en el mes (ej: día 31 en febrero),
                        // usar el último día del mes
                        if (!$fechaVencimiento->isValid()) {
                            $fechaVencimiento = $mesLiquidar->copy()->endOfMonth();
                        }

                        // Crear cuenta de cobro
                        $cuentaCobro = CuentaCobro::create([
                            'copropiedad_id' => $propiedad->id,
                            'unidad_id' => $unidad->id,
                            'periodo' => $periodo,
                            'fecha_emision' => $fechaEmision->toDateString(),
                            'fecha_vencimiento' => $fechaVencimiento->toDateString(),
                            'valor_cuotas' => $valorCuotas,
                            'valor_intereses' => 0,
                            'valor_descuentos' => 0,
                            'valor_recargos' => 0,
                            'valor_total' => $valorCuotas,
                            'estado' => 'pendiente',
                            'observaciones' => "Cuenta de cobro generada automáticamente para el período {$periodo}.",
                        ]);

                        // Crear detalles de cuenta de cobro
                        foreach ($detalles as $detalle) {
                            CuentaCobroDetalle::create([
                                'cuenta_cobro_id' => $cuentaCobro->id,
                                'concepto' => $detalle['concepto'],
                                'cuota_administracion_id' => $detalle['cuota_administracion_id'],
                                'valor' => $detalle['valor'],
                            ]);
                        }

                        // Procesar anticipo si existe
                        $valorAplicadoAnticipo = 0;
                        $saldoCartera = 0;

                        if ($recaudoAnticipo) {
                            $valorAnticipo = (float) $recaudoAnticipo->valor_pagado;
                            
                            if ($valorAnticipo >= $valorCuotas) {
                                // El anticipo es mayor o igual a la cuenta de cobro
                                // Asignar el recaudo a la cuenta de cobro
                                $recaudoAnticipo->update([
                                    'cuenta_cobro_id' => $cuentaCobro->id,
                                    'descripcion' => $recaudoAnticipo->descripcion . ' - Aplicado a cuenta ' . $periodo,
                                ]);

                                // Si el anticipo es mayor, crear un nuevo recaudo con el saldo restante
                                $saldoRestante = $valorAnticipo - $valorCuotas;
                                
                                if ($saldoRestante > 0) {
                                    // Generar número de recaudo único para el saldo
                                    $numeroRecaudoSaldo = $recaudoAnticipo->numero_recaudo . '-SALDO';
                                    $contador = 1;
                                    while (Recaudo::where('numero_recaudo', $numeroRecaudoSaldo)->exists()) {
                                        $numeroRecaudoSaldo = $recaudoAnticipo->numero_recaudo . '-SALDO-' . $contador;
                                        $contador++;
                                    }

                                    // Crear nuevo recaudo de anticipo con el saldo restante
                                    Recaudo::create([
                                        'copropiedad_id' => $propiedad->id,
                                        'unidad_id' => $unidad->id,
                                        'cuenta_cobro_id' => null,
                                        'numero_recaudo' => $numeroRecaudoSaldo,
                                        'fecha_pago' => $recaudoAnticipo->fecha_pago,
                                        'tipo_pago' => 'anticipo',
                                        'medio_pago' => $recaudoAnticipo->medio_pago,
                                        'referencia_pago' => $recaudoAnticipo->referencia_pago,
                                        'descripcion' => 'Saldo restante de anticipo',
                                        'valor_pagado' => $saldoRestante,
                                        'estado' => 'aplicado',
                                        'registrado_por' => $recaudoAnticipo->registrado_por,
                                        'activo' => true,
                                    ]);
                                }

                                // La cuenta de cobro queda pagada
                                $cuentaCobro->update(['estado' => 'pagada']);
                                $valorAplicadoAnticipo = $valorCuotas;
                                
                                // En la cartera no se suma nada porque el anticipo ya estaba registrado
                                $saldoCartera = 0;
                                
                                $this->info("  ✓ Unidad {$unidad->numero}: Cuenta de cobro creada y pagada con anticipo. Valor: $" . number_format($valorCuotas, 2, ',', '.') . 
                                    ($saldoRestante > 0 ? " (Saldo anticipo restante: $" . number_format($saldoRestante, 2, ',', '.') . ")" : ""));
                                
                            } else {
                                // El anticipo es menor a la cuenta de cobro
                                // Asignar el recaudo a la cuenta de cobro
                                $recaudoAnticipo->update([
                                    'cuenta_cobro_id' => $cuentaCobro->id,
                                    'descripcion' => $recaudoAnticipo->descripcion . ' - Aplicado a cuenta ' . $periodo,
                                ]);

                                // La cuenta de cobro queda pendiente con el saldo restante
                                $valorAplicadoAnticipo = $valorAnticipo;
                                $saldoCartera = $valorCuotas - $valorAnticipo;
                                
                                $this->info("  ✓ Unidad {$unidad->numero}: Cuenta de cobro creada. Valor: $" . number_format($valorCuotas, 2, ',', '.') . 
                                    " (Anticipo aplicado: $" . number_format($valorAnticipo, 2, ',', '.') . 
                                    ", Saldo pendiente: $" . number_format($saldoCartera, 2, ',', '.') . ")");
                            }
                        } else {
                            // No hay anticipo, comportamiento normal
                            $saldoCartera = $valorCuotas;
                            $this->info("  ✓ Unidad {$unidad->numero}: Cuenta de cobro creada por valor de $" . number_format($valorCuotas, 2, ',', '.'));
                        }

                        // Actualizar o crear cartera
                        $cartera = Cartera::where('copropiedad_id', $propiedad->id)
                            ->where('unidad_id', $unidad->id)
                            ->first();

                        if ($cartera) {
                            // Actualizar cartera sumando solo la diferencia (si hay)
                            if ($saldoCartera > 0) {
                                $cartera->update([
                                    'saldo_corriente' => $cartera->saldo_corriente + $saldoCartera,
                                    'saldo_total' => $cartera->saldo_total + $saldoCartera,
                                    'ultima_actualizacion' => Carbon::now(),
                                ]);
                            } else {
                                // Solo actualizar la fecha si no hay saldo que agregar
                                $cartera->update([
                                    'ultima_actualizacion' => Carbon::now(),
                                ]);
                            }
                        } else {
                            // Crear nueva cartera si no existe (solo si hay saldo)
                            if ($saldoCartera > 0) {
                                $cartera = Cartera::create([
                                    'copropiedad_id' => $propiedad->id,
                                    'unidad_id' => $unidad->id,
                                    'saldo_corriente' => $saldoCartera,
                                    'saldo_mora' => 0,
                                    'saldo_total' => $saldoCartera,
                                    'ultima_actualizacion' => Carbon::now(),
                                    'activo' => true,
                                ]);
                            }
                        }

                        $totalCuentasCreadas++;

                    } catch (\Exception $e) {
                        $totalErrores++;
                        $this->error("  ✗ Error procesando unidad {$unidad->numero}: " . $e->getMessage());
                        \Log::error("Error al generar cuenta de cobro para unidad {$unidad->id}: " . $e->getMessage());
                    }
                }
            }

            DB::commit();

            $this->info("\n" . str_repeat('=', 60));
            $this->info("Resumen de la generación:");
            $this->info("  - Cuentas de cobro creadas: {$totalCuentasCreadas}");
            $this->info("  - Errores: {$totalErrores}");
            $this->info(str_repeat('=', 60));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\nError general: " . $e->getMessage());
            \Log::error("Error al generar cuentas de cobro mensuales: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
