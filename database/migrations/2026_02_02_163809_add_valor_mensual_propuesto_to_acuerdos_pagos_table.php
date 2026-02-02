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
        Schema::table('acuerdos_pagos', function (Blueprint $table) {
            $table->decimal('valor_mensual_propuesto', 14, 2)
                ->nullable()
                ->after('valor_cuota')
                ->comment('Valor mensual de pago propuesto por el cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acuerdos_pagos', function (Blueprint $table) {
            $table->dropColumn('valor_mensual_propuesto');
        });
    }
};
