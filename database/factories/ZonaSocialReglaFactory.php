<?php

namespace Database\Factories;

use App\Models\ZonaSocial;
use App\Models\ZonaSocialRegla;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ZonaSocialRegla>
 */
class ZonaSocialReglaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clavesComunes = ZonaSocialRegla::getClavesComunes();
        $clave = $this->faker->randomElement($clavesComunes);

        // Valores por defecto según la clave
        $valoresPorClave = [
            ZonaSocialRegla::CLAVE_MAX_RESERVAS_MES => (string) $this->faker->numberBetween(1, 5),
            ZonaSocialRegla::CLAVE_REQUIERE_DEPOSITO => $this->faker->randomElement(['true', 'false']),
            ZonaSocialRegla::CLAVE_BLOQUEAR_EN_MORA => $this->faker->randomElement(['true', 'false']),
            ZonaSocialRegla::CLAVE_DIAS_ANTICIPACION => (string) $this->faker->numberBetween(1, 30),
            ZonaSocialRegla::CLAVE_HORAS_CANCELACION => (string) $this->faker->numberBetween(24, 168), // 1 día a 1 semana
            ZonaSocialRegla::CLAVE_PERMITE_INVITADOS => $this->faker->randomElement(['true', 'false']),
            ZonaSocialRegla::CLAVE_MAX_INVITADOS => (string) $this->faker->numberBetween(1, 10),
        ];

        $valor = $valoresPorClave[$clave] ?? $this->faker->word();

        $descripcionesPorClave = [
            ZonaSocialRegla::CLAVE_MAX_RESERVAS_MES => 'Número máximo de reservas que un residente puede hacer por mes',
            ZonaSocialRegla::CLAVE_REQUIERE_DEPOSITO => 'Indica si se requiere depósito para reservar la zona',
            ZonaSocialRegla::CLAVE_BLOQUEAR_EN_MORA => 'Indica si se bloquean reservas para residentes en mora',
            ZonaSocialRegla::CLAVE_DIAS_ANTICIPACION => 'Días de anticipación mínimos para hacer una reserva',
            ZonaSocialRegla::CLAVE_HORAS_CANCELACION => 'Horas mínimas de anticipación para cancelar una reserva',
            ZonaSocialRegla::CLAVE_PERMITE_INVITADOS => 'Indica si se permiten invitados en las reservas',
            ZonaSocialRegla::CLAVE_MAX_INVITADOS => 'Número máximo de invitados permitidos',
        ];

        return [
            'zona_social_id' => ZonaSocial::factory(),
            'clave' => $clave,
            'valor' => $valor,
            'descripcion' => $descripcionesPorClave[$clave] ?? $this->faker->optional(0.5)->sentence(),
        ];
    }

    /**
     * Set a specific rule key.
     */
    public function clave(string $clave, string $valor = null, string $descripcion = null): static
    {
        return $this->state(function (array $attributes) use ($clave, $valor, $descripcion) {
            $valoresPorClave = [
                ZonaSocialRegla::CLAVE_MAX_RESERVAS_MES => (string) $this->faker->numberBetween(1, 5),
                ZonaSocialRegla::CLAVE_REQUIERE_DEPOSITO => 'true',
                ZonaSocialRegla::CLAVE_BLOQUEAR_EN_MORA => 'true',
                ZonaSocialRegla::CLAVE_DIAS_ANTICIPACION => (string) $this->faker->numberBetween(1, 30),
                ZonaSocialRegla::CLAVE_HORAS_CANCELACION => (string) $this->faker->numberBetween(24, 168),
                ZonaSocialRegla::CLAVE_PERMITE_INVITADOS => 'true',
                ZonaSocialRegla::CLAVE_MAX_INVITADOS => (string) $this->faker->numberBetween(1, 10),
            ];

            return [
                'clave' => $clave,
                'valor' => $valor ?? ($valoresPorClave[$clave] ?? $this->faker->word()),
                'descripcion' => $descripcion ?? $attributes['descripcion'] ?? null,
            ];
        });
    }
}
