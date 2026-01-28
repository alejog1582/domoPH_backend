<?php

namespace Database\Seeders;

use App\Models\CuentaCobro;
use App\Models\CuentaCobroDetalle;
use App\Models\CuotaAdministracion;
use App\Models\Propiedad;
use App\Models\Unidad;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CuentaCobroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $propiedades = Propiedad::with(['unidades'])->get();

        foreach ($propiedades as $propiedad) {
            // Tomamos el mes actual y el siguiente como ejemplo
            $periodos = [
                Carbon::now()->format('Y-m'),
                Carbon::now()->copy()->addMonth()->format('Y-m'),
            ];

            foreach ($propiedad->unidades as $unidad) {
                foreach ($periodos as $periodo) {
                    // Evitar duplicados
                    if (CuentaCobro::where('copropiedad_id', $propiedad->id)
                        ->where('unidad_id', $unidad->id)
                        ->where('periodo', $periodo)
                        ->exists()) {
                        continue;
                    }

                    $fechaEmision = Carbon::createFromFormat('Y-m', $periodo)->startOfMonth();
                    $fechaVencimiento = $fechaEmision->copy()->addDays(15);

                    // Valores base simulando cuotas ordinarias y extraordinarias
                    $valorCuotaOrdinaria = 150000;
                    $valorCuotaExtra = 0;
                    $intereses = 0;
                    $descuentos = 0;
                    $recargos = 0;

                    // Buscar una cuota de administración ordinaria activa
                    $cuotaOrdinaria = CuotaAdministracion::where('propiedad_id', $propiedad->id)
                        ->activas()
                        ->ordinarias()
                        ->first();

                    if ($cuotaOrdinaria) {
                        $valorCuotaOrdinaria = $cuotaOrdinaria->calcularParaUnidad($unidad);
                    }

                    $valorCuotas = $valorCuotaOrdinaria + $valorCuotaExtra;
                    $valorTotal = $valorCuotas + $intereses + $recargos - $descuentos;

                    $cuenta = CuentaCobro::create([
                        'copropiedad_id' => $propiedad->id,
                        'unidad_id' => $unidad->id,
                        'periodo' => $periodo,
                        'fecha_emision' => $fechaEmision->toDateString(),
                        'fecha_vencimiento' => $fechaVencimiento->toDateString(),
                        'valor_cuotas' => $valorCuotas,
                        'valor_intereses' => $intereses,
                        'valor_descuentos' => $descuentos,
                        'valor_recargos' => $recargos,
                        'valor_total' => $valorTotal,
                        'estado' => 'pendiente',
                        'observaciones' => 'Cuenta de cobro generada automáticamente para pruebas.',
                    ]);

                    // Detalle de cuota ordinaria
                    CuentaCobroDetalle::create([
                        'cuenta_cobro_id' => $cuenta->id,
                        'concepto' => 'Cuota ordinaria',
                        'cuota_administracion_id' => $cuotaOrdinaria?->id,
                        'valor' => $valorCuotaOrdinaria,
                    ]);
                }
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

