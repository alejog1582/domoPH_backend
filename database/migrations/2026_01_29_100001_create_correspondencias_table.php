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
        Schema::create('correspondencias', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece la correspondencia');

            $table->foreignId('unidad_id')
                ->constrained('unidades')
                ->onDelete('cascade')
                ->comment('ID de la unidad destinataria de la correspondencia');

            $table->foreignId('residente_id')
                ->nullable()
                ->constrained('residentes')
                ->nullOnDelete()
                ->comment('ID del residente destinatario específico (opcional)');

            // Información de la correspondencia
            $table->enum('tipo', ['paquete', 'documento', 'factura', 'domicilio', 'otro'])
                ->default('paquete')
                ->comment('Tipo de correspondencia: paquete, documento, factura, domicilio u otro');

            $table->string('descripcion', 255)
                ->nullable()
                ->comment('Descripción del contenido de la correspondencia');

            $table->string('remitente', 150)
                ->nullable()
                ->comment('Nombre o empresa remitente de la correspondencia');

            $table->string('numero_guia', 100)
                ->nullable()
                ->comment('Número de guía o tracking de la correspondencia');

            // Estado y fechas
            $table->enum('estado', ['recibido', 'entregado', 'devuelto', 'perdido'])
                ->default('recibido')
                ->comment('Estado actual de la correspondencia');

            $table->dateTime('fecha_recepcion')
                ->comment('Fecha y hora en que se recibió la correspondencia en portería');

            $table->dateTime('fecha_entrega')
                ->nullable()
                ->comment('Fecha y hora en que se entregó la correspondencia al destinatario');

            // Personal involucrado
            $table->string('recibido_por', 150)
                ->nullable()
                ->comment('Nombre de la persona que recibió la correspondencia en portería');

            $table->string('entregado_a', 150)
                ->nullable()
                ->comment('Nombre de la persona a quien se entregó la correspondencia');

            // Observaciones
            $table->text('observaciones')
                ->nullable()
                ->comment('Observaciones adicionales sobre la correspondencia');

            // Control
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si el registro de correspondencia está activo');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('unidad_id');
            $table->index('residente_id');
            $table->index('estado');
            $table->index('tipo');
            $table->index('fecha_recepcion');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correspondencias');
    }
};
