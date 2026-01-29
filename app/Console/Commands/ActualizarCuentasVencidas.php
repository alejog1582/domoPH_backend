<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CuentaCobro;
use App\Models\Cartera;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActualizarCuentasVencidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cartera:actualizar-cuentas-vencidas {--mes= : Mes a procesar en formato YYYY-MM (opcional, por defecto mes anterior)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza las cuentas de cobro pendientes del mes a estado vencidas y ajusta los saldos de cartera';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Obtener el mes a procesar (por defecto el mes actual, ya que se ejecuta el último día del mes)
        $mesProcesar = $this->option('mes') 
            ? Carbon::createFromFormat('Y-m', $this->option('mes')) 
            : Carbon::now();
        
        $periodo = $mesProcesar->format('Y-m');
        
        $this->info("Iniciando actualización de cuentas vencidas para el período: {$periodo}");

        DB::beginTransaction();

        try {
            // Obtener todas las cuentas de cobro pendientes del mes especificado
            $cuentasPendientes = CuentaCobro::where('periodo', $periodo)
                ->where('estado', 'pendiente')
                ->get();

            if ($cuentasPendientes->isEmpty()) {
                $this->info("No se encontraron cuentas de cobro pendientes para el período {$periodo}.");
                DB::commit();
                return Command::SUCCESS;
            }

            $this->info("Se encontraron {$cuentasPendientes->count()} cuenta(s) de cobro pendiente(s) para actualizar.");

            $cuentasActualizadas = 0;
            $carterasActualizadas = 0;
            $unidadesProcesadas = [];

            // Primero, cambiar el estado de todas las cuentas a vencida
            foreach ($cuentasPendientes as $cuentaCobro) {
                $cuentaCobro->update(['estado' => 'vencida']);
                $cuentasActualizadas++;
            }

            // Agrupar cuentas por unidad para actualizar carteras una sola vez por unidad
            $cuentasPorUnidad = $cuentasPendientes->groupBy(function($cuenta) {
                return $cuenta->copropiedad_id . '-' . $cuenta->unidad_id;
            });

            // Actualizar carteras por unidad
            foreach ($cuentasPorUnidad as $unidadKey => $cuentasUnidad) {
                $primeraCuenta = $cuentasUnidad->first();
                
                // Obtener la cartera de la unidad
                $cartera = Cartera::where('copropiedad_id', $primeraCuenta->copropiedad_id)
                    ->where('unidad_id', $primeraCuenta->unidad_id)
                    ->first();

                if ($cartera) {
                    // Sumar el saldo_corriente a saldo_mora
                    $nuevoSaldoMora = $cartera->saldo_mora + $cartera->saldo_corriente;
                    
                    // Actualizar la cartera: mover saldo_corriente a saldo_mora y dejar saldo_corriente en cero
                    $cartera->update([
                        'saldo_mora' => $nuevoSaldoMora,
                        'saldo_corriente' => 0,
                        'saldo_total' => $nuevoSaldoMora,
                        'ultima_actualizacion' => Carbon::now(),
                    ]);

                    $carterasActualizadas++;
                    $this->info("  ✓ Unidad {$primeraCuenta->unidad_id}: Cartera actualizada. Saldo corriente movido a mora.");
                } else {
                    $this->warn("  ⚠ Unidad {$primeraCuenta->unidad_id}: No se encontró cartera.");
                }
            }

            DB::commit();

            $this->info("\n" . str_repeat('=', 60));
            $this->info("Resumen de la actualización:");
            $this->info("  - Cuentas de cobro actualizadas: {$cuentasActualizadas}");
            $this->info("  - Carteras actualizadas: {$carterasActualizadas}");
            $this->info("  - Período procesado: {$periodo}");
            $this->info(str_repeat('=', 60));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\nError al actualizar cuentas vencidas: " . $e->getMessage());
            \Log::error("Error al actualizar cuentas vencidas: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
