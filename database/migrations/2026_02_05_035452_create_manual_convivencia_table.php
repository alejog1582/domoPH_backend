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
        Schema::create('manual_convivencia', function (Blueprint $table) {
            $table->id()
                ->comment('ID único del registro del manual de convivencia');

            // Relación con la copropiedad
            $table->foreignId('copropiedad_id')
                ->constrained('propiedades')
                ->onDelete('cascade')
                ->comment('ID de la copropiedad a la que pertenece el manual de convivencia');

            // Archivo del manual
            $table->string('manual_url')
                ->nullable()
                ->comment('URL del archivo del manual de convivencia cargado por el administrador (PDF u otro formato)');

            // Contenido HTML
            $table->longText('principales_deberes')
                ->nullable()
                ->comment('Contenido extenso con formato HTML donde se almacenan los principales deberes de los residentes');

            $table->longText('principales_obligaciones')
                ->nullable()
                ->comment('Contenido extenso con formato HTML donde se almacenan las principales obligaciones de los residentes');

            // Control
            $table->boolean('activo')
                ->default(true)
                ->comment('Indica si el manual está vigente');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('copropiedad_id', 'idx_manual_convivencia_copropiedad');
            $table->index('activo', 'idx_manual_convivencia_activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_convivencia');
    }
};
