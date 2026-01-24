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
        Schema::create('configuraciones_globales', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->text('valor')->nullable();
            $table->string('tipo')->default('string'); // string, integer, boolean, json
            $table->text('descripcion')->nullable();
            $table->string('categoria')->nullable(); // general, pagos, notificaciones, etc.
            $table->boolean('editable')->default(true);
            $table->timestamps();
            
            $table->index('clave');
            $table->index('categoria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones_globales');
    }
};
