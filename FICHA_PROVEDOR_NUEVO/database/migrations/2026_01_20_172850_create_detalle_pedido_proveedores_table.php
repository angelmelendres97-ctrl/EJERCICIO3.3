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
        Schema::create('detalle_pedido_proveedores', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('id_detalle_pedido')
                ->constrained('detalles_pedidos_compras')
                ->cascadeOnDelete();

            $table->foreignId('id_proveedor')
                ->constrained('proveedores')
                ->cascadeOnDelete();

            // Attributes
            $table->boolean('seleccionado')->default(true);
            $table->decimal('costo', 10, 2)->nullable();

            // Unique constraint to prevent duplicate provider assignment for same item
            $table->unique(['id_detalle_pedido', 'id_proveedor']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido_proveedores');
    }
};
