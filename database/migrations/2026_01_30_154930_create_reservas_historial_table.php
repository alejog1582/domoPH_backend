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
        Schema::create('reservas_historial', function (Blueprint $table) {
            $table->id();

            // ============================================
            // 🔗 RELACIONES
            // ============================================
            $table->foreignId('reserva_id')
                ->constrained('reservas')
                ->onDelete('cascade')
                ->comment('ID de la reserva a la que pertenece este registro de historial');

            // ============================================
            // 📝 INFORMACIÓN DEL CAMBIO
            // ============================================
            $table->string('estado_anterior', 50)
                ->nullable()
                ->comment('Estado anterior de la reserva antes del cambio');

            $table->string('estado_nuevo', 50)
                ->comment('Nuevo estado de la reserva después del cambio');

            $table->text('comentario')
                ->nullable()
                ->comment('Comentario o nota sobre el cambio realizado');

            // ============================================
            // 👤 AUDITORÍA
            // ============================================
            $table->foreignId('cambiado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('ID del usuario que realizó el cambio');

            $table->dateTime('fecha_cambio')
                ->comment('Fecha y hora en que se realizó el cambio');

            $table->timestamps();

            // ============================================
            // 📊 ÍNDICES
            // ============================================
            $table->index('reserva_id');
            $table->index('cambiado_por');
            $table->index('fecha_cambio');
            $table->index(['reserva_id', 'fecha_cambio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas_historial');
    }
};
