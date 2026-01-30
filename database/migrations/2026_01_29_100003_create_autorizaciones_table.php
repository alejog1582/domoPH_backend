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
        Schema::create('autorizaciones', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad donde se otorga la autorización');

            $table->foreignId('unidad_id')
                ->nullable()
                ->constrained('unidades')
                ->nullOnDelete()
                ->comment('ID de la unidad específica (nullable para autorizaciones generales)');

            $table->foreignId('residente_id')
                ->nullable()
                ->constrained('residentes')
                ->nullOnDelete()
                ->comment('ID del residente que otorga la autorización (opcional)');

            // Información de la persona autorizada
            $table->string('nombre_autorizado', 150)
                ->comment('Nombre completo de la persona autorizada');

            $table->string('documento_autorizado', 50)
                ->nullable()
                ->comment('Número de documento de identidad de la persona autorizada');

            // Tipo de autorización
            $table->enum('tipo_autorizado', ['familiar', 'empleado', 'aseo', 'mantenimiento', 'proveedor', 'otro'])
                ->default('otro')
                ->comment('Tipo de persona autorizada: familiar, empleado, aseo, mantenimiento, proveedor u otro');

            $table->enum('tipo_acceso', ['peatonal', 'vehicular', 'ambos'])
                ->default('peatonal')
                ->comment('Tipo de acceso autorizado: peatonal, vehicular o ambos');

            $table->string('placa_vehiculo', 20)
                ->nullable()
                ->comment('Placa del vehículo autorizado (si aplica)');

            // Horarios y días
            $table->json('dias_autorizados')
                ->nullable()
                ->comment('Días de la semana autorizados (ej: ["lunes","martes","miercoles"])');

            $table->time('hora_desde')
                ->nullable()
                ->comment('Hora de inicio del horario autorizado');

            $table->time('hora_hasta')
                ->nullable()
                ->comment('Hora de fin del horario autorizado');

            // Fechas de vigencia
            $table->date('fecha_inicio')
                ->nullable()
                ->comment('Fecha de inicio de vigencia de la autorización');

            $table->date('fecha_fin')
                ->nullable()
                ->comment('Fecha de fin de vigencia de la autorización');

            // Estado
            $table->enum('estado', ['activa', 'vencida', 'suspendida'])
                ->default('activa')
                ->comment('Estado de la autorización: activa, vencida o suspendida');

            // Observaciones
            $table->text('observaciones')
                ->nullable()
                ->comment('Observaciones adicionales sobre la autorización');

            // Control
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si la autorización está activa');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('unidad_id');
            $table->index('residente_id');
            $table->index('tipo_autorizado');
            $table->index('estado');
            $table->index('fecha_inicio');
            $table->index('fecha_fin');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autorizaciones');
    }
};
