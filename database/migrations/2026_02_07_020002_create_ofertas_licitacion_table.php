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
        Schema::create('ofertas_licitacion', function (Blueprint $table) {
            $table->id()->comment('ID único de la oferta');
            $table->foreignId('licitacion_id')
                ->constrained('licitaciones')
                ->onDelete('cascade')
                ->comment('ID de la licitación a la que pertenece la oferta');
            $table->string('nombre_proveedor')->comment('Nombre del proveedor que presenta la oferta');
            $table->string('nit_proveedor')->nullable()->comment('NIT del proveedor');
            $table->string('email_contacto')->comment('Email de contacto del proveedor');
            $table->string('telefono_contacto')->nullable()->comment('Teléfono de contacto del proveedor');
            $table->text('descripcion_oferta')->comment('Descripción detallada de la oferta');
            $table->decimal('valor_ofertado', 15, 2)->comment('Valor ofertado por el proveedor');
            $table->enum('estado', ['recibida', 'en_revision', 'seleccionada', 'rechazada'])
                ->default('recibida')
                ->comment('Estado de la oferta');
            $table->date('fecha_postulacion')->comment('Fecha en que se postuló la oferta');
            $table->boolean('es_ganadora')->default(false)->comment('Indica si la oferta es la ganadora');
            $table->timestamps();
            
            // Índices
            $table->index('licitacion_id', 'idx_ofertas_licitacion_licitacion');
            $table->index('estado', 'idx_ofertas_licitacion_estado');
            $table->index('es_ganadora', 'idx_ofertas_licitacion_ganadora');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ofertas_licitacion');
    }
};
