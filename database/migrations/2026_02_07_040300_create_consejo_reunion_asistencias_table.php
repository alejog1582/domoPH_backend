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
        Schema::create('consejo_reunion_asistencias', function (Blueprint $table) {
            $table->id()->comment('ID único del registro de asistencia');
            $table->foreignId('reunion_id')
                ->constrained('consejo_reuniones')
                ->onDelete('cascade')
                ->comment('ID de la reunión');
            $table->foreignId('integrante_id')
                ->constrained('consejo_integrantes')
                ->onDelete('cascade')
                ->comment('ID del integrante');
            $table->boolean('asistio')->default(false)->comment('Indica si asistió a la reunión');
            $table->time('hora_ingreso')->nullable()->comment('Hora de ingreso a la reunión');
            $table->time('hora_salida')->nullable()->comment('Hora de salida de la reunión');
            $table->timestamps();

            // Índices
            $table->index('reunion_id');
            $table->index('integrante_id');
            $table->unique(['reunion_id', 'integrante_id'], 'unique_reunion_integrante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejo_reunion_asistencias');
    }
};
