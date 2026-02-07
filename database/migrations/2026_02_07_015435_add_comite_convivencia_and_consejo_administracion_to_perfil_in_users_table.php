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
        // Cambiar temporalmente a VARCHAR para evitar problemas con valores existentes
        DB::statement("ALTER TABLE users MODIFY COLUMN perfil VARCHAR(50) NULL COMMENT 'Perfil del usuario en el sistema'");
        
        // Actualizar cualquier valor inválido a NULL
        $valoresValidos = ['superadministrador', 'administrador', 'residente', 'porteria', 'proveedor', 'comite_convivencia', 'consejo_administracion'];
        $usuarios = DB::table('users')->whereNotNull('perfil')->get();
        
        foreach ($usuarios as $usuario) {
            if (!in_array($usuario->perfil, $valoresValidos)) {
                DB::table('users')->where('id', $usuario->id)->update(['perfil' => null]);
            }
        }
        
        // Modificar el ENUM para agregar los nuevos perfiles
        DB::statement("ALTER TABLE users MODIFY COLUMN perfil ENUM('superadministrador', 'administrador', 'residente', 'porteria', 'proveedor', 'comite_convivencia', 'consejo_administracion') NULL COMMENT 'Perfil del usuario en el sistema'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cambiar temporalmente a VARCHAR para evitar problemas con valores existentes
        DB::statement("ALTER TABLE users MODIFY COLUMN perfil VARCHAR(50) NULL COMMENT 'Perfil del usuario en el sistema'");
        
        // Actualizar los valores nuevos a NULL o a un valor válido
        $valoresValidos = ['superadministrador', 'administrador', 'residente', 'porteria', 'proveedor'];
        DB::table('users')
            ->whereIn('perfil', ['comite_convivencia', 'consejo_administracion'])
            ->update(['perfil' => null]);
        
        // Revertir a los perfiles originales
        DB::statement("ALTER TABLE users MODIFY COLUMN perfil ENUM('superadministrador', 'administrador', 'residente', 'porteria', 'proveedor') NULL COMMENT 'Perfil del usuario en el sistema'");
    }
};
