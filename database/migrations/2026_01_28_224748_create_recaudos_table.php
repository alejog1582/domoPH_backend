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
        Schema::create('recaudos', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece el recaudo');

            $table->foreignId('unidad_id')
                ->constrained('unidades')
                ->onDelete('cascade')
                ->comment('ID de la unidad a la que pertenece el recaudo');

            $table->foreignId('cuenta_cobro_id')
                ->nullable()
                ->constrained('cuenta_cobros')
                ->nullOnDelete()
                ->comment('ID de la cuenta de cobro asociada (nullable para abonos sin cuenta específica)');

            // Información del pago
            $table->string('numero_recaudo', 50)
                ->unique()
                ->comment('Consecutivo o referencia única del pago');

            $table->dateTime('fecha_pago')
                ->comment('Fecha y hora en que se realizó el pago');

            $table->enum('tipo_pago', ['parcial', 'total', 'anticipo'])
                ->default('parcial')
                ->comment('Tipo de pago: parcial, total o anticipo');

            $table->enum('medio_pago', ['efectivo', 'transferencia', 'consignacion', 'tarjeta', 'pse', 'otro'])
                ->default('efectivo')
                ->comment('Medio de pago utilizado');

            $table->string('referencia_pago', 100)
                ->nullable()
                ->comment('Referencia o número de transacción del pago');

            $table->string('descripcion', 255)
                ->nullable()
                ->comment('Descripción adicional del recaudo');

            // Valores del pago
            $table->decimal('valor_pagado', 14, 2)
                ->comment('Valor total pagado en este recaudo');

            // Estado y control
            $table->enum('estado', ['registrado', 'aplicado', 'anulado'])
                ->default('registrado')
                ->comment('Estado del recaudo: registrado, aplicado o anulado');

            $table->foreignId('registrado_por')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('ID del usuario que registró el recaudo');

            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si el recaudo está activo');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('unidad_id');
            $table->index('cuenta_cobro_id');
            $table->index('numero_recaudo');
            $table->index('fecha_pago');
            $table->index('estado');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recaudos');
    }
};
