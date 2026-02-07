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
        Schema::create('asamblea_votacion_opciones', function (Blueprint $table) {
            $table->id()->comment('ID único de la opción');
            $table->foreignId('votacion_id')
                ->constrained('asamblea_votaciones')
                ->onDelete('cascade')
                ->comment('ID de la votación');
            $table->string('opcion')->comment('Texto de la opción');
            $table->integer('orden')->default(0)->comment('Orden de visualización');
            $table->timestamps();

            // Índices
            $table->index('votacion_id');
            $table->index('orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asamblea_votacion_opciones');
    }
};
