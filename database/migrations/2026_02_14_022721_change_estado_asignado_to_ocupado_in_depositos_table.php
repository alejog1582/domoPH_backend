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
        // Primero actualizar los valores existentes de 'asignado' a 'ocupado'
        DB::table('depositos')
            ->where('estado', 'asignado')
            ->update(['estado' => 'ocupado']);

        // Cambiar el ENUM de 'asignado' a 'ocupado'
        DB::statement("ALTER TABLE depositos MODIFY COLUMN estado ENUM('disponible', 'ocupado', 'en_mantenimiento', 'inhabilitado') DEFAULT 'disponible'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Actualizar los valores existentes de 'ocupado' a 'asignado'
        DB::table('depositos')
            ->where('estado', 'ocupado')
            ->update(['estado' => 'asignado']);

        // Revertir el ENUM de 'ocupado' a 'asignado'
        DB::statement("ALTER TABLE depositos MODIFY COLUMN estado ENUM('disponible', 'asignado', 'en_mantenimiento', 'inhabilitado') DEFAULT 'disponible'");
    }
};
