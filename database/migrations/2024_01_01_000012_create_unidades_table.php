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
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propiedad_id')->constrained('propiedades')->onDelete('cascade');
            $table->string('numero')->nullable(); // Apto 101, Casa 5, etc.
            $table->string('torre')->nullable();
            $table->string('bloque')->nullable();
            $table->enum('tipo', ['apartamento', 'casa', 'local', 'parqueadero', 'bodega', 'otro'])->default('apartamento');
            $table->decimal('area_m2', 10, 2)->nullable();
            $table->integer('coeficiente')->default(0); // Coeficiente de copropiedad
            $table->integer('habitaciones')->nullable();
            $table->integer('banos')->nullable();
            $table->enum('estado', ['ocupada', 'desocupada', 'en_construccion', 'mantenimiento'])->default('desocupada');
            $table->text('observaciones')->nullable();
            $table->json('caracteristicas')->nullable(); // Balcón, terraza, etc.
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('propiedad_id');
            $table->index(['propiedad_id', 'numero']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
