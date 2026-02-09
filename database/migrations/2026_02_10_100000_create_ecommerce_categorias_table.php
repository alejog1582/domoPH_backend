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
        Schema::create('ecommerce_categorias', function (Blueprint $table) {
            $table->id();
            
            // Información básica
            $table->string('nombre', 100)
                ->comment('Nombre de la categoría, ej: "Parqueaderos", "Servicios"');
            $table->string('slug', 100)
                ->unique()
                ->comment('Slug único para la categoría (URL-friendly)');
            $table->text('descripcion')
                ->nullable()
                ->comment('Descripción de la categoría');
            $table->string('icono', 100)
                ->nullable()
                ->comment('Nombre del icono o clase CSS para el icono de la categoría');
            
            // Control
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si la categoría está activa y visible');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('slug');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_categorias');
    }
};
