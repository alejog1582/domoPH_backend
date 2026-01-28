<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Propiedad;
use App\Models\Unidad;
use App\Models\CuotaAdministracion;
use App\Models\CuentaCobro;
use App\Models\CuentaCobroDetalle;
use App\Models\Cartera;
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

                        // Calcular fecha de vencimiento (15 días después de la emisión)
                        $fechaVencimiento = $fechaEmision->copy()->addDays(15);

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

                        // Actualizar o crear cartera
                        $cartera = Cartera::where('copropiedad_id', $propiedad->id)
                            ->where('unidad_id', $unidad->id)
                            ->first();

                        if ($cartera) {
                            // Actualizar cartera sumando el nuevo saldo
                            $cartera->update([
                                'saldo_corriente' => $cartera->saldo_corriente + $valorCuotas,
                                'saldo_total' => $cartera->saldo_total + $valorCuotas,
                                'ultima_actualizacion' => Carbon::now(),
                            ]);
                        } else {
                            // Crear nueva cartera si no existe
                            $cartera = Cartera::create([
                                'copropiedad_id' => $propiedad->id,
                                'unidad_id' => $unidad->id,
                                'saldo_corriente' => $valorCuotas,
                                'saldo_mora' => 0,
                                'saldo_total' => $valorCuotas,
                                'ultima_actualizacion' => Carbon::now(),
                                'activo' => true,
                            ]);
                        }

                        $totalCuentasCreadas++;
                        $this->info("  ✓ Unidad {$unidad->numero}: Cuenta de cobro creada por valor de $" . number_format($valorCuotas, 2, ',', '.'));

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
