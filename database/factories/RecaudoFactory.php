<?php

namespace Database\Factories;

use App\Models\Recaudo;
use App\Models\Propiedad;
use App\Models\Unidad;
use App\Models\CuentaCobro;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recaudo>
 */
class RecaudoFactory extends Factory
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
        $cuentaCobro = CuentaCobro::where('copropiedad_id', $propiedad->id)
            ->where('unidad_id', $unidad->id)
            ->first();
        $usuario = User::first() ?? User::factory()->create();

        $numeroRecaudo = 'REC-' . str_pad($propiedad->id, 3, '0', STR_PAD_LEFT) . '-' . 
                         str_pad($unidad->id, 3, '0', STR_PAD_LEFT) . '-' . 
                         str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        return [
            'copropiedad_id' => $propiedad->id,
            'unidad_id' => $unidad->id,
            'cuenta_cobro_id' => $cuentaCobro?->id,
            'numero_recaudo' => $numeroRecaudo,
            'fecha_pago' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'tipo_pago' => $this->faker->randomElement(['parcial', 'total', 'anticipo']),
            'medio_pago' => $this->faker->randomElement(['efectivo', 'transferencia', 'consignacion', 'tarjeta', 'pse', 'otro']),
            'referencia_pago' => 'REF-' . $this->faker->numerify('######'),
            'descripcion' => $this->faker->optional()->sentence(),
            'valor_pagado' => $this->faker->randomFloat(2, 50000, 500000),
            'estado' => $this->faker->randomElement(['registrado', 'aplicado', 'anulado']),
            'registrado_por' => $usuario->id,
            'activo' => true,
        ];
    }
}
