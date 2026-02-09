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
        Schema::create('ecommerce_publicacion_imagenes', function (Blueprint $table) {
            $table->id();
            
            // Relación con la publicación
            $table->foreignId('publicacion_id')
                ->constrained('ecommerce_publicaciones')
                ->onDelete('cascade')
                ->comment('ID de la publicación a la que pertenece la imagen');
            
            // Información de la imagen
            $table->string('ruta_imagen', 500)
                ->comment('Ruta o URL de la imagen');
            $table->integer('orden')
                ->default(0)
                ->comment('Orden de visualización de la imagen (0 = principal)');
            
            $table->timestamps();
            
            // Índices
            $table->index('publicacion_id');
            $table->index(['publicacion_id', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_publicacion_imagenes');
    }
};
