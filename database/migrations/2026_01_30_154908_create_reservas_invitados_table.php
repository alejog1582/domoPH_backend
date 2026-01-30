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
        Schema::create('reservas_invitados', function (Blueprint $table) {
            $table->id();

            // ============================================
            // 🔗 RELACIONES
            // ============================================
            $table->foreignId('reserva_id')
                ->constrained('reservas')
                ->onDelete('cascade')
                ->comment('ID de la reserva a la que pertenece el invitado');

            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad (para optimizar consultas)');

            $table->foreignId('residente_id')
                ->nullable()
                ->constrained('residentes')
                ->nullOnDelete()
                ->comment('ID del residente si el invitado es residente de la copropiedad (nullable para invitados externos)');

            // ============================================
            // 👤 DATOS DEL INVITADO
            // ============================================
            $table->string('nombre', 150)
                ->comment('Nombre completo del invitado');

            $table->string('documento', 50)
                ->nullable()
                ->comment('Número de documento de identidad del invitado');

            $table->string('telefono', 50)
                ->nullable()
                ->comment('Teléfono de contacto del invitado');

            // ============================================
            // 🚶 TIPO DE INVITADO
            // ============================================
            $table->enum('tipo', ['peatonal', 'vehicular'])
                ->default('peatonal')
                ->comment('Tipo de acceso del invitado: peatonal o vehicular');

            $table->string('placa', 20)
                ->nullable()
                ->comment('Placa del vehículo si el tipo es vehicular');

            // ============================================
            // ⚙️ CONTROL DE ACCESO
            // ============================================
            $table->enum('estado', ['registrado', 'autorizado', 'rechazado', 'ingresado', 'salido'])
                ->default('registrado')
                ->comment('Estado del invitado: registrado, autorizado, rechazado, ingresado o salido');

            $table->dateTime('fecha_ingreso')
                ->nullable()
                ->comment('Fecha y hora en que el invitado ingresó a la propiedad');

            $table->dateTime('fecha_salida')
                ->nullable()
                ->comment('Fecha y hora en que el invitado salió de la propiedad');

            // ============================================
            // 🧾 METADATOS
            // ============================================
            $table->text('observaciones')
                ->nullable()
                ->comment('Observaciones adicionales sobre el invitado');

            $table->timestamps();
            $table->softDeletes();

            // ============================================
            // 📊 ÍNDICES
            // ============================================
            $table->index('reserva_id');
            $table->index('copropiedad_id');
            $table->index('residente_id');
            $table->index('tipo');
            $table->index('estado');
            $table->index(['reserva_id', 'estado']);
            $table->index(['copropiedad_id', 'fecha_ingreso']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas_invitados');
    }
};
