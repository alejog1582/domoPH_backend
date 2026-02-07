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
        Schema::create('asambleas', function (Blueprint $table) {
            $table->id()->comment('ID único de la asamblea');
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad');
            $table->string('titulo')->comment('Título de la asamblea');
            $table->text('descripcion')->nullable()->comment('Descripción de la asamblea');
            $table->enum('tipo', ['ordinaria', 'extraordinaria'])->comment('Tipo de asamblea');
            $table->enum('modalidad', ['presencial', 'virtual', 'mixta'])->comment('Modalidad de la asamblea');
            $table->dateTime('fecha_inicio')->comment('Fecha y hora de inicio de la asamblea');
            $table->dateTime('fecha_fin')->comment('Fecha y hora de fin de la asamblea');
            $table->enum('estado', ['programada', 'en_curso', 'finalizada', 'cancelada'])->default('programada')->comment('Estado de la asamblea');
            $table->decimal('quorum_minimo', 5, 2)->comment('Porcentaje mínimo de quorum requerido');
            $table->decimal('quorum_actual', 5, 2)->nullable()->comment('Porcentaje actual de quorum alcanzado');
            $table->string('url_transmision')->nullable()->comment('URL de transmisión en vivo');
            $table->enum('proveedor_transmision', ['daily', 'livekit', 'agora', 'twilio'])->nullable()->comment('Proveedor de transmisión');
            $table->text('token_transmision')->nullable()->comment('Token de acceso para transmisión');
            $table->foreignId('creado_por')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('Usuario que creó la asamblea');
            $table->boolean('activo')->default(true)->comment('Indica si la asamblea está activa');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('estado');
            $table->index('fecha_inicio');
            $table->index('creado_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asambleas');
    }
};
