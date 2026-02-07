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
        Schema::create('asamblea_asistencias', function (Blueprint $table) {
            $table->id()->comment('ID único de la asistencia');
            $table->foreignId('asamblea_id')
                ->constrained('asambleas')
                ->onDelete('cascade')
                ->comment('ID de la asamblea');
            $table->foreignId('residente_id')
                ->nullable()
                ->constrained('residentes')
                ->onDelete('cascade')
                ->comment('ID del residente');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID del usuario (si no es residente)');
            $table->enum('rol', ['moderador', 'orador', 'oyente'])->default('oyente')->comment('Rol en la asamblea');
            $table->boolean('presente')->default(false)->comment('Indica si está presente');
            $table->dateTime('hora_ingreso')->nullable()->comment('Hora de ingreso a la asamblea');
            $table->dateTime('hora_salida')->nullable()->comment('Hora de salida de la asamblea');
            $table->decimal('coeficiente_voto', 8, 4)->default(0)->comment('Coeficiente de voto del participante');
            $table->timestamps();

            // Índices
            $table->index('asamblea_id');
            $table->index('residente_id');
            $table->index('user_id');
            $table->index('rol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asamblea_asistencias');
    }
};
