<?php

namespace Database\Factories;

use App\Models\CuotaAdministracion;
use App\Models\Propiedad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CuotaAdministracion>
 */
class CuotaAdministracionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $conceptos = CuotaAdministracion::getConceptos();
        $concepto = $this->faker->randomElement($conceptos);
        
        // Decidir si es por coeficiente o fija (70% probabilidad de ser por coeficiente)
        $esPorCoeficiente = $this->faker->boolean(70);
        
        // Decidir si tiene rango de fechas (60% probabilidad)
        $tieneRango = $this->faker->boolean(60);
        
        $mesDesde = null;
        $mesHasta = null;
        
        if ($tieneRango) {
            $mesDesde = $this->faker->dateTimeBetween('-1 year', '+1 year');
            $mesHasta = $this->faker->dateTimeBetween($mesDesde, '+2 years');
            $mesDesde = $mesDesde->format('Y-m-01'); // Primer día del mes
            $mesHasta = $mesHasta->format('Y-m-t'); // Último día del mes
        }

        return [
            'propiedad_id' => Propiedad::factory(),
            'concepto' => $concepto,
            'coeficiente' => $esPorCoeficiente ? $this->faker->randomFloat(4, 0.0001, 1.0) : null,
            'valor' => $this->faker->randomFloat(2, 50000, 500000),
            'mes_desde' => $mesDesde,
            'mes_hasta' => $mesHasta,
            'activo' => $this->faker->boolean(90),
        ];
    }

    /**
     * Indicate that the fee is ordinary.
     */
    public function ordinaria(): static
    {
        return $this->state(fn (array $attributes) => [
            'concepto' => CuotaAdministracion::CONCEPTO_CUOTA_ORDINARIA,
        ]);
    }

    /**
     * Indicate that the fee is extraordinary.
     */
    public function extraordinaria(): static
    {
        return $this->state(fn (array $attributes) => [
            'concepto' => CuotaAdministracion::CONCEPTO_CUOTA_EXTRAORDINARIA,
        ]);
    }

    /**
     * Indicate that the fee is calculated by coefficient.
     */
    public function porCoeficiente(float $coeficiente = null): static
    {
        return $this->state(fn (array $attributes) => [
            'coeficiente' => $coeficiente ?? $this->faker->randomFloat(4, 0.0001, 1.0),
        ]);
    }

    /**
     * Indicate that the fee is fixed (not by coefficient).
     */
    public function fija(): static
    {
        return $this->state(fn (array $attributes) => [
            'coeficiente' => null,
        ]);
    }

    /**
     * Set a date range for the fee.
     */
    public function conRango(string $mesDesde, string $mesHasta): static
    {
        return $this->state(fn (array $attributes) => [
            'mes_desde' => $mesDesde,
            'mes_hasta' => $mesHasta,
        ]);
    }

    /**
     * Indicate that the fee is indefinite (no date range).
     */
    public function indefinida(): static
    {
        return $this->state(fn (array $attributes) => [
            'mes_desde' => null,
            'mes_hasta' => null,
        ]);
    }

    /**
     * Indicate that the fee is active.
     */
    public function activa(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => true,
        ]);
    }

    /**
     * Indicate that the fee is inactive.
     */
    public function inactiva(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
