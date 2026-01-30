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
        Schema::create('comunicado_unidad', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('comunicado_id')
                ->constrained('comunicados')
                ->onDelete('cascade')
                ->comment('ID del comunicado');

            $table->foreignId('unidad_id')
                ->constrained('unidades')
                ->onDelete('cascade')
                ->comment('ID de la unidad destinataria del comunicado');

            // Tracking de lectura
            $table->boolean('leido')
                ->default(false)
                ->comment('Indica si el comunicado ha sido leído por la unidad');

            $table->dateTime('fecha_lectura')
                ->nullable()
                ->comment('Fecha y hora en que se marcó el comunicado como leído');

            $table->timestamps();

            // Índice único: un comunicado solo puede estar asociado una vez a una unidad
            $table->unique(['comunicado_id', 'unidad_id'], 'comunicado_unidad_unique');

            // Índices adicionales
            $table->index('comunicado_id');
            $table->index('unidad_id');
            $table->index('leido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunicado_unidad');
    }
};
