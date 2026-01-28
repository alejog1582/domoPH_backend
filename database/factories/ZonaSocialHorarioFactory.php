<?php

namespace Database\Factories;

use App\Models\ZonaSocial;
use App\Models\ZonaSocialHorario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ZonaSocialHorario>
 */
class ZonaSocialHorarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $diasSemana = ZonaSocialHorario::getDiasSemana();
        $diaSemana = $this->faker->randomElement($diasSemana);

        // Horarios comunes: 6:00 AM - 10:00 PM
        $horaInicio = $this->faker->time('H:i', '06:00');
        $horaFin = $this->faker->time('H:i', '22:00');

        // Asegurar que hora_fin sea después de hora_inicio
        if (strtotime($horaFin) <= strtotime($horaInicio)) {
            $horaFin = date('H:i', strtotime($horaInicio . ' +' . $this->faker->numberBetween(2, 8) . ' hours'));
        }

        return [
            'zona_social_id' => ZonaSocial::factory(),
            'dia_semana' => $diaSemana,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'activo' => $this->faker->boolean(90),
        ];
    }

    /**
     * Indicate that the schedule is active.
     */
    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => true,
        ]);
    }

    /**
     * Indicate that the schedule is inactive.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Set a specific day of the week.
     */
    public function dia(string $diaSemana): static
    {
        return $this->state(fn (array $attributes) => [
            'dia_semana' => $diaSemana,
        ]);
    }

    /**
     * Set specific hours.
     */
    public function horario(string $horaInicio, string $horaFin): static
    {
        return $this->state(fn (array $attributes) => [
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
        ]);
    }

    /**
     * Create a full week schedule (Monday to Sunday).
     */
    public function semanaCompleta(): static
    {
        $diasSemana = ZonaSocialHorario::getDiasSemana();
        $horarios = [
            ['06:00', '22:00'], // Lunes a Viernes
            ['08:00', '20:00'], // Sábado
            ['08:00', '20:00'], // Domingo
        ];

        return $this->state(function (array $attributes) use ($diasSemana, $horarios) {
            $index = array_search($attributes['dia_semana'], $diasSemana);
            $horario = $index < 5 ? $horarios[0] : $horarios[$index - 4];
            
            return [
                'hora_inicio' => $horario[0],
                'hora_fin' => $horario[1],
            ];
        });
    }
}
