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
        Schema::create('consejo_integrantes', function (Blueprint $table) {
            $table->id()->comment('ID único del integrante');
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('ID del usuario si está registrado en el sistema');
            $table->string('nombre')->comment('Nombre completo del integrante');
            $table->string('email')->comment('Correo electrónico del integrante');
            $table->string('telefono')->nullable()->comment('Teléfono de contacto');
            $table->string('unidad_apartamento')->nullable()->comment('Unidad o apartamento del integrante');
            $table->string('cargo')->comment('Cargo en el consejo (presidente, vicepresidente, secretario, vocal, etc.)');
            $table->boolean('tiene_voz')->default(true)->comment('Indica si el integrante tiene voz en las reuniones');
            $table->boolean('tiene_voto')->default(true)->comment('Indica si el integrante tiene voto en las decisiones');
            $table->boolean('puede_convocar')->default(false)->comment('Indica si puede convocar reuniones');
            $table->boolean('puede_firmar_actas')->default(false)->comment('Indica si puede firmar actas');
            $table->date('fecha_inicio_periodo')->comment('Fecha de inicio del periodo en el consejo');
            $table->date('fecha_fin_periodo')->nullable()->comment('Fecha de fin del periodo en el consejo');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo')->comment('Estado del integrante');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('user_id');
            $table->index('estado');
            $table->index('cargo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejo_integrantes');
    }
};
