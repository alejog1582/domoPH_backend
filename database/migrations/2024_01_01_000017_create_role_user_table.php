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
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('propiedad_id')->nullable()->constrained('propiedades')->onDelete('cascade');
            $table->timestamps();
            
            // Un usuario puede tener el mismo rol en diferentes propiedades
            $table->unique(['user_id', 'role_id', 'propiedad_id'], 'user_role_propiedad_unique');
            $table->index('user_id');
            $table->index('role_id');
            $table->index('propiedad_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
