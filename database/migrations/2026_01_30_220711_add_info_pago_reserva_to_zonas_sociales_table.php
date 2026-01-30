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
            $table->text('info_pago_reserva')
                ->nullable()
                ->after('valor_deposito')
                ->comment('Información de cuenta bancaria o método de pago para reservas con costo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zonas_sociales', function (Blueprint $table) {
            $table->dropColumn('info_pago_reserva');
        });
    }
};
