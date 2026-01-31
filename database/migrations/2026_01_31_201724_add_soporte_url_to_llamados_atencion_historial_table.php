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
        Schema::table('llamados_atencion_historial', function (Blueprint $table) {
            $table->string('soporte_url')
                ->nullable()
                ->after('comentario')
                ->comment('URL del soporte fotográfico adjunto a la respuesta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('llamados_atencion_historial', function (Blueprint $table) {
            $table->dropColumn('soporte_url');
        });
    }
};
