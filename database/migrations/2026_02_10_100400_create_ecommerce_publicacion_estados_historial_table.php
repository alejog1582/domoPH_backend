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
        Schema::create('ecommerce_publicacion_estados_historial', function (Blueprint $table) {
            $table->id();
            
            // Relación con la publicación
            $table->foreignId('publicacion_id')
                ->constrained('ecommerce_publicaciones')
                ->onDelete('cascade')
                ->comment('ID de la publicación cuyo estado cambió');
            
            // Información del cambio de estado
            $table->enum('estado_anterior', ['publicado', 'pausado', 'finalizado'])
                ->nullable()
                ->comment('Estado anterior de la publicación');
            $table->enum('estado_nuevo', ['publicado', 'pausado', 'finalizado'])
                ->comment('Nuevo estado de la publicación');
            
            // Información de quién hizo el cambio
            $table->foreignId('cambiado_por')
                ->nullable()
                ->constrained('residentes')
                ->onDelete('set null')
                ->comment('ID del residente que realizó el cambio de estado');
            
            // Información adicional
            $table->text('comentario')
                ->nullable()
                ->comment('Comentario opcional sobre el cambio de estado');
            $table->dateTime('fecha_cambio')
                ->comment('Fecha y hora en que se realizó el cambio de estado');
            
            $table->timestamps();
            
            // Índices
            $table->index('publicacion_id');
            $table->index('cambiado_por');
            $table->index('fecha_cambio');
            $table->index(['publicacion_id', 'fecha_cambio'], 'idx_ec_pub_est_hist_pub_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_publicacion_estados_historial');
    }
};
