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
        Schema::create('llamados_atencion_historial', function (Blueprint $table) {
            $table->id();

            // Relación con el llamado de atención
            $table->foreignId('llamado_atencion_id')
                ->constrained('llamados_atencion')
                ->onDelete('cascade')
                ->comment('ID del llamado de atención al que pertenece este registro de historial');

            // Información del cambio
            $table->enum('estado_anterior', ['abierto', 'en_proceso', 'cerrado', 'anulado'])
                ->nullable()
                ->comment('Estado anterior del llamado de atención');

            $table->enum('estado_nuevo', ['abierto', 'en_proceso', 'cerrado', 'anulado'])
                ->comment('Estado nuevo del llamado de atención');

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
            $table->index('llamado_atencion_id');
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
        Schema::dropIfExists('llamados_atencion_historial');
    }
};
