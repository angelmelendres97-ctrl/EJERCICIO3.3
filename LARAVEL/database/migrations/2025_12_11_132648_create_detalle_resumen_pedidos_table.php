<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detalle_resumen_pedidos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_orden_compra')->constrained('orden_compras');
            $table->foreignId('id_resumen_pedidos')->constrained('resumen_pedidos');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_resumen_pedidos');
    }
};
