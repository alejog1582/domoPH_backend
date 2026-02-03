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
        Schema::table('parqueaderos', function (Blueprint $table) {
            $table->enum('tipo_vehiculo', ['carro', 'moto'])
                ->nullable()
                ->after('tipo')
                ->comment('Tipo de vehículo para el cual está destinado el parqueadero: carro o moto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parqueaderos', function (Blueprint $table) {
            $table->dropColumn('tipo_vehiculo');
        });
    }
};
