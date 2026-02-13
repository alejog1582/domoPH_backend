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
        Schema::create('comunicaciones_cobranza', function (Blueprint $table) {
            $table->id();

            // Relación con copropiedad
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece la comunicación');

            // Información básica
            $table->string('nombre', 150)
                ->comment('Nombre de la comunicación (ej: Recordatorio preventivo, Cobranza mora 30 días)');

            $table->text('descripcion')
                ->nullable()
                ->comment('Descripción adicional de la comunicación');

            // Canal de envío
            $table->enum('canal', ['email', 'whatsapp', 'ambos'])
                ->default('email')
                ->comment('Canal de envío: email, whatsapp o ambos');

            // Configuración de fecha de envío
            $table->unsignedTinyInteger('dia_envio_mes')
                ->comment('Día del mes en que se ejecuta el envío automático (1-31)');

            // Filtro por rango de mora
            $table->unsignedInteger('dias_mora_desde')
                ->comment('Días de mora desde (0 = preventivo)');

            $table->unsignedInteger('dias_mora_hasta')
                ->nullable()
                ->comment('Días de mora hasta (NULL = sin límite superior)');

            // Contenido del mensaje
            $table->string('asunto', 255)
                ->nullable()
                ->comment('Asunto del email (solo para canal email)');

            $table->longText('mensaje_email')
                ->nullable()
                ->comment('Mensaje para email con variables dinámicas');

            $table->longText('mensaje_whatsapp')
                ->nullable()
                ->comment('Mensaje para WhatsApp con variables dinámicas');

            // Configuración adicional
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si la comunicación está activa');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('activo');
            $table->index('dia_envio_mes');
            $table->index(['dias_mora_desde', 'dias_mora_hasta']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunicaciones_cobranza');
    }
};
