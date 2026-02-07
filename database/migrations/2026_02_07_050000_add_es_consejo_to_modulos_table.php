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
        Schema::table('modulos', function (Blueprint $table) {
            $table->boolean('es_consejo')->default(false)->after('es_admin')->comment('Indica si el módulo pertenece al Consejo de Administración');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            $table->dropColumn('es_consejo');
        });
    }
};
