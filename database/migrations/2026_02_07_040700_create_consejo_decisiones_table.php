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
        Schema::create('consejo_decisiones', function (Blueprint $table) {
            $table->id()->comment('ID único de la decisión');
            $table->foreignId('acta_id')
                ->constrained('consejo_actas')
                ->onDelete('cascade')
                ->comment('ID del acta');
            $table->text('descripcion')->comment('Descripción de la decisión');
            $table->string('responsable')->nullable()->comment('Persona responsable de ejecutar la decisión');
            $table->date('fecha_compromiso')->nullable()->comment('Fecha comprometida para cumplir la decisión');
            $table->enum('estado', ['pendiente', 'en_proceso', 'cumplida'])->default('pendiente')->comment('Estado de la decisión');
            $table->timestamps();

            // Índices
            $table->index('acta_id');
            $table->index('estado');
            $table->index('fecha_compromiso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejo_decisiones');
    }
};
