<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CuentaCobro;
use App\Models\Propiedad;
use App\Models\Unidad;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CuentaCobro>
 */
class CuentaCobroFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $propiedad = Propiedad::first() ?? Propiedad::factory()->create();
        $unidad = Unidad::where('propiedad_id', $propiedad->id)->first()
            ?? Unidad::factory()->create(['propiedad_id' => $propiedad->id]);

        $periodo = $this->faker->date('Y-m');
        $fechaEmision = $this->faker->dateTimeBetween('-1 month', 'now');

        $valorCuotas = $this->faker->randomFloat(2, 50000, 300000);
        $intereses = $this->faker->randomFloat(2, 0, 20000);
        $descuentos = $this->faker->randomFloat(2, 0, 50000);
        $recargos = $this->faker->randomFloat(2, 0, 30000);
        $valorTotal = $valorCuotas + $intereses + $recargos - $descuentos;

        return [
            'copropiedad_id' => $propiedad->id,
            'unidad_id' => $unidad->id,
            'periodo' => $periodo,
            'fecha_emision' => $fechaEmision->format('Y-m-d'),
            'fecha_vencimiento' => (clone $fechaEmision)->modify('+15 days')->format('Y-m-d'),
            'valor_cuotas' => $valorCuotas,
            'valor_intereses' => $intereses,
            'valor_descuentos' => $descuentos,
            'valor_recargos' => $recargos,
            'valor_total' => $valorTotal,
            'estado' => $this->faker->randomElement(['pendiente', 'pagada', 'vencida']),
            'observaciones' => $this->faker->optional()->sentence(),
        ];
    }
}
