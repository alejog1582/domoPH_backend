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
        Schema::create('solicitudes_comerciales', function (Blueprint $table) {
            $table->id()->comment('ID único de la solicitud comercial');
            $table->enum('tipo_solicitud', ['cotizacion', 'demo', 'contacto'])
                ->comment('Tipo de solicitud: cotización, demo o contacto');
            $table->string('nombre_contacto', 200)->comment('Nombre del contacto');
            $table->string('empresa', 200)->nullable()->comment('Nombre de la empresa');
            $table->string('email', 255)->comment('Correo electrónico del contacto');
            $table->string('telefono', 50)->comment('Teléfono de contacto');
            $table->string('ciudad', 100)->nullable()->comment('Ciudad del contacto');
            $table->string('pais', 100)->nullable()->comment('País del contacto');
            $table->text('mensaje')->comment('Mensaje o descripción de la solicitud');
            $table->enum('origen', ['landing', 'web', 'whatsapp', 'referido', 'otro'])->nullable()->comment('Origen de la solicitud');
            $table->enum('estado_gestion', ['pendiente', 'en_proceso', 'contactado', 'cerrado_ganado', 'cerrado_perdido'])
                ->default('pendiente')
                ->comment('Estado de gestión de la solicitud');
            $table->enum('prioridad', ['baja', 'media', 'alta'])
                ->default('media')
                ->comment('Prioridad de la solicitud');
            $table->dateTime('fecha_contacto')->nullable()->comment('Fecha de último contacto');
            $table->foreignId('asignado_a_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Usuario asignado para gestionar la solicitud');
            $table->json('metadata')->nullable()->comment('Datos adicionales en formato JSON');
            $table->boolean('activo')->default(true)->comment('Indica si la solicitud está activa');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('tipo_solicitud', 'idx_sol_com_tipo');
            $table->index('estado_gestion', 'idx_sol_com_estado');
            $table->index('prioridad', 'idx_sol_com_prioridad');
            $table->index('asignado_a_user_id', 'idx_sol_com_asignado');
            $table->index('fecha_contacto', 'idx_sol_com_fecha_contacto');
            $table->index('activo', 'idx_sol_com_activo');
            $table->index('created_at', 'idx_sol_com_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_comerciales');
    }
};
