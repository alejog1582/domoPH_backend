<?php

namespace Database\Factories;

use App\Models\RecaudoDetalle;
use App\Models\Recaudo;
use App\Models\CuentaCobroDetalle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecaudoDetalle>
 */
class RecaudoDetalleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $recaudo = Recaudo::first() ?? Recaudo::factory()->create();
        $cuentaCobroDetalle = $recaudo->cuentaCobro 
            ? $recaudo->cuentaCobro->detalles()->first() 
            : null;

        return [
            'recaudo_id' => $recaudo->id,
            'cuenta_cobro_detalle_id' => $cuentaCobroDetalle?->id,
            'concepto' => $this->faker->randomElement(['Cuota ordinaria', 'Cuota extraordinaria', 'Abono a saldo general']),
            'valor_aplicado' => $this->faker->randomFloat(2, 10000, 200000),
        ];
    }
}
