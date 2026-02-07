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
        Schema::create('asamblea_documentos', function (Blueprint $table) {
            $table->id()->comment('ID único del documento');
            $table->foreignId('asamblea_id')
                ->constrained('asambleas')
                ->onDelete('cascade')
                ->comment('ID de la asamblea');
            $table->string('nombre')->comment('Nombre del documento');
            $table->enum('tipo', ['orden_dia', 'acta', 'presupuesto', 'soporte', 'otro'])->comment('Tipo de documento');
            $table->string('archivo_url')->comment('URL del archivo');
            $table->enum('visible_para', ['todos', 'propietarios', 'administracion'])->default('todos')->comment('Visibilidad del documento');
            $table->foreignId('subido_por')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('Usuario que subió el documento');
            $table->boolean('activo')->default(true)->comment('Indica si el documento está activo');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('asamblea_id');
            $table->index('tipo');
            $table->index('visible_para');
            $table->index('subido_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asamblea_documentos');
    }
};
