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
        Schema::create('liquidacion_parqueaderos_visitantes', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('visita_id')
                ->comment('ID de la visita asociada');
            
            $table->foreign('visita_id', 'liq_parq_vis_visita_fk')
                ->references('id')
                ->on('visitas')
                ->onDelete('cascade');

            $table->foreignId('parqueadero_id')
                ->comment('ID del parqueadero ocupado');
            
            $table->foreign('parqueadero_id', 'liq_parq_vis_parq_fk')
                ->references('id')
                ->on('parqueaderos')
                ->onDelete('cascade');

            // Horarios
            $table->dateTime('hora_llegada')
                ->comment('Fecha y hora de llegada del vehículo al parqueadero');

            $table->dateTime('hora_salida')
                ->nullable()
                ->comment('Fecha y hora de salida del vehículo del parqueadero');

            // Cálculos de tiempo
            $table->integer('minutos_totales')
                ->default(0)
                ->comment('Total de minutos de estacionamiento');

            $table->integer('minutos_gracia')
                ->default(0)
                ->comment('Minutos de gracia aplicados (no cobrados)');

            $table->integer('minutos_cobrados')
                ->default(0)
                ->comment('Minutos totales menos minutos de gracia (minutos a cobrar)');

            // Valores monetarios
            $table->decimal('valor_minuto', 10, 2)
                ->default(0)
                ->comment('Valor por minuto aplicado al momento de la liquidación');

            $table->decimal('valor_total', 12, 2)
                ->default(0)
                ->comment('Valor total a cobrar (minutos_cobrados * valor_minuto)');

            // Estado y liquidación
            $table->enum('estado', ['en_curso', 'pagado'])
                ->default('en_curso')
                ->comment('Estado de la liquidación: en_curso o pagado');

            $table->date('fecha_liquidacion')
                ->nullable()
                ->comment('Fecha en que se realizó la liquidación/pago');

            $table->foreignId('usuario_liquidador_id')
                ->nullable()
                ->comment('ID del usuario que realizó la liquidación');
            
            $table->foreign('usuario_liquidador_id', 'liq_parq_vis_user_fk')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->enum('metodo_pago', ['efectivo', 'billetera_virtual'])
                ->nullable()
                ->comment('Método de pago utilizado: efectivo o billetera_virtual');

            // Observaciones
            $table->text('observaciones')
                ->nullable()
                ->comment('Observaciones adicionales sobre la liquidación');

            // Control
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si el registro está activo');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('visita_id');
            $table->index('parqueadero_id');
            $table->index('estado');
            $table->index('fecha_liquidacion');
            $table->index('usuario_liquidador_id');
            $table->index('hora_llegada');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidacion_parqueaderos_visitantes');
    }
};
