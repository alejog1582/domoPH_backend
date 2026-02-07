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
        Schema::create('consejo_reunion_agenda', function (Blueprint $table) {
            $table->id()->comment('ID único del tema de agenda');
            $table->foreignId('reunion_id')
                ->constrained('consejo_reuniones')
                ->onDelete('cascade')
                ->comment('ID de la reunión');
            $table->integer('orden')->comment('Orden de presentación del tema');
            $table->string('tema')->comment('Tema o punto de agenda');
            $table->string('responsable')->nullable()->comment('Persona responsable del tema');
            $table->timestamps();

            // Índices
            $table->index('reunion_id');
            $table->index('orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejo_reunion_agenda');
    }
};
