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
        Schema::create('cuenta_cobros', function (Blueprint $table) {
            $table->id();

            // Relación con la copropiedad
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece la cuenta de cobro');

            // Relación con la unidad
            $table->foreignId('unidad_id')
                ->constrained('unidades')
                ->onDelete('cascade')
                ->comment('ID de la unidad a la que pertenece la cuenta de cobro');

            // Periodo de la cuenta de cobro (YYYY-MM)
            $table->string('periodo', 7)
                ->comment('Periodo de facturación en formato YYYY-MM (ej: 2026-01)');

            // Fechas
            $table->date('fecha_emision')
                ->comment('Fecha de emisión de la cuenta de cobro');
            $table->date('fecha_vencimiento')
                ->nullable()
                ->comment('Fecha de vencimiento de la cuenta de cobro');

            // Valores
            $table->decimal('valor_cuotas', 14, 2)
                ->default(0)
                ->comment('Valor total de cuotas ordinarias y extraordinarias');
            $table->decimal('valor_intereses', 14, 2)
                ->default(0)
                ->comment('Valor de intereses generados');
            $table->decimal('valor_descuentos', 14, 2)
                ->default(0)
                ->comment('Valor total de descuentos aplicados');
            $table->decimal('valor_recargos', 14, 2)
                ->default(0)
                ->comment('Valor total de recargos aplicados');
            $table->decimal('valor_total', 14, 2)
                ->comment('Valor total a pagar = valor_cuotas + intereses + recargos - descuentos');

            // Estado
            $table->enum('estado', ['pendiente', 'pagada', 'vencida', 'anulada'])
                ->default('pendiente')
                ->comment('Estado de la cuenta de cobro');

            $table->text('observaciones')
                ->nullable()
                ->comment('Observaciones adicionales de la cuenta de cobro');

            $table->timestamps();
            $table->softDeletes();

            // Unicidad: una cuenta de cobro por unidad y periodo en una copropiedad
            $table->unique(['copropiedad_id', 'unidad_id', 'periodo'], 'cuentas_cobro_coprop_unidad_periodo_unique');

            // Índices adicionales
            $table->index('copropiedad_id');
            $table->index('unidad_id');
            $table->index('periodo');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuenta_cobros');
    }
};
