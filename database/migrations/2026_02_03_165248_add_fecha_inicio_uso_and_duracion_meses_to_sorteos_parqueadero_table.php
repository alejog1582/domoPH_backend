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
            $table->date('fecha_inicio_uso')
                ->nullable()
                ->after('fecha_sorteo')
                ->comment('Fecha desde la cual los ganadores del sorteo pueden comenzar a usar el parqueadero asignado');
            
            $table->unsignedInteger('duracion_meses')
                ->nullable()
                ->after('fecha_inicio_uso')
                ->comment('Cantidad de meses que dura la asignación del parqueadero para los ganadores del sorteo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sorteos_parqueadero', function (Blueprint $table) {
            $table->dropColumn(['fecha_inicio_uso', 'duracion_meses']);
        });
    }
};
