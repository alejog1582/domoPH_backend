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
        Schema::table('reservas_invitados', function (Blueprint $table) {
            $table->foreignId('unidad_id')
                ->nullable()
                ->after('residente_id')
                ->constrained('unidades')
                ->nullOnDelete()
                ->comment('ID de la unidad si el invitado es residente de la copropiedad (nullable para invitados externos)');
            
            $table->index('unidad_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas_invitados', function (Blueprint $table) {
            $table->dropForeign(['unidad_id']);
            $table->dropIndex(['unidad_id']);
            $table->dropColumn('unidad_id');
        });
    }
};
