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
        Schema::create('zona_social_imagenes', function (Blueprint $table) {
            $table->id();
            
            // Relación con la zona social
            $table->foreignId('zona_social_id')
                ->constrained('zonas_sociales')
                ->onDelete('cascade')
                ->comment('ID de la zona social a la que pertenece la imagen');
            
            // Información de la imagen
            $table->string('url_imagen', 255)
                ->comment('URL de la imagen almacenada (Cloudinary, S3, etc.)');
            $table->integer('orden')
                ->default(0)
                ->comment('Orden de visualización de la imagen (para galería)');
            
            // Estado
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si la imagen está activa y visible');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('zona_social_id');
            $table->index(['zona_social_id', 'activo']);
            $table->index(['zona_social_id', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zona_social_imagenes');
    }
};
