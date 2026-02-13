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
        Schema::create('comunicacion_cobranza_envios', function (Blueprint $table) {
            $table->id();

            // Relación con la comunicación de cobranza
            $table->foreignId('comunicacion_cobranza_id')
                ->constrained('comunicaciones_cobranza')
                ->onDelete('cascade')
                ->comment('ID de la comunicación de cobranza que generó este envío');

            // Relaciones con entidades
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad (para optimizar consultas)');

            $table->foreignId('unidad_id')
                ->constrained('unidades')
                ->onDelete('cascade')
                ->comment('ID de la unidad destinataria');

            $table->foreignId('residente_id')
                ->constrained('residentes')
                ->onDelete('cascade')
                ->comment('ID del residente destinatario');

            // Datos financieros al momento del envío (auditoría histórica)
            $table->decimal('saldo', 12, 2)
                ->comment('Saldo pendiente al momento del envío');

            $table->unsignedInteger('dias_mora')
                ->comment('Días de mora al momento del envío');

            // Canal utilizado
            $table->enum('canal', ['email', 'whatsapp'])
                ->comment('Canal utilizado para este envío específico');

            // Datos de envío
            $table->string('email_destino', 150)
                ->nullable()
                ->comment('Email destino (si el canal es email)');

            $table->string('telefono_destino', 50)
                ->nullable()
                ->comment('Teléfono destino (si el canal es WhatsApp)');

            // Estado del envío
            $table->enum('estado', ['pendiente', 'enviado', 'fallido'])
                ->default('pendiente')
                ->comment('Estado del envío: pendiente, enviado o fallido');

            $table->timestamp('fecha_envio')
                ->nullable()
                ->comment('Fecha y hora en que se realizó el envío');

            $table->text('respuesta_proveedor')
                ->nullable()
                ->comment('Respuesta del servicio externo (WhatsApp API, SMTP, etc.)');

            $table->text('error')
                ->nullable()
                ->comment('Mensaje de error si el envío falló');

            $table->timestamps();

            // Índices
            $table->index('comunicacion_cobranza_id');
            $table->index('copropiedad_id');
            $table->index('unidad_id');
            $table->index('residente_id');
            $table->index('estado');
            $table->index('fecha_envio');
            $table->index(['copropiedad_id', 'estado']);
            $table->index(['unidad_id', 'fecha_envio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunicacion_cobranza_envios');
    }
};
