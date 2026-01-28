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
        Schema::create('zona_social_reglas', function (Blueprint $table) {
            $table->id();
            
            // Relación con la zona social
            $table->foreignId('zona_social_id')
                ->constrained('zonas_sociales')
                ->onDelete('cascade')
                ->comment('ID de la zona social a la que pertenece la regla');
            
            // Información de la regla
            $table->string('clave', 100)
                ->comment('Clave identificadora de la regla, ej: "max_reservas_mes", "requiere_deposito", "bloquear_en_mora"');
            $table->string('valor', 255)
                ->comment('Valor de la regla (puede ser texto, número, booleano como string)');
            $table->string('descripcion', 255)
                ->nullable()
                ->comment('Descripción opcional de la regla');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('zona_social_id');
            $table->index(['zona_social_id', 'clave']);
            
            // Índice único para evitar reglas duplicadas con la misma clave
            $table->unique(['zona_social_id', 'clave'], 'unique_regla_zona');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zona_social_reglas');
    }
};
