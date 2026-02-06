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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('perfil', ['superadministrador', 'administrador', 'residente', 'porteria', 'proveedor'])
                ->nullable()
                ->after('propiedad_id')
                ->comment('Perfil del usuario en el sistema');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('perfil');
        });
    }
};
