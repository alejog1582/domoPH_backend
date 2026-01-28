<?php

namespace Database\Seeders;

use App\Models\Propiedad;
use App\Models\Unidad;
use App\Models\CuentaCobro;
use App\Models\CuentaCobroDetalle;
use App\Models\Recaudo;
use App\Models\RecaudoDetalle;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecaudoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Obtener un usuario para registrar los recaudos
        $usuario = User::first();
        
        if (!$usuario) {
            $this->command->warn('No se encontró ningún usuario. Se requiere al menos un usuario para registrar recaudos.');
            DB::statement('SET_FOREIGN_KEY_CHECKS=1;');
            return;
        }

        $propiedades = Propiedad::where('estado', 'activa')->get();

        if ($propiedades->isEmpty()) {
            $this->command->warn('No se encontraron propiedades activas.');
            DB::statement('SET_FOREIGN_KEY_CHECKS=1;');
            return;
        }

        $this->command->info("Creando recaudos de ejemplo para {$propiedades->count()} propiedad(es)...");

        foreach ($propiedades as $propiedad) {
            $unidades = Unidad::where('propiedad_id', $propiedad->id)->limit(5)->get();

            foreach ($unidades as $index => $unidad) {
                // Obtener una cuenta de cobro pendiente de esta unidad
                $cuentaCobro = CuentaCobro::where('copropiedad_id', $propiedad->id)
                    ->where('unidad_id', $unidad->id)
                    ->where('estado', 'pendiente')
                    ->first();

                if (!$cuentaCobro) {
                    continue;
                }

                $numeroRecaudo = 'REC-' . str_pad($propiedad->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($unidad->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);

                // Ejemplo 1: Pago parcial (50% del valor total)
                if ($index % 3 == 0) {
                    $valorParcial = $cuentaCobro->valor_total * 0.5;
                    $recaudo = Recaudo::create([
                        'copropiedad_id' => $propiedad->id,
                        'unidad_id' => $unidad->id,
                        'cuenta_cobro_id' => $cuentaCobro->id,
                        'numero_recaudo' => $numeroRecaudo . '-P',
                        'fecha_pago' => Carbon::now()->subDays(rand(1, 10)),
                        'tipo_pago' => 'parcial',
                        'medio_pago' => fake()->randomElement(['transferencia', 'consignacion', 'efectivo']),
                        'referencia_pago' => 'REF-' . rand(100000, 999999),
                        'descripcion' => 'Pago parcial de cuenta de cobro',
                        'valor_pagado' => $valorParcial,
                        'estado' => 'aplicado',
                        'registrado_por' => $usuario->id,
                        'activo' => true,
                    ]);

                    // Crear detalles del recaudo distribuyendo el pago entre los conceptos
                    $detallesCuenta = $cuentaCobro->detalles;
                    foreach ($detallesCuenta as $detalleCuenta) {
                        $valorAplicado = ($detalleCuenta->valor / $cuentaCobro->valor_total) * $valorParcial;
                        RecaudoDetalle::create([
                            'recaudo_id' => $recaudo->id,
                            'cuenta_cobro_detalle_id' => $detalleCuenta->id,
                            'concepto' => $detalleCuenta->concepto,
                            'valor_aplicado' => $valorAplicado,
                        ]);
                    }

                    // Actualizar estado de la cuenta de cobro si el saldo pendiente es 0
                    $saldoPendiente = $cuentaCobro->calcularSaldoPendiente();
                    if ($saldoPendiente <= 0) {
                        $cuentaCobro->update(['estado' => 'pagada']);
                    }

                    // Actualizar cartera
                    $this->actualizarCartera($propiedad->id, $unidad->id, $valorParcial);
                }

                // Ejemplo 2: Pago total
                elseif ($index % 3 == 1) {
                    $recaudo = Recaudo::create([
                        'copropiedad_id' => $propiedad->id,
                        'unidad_id' => $unidad->id,
                        'cuenta_cobro_id' => $cuentaCobro->id,
                        'numero_recaudo' => $numeroRecaudo . '-T',
                        'fecha_pago' => Carbon::now()->subDays(rand(1, 5)),
                        'tipo_pago' => 'total',
                        'medio_pago' => fake()->randomElement(['transferencia', 'tarjeta', 'pse']),
                        'referencia_pago' => 'REF-' . rand(100000, 999999),
                        'descripcion' => 'Pago total de cuenta de cobro',
                        'valor_pagado' => $cuentaCobro->valor_total,
                        'estado' => 'aplicado',
                        'registrado_por' => $usuario->id,
                        'activo' => true,
                    ]);

                    // Crear detalles del recaudo
                    foreach ($cuentaCobro->detalles as $detalleCuenta) {
                        RecaudoDetalle::create([
                            'recaudo_id' => $recaudo->id,
                            'cuenta_cobro_detalle_id' => $detalleCuenta->id,
                            'concepto' => $detalleCuenta->concepto,
                            'valor_aplicado' => $detalleCuenta->valor,
                        ]);
                    }

                    // Actualizar estado de la cuenta de cobro a pagada
                    $cuentaCobro->update(['estado' => 'pagada']);

                    // Actualizar cartera
                    $this->actualizarCartera($propiedad->id, $unidad->id, $cuentaCobro->valor_total);
                }

                // Ejemplo 3: Abono sin cuenta de cobro (abono a saldo general)
                else {
                    $valorAbono = rand(50000, 200000);
                    $recaudo = Recaudo::create([
                        'copropiedad_id' => $propiedad->id,
                        'unidad_id' => $unidad->id,
                        'cuenta_cobro_id' => null, // Sin cuenta específica
                        'numero_recaudo' => $numeroRecaudo . '-A',
                        'fecha_pago' => Carbon::now()->subDays(rand(1, 15)),
                        'tipo_pago' => 'anticipo',
                        'medio_pago' => fake()->randomElement(['efectivo', 'transferencia']),
                        'referencia_pago' => 'ABO-' . rand(100000, 999999),
                        'descripcion' => 'Abono a saldo general de cartera',
                        'valor_pagado' => $valorAbono,
                        'estado' => 'aplicado',
                        'registrado_por' => $usuario->id,
                        'activo' => true,
                    ]);

                    // Crear detalle sin cuenta_cobro_detalle_id
                    RecaudoDetalle::create([
                        'recaudo_id' => $recaudo->id,
                        'cuenta_cobro_detalle_id' => null,
                        'concepto' => 'Abono a saldo general',
                        'valor_aplicado' => $valorAbono,
                    ]);

                    // Actualizar cartera (reduce el saldo)
                    $this->actualizarCartera($propiedad->id, $unidad->id, -$valorAbono);
                }
            }
        }

        DB::statement('SET_FOREIGN_KEY_CHECKS=1;');
        $this->command->info("\nSeeder de recaudos completado exitosamente.");
    }

    /**
     * Actualizar la cartera de una unidad
     *
     * @param int $propiedadId
     * @param int $unidadId
     * @param float $valor Valor positivo aumenta saldo, negativo lo reduce
     * @return void
     */
    private function actualizarCartera($propiedadId, $unidadId, $valor)
    {
        $cartera = \App\Models\Cartera::where('copropiedad_id', $propiedadId)
            ->where('unidad_id', $unidadId)
            ->first();

        if ($cartera) {
            // Si el valor es negativo (abono), reduce el saldo
            // Si es positivo (pago de cuenta), puede reducir saldo corriente o mora según corresponda
            if ($valor < 0) {
                // Abono reduce el saldo total y corriente
                $cartera->update([
                    'saldo_corriente' => max(0, $cartera->saldo_corriente + $valor),
                    'saldo_total' => max(0, $cartera->saldo_total + $valor),
                    'ultima_actualizacion' => Carbon::now(),
                ]);
            } else {
                // Pago reduce primero el saldo corriente, luego el de mora
                $saldoCorrienteReducido = max(0, $cartera->saldo_corriente - $valor);
                $restante = $valor - $cartera->saldo_corriente;
                $saldoMoraReducido = max(0, $cartera->saldo_mora - max(0, $restante));

                $cartera->update([
                    'saldo_corriente' => $saldoCorrienteReducido,
                    'saldo_mora' => $saldoMoraReducido,
                    'saldo_total' => $saldoCorrienteReducido + $saldoMoraReducido,
                    'ultima_actualizacion' => Carbon::now(),
                ]);
            }
        }
    }

}
