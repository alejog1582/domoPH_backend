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
        Schema::create('pqrs_historial', function (Blueprint $table) {
            $table->id();

            // Relación con la PQRS
            $table->foreignId('pqrs_id')
                ->constrained('pqrs')
                ->onDelete('cascade')
                ->comment('ID de la PQRS a la que pertenece este registro de historial');

            // Información del cambio
            $table->enum('estado_anterior', ['radicada', 'en_proceso', 'respondida', 'cerrada', 'rechazada'])
                ->nullable()
                ->comment('Estado anterior de la PQRS');

            $table->enum('estado_nuevo', ['radicada', 'en_proceso', 'respondida', 'cerrada', 'rechazada'])
                ->comment('Estado nuevo de la PQRS');

            $table->text('comentario')
                ->nullable()
                ->comment('Comentario sobre el cambio de estado');

            // Auditoría
            $table->foreignId('cambiado_por')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID del usuario que realizó el cambio de estado');

            $table->dateTime('fecha_cambio')
                ->comment('Fecha y hora en que se realizó el cambio de estado');

            $table->timestamps();

            // Índices
            $table->index('pqrs_id');
            $table->index('estado_anterior');
            $table->index('estado_nuevo');
            $table->index('fecha_cambio');
            $table->index('cambiado_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pqrs_historial');
    }
};
