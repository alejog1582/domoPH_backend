<?php

namespace Database\Factories;

use App\Models\ZonaSocial;
use App\Models\ZonaSocialImagen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ZonaSocialImagen>
 */
class ZonaSocialImagenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'zona_social_id' => ZonaSocial::factory(),
            'url_imagen' => $this->faker->imageUrl(800, 600, 'building', true, 'Zona Social'),
            'orden' => $this->faker->numberBetween(0, 10),
            'activo' => $this->faker->boolean(90),
        ];
    }

    /**
     * Indicate that the image is active.
     */
    public function activa(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => true,
        ]);
    }

    /**
     * Indicate that the image is inactive.
     */
    public function inactiva(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Set the order of the image.
     */
    public function orden(int $orden): static
    {
        return $this->state(fn (array $attributes) => [
            'orden' => $orden,
        ]);
    }
}
