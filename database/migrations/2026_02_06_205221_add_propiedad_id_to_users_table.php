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
            $table->string('propiedad_id')->nullable()->after('avatar')->comment('IDs de propiedades separados por comas. Para administradores: ID de la propiedad que los creó. Para residentes: IDs de las propiedades desde donde fueron creados.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('propiedad_id');
        });
    }
};
