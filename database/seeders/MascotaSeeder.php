<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mascota;
use App\Models\Unidad;
use App\Models\Residente;
use Illuminate\Support\Facades\DB;

class MascotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener unidades y residentes existentes
        $unidades = Unidad::with('residentes')->get();

        if ($unidades->isEmpty()) {
            $this->command->warn('No hay unidades disponibles. Por favor, ejecuta primero los seeders de propiedades y unidades.');
            return;
        }

        // Obtener una unidad con residentes
        $unidad = $unidades->firstWhere(function ($unidad) {
            return $unidad->residentes->isNotEmpty();
        });

        if (!$unidad) {
            $this->command->warn('No hay residentes disponibles. Por favor, crea residentes primero.');
            return;
        }

        $residente = $unidad->residentes->first();

        // Crear 5 mascotas de ejemplo
        $mascotas = [
            [
                'unidad_id' => $unidad->id,
                'residente_id' => $residente->id,
                'nombre' => 'Max',
                'tipo' => Mascota::TIPO_PERRO,
                'raza' => 'Labrador Retriever',
                'color' => 'Dorado',
                'sexo' => Mascota::SEXO_MACHO,
                'fecha_nacimiento' => '2020-03-15',
                'edad_aproximada' => null,
                'peso_kg' => 28.50,
                'tamanio' => Mascota::TAMANIO_GRANDE,
                'numero_chip' => '1234567890',
                'vacunado' => true,
                'esterilizado' => true,
                'estado_salud' => Mascota::ESTADO_SALUD_SALUDABLE,
                'foto_url' => null,
                'foto_url_vacunas' => null,
                'fecha_vigencia_vacunas' => '2025-12-31',
                'observaciones' => 'Mascota muy amigable y sociable. Le encanta jugar en el parque.',
                'activo' => true,
            ],
            [
                'unidad_id' => $unidad->id,
                'residente_id' => $residente->id,
                'nombre' => 'Luna',
                'tipo' => Mascota::TIPO_GATO,
                'raza' => 'Persa',
                'color' => 'Blanco',
                'sexo' => Mascota::SEXO_HEMBRA,
                'fecha_nacimiento' => null,
                'edad_aproximada' => 36, // 3 años en meses
                'peso_kg' => 4.20,
                'tamanio' => Mascota::TAMANIO_PEQUENO,
                'numero_chip' => '0987654321',
                'vacunado' => true,
                'esterilizado' => true,
                'estado_salud' => Mascota::ESTADO_SALUD_SALUDABLE,
                'foto_url' => null,
                'foto_url_vacunas' => null,
                'fecha_vigencia_vacunas' => '2025-06-15',
                'observaciones' => 'Gata tranquila y cariñosa. Prefiere estar en interiores.',
                'activo' => true,
            ],
            [
                'unidad_id' => $unidad->id,
                'residente_id' => $residente->id,
                'nombre' => 'Rocky',
                'tipo' => Mascota::TIPO_PERRO,
                'raza' => 'Bulldog Francés',
                'color' => 'Atigrado',
                'sexo' => Mascota::SEXO_MACHO,
                'fecha_nacimiento' => '2022-08-20',
                'edad_aproximada' => null,
                'peso_kg' => 12.30,
                'tamanio' => Mascota::TAMANIO_MEDIANO,
                'numero_chip' => null,
                'vacunado' => true,
                'esterilizado' => false,
                'estado_salud' => Mascota::ESTADO_SALUD_SALUDABLE,
                'foto_url' => null,
                'foto_url_vacunas' => null,
                'fecha_vigencia_vacunas' => '2025-03-20',
                'observaciones' => 'Perro juguetón y enérgico. Requiere ejercicio diario.',
                'activo' => true,
            ],
            [
                'unidad_id' => $unidad->id,
                'residente_id' => $residente->id,
                'nombre' => 'Paco',
                'tipo' => Mascota::TIPO_AVE,
                'raza' => 'Loro',
                'color' => 'Verde y Rojo',
                'sexo' => Mascota::SEXO_DESCONOCIDO,
                'fecha_nacimiento' => null,
                'edad_aproximada' => 60, // 5 años en meses
                'peso_kg' => 0.45,
                'tamanio' => Mascota::TAMANIO_PEQUENO,
                'numero_chip' => null,
                'vacunado' => false,
                'esterilizado' => false,
                'estado_salud' => Mascota::ESTADO_SALUD_SALUDABLE,
                'foto_url' => null,
                'foto_url_vacunas' => null,
                'fecha_vigencia_vacunas' => null,
                'observaciones' => 'Loro muy hablador. Conoce varias palabras y frases.',
                'activo' => true,
            ],
            [
                'unidad_id' => $unidad->id,
                'residente_id' => $residente->id,
                'nombre' => 'Bella',
                'tipo' => Mascota::TIPO_PERRO,
                'raza' => 'Golden Retriever',
                'color' => 'Dorado',
                'sexo' => Mascota::SEXO_HEMBRA,
                'fecha_nacimiento' => '2021-11-10',
                'edad_aproximada' => null,
                'peso_kg' => 25.80,
                'tamanio' => Mascota::TAMANIO_GRANDE,
                'numero_chip' => '1122334455',
                'vacunado' => true,
                'esterilizado' => true,
                'estado_salud' => Mascota::ESTADO_SALUD_EN_TRATAMIENTO,
                'foto_url' => null,
                'foto_url_vacunas' => null,
                'fecha_vigencia_vacunas' => '2025-09-30',
                'observaciones' => 'Actualmente en tratamiento por alergia estacional. Requiere medicación diaria.',
                'activo' => true,
            ],
        ];

        foreach ($mascotas as $mascotaData) {
            Mascota::create($mascotaData);
        }

        $this->command->info('Se han creado 5 mascotas de ejemplo exitosamente.');
    }
}
