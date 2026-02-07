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
        Schema::create('votaciones', function (Blueprint $table) {
            $table->id()->comment('ID único del registro de votación');
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece la votación');
            $table->string('titulo')->comment('Título de la votación');
            $table->text('descripcion')->nullable()->comment('Descripción detallada de la votación');
            $table->date('fecha_inicio')->comment('Fecha de inicio de la votación');
            $table->date('fecha_fin')->comment('Fecha de fin de vigencia de la votación');
            $table->enum('estado', ['activa', 'cerrada', 'anulada'])
                ->default('activa')
                ->comment('Estado de la votación: activa, cerrada o anulada');
            $table->boolean('activo')->default(true)->comment('Indica si la votación está activa');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('copropiedad_id', 'idx_votaciones_copropiedad');
            $table->index('estado', 'idx_votaciones_estado');
            $table->index('fecha_inicio', 'idx_votaciones_fecha_inicio');
            $table->index('fecha_fin', 'idx_votaciones_fecha_fin');
            $table->index('activo', 'idx_votaciones_activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votaciones');
    }
};
