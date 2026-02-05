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
        // Convertir valores decimales a enteros (redondeando si es necesario)
        DB::statement('UPDATE cuotas_administracion SET coeficiente = ROUND(coeficiente) WHERE coeficiente IS NOT NULL');
        
        Schema::table('cuotas_administracion', function (Blueprint $table) {
            $table->integer('coeficiente')
                ->nullable()
                ->comment('Coeficiente para cálculo proporcional. Si es null, la cuota es fija por unidad')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuotas_administracion', function (Blueprint $table) {
            $table->decimal('coeficiente', 10, 4)
                ->nullable()
                ->comment('Coeficiente para cálculo proporcional. Si es null, la cuota es fija por unidad')
                ->change();
        });
    }
};
