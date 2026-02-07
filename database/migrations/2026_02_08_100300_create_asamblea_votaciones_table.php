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
        Schema::create('asamblea_votaciones', function (Blueprint $table) {
            $table->id()->comment('ID único de la votación');
            $table->foreignId('asamblea_id')
                ->constrained('asambleas')
                ->onDelete('cascade')
                ->comment('ID de la asamblea');
            $table->string('titulo')->comment('Título de la votación');
            $table->text('descripcion')->nullable()->comment('Descripción de la votación');
            $table->enum('tipo', ['si_no', 'opcion_multiple'])->comment('Tipo de votación');
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta')->comment('Estado de la votación');
            $table->dateTime('fecha_inicio')->comment('Fecha y hora de inicio de la votación');
            $table->dateTime('fecha_fin')->nullable()->comment('Fecha y hora de fin de la votación');
            $table->timestamps();

            // Índices
            $table->index('asamblea_id');
            $table->index('estado');
            $table->index('fecha_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asamblea_votaciones');
    }
};
