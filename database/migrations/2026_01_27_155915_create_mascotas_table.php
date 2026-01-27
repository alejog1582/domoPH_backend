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
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('unidad_id')->constrained('unidades')->onDelete('cascade');
            $table->foreignId('residente_id')->constrained('residentes')->onDelete('cascade');
            
            // Información básica
            $table->string('nombre', 100); // Nombre de la mascota
            $table->enum('tipo', ['perro', 'gato', 'ave', 'reptil', 'roedor', 'otro']); // Tipo de mascota
            $table->string('raza', 100)->nullable(); // Raza de la mascota
            $table->string('color', 100)->nullable(); // Color predominante
            $table->enum('sexo', ['macho', 'hembra', 'desconocido']); // Sexo de la mascota
            
            // Información de edad y tamaño
            $table->date('fecha_nacimiento')->nullable(); // Fecha de nacimiento si se conoce
            $table->integer('edad_aproximada')->nullable(); // Edad aproximada en meses o años
            $table->decimal('peso_kg', 5, 2)->nullable(); // Peso en kilogramos
            $table->enum('tamanio', ['pequeño', 'mediano', 'grande'])->nullable(); // Tamaño de la mascota
            
            // Identificación y salud
            $table->string('numero_chip', 100)->nullable(); // Número de chip de identificación
            $table->boolean('vacunado')->default(false); // Indica si está vacunado
            $table->boolean('esterilizado')->default(false); // Indica si está esterilizado
            $table->enum('estado_salud', ['saludable', 'en_tratamiento', 'crónico', 'desconocido'])->nullable(); // Estado de salud actual
            
            // Documentación
            $table->string('foto_url', 255)->nullable(); // URL de la foto principal de la mascota
            $table->string('foto_url_vacunas', 255)->nullable(); // URL de la foto del carnet de vacunas
            $table->date('fecha_vigencia_vacunas')->nullable(); // Fecha de vigencia de las vacunas
            
            // Información adicional
            $table->text('observaciones')->nullable(); // Observaciones adicionales sobre la mascota
            $table->boolean('activo')->default(true); // Indica si la mascota está activa en el sistema
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para mejorar el rendimiento de las consultas
            $table->index('unidad_id');
            $table->index('residente_id');
            $table->index('tipo');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};
