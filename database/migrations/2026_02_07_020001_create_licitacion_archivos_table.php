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
        Schema::create('licitacion_archivos', function (Blueprint $table) {
            $table->id()->comment('ID único del archivo');
            $table->foreignId('licitacion_id')
                ->constrained('licitaciones')
                ->onDelete('cascade')
                ->comment('ID de la licitación a la que pertenece el archivo');
            $table->string('nombre_archivo')->comment('Nombre del archivo');
            $table->string('url_archivo')->comment('URL del archivo almacenado');
            $table->string('tipo_archivo')->nullable()->comment('Tipo de archivo (MIME type)');
            $table->timestamps();
            
            // Índices
            $table->index('licitacion_id', 'idx_licitacion_archivos_licitacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licitacion_archivos');
    }
};
