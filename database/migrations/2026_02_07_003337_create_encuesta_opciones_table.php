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
        Schema::create('encuesta_opciones', function (Blueprint $table) {
            $table->id()->comment('ID único de la opción de encuesta');
            $table->foreignId('encuesta_id')
                ->constrained('encuestas')
                ->onDelete('cascade')
                ->comment('ID de la encuesta a la que pertenece la opción');
            $table->string('texto_opcion')->comment('Texto de la opción de respuesta');
            $table->integer('orden')->default(0)->comment('Orden de visualización de la opción');
            $table->boolean('activo')->default(true)->comment('Indica si la opción está activa');
            $table->timestamps();
            
            // Índices
            $table->index('encuesta_id', 'idx_encuesta_opciones_encuesta');
            $table->index('orden', 'idx_encuesta_opciones_orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encuesta_opciones');
    }
};
