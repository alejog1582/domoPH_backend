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
        // Modificar el ENUM para agregar los nuevos perfiles
        DB::statement("ALTER TABLE users MODIFY COLUMN perfil ENUM('superadministrador', 'administrador', 'residente', 'porteria', 'proveedor', 'comite_convivencia', 'consejo_administracion') NULL COMMENT 'Perfil del usuario en el sistema'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los perfiles originales
        DB::statement("ALTER TABLE users MODIFY COLUMN perfil ENUM('superadministrador', 'administrador', 'residente', 'porteria', 'proveedor') NULL COMMENT 'Perfil del usuario en el sistema'");
    }
};
