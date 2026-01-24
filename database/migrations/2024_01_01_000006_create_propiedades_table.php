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
        Schema::create('propiedades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nit')->unique()->nullable();
            $table->text('direccion');
            $table->string('ciudad');
            $table->string('departamento');
            $table->string('codigo_postal')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('color_primario')->default('#0066CC');
            $table->string('color_secundario')->default('#FFFFFF');
            $table->text('descripcion')->nullable();
            $table->integer('total_unidades')->default(0);
            $table->enum('estado', ['activa', 'suspendida', 'cancelada'])->default('activa');
            $table->foreignId('plan_id')->nullable()->constrained('planes')->onDelete('set null');
            $table->date('fecha_inicio_suscripcion')->nullable();
            $table->date('fecha_fin_suscripcion')->nullable();
            $table->boolean('trial_activo')->default(false);
            $table->date('fecha_fin_trial')->nullable();
            $table->json('configuracion_personalizada')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('nit');
            $table->index('estado');
            $table->index('plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propiedades');
    }
};
