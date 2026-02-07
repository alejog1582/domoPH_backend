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
        Schema::create('consejo_actas', function (Blueprint $table) {
            $table->id()->comment('ID único del acta');
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad');
            $table->foreignId('reunion_id')
                ->constrained('consejo_reuniones')
                ->onDelete('cascade')
                ->comment('ID de la reunión');
            $table->enum('tipo_reunion', ['ordinaria', 'extraordinaria'])->comment('Tipo de reunión');
            $table->date('fecha_acta')->comment('Fecha del acta');
            $table->boolean('quorum')->default(false)->comment('Indica si hubo quorum en la reunión');
            $table->longText('contenido')->comment('Contenido completo del acta');
            $table->enum('estado', ['borrador', 'finalizada', 'firmada'])->default('borrador')->comment('Estado del acta');
            $table->boolean('visible_residentes')->default(false)->comment('Indica si el acta es visible para residentes');
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('Usuario que creó el acta');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id');
            $table->index('reunion_id');
            $table->index('estado');
            $table->index('fecha_acta');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejo_actas');
    }
};
