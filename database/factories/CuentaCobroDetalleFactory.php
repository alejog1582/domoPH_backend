<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CuentaCobro;
use App\Models\CuentaCobroDetalle;
use App\Models\CuotaAdministracion;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CuentaCobroDetalle>
 */
class CuentaCobroDetalleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cuenta = CuentaCobro::first() ?? CuentaCobro::factory()->create();
        $cuota = CuotaAdministracion::first();

        return [
            'cuenta_cobro_id' => $cuenta->id,
            'concepto' => $this->faker->randomElement(['Cuota ordinaria', 'Cuota extraordinaria', 'Interés de mora', 'Descuento']),
            'cuota_administracion_id' => $cuota?->id,
            'valor' => $this->faker->randomFloat(2, 10000, 200000),
        ];
    }
}
