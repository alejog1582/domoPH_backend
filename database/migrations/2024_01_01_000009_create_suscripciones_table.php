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
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propiedad_id')->constrained('propiedades')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('planes')->onDelete('restrict');
            $table->enum('tipo', ['mensual', 'anual'])->default('mensual');
            $table->enum('estado', ['activa', 'suspendida', 'cancelada', 'expirada'])->default('activa');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_proximo_pago')->nullable();
            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago')->nullable(); // stripe, paypal, transferencia, etc.
            $table->string('referencia_pago')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('renovacion_automatica')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('propiedad_id');
            $table->index('plan_id');
            $table->index('estado');
            $table->index('fecha_fin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suscripciones');
    }
};
