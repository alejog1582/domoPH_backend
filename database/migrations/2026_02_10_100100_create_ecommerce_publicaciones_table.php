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
        Schema::create('ecommerce_publicaciones', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece la publicación');
            
            $table->foreignId('residente_id')
                ->constrained('residentes')
                ->onDelete('cascade')
                ->comment('ID del residente que publica el anuncio');
            
            $table->foreignId('categoria_id')
                ->constrained('ecommerce_categorias')
                ->onDelete('restrict')
                ->comment('ID de la categoría de la publicación');
            
            // Tipo de publicación
            $table->enum('tipo_publicacion', ['venta', 'arriendo', 'servicio', 'otro'])
                ->default('venta')
                ->comment('Tipo de publicación: venta, arriendo, servicio u otro');
            
            // Información del anuncio
            $table->string('titulo', 255)
                ->comment('Título de la publicación');
            $table->longText('descripcion')
                ->nullable()
                ->comment('Descripción detallada de la publicación');
            
            // Información de precio
            $table->decimal('precio', 15, 2)
                ->nullable()
                ->comment('Precio de venta, arriendo o servicio');
            $table->string('moneda', 3)
                ->default('COP')
                ->comment('Código de moneda (COP, USD, EUR, etc.)');
            $table->boolean('es_negociable')
                ->default(false)
                ->comment('Indica si el precio es negociable');
            
            // Estado de la publicación
            $table->enum('estado', ['publicado', 'pausado', 'finalizado'])
                ->default('publicado')
                ->comment('Estado actual de la publicación');
            
            // Fechas
            $table->dateTime('fecha_publicacion')
                ->comment('Fecha y hora en que se publicó el anuncio');
            $table->dateTime('fecha_cierre')
                ->nullable()
                ->comment('Fecha y hora en que se finalizó o cerró la publicación');
            
            // Control
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si la publicación está activa en el sistema');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('copropiedad_id');
            $table->index('residente_id');
            $table->index('categoria_id');
            $table->index('tipo_publicacion');
            $table->index('estado');
            $table->index('fecha_publicacion');
            $table->index('activo');
            $table->index(['copropiedad_id', 'estado', 'activo'], 'idx_ec_pub_coprop_est_act');
            $table->index(['copropiedad_id', 'categoria_id', 'activo'], 'idx_ec_pub_coprop_cat_act');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_publicaciones');
    }
};
