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
        Schema::create('consejo_tarea_seguimientos', function (Blueprint $table) {
            $table->id()->comment('ID único del seguimiento');
            $table->foreignId('tarea_id')
                ->constrained('consejo_tareas')
                ->onDelete('cascade')
                ->comment('ID de la tarea');
            $table->text('comentario')->comment('Comentario del seguimiento');
            $table->string('estado_anterior')->nullable()->comment('Estado anterior de la tarea');
            $table->string('estado_nuevo')->nullable()->comment('Nuevo estado de la tarea');
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('Usuario que creó el seguimiento');
            $table->timestamps();

            // Índices
            $table->index('tarea_id');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejo_tarea_seguimientos');
    }
};
