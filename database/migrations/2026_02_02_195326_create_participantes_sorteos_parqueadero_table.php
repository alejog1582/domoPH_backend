<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('participantes_sorteos_parqueadero', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('sorteo_parqueadero_id')
                ->constrained('sorteos_parqueadero')
                ->onDelete('cascade')
                ->comment('ID del sorteo de parqueaderos al que pertenece la inscripción');

            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece el participante');

            $table->foreignId('unidad_id')
                ->constrained('unidades')
                ->onDelete('cascade')
                ->comment('ID de la unidad del participante');

            $table->foreignId('residente_id')
                ->constrained('residentes')
                ->onDelete('cascade')
                ->comment('ID del residente que se inscribe al sorteo');

            // Información del vehículo
            $table->enum('tipo_vehiculo', ['carro', 'moto'])
                ->comment('Tipo de vehículo: carro o moto');

            $table->string('placa', 10)
                ->comment('Placa del vehículo inscrito');

            // Documentos del vehículo (URLs de archivos en Cloudinary o storage)
            $table->string('tarjeta_propiedad_url')
                ->nullable()
                ->comment('URL de la tarjeta de propiedad del vehículo');

            $table->string('soat_url')
                ->nullable()
                ->comment('URL del SOAT del vehículo');

            $table->string('tecnomecanica_url')
                ->nullable()
                ->comment('URL del certificado de tecnomecánica del vehículo');

            // Resultado del sorteo
            $table->string('parqueadero_asignado')
                ->nullable()
                ->comment('Número o código del parqueadero asignado al participante (NULL si no fue favorecido)');

            $table->boolean('fue_favorecido')
                ->default(false)
                ->comment('Indica si el participante resultó favorecido en el sorteo');

            // Auditoría
            $table->datetime('fecha_inscripcion')
                ->comment('Fecha y hora en que el residente se inscribió al sorteo');

            // Control
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si la inscripción está activa');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('sorteo_parqueadero_id');
            $table->index('copropiedad_id');
            $table->index('unidad_id');
            $table->index('residente_id');
            $table->index('tipo_vehiculo');
            $table->index('fue_favorecido');
            $table->index('activo');
            $table->index('fecha_inscripcion');

            // Índices compuestos para búsquedas frecuentes (nombres cortos para evitar límite de MySQL)
            $table->index(['sorteo_parqueadero_id', 'residente_id'], 'idx_sorteo_residente');
            $table->index(['sorteo_parqueadero_id', 'fue_favorecido'], 'idx_sorteo_favorecido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participantes_sorteos_parqueadero');
    }
};
