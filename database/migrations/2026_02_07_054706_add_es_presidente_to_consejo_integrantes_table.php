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
        Schema::table('consejo_integrantes', function (Blueprint $table) {
            $table->boolean('es_presidente')->default(false)->after('cargo')->comment('Indica si el integrante es el presidente del consejo. Solo puede haber uno activo con esta bandera en true.');
        });

        // Crear índice para mejorar búsquedas
        Schema::table('consejo_integrantes', function (Blueprint $table) {
            $table->index(['copropiedad_id', 'es_presidente', 'estado'], 'idx_presidente_activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consejo_integrantes', function (Blueprint $table) {
            $table->dropIndex('idx_presidente_activo');
            $table->dropColumn('es_presidente');
        });
    }
};
