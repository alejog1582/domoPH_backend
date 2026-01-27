<?php

namespace Database\Factories;

use App\Models\Mascota;
use App\Models\Unidad;
use App\Models\Residente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mascota>
 */
class MascotaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipos = Mascota::getTipos();
        $sexos = Mascota::getSexos();
        $tamanios = Mascota::getTamanios();
        $estadosSalud = Mascota::getEstadosSalud();

        $tipo = $this->faker->randomElement($tipos);
        $sexo = $this->faker->randomElement($sexos);
        $tamanio = $this->faker->randomElement($tamanios);
        $estadoSalud = $this->faker->randomElement($estadosSalud);

        // Nombres comunes según el tipo
        $nombresPorTipo = [
            'perro' => ['Max', 'Luna', 'Rocky', 'Bella', 'Charlie', 'Daisy', 'Buddy', 'Molly'],
            'gato' => ['Whiskers', 'Luna', 'Simba', 'Mia', 'Oliver', 'Lily', 'Charlie', 'Chloe'],
            'ave' => ['Paco', 'Lola', 'Rio', 'Sunny', 'Kiwi', 'Coco'],
            'reptil' => ['Spike', 'Rex', 'Shelly', 'Iggy'],
            'roedor' => ['Nibbles', 'Cheeks', 'Peanut', 'Coco'],
            'otro' => ['Patches', 'Spot', 'Shadow', 'Misty'],
        ];

        $nombres = $nombresPorTipo[$tipo] ?? ['Mascota'];
        $nombre = $this->faker->randomElement($nombres);

        // Razas comunes según el tipo
        $razasPorTipo = [
            'perro' => ['Labrador', 'Golden Retriever', 'Bulldog', 'Pastor Alemán', 'Beagle', 'Poodle', 'Chihuahua', 'Boxer'],
            'gato' => ['Persa', 'Siamés', 'Maine Coon', 'British Shorthair', 'Ragdoll', 'Bengal', 'Sphynx'],
            'ave' => ['Canario', 'Periquito', 'Cacatúa', 'Agapornis', 'Loro'],
            'reptil' => ['Iguana', 'Gecko', 'Tortuga', 'Serpiente'],
            'roedor' => ['Hámster', 'Cobaya', 'Ratón', 'Conejo'],
        ];

        $razas = $razasPorTipo[$tipo] ?? ['Mestizo'];
        $raza = $this->faker->randomElement($razas);

        // Colores comunes
        $colores = ['Negro', 'Blanco', 'Marrón', 'Gris', 'Atigrado', 'Dorado', 'Naranja', 'Tricolor', 'Beige'];

        // Peso según tamaño
        $pesoPorTamanio = [
            'pequeño' => $this->faker->randomFloat(2, 1, 10),
            'mediano' => $this->faker->randomFloat(2, 10, 25),
            'grande' => $this->faker->randomFloat(2, 25, 60),
        ];

        $fechaNacimiento = $this->faker->optional(0.7)->dateTimeBetween('-10 years', 'now');
        $edadAproximada = $fechaNacimiento 
            ? null 
            : $this->faker->optional(0.6)->numberBetween(1, 120); // En meses

        // Obtener una unidad y residente existentes, o crear si no existen
        $unidad = Unidad::inRandomOrder()->first() ?? Unidad::factory()->create();
        $residente = Residente::where('unidad_id', $unidad->id)->inRandomOrder()->first() 
            ?? Residente::factory()->create(['unidad_id' => $unidad->id]);

        return [
            'unidad_id' => $unidad->id,
            'residente_id' => $residente->id,
            'nombre' => $nombre,
            'tipo' => $tipo,
            'raza' => $raza,
            'color' => $this->faker->optional(0.8)->randomElement($colores),
            'sexo' => $sexo,
            'fecha_nacimiento' => $fechaNacimiento ? $fechaNacimiento->format('Y-m-d') : null,
            'edad_aproximada' => $edadAproximada,
            'peso_kg' => $this->faker->optional(0.7)->randomFloat(2, 0.5, 60),
            'tamanio' => $tamanio,
            'numero_chip' => $this->faker->optional(0.5)->numerify('##########'),
            'vacunado' => $this->faker->boolean(70),
            'esterilizado' => $this->faker->boolean(60),
            'estado_salud' => $estadoSalud,
            'foto_url' => $this->faker->optional(0.3)->imageUrl(400, 400, 'animals'),
            'foto_url_vacunas' => $this->faker->optional(0.2)->imageUrl(400, 400, 'document'),
            'fecha_vigencia_vacunas' => $this->faker->optional(0.4)->dateTimeBetween('now', '+2 years'),
            'observaciones' => $this->faker->optional(0.3)->sentence(),
            'activo' => $this->faker->boolean(90),
        ];
    }

    /**
     * Indicate that the pet is vaccinated.
     */
    public function vacunada(): static
    {
        return $this->state(fn (array $attributes) => [
            'vacunado' => true,
            'fecha_vigencia_vacunas' => fake()->dateTimeBetween('now', '+2 years'),
        ]);
    }

    /**
     * Indicate that the pet is sterilized.
     */
    public function esterilizada(): static
    {
        return $this->state(fn (array $attributes) => [
            'esterilizado' => true,
        ]);
    }

    /**
     * Indicate that the pet is inactive.
     */
    public function inactiva(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
