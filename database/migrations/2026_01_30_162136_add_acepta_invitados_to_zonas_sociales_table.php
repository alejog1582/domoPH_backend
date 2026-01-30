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
        Schema::table('zonas_sociales', function (Blueprint $table) {
            $table->boolean('acepta_invitados')
                ->default(false)
                ->after('permite_reservas_en_mora')
                ->comment('Indica si la zona social acepta invitados en las reservas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zonas_sociales', function (Blueprint $table) {
            $table->dropColumn('acepta_invitados');
        });
    }
};
