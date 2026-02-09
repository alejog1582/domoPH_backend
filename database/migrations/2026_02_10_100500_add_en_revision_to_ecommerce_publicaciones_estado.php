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
        // Modificar el ENUM de estado en ecommerce_publicaciones
        DB::statement("ALTER TABLE ecommerce_publicaciones MODIFY COLUMN estado ENUM('publicado', 'pausado', 'finalizado', 'en_revision') DEFAULT 'en_revision' COMMENT 'Estado actual de la publicación'");
        
        // Modificar los ENUMs en ecommerce_publicacion_estados_historial
        DB::statement("ALTER TABLE ecommerce_publicacion_estados_historial MODIFY COLUMN estado_anterior ENUM('publicado', 'pausado', 'finalizado', 'en_revision') NULL COMMENT 'Estado anterior de la publicación'");
        DB::statement("ALTER TABLE ecommerce_publicacion_estados_historial MODIFY COLUMN estado_nuevo ENUM('publicado', 'pausado', 'finalizado', 'en_revision') NOT NULL COMMENT 'Nuevo estado de la publicación'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los valores originales
        DB::statement("ALTER TABLE ecommerce_publicaciones MODIFY COLUMN estado ENUM('publicado', 'pausado', 'finalizado') DEFAULT 'publicado' COMMENT 'Estado actual de la publicación'");
        DB::statement("ALTER TABLE ecommerce_publicacion_estados_historial MODIFY COLUMN estado_anterior ENUM('publicado', 'pausado', 'finalizado') NULL COMMENT 'Estado anterior de la publicación'");
        DB::statement("ALTER TABLE ecommerce_publicacion_estados_historial MODIFY COLUMN estado_nuevo ENUM('publicado', 'pausado', 'finalizado') NOT NULL COMMENT 'Nuevo estado de la publicación'");
    }
};
