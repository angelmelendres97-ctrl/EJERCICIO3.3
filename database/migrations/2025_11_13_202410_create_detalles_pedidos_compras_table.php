<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalles_pedidos_compras', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('id_pedido_compra');
            $table->integer('dped_cod_dped');
            $table->integer('id_bodega');
            $table->string('nombre_bodega');
            $table->string('codigo_producto');
            $table->string('producto');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('costo', 10, 2);
            $table->decimal('inpuesto', 10, 2);
            $table->text('detalle')->nullable();

            // Índices y relaciones
            $table->foreign('id_pedido_compra')->references('id')->on('pedidos_compras')->onDelete('cascade');
            $table->index('dped_cod_dped');
            $table->index('id_bodega');
            $table->index('codigo_producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_pedidos_compras');
    }
};
