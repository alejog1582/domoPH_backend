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
        Schema::create('consejo_reuniones', function (Blueprint $table) {
            $table->id()->comment('ID único de la reunión');
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad');
            $table->string('titulo')->comment('Título de la reunión');
            $table->enum('tipo_reunion', ['ordinaria', 'extraordinaria'])->comment('Tipo de reunión');
            $table->enum('modalidad', ['presencial', 'virtual', 'mixta'])->comment('Modalidad de la reunión');
            $table->dateTime('fecha_inicio')->comment('Fecha y hora de inicio de la reunión');
            $table->dateTime('fecha_fin')->nullable()->comment('Fecha y hora de fin de la reunión');
            $table->string('lugar')->nullable()->comment('Lugar donde se realizará la reunión (si es presencial)');
            $table->string('enlace_virtual')->nullable()->comment('Enlace para reunión virtual');
            $table->enum('estado', ['programada', 'cancelada', 'realizada'])->default('programada')->comment('Estado de la reunión');
            $table->text('observaciones')->nullable()->comment('Observaciones adicionales');
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('Usuario que creó la reunión');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('tipo_reunion');
            $table->index('estado');
            $table->index('fecha_inicio');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejo_reuniones');
    }
};
