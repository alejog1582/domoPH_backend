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
        Schema::create('logs_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('propiedad_id')->nullable()->constrained('propiedades')->onDelete('set null');
            $table->string('accion'); // create, update, delete, login, etc.
            $table->string('modelo')->nullable(); // Nombre del modelo afectado
            $table->unsignedBigInteger('modelo_id')->nullable(); // ID del registro afectado
            $table->text('descripcion')->nullable();
            $table->json('datos_anteriores')->nullable(); // Datos antes del cambio
            $table->json('datos_nuevos')->nullable(); // Datos después del cambio
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('modulo')->nullable(); // Módulo del sistema donde ocurrió
            $table->enum('nivel', ['info', 'warning', 'error', 'critical'])->default('info');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('propiedad_id');
            $table->index('accion');
            $table->index('modelo');
            $table->index('created_at');
            $table->index(['modelo', 'modelo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_auditoria');
    }
};
