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
        Schema::create('votacion_opciones', function (Blueprint $table) {
            $table->id()->comment('ID único de la opción de votación');
            $table->foreignId('votacion_id')
                ->constrained('votaciones')
                ->onDelete('cascade')
                ->comment('ID de la votación a la que pertenece la opción');
            $table->string('texto_opcion')->comment('Texto de la opción de voto');
            $table->integer('orden')->default(0)->comment('Orden de visualización de la opción');
            $table->boolean('activo')->default(true)->comment('Indica si la opción está activa');
            $table->timestamps();
            
            // Índices
            $table->index('votacion_id', 'idx_votacion_opciones_votacion');
            $table->index('orden', 'idx_votacion_opciones_orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votacion_opciones');
    }
};
