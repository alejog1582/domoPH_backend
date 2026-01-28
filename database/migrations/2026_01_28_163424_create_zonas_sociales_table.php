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
        Schema::create('zonas_sociales', function (Blueprint $table) {
            $table->id();
            
            // Relación con la propiedad (copropiedad)
            $table->foreignId('propiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la propiedad a la que pertenece la zona social');
            
            // Información básica
            $table->string('nombre', 150)
                ->comment('Nombre de la zona social, ej: "Salón Social Torre 4"');
            $table->text('descripcion')
                ->nullable()
                ->comment('Descripción detallada de la zona social');
            $table->string('ubicacion', 150)
                ->nullable()
                ->comment('Ubicación física: Torre, bloque o piso');
            
            // Capacidad y límites
            $table->integer('capacidad_maxima')
                ->comment('Capacidad máxima de personas que puede albergar la zona');
            $table->integer('max_invitados_por_reserva')
                ->nullable()
                ->comment('Número máximo de invitados permitidos por reserva');
            
            // Tiempos de uso
            $table->integer('tiempo_minimo_uso_horas')
                ->default(1)
                ->comment('Tiempo mínimo de uso en horas');
            $table->integer('tiempo_maximo_uso_horas')
                ->default(14)
                ->comment('Tiempo máximo de uso en horas');
            
            // Configuración de reservas
            $table->integer('reservas_simultaneas')
                ->default(1)
                ->comment('Número de reservas simultáneas permitidas');
            
            // Valores económicos
            $table->decimal('valor_alquiler', 12, 2)
                ->nullable()
                ->comment('Valor del alquiler de la zona social');
            $table->decimal('valor_deposito', 12, 2)
                ->nullable()
                ->comment('Valor del depósito requerido');
            
            // Configuraciones de aprobación y mora
            $table->boolean('requiere_aprobacion')
                ->default(false)
                ->comment('Indica si las reservas requieren aprobación del administrador');
            $table->boolean('permite_reservas_en_mora')
                ->default(false)
                ->comment('Indica si se permiten reservas a residentes en mora');
            
            // Documentación
            $table->string('reglamento_url', 255)
                ->nullable()
                ->comment('URL del reglamento de uso de la zona social');
            
            // Estado
            $table->enum('estado', ['activa', 'inactiva', 'mantenimiento'])
                ->default('activa')
                ->comment('Estado actual de la zona social');
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si la zona social está activa en el sistema');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('propiedad_id');
            $table->index('estado');
            $table->index('activo');
            $table->index(['propiedad_id', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zonas_sociales');
    }
};
