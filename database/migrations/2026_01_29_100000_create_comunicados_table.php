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
        Schema::create('comunicados', function (Blueprint $table) {
            $table->id();

            // Relación con la copropiedad
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece el comunicado');

            // Información del comunicado
            $table->string('titulo', 200)
                ->comment('Título del comunicado');
            
            $table->string('slug', 220)
                ->comment('Slug único del comunicado para URLs amigables');
            
            $table->text('contenido')
                ->comment('Contenido completo del comunicado');
            
            $table->string('resumen')
                ->nullable()
                ->comment('Resumen o extracto del comunicado');
            
            // Tipo y visibilidad
            $table->enum('tipo', ['general', 'urgente', 'informativo', 'mantenimiento'])
                ->default('general')
                ->comment('Tipo de comunicado: general, urgente, informativo o mantenimiento');
            
            $table->boolean('publicado')
                ->default(false)
                ->comment('Indica si el comunicado está publicado');
            
            $table->dateTime('fecha_publicacion')
                ->nullable()
                ->comment('Fecha y hora de publicación del comunicado');
            
            $table->enum('visible_para', ['todos', 'propietarios', 'arrendatarios', 'administracion'])
                ->default('todos')
                ->comment('Audiencia a la que va dirigido el comunicado');
            
            // Multimedia
            $table->string('imagen_portada')
                ->nullable()
                ->comment('Ruta de la imagen de portada del comunicado');
            
            // Autor
            $table->foreignId('autor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('ID del usuario autor del comunicado');
            
            // Control
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si el comunicado está activo');

            $table->timestamps();
            $table->softDeletes();

            // Unicidad: slug único por copropiedad
            $table->unique(['copropiedad_id', 'slug'], 'comunicados_copropiedad_slug_unique');

            // Índices adicionales
            $table->index('copropiedad_id');
            $table->index('tipo');
            $table->index('publicado');
            $table->index('fecha_publicacion');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunicados');
    }
};
