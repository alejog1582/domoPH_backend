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
        Schema::create('solicitud_seguimientos', function (Blueprint $table) {
            $table->id()->comment('ID único del seguimiento');
            $table->foreignId('solicitud_comercial_id')
                ->constrained('solicitudes_comerciales')
                ->onDelete('cascade')
                ->comment('ID de la solicitud comercial');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID del usuario que realizó el seguimiento');
            $table->text('comentario')->comment('Comentario o nota del seguimiento');
            $table->enum('estado_resultante', ['pendiente', 'en_proceso', 'contactado', 'cerrado_ganado', 'cerrado_perdido'])
                ->nullable()
                ->comment('Estado resultante después del seguimiento (opcional)');
            $table->dateTime('proximo_contacto')->nullable()->comment('Fecha programada para próximo contacto');
            $table->timestamps();
            
            // Índices
            $table->index('solicitud_comercial_id', 'idx_sol_seg_solicitud');
            $table->index('user_id', 'idx_sol_seg_user');
            $table->index('proximo_contacto', 'idx_sol_seg_prox_contacto');
            $table->index('created_at', 'idx_sol_seg_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_seguimientos');
    }
};
