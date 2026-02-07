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
        Schema::create('votos', function (Blueprint $table) {
            $table->id()->comment('ID único del voto');
            $table->foreignId('votacion_id')
                ->constrained('votaciones')
                ->onDelete('cascade')
                ->comment('ID de la votación a la que pertenece el voto');
            $table->foreignId('residente_id')
                ->constrained('residentes')
                ->onDelete('cascade')
                ->comment('ID del residente que emitió el voto');
            $table->foreignId('opcion_id')
                ->constrained('votacion_opciones')
                ->onDelete('cascade')
                ->comment('ID de la opción seleccionada en el voto');
            $table->timestamp('created_at')->useCurrent()->comment('Fecha y hora de creación del voto');
            
            // Índices
            $table->index('votacion_id', 'idx_votos_votacion');
            $table->index('residente_id', 'idx_votos_residente');
            $table->index('opcion_id', 'idx_votos_opcion');
            
            // Restricción única: un residente solo puede votar una vez por votación
            $table->unique(['votacion_id', 'residente_id'], 'unique_votacion_residente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votos');
    }
};
