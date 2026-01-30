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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();

            // ============================================
            // 🔗 RELACIONES PRINCIPALES
            // ============================================
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece la reserva');

            $table->foreignId('unidad_id')
                ->constrained('unidades')
                ->onDelete('cascade')
                ->comment('ID de la unidad que realiza la reserva');

            $table->foreignId('residente_id')
                ->nullable()
                ->constrained('residentes')
                ->nullOnDelete()
                ->comment('ID del residente que realiza la reserva (nullable si es un invitado externo)');

            $table->foreignId('zona_social_id')
                ->constrained('zonas_sociales')
                ->onDelete('cascade')
                ->comment('ID de la zona social que se está reservando');

            // ============================================
            // 👤 INFORMACIÓN DEL SOLICITANTE
            // ============================================
            $table->string('nombre_solicitante', 150)
                ->comment('Nombre completo de la persona que solicita la reserva');

            $table->string('telefono_solicitante', 50)
                ->nullable()
                ->comment('Teléfono de contacto del solicitante');

            $table->string('email_solicitante', 150)
                ->nullable()
                ->comment('Correo electrónico del solicitante');

            // ============================================
            // 📅 INFORMACIÓN DE LA RESERVA
            // ============================================
            $table->date('fecha_reserva')
                ->comment('Fecha en la que se realizará la reserva');

            $table->time('hora_inicio')
                ->comment('Hora de inicio de la reserva');

            $table->time('hora_fin')
                ->comment('Hora de finalización de la reserva');

            $table->integer('duracion_minutos')
                ->nullable()
                ->comment('Duración total de la reserva en minutos (calculado automáticamente)');

            $table->integer('cantidad_invitados')
                ->default(0)
                ->comment('Cantidad de invitados que asistirán a la reserva');

            $table->text('descripcion')
                ->nullable()
                ->comment('Descripción o motivo de la reserva');

            // ============================================
            // 💰 INFORMACIÓN ECONÓMICA
            // ============================================
            $table->decimal('costo_reserva', 14, 2)
                ->default(0)
                ->comment('Costo total de la reserva');

            $table->decimal('deposito_garantia', 14, 2)
                ->default(0)
                ->comment('Valor del depósito de garantía requerido');

            $table->boolean('requiere_pago')
                ->default(false)
                ->comment('Indica si la reserva requiere pago');

            $table->enum('estado_pago', ['pendiente', 'pagado', 'exento', 'reembolsado'])
                ->default('pendiente')
                ->comment('Estado del pago: pendiente, pagado, exento o reembolsado');

            // ============================================
            // ⚙️ CONTROL Y VALIDACIÓN
            // ============================================
            $table->enum('estado', ['solicitada', 'aprobada', 'rechazada', 'cancelada', 'finalizada'])
                ->default('solicitada')
                ->comment('Estado de la reserva: solicitada, aprobada, rechazada, cancelada o finalizada');

            $table->foreignId('aprobada_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('ID del usuario administrador que aprobó la reserva');

            $table->dateTime('fecha_aprobacion')
                ->nullable()
                ->comment('Fecha y hora en que se aprobó la reserva');

            $table->text('motivo_rechazo')
                ->nullable()
                ->comment('Motivo por el cual se rechazó la reserva');

            $table->text('motivo_cancelacion')
                ->nullable()
                ->comment('Motivo por el cual se canceló la reserva');

            // ============================================
            // 🚨 REGLAS Y CONTROL
            // ============================================
            $table->boolean('es_exclusiva')
                ->default(true)
                ->comment('Indica si la reserva es exclusiva (no permite otras reservas simultáneas)');

            $table->boolean('permite_invitados')
                ->default(true)
                ->comment('Indica si la reserva permite invitados');

            $table->boolean('incumplimiento')
                ->default(false)
                ->comment('Indica si hubo incumplimiento de las reglas durante la reserva');

            $table->text('observaciones_admin')
                ->nullable()
                ->comment('Observaciones internas del administrador sobre la reserva');

            // ============================================
            // 📎 EVIDENCIAS
            // ============================================
            $table->json('adjuntos')
                ->nullable()
                ->comment('Archivos adjuntos relacionados con la reserva (contratos, comprobantes, etc.) en formato JSON');

            // ============================================
            // 🧾 METADATOS
            // ============================================
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si la reserva está activa en el sistema');

            $table->timestamps();
            $table->softDeletes();

            // ============================================
            // 📊 ÍNDICES
            // ============================================
            $table->index('copropiedad_id');
            $table->index('unidad_id');
            $table->index('residente_id');
            $table->index('zona_social_id');
            $table->index('fecha_reserva');
            $table->index('estado');
            $table->index('estado_pago');
            $table->index('aprobada_por');
            $table->index('activo');
            $table->index(['copropiedad_id', 'fecha_reserva']);
            $table->index(['zona_social_id', 'fecha_reserva', 'estado']);
            $table->index(['unidad_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
