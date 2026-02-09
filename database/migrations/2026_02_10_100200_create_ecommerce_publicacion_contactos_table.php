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
        Schema::create('ecommerce_publicacion_contactos', function (Blueprint $table) {
            $table->id();
            
            // Relación con la publicación
            $table->foreignId('publicacion_id')
                ->constrained('ecommerce_publicaciones')
                ->onDelete('cascade')
                ->comment('ID de la publicación a la que pertenece el contacto');
            
            // Información de contacto
            $table->string('nombre_contacto', 150)
                ->comment('Nombre de la persona de contacto');
            $table->string('telefono', 20)
                ->comment('Número de teléfono de contacto');
            $table->boolean('whatsapp')
                ->default(false)
                ->comment('Indica si el teléfono tiene WhatsApp disponible');
            $table->string('email', 150)
                ->nullable()
                ->comment('Email de contacto (opcional)');
            $table->text('observaciones')
                ->nullable()
                ->comment('Observaciones adicionales sobre el método de contacto');
            
            $table->timestamps();
            
            // Índices
            $table->index('publicacion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_publicacion_contactos');
    }
};
