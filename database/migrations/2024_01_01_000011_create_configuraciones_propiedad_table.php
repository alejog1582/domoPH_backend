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
        Schema::create('configuraciones_propiedad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propiedad_id')->constrained('propiedades')->onDelete('cascade');
            $table->string('clave');
            $table->text('valor')->nullable();
            $table->string('tipo')->default('string'); // string, integer, boolean, json
            $table->text('descripcion')->nullable();
            $table->timestamps();
            
            $table->unique(['propiedad_id', 'clave']);
            $table->index('propiedad_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones_propiedad');
    }
};
