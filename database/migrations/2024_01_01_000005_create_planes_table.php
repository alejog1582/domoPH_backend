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
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->decimal('precio_mensual', 10, 2);
            $table->decimal('precio_anual', 10, 2)->nullable();
            $table->integer('max_unidades')->nullable(); // null = ilimitado
            $table->integer('max_usuarios')->nullable(); // null = ilimitado
            $table->integer('max_almacenamiento_mb')->nullable(); // null = ilimitado
            $table->boolean('soporte_prioritario')->default(false);
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->json('caracteristicas')->nullable(); // Array de características del plan
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('slug');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
