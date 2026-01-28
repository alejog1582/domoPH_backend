<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cartera;
use App\Models\Propiedad;
use App\Models\Unidad;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cartera>
 */
class CarteraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Obtener (o crear) una propiedad y una unidad asociada para vincular la cartera
        $propiedad = Propiedad::first() ?? Propiedad::factory()->create();
        $unidad = Unidad::where('propiedad_id', $propiedad->id)->first()
            ?? Unidad::factory()->create(['propiedad_id' => $propiedad->id]);

        $saldoMora = $this->faker->randomFloat(2, 0, 500000);
        $saldoCorriente = $this->faker->randomFloat(2, 0, 1000000);
        $saldoTotal = $saldoMora + $saldoCorriente;

        return [
            'copropiedad_id' => $propiedad->id,
            'unidad_id' => $unidad->id,
            'saldo_total' => $saldoTotal,
            'saldo_mora' => $saldoMora,
            'saldo_corriente' => $saldoCorriente,
            'ultima_actualizacion' => Carbon::now(),
            'activo' => true,
        ];
    }
}
