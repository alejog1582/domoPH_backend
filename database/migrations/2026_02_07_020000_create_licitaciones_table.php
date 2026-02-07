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
        Schema::create('licitaciones', function (Blueprint $table) {
            $table->id()->comment('ID único del registro de licitación');
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad que publica la licitación');
            $table->string('titulo', 200)->comment('Título de la licitación');
            $table->text('descripcion')->comment('Descripción detallada de la necesidad');
            $table->enum('categoria', ['mantenimiento', 'seguridad', 'servicios', 'obra_civil', 'tecnologia', 'otro'])
                ->comment('Categoría de la licitación');
            $table->decimal('presupuesto_estimado', 15, 2)->nullable()->comment('Presupuesto estimado para la licitación');
            $table->date('fecha_publicacion')->nullable()->comment('Fecha en que se publica la licitación');
            $table->date('fecha_cierre')->comment('Fecha de cierre para recibir ofertas');
            $table->enum('estado', ['borrador', 'publicada', 'cerrada', 'adjudicada', 'anulada'])
                ->default('borrador')
                ->comment('Estado de la licitación');
            $table->boolean('visible_publicamente')->default(true)->comment('Indica si la licitación es visible públicamente');
            $table->foreignId('creado_por')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID del usuario que creó la licitación');
            $table->boolean('activo')->default(true)->comment('Indica si la licitación está activa');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('copropiedad_id', 'idx_licitaciones_copropiedad');
            $table->index('categoria', 'idx_licitaciones_categoria');
            $table->index('estado', 'idx_licitaciones_estado');
            $table->index('fecha_publicacion', 'idx_licitaciones_fecha_publicacion');
            $table->index('fecha_cierre', 'idx_licitaciones_fecha_cierre');
            $table->index('activo', 'idx_licitaciones_activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licitaciones');
    }
};
