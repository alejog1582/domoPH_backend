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
        Schema::create('consejo_tareas', function (Blueprint $table) {
            $table->id()->comment('ID único de la tarea');
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad');
            $table->foreignId('acta_id')
                ->nullable()
                ->constrained('consejo_actas')
                ->onDelete('set null')
                ->comment('ID del acta relacionada (opcional)');
            $table->foreignId('decision_id')
                ->nullable()
                ->constrained('consejo_decisiones')
                ->onDelete('set null')
                ->comment('ID de la decisión relacionada (opcional)');
            $table->string('titulo')->comment('Título de la tarea');
            $table->text('descripcion')->comment('Descripción detallada de la tarea');
            $table->foreignId('responsable_id')
                ->nullable()
                ->constrained('consejo_integrantes')
                ->onDelete('set null')
                ->comment('ID del integrante responsable');
            $table->date('fecha_inicio')->nullable()->comment('Fecha de inicio de la tarea');
            $table->date('fecha_vencimiento')->nullable()->comment('Fecha de vencimiento de la tarea');
            $table->enum('prioridad', ['baja', 'media', 'alta'])->default('media')->comment('Prioridad de la tarea');
            $table->enum('estado', ['pendiente', 'en_progreso', 'bloqueada', 'finalizada'])->default('pendiente')->comment('Estado de la tarea');
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('Usuario que creó la tarea');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('acta_id');
            $table->index('decision_id');
            $table->index('responsable_id');
            $table->index('estado');
            $table->index('prioridad');
            $table->index('fecha_vencimiento');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejo_tareas');
    }
};
