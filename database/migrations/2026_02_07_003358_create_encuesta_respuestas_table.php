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
        Schema::create('encuesta_respuestas', function (Blueprint $table) {
            $table->id()->comment('ID único de la respuesta de encuesta');
            $table->foreignId('encuesta_id')
                ->constrained('encuestas')
                ->onDelete('cascade')
                ->comment('ID de la encuesta a la que pertenece la respuesta');
            $table->foreignId('residente_id')
                ->constrained('residentes')
                ->onDelete('cascade')
                ->comment('ID del residente que respondió la encuesta');
            $table->foreignId('opcion_id')
                ->nullable()
                ->constrained('encuesta_opciones')
                ->onDelete('cascade')
                ->comment('ID de la opción seleccionada (para encuestas de opción múltiple)');
            $table->text('respuesta_abierta')->nullable()->comment('Respuesta abierta del residente');
            $table->timestamp('created_at')->useCurrent()->comment('Fecha y hora de creación de la respuesta');
            
            // Índices
            $table->index('encuesta_id', 'idx_encuesta_respuestas_encuesta');
            $table->index('residente_id', 'idx_encuesta_respuestas_residente');
            $table->index('opcion_id', 'idx_encuesta_respuestas_opcion');
            
            // Restricción única: un residente solo puede responder una vez por encuesta
            $table->unique(['encuesta_id', 'residente_id'], 'unique_encuesta_residente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encuesta_respuestas');
    }
};
