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
        Schema::table('visitas', function (Blueprint $table) {
            $table->foreignId('parqueadero_id')
                ->nullable()
                ->after('placa_vehiculo')
                ->constrained('parqueaderos')
                ->nullOnDelete()
                ->comment('ID del parqueadero asignado para visitas vehiculares');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitas', function (Blueprint $table) {
            $table->dropForeign(['parqueadero_id']);
            $table->dropColumn('parqueadero_id');
        });
    }
};
