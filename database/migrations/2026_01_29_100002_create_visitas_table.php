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
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad donde se registra la visita');

            $table->foreignId('unidad_id')
                ->constrained('unidades')
                ->onDelete('cascade')
                ->comment('ID de la unidad visitada');

            $table->foreignId('residente_id')
                ->nullable()
                ->constrained('residentes')
                ->nullOnDelete()
                ->comment('ID del residente que recibe la visita (opcional)');

            // Información del visitante
            $table->string('nombre_visitante', 150)
                ->comment('Nombre completo del visitante');

            $table->string('documento_visitante', 50)
                ->nullable()
                ->comment('Número de documento de identidad del visitante');

            // Tipo de visita
            $table->enum('tipo_visita', ['peatonal', 'vehicular'])
                ->default('peatonal')
                ->comment('Tipo de visita: peatonal o vehicular');

            $table->string('placa_vehiculo', 20)
                ->nullable()
                ->comment('Placa del vehículo (si aplica)');

            // Motivo y fechas
            $table->string('motivo', 200)
                ->nullable()
                ->comment('Motivo de la visita');

            $table->dateTime('fecha_ingreso')
                ->comment('Fecha y hora de ingreso del visitante');

            $table->dateTime('fecha_salida')
                ->nullable()
                ->comment('Fecha y hora de salida del visitante');

            // Estado
            $table->enum('estado', ['activa', 'finalizada', 'cancelada', 'bloqueada', 'programada'])
                ->default('activa')
                ->comment('Estado de la visita: activa, finalizada, cancelada o bloqueada');

            // Registro
            $table->foreignId('registrada_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('ID del usuario que registró la visita');

            // Observaciones
            $table->text('observaciones')
                ->nullable()
                ->comment('Observaciones adicionales sobre la visita');

            // Control
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si el registro de visita está activo');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('unidad_id');
            $table->index('residente_id');
            $table->index('tipo_visita');
            $table->index('estado');
            $table->index('fecha_ingreso');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};
