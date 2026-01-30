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
        Schema::create('pqrs', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece la PQRS');

            $table->foreignId('unidad_id')
                ->nullable()
                ->constrained('unidades')
                ->nullOnDelete()
                ->comment('ID de la unidad desde la cual se realiza la PQRS (nullable si es general)');

            $table->foreignId('residente_id')
                ->nullable()
                ->constrained('residentes')
                ->nullOnDelete()
                ->comment('ID del residente que realiza la PQRS (nullable)');

            // Información de la PQRS
            $table->enum('tipo', ['peticion', 'queja', 'reclamo', 'sugerencia'])
                ->comment('Tipo de PQRS: peticion, queja, reclamo o sugerencia');

            $table->enum('categoria', ['administracion', 'mantenimiento', 'seguridad', 'convivencia', 'servicios', 'otro'])
                ->comment('Categoría de la PQRS: administracion, mantenimiento, seguridad, convivencia, servicios u otro');

            $table->string('asunto', 200)
                ->comment('Asunto o título de la PQRS');

            $table->text('descripcion')
                ->comment('Descripción detallada de la PQRS');

            $table->enum('prioridad', ['baja', 'media', 'alta', 'critica'])
                ->default('media')
                ->comment('Prioridad de la PQRS: baja, media, alta o critica');

            $table->enum('estado', ['radicada', 'en_proceso', 'respondida', 'cerrada', 'rechazada'])
                ->default('radicada')
                ->comment('Estado de la PQRS: radicada, en_proceso, respondida, cerrada o rechazada');

            $table->enum('canal', ['app', 'web', 'porteria', 'email'])
                ->default('web')
                ->comment('Canal por el cual se recibió la PQRS: app, web, porteria o email');

            // Número de radicado único
            $table->string('numero_radicado', 50)
                ->unique()
                ->comment('Número único de radicado de la PQRS');

            // Fechas
            $table->dateTime('fecha_radicacion')
                ->comment('Fecha y hora en que se radicó la PQRS');

            $table->dateTime('fecha_respuesta')
                ->nullable()
                ->comment('Fecha y hora en que se respondió la PQRS');

            // Respuesta
            $table->text('respuesta')
                ->nullable()
                ->comment('Respuesta o solución proporcionada a la PQRS');

            // Auditoría de respuesta
            $table->foreignId('respondido_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('ID del usuario que respondió la PQRS');

            // Adjuntos y calificación
            $table->json('adjuntos')
                ->nullable()
                ->comment('Archivos adjuntos (documentos, imágenes) en formato JSON');

            $table->tinyInteger('calificacion_servicio')
                ->nullable()
                ->comment('Calificación del servicio recibido (1 a 5)');

            // Observaciones
            $table->text('observaciones')
                ->nullable()
                ->comment('Observaciones adicionales sobre la PQRS');

            // Estado activo
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si la PQRS está activa');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('unidad_id');
            $table->index('residente_id');
            $table->index('tipo');
            $table->index('categoria');
            $table->index('prioridad');
            $table->index('estado');
            $table->index('fecha_radicacion');
            $table->index('numero_radicado');
            $table->index('canal');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pqrs');
    }
};
