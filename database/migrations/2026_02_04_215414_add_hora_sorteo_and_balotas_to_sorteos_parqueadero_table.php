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
            $table->time('hora_sorteo')
                ->nullable()
                ->after('fecha_sorteo')
                ->comment('Hora en que se realizará el sorteo');
            
            $table->unsignedInteger('balotas_blancas_carro')
                ->default(0)
                ->after('capacidad_motos')
                ->comment('Número de balotas blancas (no favorecidos) para carros');
            
            $table->unsignedInteger('balotas_blancas_moto')
                ->default(0)
                ->after('balotas_blancas_carro')
                ->comment('Número de balotas blancas (no favorecidos) para motos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sorteos_parqueadero', function (Blueprint $table) {
            $table->dropColumn(['hora_sorteo', 'balotas_blancas_carro', 'balotas_blancas_moto']);
        });
    }
};
