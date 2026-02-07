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
        Schema::create('asamblea_votos', function (Blueprint $table) {
            $table->id()->comment('ID único del voto');
            $table->foreignId('votacion_id')
                ->constrained('asamblea_votaciones')
                ->onDelete('cascade')
                ->comment('ID de la votación');
            $table->foreignId('residente_id')
                ->constrained('residentes')
                ->onDelete('cascade')
                ->comment('ID del residente que vota');
            $table->foreignId('opcion_id')
                ->constrained('asamblea_votacion_opciones')
                ->onDelete('cascade')
                ->comment('ID de la opción seleccionada');
            $table->decimal('coeficiente_aplicado', 8, 4)->default(0)->comment('Coeficiente aplicado al voto');
            $table->timestamps();

            // Índices
            $table->index('votacion_id');
            $table->index('residente_id');
            $table->index('opcion_id');

            // Constraint único para evitar doble voto
            $table->unique(['votacion_id', 'residente_id'], 'unique_voto_residente_votacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asamblea_votos');
    }
};
