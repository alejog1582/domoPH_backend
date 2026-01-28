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
        Schema::create('cuenta_cobro_detalles', function (Blueprint $table) {
            $table->id();

            // Relación con la cuenta de cobro
            $table->foreignId('cuenta_cobro_id')
                ->constrained('cuenta_cobros')
                ->onDelete('cascade')
                ->comment('ID de la cuenta de cobro a la que pertenece el detalle');

            $table->string('concepto', 150)
                ->comment('Concepto del cobro (cuota ordinaria, extraordinaria, multa, interés, etc.)');

            // Relación opcional con la cuota de administración origen del concepto
            $table->foreignId('cuota_administracion_id')
                ->nullable()
                ->constrained('cuotas_administracion')
                ->nullOnDelete()
                ->comment('ID de la cuota de administración relacionada con el detalle (si aplica)');

            $table->decimal('valor', 14, 2)
                ->comment('Valor del concepto en la cuenta de cobro');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('cuenta_cobro_id');
            $table->index('cuota_administracion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuenta_cobro_detalles');
    }
};
