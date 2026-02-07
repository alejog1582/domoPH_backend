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
        Schema::create('consejo_tarea_archivos', function (Blueprint $table) {
            $table->id()->comment('ID único del archivo');
            $table->foreignId('tarea_id')
                ->constrained('consejo_tareas')
                ->onDelete('cascade')
                ->comment('ID de la tarea');
            $table->string('nombre_archivo')->comment('Nombre del archivo');
            $table->string('ruta_archivo')->comment('Ruta o URL del archivo');
            $table->string('tipo_archivo')->nullable()->comment('Tipo MIME del archivo');
            $table->integer('tamaño')->nullable()->comment('Tamaño del archivo en bytes');
            $table->timestamps();

            // Índices
            $table->index('tarea_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejo_tarea_archivos');
    }
};
