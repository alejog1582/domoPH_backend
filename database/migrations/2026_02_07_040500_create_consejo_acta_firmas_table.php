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
        Schema::create('consejo_acta_firmas', function (Blueprint $table) {
            $table->id()->comment('ID único de la firma');
            $table->foreignId('acta_id')
                ->constrained('consejo_actas')
                ->onDelete('cascade')
                ->comment('ID del acta');
            $table->foreignId('integrante_id')
                ->constrained('consejo_integrantes')
                ->onDelete('cascade')
                ->comment('ID del integrante que firma');
            $table->string('cargo')->comment('Cargo del integrante al momento de firmar');
            $table->dateTime('fecha_firma')->nullable()->comment('Fecha y hora de la firma');
            $table->timestamps();

            // Índices
            $table->index('acta_id');
            $table->index('integrante_id');
            $table->unique(['acta_id', 'integrante_id'], 'unique_acta_integrante_firma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejo_acta_firmas');
    }
};
