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
        Schema::create('zona_social_horarios', function (Blueprint $table) {
            $table->id();
            
            // Relación con la zona social
            $table->foreignId('zona_social_id')
                ->constrained('zonas_sociales')
                ->onDelete('cascade')
                ->comment('ID de la zona social a la que pertenece el horario');
            
            // Día de la semana
            $table->enum('dia_semana', [
                'lunes',
                'martes',
                'miércoles',
                'jueves',
                'viernes',
                'sábado',
                'domingo'
            ])->comment('Día de la semana para el horario');
            
            // Horario
            $table->time('hora_inicio')
                ->comment('Hora de inicio del horario disponible');
            $table->time('hora_fin')
                ->comment('Hora de fin del horario disponible');
            
            // Estado
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si el horario está activo');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('zona_social_id');
            $table->index(['zona_social_id', 'dia_semana']);
            $table->index(['zona_social_id', 'activo']);
            
            // Índice único para evitar horarios duplicados en el mismo día
            $table->unique(['zona_social_id', 'dia_semana', 'hora_inicio', 'hora_fin'], 'unique_horario_zona');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zona_social_horarios');
    }
};
