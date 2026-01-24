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
        Schema::create('propiedad_modulos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propiedad_id')->constrained('propiedades')->onDelete('cascade');
            $table->foreignId('modulo_id')->constrained('modulos')->onDelete('cascade');
            $table->boolean('activo')->default(true);
            $table->date('fecha_activacion')->nullable();
            $table->date('fecha_desactivacion')->nullable();
            $table->json('configuracion')->nullable(); // Configuración específica del módulo para esta propiedad
            $table->timestamps();
            
            $table->unique(['propiedad_id', 'modulo_id']);
            $table->index('propiedad_id');
            $table->index('modulo_id');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propiedad_modulos');
    }
};
