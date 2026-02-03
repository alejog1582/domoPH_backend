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
        Schema::create('sorteos_parqueadero', function (Blueprint $table) {
            $table->id();

            // Relación con la copropiedad
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece el sorteo');

            // Información del sorteo
            $table->string('titulo', 150)
                ->comment('Nombre o título del sorteo de parqueaderos');

            $table->text('descripcion')
                ->nullable()
                ->comment('Información adicional y detalles del sorteo');

            // Fechas del sorteo
            $table->date('fecha_inicio_recoleccion')
                ->comment('Fecha desde la cual los residentes pueden inscribirse al sorteo');

            $table->date('fecha_fin_recoleccion')
                ->comment('Fecha límite para que los residentes se inscriban al sorteo');

            $table->date('fecha_sorteo')
                ->comment('Fecha en que se realiza el sorteo');

            // Estado del sorteo
            $table->enum('estado', ['borrador', 'activo', 'cerrado', 'anulado'])
                ->default('borrador')
                ->comment('Estado del sorteo: borrador, activo, cerrado o anulado');

            // Auditoría
            $table->foreignId('creado_por')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('ID del usuario administrador que creó el sorteo');

            // Control
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si el sorteo está activo');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('estado');
            $table->index('fecha_inicio_recoleccion');
            $table->index('fecha_fin_recoleccion');
            $table->index('fecha_sorteo');
            $table->index('activo');
            $table->index('creado_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sorteos_parqueadero');
    }
};
