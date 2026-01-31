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
        Schema::table('comunicados', function (Blueprint $table) {
            $table->boolean('destacado')
                ->default(false)
                ->after('publicado')
                ->comment('Indica si el comunicado está destacado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comunicados', function (Blueprint $table) {
            $table->dropColumn('destacado');
        });
    }
};
