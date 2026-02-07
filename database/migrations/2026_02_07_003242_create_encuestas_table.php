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
        Schema::create('encuestas', function (Blueprint $table) {
            $table->id()->comment('ID único del registro de encuesta');
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece la encuesta');
            $table->string('titulo')->comment('Título de la encuesta');
            $table->text('descripcion')->nullable()->comment('Descripción detallada de la encuesta');
            $table->enum('tipo_respuesta', ['opcion_multiple', 'respuesta_abierta'])
                ->comment('Tipo de respuesta: opción múltiple o respuesta abierta');
            $table->date('fecha_inicio')->comment('Fecha de inicio de la encuesta');
            $table->date('fecha_fin')->comment('Fecha de fin de vigencia de la encuesta');
            $table->enum('estado', ['activa', 'cerrada', 'anulada'])
                ->default('activa')
                ->comment('Estado de la encuesta: activa, cerrada o anulada');
            $table->boolean('activo')->default(true)->comment('Indica si la encuesta está activa');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('copropiedad_id', 'idx_encuestas_copropiedad');
            $table->index('estado', 'idx_encuestas_estado');
            $table->index('fecha_inicio', 'idx_encuestas_fecha_inicio');
            $table->index('fecha_fin', 'idx_encuestas_fecha_fin');
            $table->index('activo', 'idx_encuestas_activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encuestas');
    }
};
