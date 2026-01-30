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
        Schema::create('comunicado_residente', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('comunicado_id')
                ->constrained('comunicados')
                ->onDelete('cascade')
                ->comment('ID del comunicado');

            $table->foreignId('residente_id')
                ->constrained('residentes')
                ->onDelete('cascade')
                ->comment('ID del residente destinatario del comunicado');

            // Tracking de lectura
            $table->boolean('leido')
                ->default(false)
                ->comment('Indica si el comunicado ha sido leído por el residente');

            $table->dateTime('fecha_lectura')
                ->nullable()
                ->comment('Fecha y hora en que se marcó el comunicado como leído');

            $table->timestamps();

            // Índice único: un comunicado solo puede estar asociado una vez a un residente
            $table->unique(['comunicado_id', 'residente_id'], 'comunicado_residente_unique');

            // Índices adicionales
            $table->index('comunicado_id');
            $table->index('residente_id');
            $table->index('leido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunicado_residente');
    }
};
