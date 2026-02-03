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
        Schema::create('depositos', function (Blueprint $table) {
            $table->id();

            // Relación con la copropiedad
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece el depósito');

            // Información del depósito
            $table->string('codigo', 50)
                ->comment('Identificador visible del depósito (Ej: D-03)');

            $table->string('nivel', 50)
                ->nullable()
                ->comment('Nivel o ubicación del depósito (sótano, piso, torre, etc.)');

            $table->enum('estado', ['disponible', 'asignado', 'en_mantenimiento', 'inhabilitado'])
                ->default('disponible')
                ->comment('Estado actual del depósito');

            $table->decimal('area_m2', 8, 2)
                ->nullable()
                ->comment('Área del depósito en metros cuadrados');

            $table->text('observaciones')
                ->nullable()
                ->comment('Observaciones adicionales sobre el depósito');

            // Asignación
            $table->foreignId('unidad_id')
                ->nullable()
                ->constrained('unidades')
                ->onDelete('set null')
                ->comment('ID de la unidad a la que está asignado el depósito (nullable si no está asignado)');

            $table->foreignId('residente_responsable_id')
                ->nullable()
                ->constrained('residentes')
                ->onDelete('set null')
                ->comment('ID del residente responsable del uso del depósito');

            $table->date('fecha_asignacion')
                ->nullable()
                ->comment('Fecha en que se asignó el depósito a la unidad/residente');

            // Auditoría
            $table->foreignId('creado_por')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('ID del usuario que creó el registro');

            // Control
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si el depósito está activo');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('estado');
            $table->index('unidad_id');
            $table->index('residente_responsable_id');
            $table->index('activo');

            // Clave única compuesta: código único por copropiedad
            $table->unique(['copropiedad_id', 'codigo'], 'idx_deposito_codigo_unico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depositos');
    }
};
