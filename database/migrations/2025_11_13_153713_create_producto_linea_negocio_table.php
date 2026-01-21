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
        Schema::create('producto_linea_negocio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('linea_negocio_id')->constrained('linea_negocios')->onDelete('cascade');
            $table->timestamps();

            // Índice único para evitar duplicados
            $table->unique(['producto_id', 'linea_negocio_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_linea_negocio');
    }
};
