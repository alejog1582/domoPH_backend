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
        Schema::create('carteras', function (Blueprint $table) {
            $table->id();

            // Relación con la copropiedad
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece la cartera');

            // Relación con la unidad
            $table->foreignId('unidad_id')
                ->constrained('unidades')
                ->onDelete('cascade')
                ->comment('ID de la unidad a la que pertenece la cartera');

            // Saldos consolidados
            $table->decimal('saldo_total', 14, 2)
                ->default(0)
                ->comment('Saldo consolidado actual de la unidad (deuda o saldo a favor)');

            $table->decimal('saldo_mora', 14, 2)
                ->default(0)
                ->comment('Valor en mora');

            $table->decimal('saldo_corriente', 14, 2)
                ->default(0)
                ->comment('Valor no vencido');

            // Metadatos
            $table->timestamp('ultima_actualizacion')
                ->nullable()
                ->comment('Fecha y hora de la última actualización de la cartera');

            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si el registro de cartera está activo');

            $table->timestamps();
            $table->softDeletes();

            // Índice único para garantizar un solo registro de cartera por unidad en una copropiedad
            $table->unique(['copropiedad_id', 'unidad_id'], 'carteras_copropiedad_unidad_unique');

            // Índices adicionales
            $table->index('copropiedad_id');
            $table->index('unidad_id');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carteras');
    }
};
