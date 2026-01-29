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
        Schema::create('acuerdos_pagos', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece el acuerdo de pago');

            $table->foreignId('unidad_id')
                ->constrained('unidades')
                ->onDelete('cascade')
                ->comment('ID de la unidad a la que pertenece el acuerdo de pago');

            $table->foreignId('cartera_id')
                ->constrained('carteras')
                ->onDelete('cascade')
                ->comment('ID de la cartera asociada al acuerdo de pago');

            $table->foreignId('cuenta_cobro_id')
                ->nullable()
                ->constrained('cuenta_cobros')
                ->nullOnDelete()
                ->comment('ID de la cuenta de cobro específica que originó el acuerdo (opcional)');

            // Información del acuerdo
            $table->string('numero_acuerdo', 50)
                ->comment('Número único del acuerdo de pago dentro de la copropiedad');

            $table->date('fecha_acuerdo')
                ->comment('Fecha en que se estableció el acuerdo de pago');

            $table->date('fecha_inicio')
                ->comment('Fecha de inicio del acuerdo de pago');

            $table->date('fecha_fin')
                ->nullable()
                ->comment('Fecha de finalización del acuerdo de pago (nullable si es indefinido)');

            $table->text('descripcion')
                ->nullable()
                ->comment('Descripción detallada del acuerdo de pago');

            // Valores financieros
            $table->decimal('saldo_original', 14, 2)
                ->comment('Saldo en mora al momento de establecer el acuerdo');

            $table->decimal('valor_acordado', 14, 2)
                ->comment('Valor total acordado a pagar en el acuerdo');

            $table->decimal('valor_inicial', 14, 2)
                ->default(0)
                ->comment('Valor de la cuota inicial del acuerdo (si aplica)');

            $table->decimal('saldo_pendiente', 14, 2)
                ->comment('Saldo restante pendiente del acuerdo de pago');

            $table->integer('numero_cuotas')
                ->default(1)
                ->comment('Número total de cuotas en que se divide el acuerdo');

            $table->decimal('valor_cuota', 14, 2)
                ->comment('Valor de cada cuota del acuerdo');

            $table->decimal('interes_acuerdo', 5, 2)
                ->default(0)
                ->comment('Porcentaje de interés aplicado al acuerdo (si aplica)');

            $table->decimal('valor_intereses', 14, 2)
                ->default(0)
                ->comment('Valor total de intereses calculados para el acuerdo');

            // Estado del acuerdo
            $table->enum('estado', ['pendiente', 'activo', 'cumplido', 'incumplido', 'cancelado'])
                ->default('pendiente')
                ->comment('Estado del acuerdo: pendiente, activo, cumplido, incumplido o cancelado');

            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si el acuerdo de pago está activo');

            // Auditoría
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('ID del usuario que registró el acuerdo de pago');

            $table->timestamps();
            $table->softDeletes();

            // Restricción única: número de acuerdo único por copropiedad
            $table->unique(['copropiedad_id', 'numero_acuerdo'], 'acuerdos_pagos_copropiedad_numero_unique');

            // Índices adicionales
            $table->index('copropiedad_id');
            $table->index('unidad_id');
            $table->index('cartera_id');
            $table->index('cuenta_cobro_id');
            $table->index('numero_acuerdo');
            $table->index('estado');
            $table->index('activo');
            $table->index('fecha_acuerdo');
            $table->index('fecha_inicio');
            $table->index('fecha_fin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acuerdos_pagos');
    }
};
