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
        Schema::create('llamados_atencion', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece el llamado de atención');

            $table->foreignId('unidad_id')
                ->nullable()
                ->constrained('unidades')
                ->nullOnDelete()
                ->comment('ID de la unidad a la que se dirige el llamado de atención (nullable si es general)');

            $table->foreignId('residente_id')
                ->nullable()
                ->constrained('residentes')
                ->nullOnDelete()
                ->comment('ID del residente específico al que se dirige el llamado (nullable)');

            // Información del llamado
            $table->enum('tipo', ['convivencia', 'ruido', 'mascotas', 'parqueadero', 'seguridad', 'otro'])
                ->comment('Tipo de llamado de atención: convivencia, ruido, mascotas, parqueadero, seguridad u otro');

            $table->string('motivo', 200)
                ->comment('Motivo del llamado de atención');

            $table->text('descripcion')
                ->comment('Descripción detallada del llamado de atención');

            $table->enum('nivel', ['leve', 'moderado', 'grave'])
                ->default('leve')
                ->comment('Nivel de gravedad del llamado: leve, moderado o grave');

            $table->enum('estado', ['abierto', 'en_proceso', 'cerrado', 'anulado'])
                ->default('abierto')
                ->comment('Estado del llamado: abierto, en_proceso, cerrado o anulado');

            // Fechas
            $table->dateTime('fecha_evento')
                ->comment('Fecha y hora en que ocurrió el hecho que generó el llamado');

            $table->dateTime('fecha_registro')
                ->comment('Fecha y hora en que se registró el llamado de atención');

            // Auditoría
            $table->foreignId('registrado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('ID del usuario que registró el llamado de atención');

            // Evidencia y observaciones
            $table->json('evidencia')
                ->nullable()
                ->comment('Archivos de evidencia (fotos, videos, documentos) en formato JSON');

            $table->text('observaciones')
                ->nullable()
                ->comment('Observaciones adicionales sobre el llamado de atención');

            // Control de reincidencia
            $table->boolean('es_reincidencia')
                ->default(false)
                ->comment('Indica si este llamado es una reincidencia de un llamado anterior');

            // Estado activo
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si el llamado de atención está activo');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('unidad_id');
            $table->index('residente_id');
            $table->index('tipo');
            $table->index('nivel');
            $table->index('estado');
            $table->index('fecha_evento');
            $table->index('fecha_registro');
            $table->index('es_reincidencia');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llamados_atencion');
    }
};
