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
        Schema::table('sorteos_parqueadero', function (Blueprint $table) {
            $table->unsignedInteger('capacidad_autos')
                ->default(0)
                ->after('fecha_sorteo')
                ->comment('Número de parqueaderos disponibles para carros');

            $table->unsignedInteger('capacidad_motos')
                ->default(0)
                ->after('capacidad_autos')
                ->comment('Número de parqueaderos disponibles para motos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sorteos_parqueadero', function (Blueprint $table) {
            $table->dropColumn(['capacidad_autos', 'capacidad_motos']);
        });
    }
};
