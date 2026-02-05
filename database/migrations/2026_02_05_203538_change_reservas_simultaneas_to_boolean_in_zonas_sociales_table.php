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
        // Convertir valores existentes: 0 -> false, >= 1 -> true
        DB::statement('UPDATE zonas_sociales SET reservas_simultaneas = CASE WHEN reservas_simultaneas = 0 THEN 0 ELSE 1 END');
        
        Schema::table('zonas_sociales', function (Blueprint $table) {
            $table->boolean('reservas_simultaneas')
                ->default(true)
                ->comment('Indica si permite reservas simultáneas (true) o es exclusiva (false)')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zonas_sociales', function (Blueprint $table) {
            // Convertir boolean a integer: false -> 0, true -> 1
            $table->integer('reservas_simultaneas')
                ->default(1)
                ->comment('Número de reservas simultáneas permitidas')
                ->change();
        });
        
        // Convertir valores: false -> 0, true -> 1
        DB::statement('UPDATE zonas_sociales SET reservas_simultaneas = CASE WHEN reservas_simultaneas = 0 THEN 0 ELSE 1 END');
    }
};
