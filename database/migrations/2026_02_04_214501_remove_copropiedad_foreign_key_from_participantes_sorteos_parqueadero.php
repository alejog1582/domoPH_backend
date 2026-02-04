<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar si la columna existe antes de intentar eliminar la foreign key
        if (Schema::hasColumn('participantes_sorteos_parqueadero', 'copropiedad_id')) {
            // Verificar si la foreign key existe antes de intentar eliminarla
            $foreignKeyExists = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'participantes_sorteos_parqueadero' 
                AND CONSTRAINT_NAME = 'participantes_sorteos_parqueadero_copropiedad_id_foreign'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            if (!empty($foreignKeyExists)) {
                Schema::table('participantes_sorteos_parqueadero', function (Blueprint $table) {
                    $table->dropForeign('participantes_sorteos_parqueadero_copropiedad_id_foreign');
                });
            }
            
            // Verificar si el índice existe antes de intentar eliminarlo
            $indexExists = DB::select("
                SELECT INDEX_NAME 
                FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'participantes_sorteos_parqueadero' 
                AND INDEX_NAME = 'participantes_sorteos_parqueadero_copropiedad_id_index'
            ");
            
            if (!empty($indexExists)) {
                Schema::table('participantes_sorteos_parqueadero', function (Blueprint $table) {
                    $table->dropIndex('participantes_sorteos_parqueadero_copropiedad_id_index');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participantes_sorteos_parqueadero', function (Blueprint $table) {
            // Restaurar el índice
            $table->index('copropiedad_id');
            
            // Restaurar la foreign key
            $table->foreign('copropiedad_id')
                ->references('id')
                ->on('propiedades')
                ->onDelete('cascade');
        });
    }
};
