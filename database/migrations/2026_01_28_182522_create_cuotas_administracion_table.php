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
        Schema::create('cuotas_administracion', function (Blueprint $table) {
            $table->id();
            
            // Relación con la propiedad (copropiedad)
            $table->foreignId('propiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la propiedad a la que pertenece la cuota');
            
            // Tipo de cuota
            $table->enum('concepto', ['cuota_ordinaria', 'cuota_extraordinaria'])
                ->comment('Tipo de cuota: ordinaria o extraordinaria');
            
            // Cálculo de la cuota
            $table->decimal('coeficiente', 10, 4)
                ->nullable()
                ->comment('Coeficiente para cálculo proporcional. Si es null, la cuota es fija por unidad');
            $table->decimal('valor', 12, 2)
                ->comment('Valor base de la cuota (valor total o valor fijo por unidad según la lógica)');
            
            // Rango de aplicación
            $table->date('mes_desde')
                ->nullable()
                ->comment('Mes desde el cual aplica la cuota. Si es null, la cuota es indefinida');
            $table->date('mes_hasta')
                ->nullable()
                ->comment('Mes hasta el cual aplica la cuota. Si es null, la cuota es indefinida');
            
            // Estado
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si la cuota está activa');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('propiedad_id');
            $table->index('concepto');
            $table->index('activo');
            $table->index(['propiedad_id', 'concepto']);
            $table->index(['propiedad_id', 'activo']);
            $table->index(['mes_desde', 'mes_hasta']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas_administracion');
    }
};
