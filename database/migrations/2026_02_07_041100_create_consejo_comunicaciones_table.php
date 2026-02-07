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
        Schema::create('consejo_comunicaciones', function (Blueprint $table) {
            $table->id()->comment('ID único de la comunicación');
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad');
            $table->string('titulo')->comment('Título de la comunicación');
            $table->longText('contenido')->comment('Contenido de la comunicación');
            $table->enum('tipo', ['informativa', 'urgente', 'circular', 'recordatorio'])->comment('Tipo de comunicación');
            $table->enum('visible_para', ['consejo', 'propietarios', 'residentes', 'todos'])->default('todos')->comment('Audiencia de la comunicación');
            $table->enum('estado', ['borrador', 'publicada'])->default('borrador')->comment('Estado de la comunicación');
            $table->dateTime('fecha_publicacion')->nullable()->comment('Fecha y hora de publicación');
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('Usuario que creó la comunicación');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('tipo');
            $table->index('estado');
            $table->index('visible_para');
            $table->index('fecha_publicacion');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejo_comunicaciones');
    }
};
