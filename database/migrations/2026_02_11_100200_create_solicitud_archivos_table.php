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
        Schema::create('solicitud_archivos', function (Blueprint $table) {
            $table->id()->comment('ID único del archivo');
            $table->foreignId('solicitud_comercial_id')
                ->constrained('solicitudes_comerciales')
                ->onDelete('cascade')
                ->comment('ID de la solicitud comercial');
            $table->string('nombre_archivo', 255)->comment('Nombre original del archivo');
            $table->string('ruta_archivo', 500)->comment('Ruta donde se almacena el archivo');
            $table->string('tipo_mime', 100)->nullable()->comment('Tipo MIME del archivo');
            $table->bigInteger('tamaño')->nullable()->comment('Tamaño del archivo en bytes');
            $table->foreignId('cargado_por_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('ID del usuario que cargó el archivo');
            $table->timestamps();
            
            // Índices
            $table->index('solicitud_comercial_id', 'idx_sol_arch_solicitud');
            $table->index('cargado_por_user_id', 'idx_sol_arch_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_archivos');
    }
};
