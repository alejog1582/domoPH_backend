<?php

namespace Database\Factories;

use App\Models\ZonaSocial;
use App\Models\Propiedad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ZonaSocial>
 */
class ZonaSocialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tiposZonas = [
            'Salón Social',
            'Salón de Eventos',
            'Piscina',
            'Gimnasio',
            'Cancha de Tenis',
            'Cancha de Fútbol',
            'Parque Infantil',
            'Zona BBQ',
            'Sala de Juegos',
            'Biblioteca',
            'Terraza',
            'Sala de Reuniones',
        ];

        $torres = ['Torre 1', 'Torre 2', 'Torre 3', 'Torre 4', 'Torre 5', 'Bloque A', 'Bloque B', 'Piso 1', 'Piso 2'];
        $ubicaciones = ['Planta Baja', 'Piso 1', 'Piso 2', 'Piso 3', 'Terraza', 'Exterior', 'Interior'];

        $nombre = $this->faker->randomElement($tiposZonas);
        $torre = $this->faker->optional(0.6)->randomElement($torres);
        if ($torre) {
            $nombre .= ' ' . $torre;
        }

        $estados = ZonaSocial::getEstados();
        $estado = $this->faker->randomElement($estados);

        return [
            'propiedad_id' => Propiedad::factory(),
            'nombre' => $nombre,
            'descripcion' => $this->faker->optional(0.8)->paragraph(),
            'ubicacion' => $this->faker->optional(0.7)->randomElement($ubicaciones),
            'capacidad_maxima' => $this->faker->numberBetween(10, 200),
            'max_invitados_por_reserva' => $this->faker->optional(0.6)->numberBetween(0, 10),
            'tiempo_minimo_uso_horas' => $this->faker->numberBetween(1, 4),
            'tiempo_maximo_uso_horas' => $this->faker->numberBetween(4, 24),
            'reservas_simultaneas' => $this->faker->boolean(80), // 80% de probabilidad de permitir reservas simultáneas
            'valor_alquiler' => $this->faker->optional(0.5)->randomFloat(2, 50000, 500000),
            'valor_deposito' => $this->faker->optional(0.4)->randomFloat(2, 100000, 1000000),
            'requiere_aprobacion' => $this->faker->boolean(30),
            'permite_reservas_en_mora' => $this->faker->boolean(20),
            'reglamento_url' => $this->faker->optional(0.3)->url(),
            'estado' => $estado,
            'activo' => $estado === ZonaSocial::ESTADO_ACTIVA ? $this->faker->boolean(90) : false,
        ];
    }

    /**
     * Indicate that the zone is active.
     */
    public function activa(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => ZonaSocial::ESTADO_ACTIVA,
            'activo' => true,
        ]);
    }

    /**
     * Indicate that the zone is inactive.
     */
    public function inactiva(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => ZonaSocial::ESTADO_INACTIVA,
            'activo' => false,
        ]);
    }

    /**
     * Indicate that the zone is in maintenance.
     */
    public function enMantenimiento(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => ZonaSocial::ESTADO_MANTENIMIENTO,
            'activo' => false,
        ]);
    }

    /**
     * Indicate that the zone requires approval.
     */
    public function requiereAprobacion(): static
    {
        return $this->state(fn (array $attributes) => [
            'requiere_aprobacion' => true,
        ]);
    }

    /**
     * Indicate that the zone has rental value.
     */
    public function conAlquiler(float $valor = null): static
    {
        return $this->state(fn (array $attributes) => [
            'valor_alquiler' => $valor ?? $this->faker->randomFloat(2, 50000, 500000),
        ]);
    }
}
