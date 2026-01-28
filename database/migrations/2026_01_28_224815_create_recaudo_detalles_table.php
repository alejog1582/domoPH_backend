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
        Schema::create('recaudo_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recaudo_id')
                ->constrained('recaudos')
                ->onDelete('cascade')
                ->comment('ID del recaudo al que pertenece el detalle');

            $table->foreignId('cuenta_cobro_detalle_id')
                ->nullable()
                ->constrained('cuenta_cobro_detalles')
                ->nullOnDelete()
                ->comment('ID del detalle de cuenta de cobro al que se aplica el pago (nullable para abonos generales)');

            $table->string('concepto', 150)
                ->comment('Concepto al que se aplica el pago');

            $table->decimal('valor_aplicado', 14, 2)
                ->comment('Valor aplicado a este concepto');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('recaudo_id');
            $table->index('cuenta_cobro_detalle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recaudo_detalles');
    }
};
