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
        Schema::create('detalle_orden_compras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_orden_compra')->constrained('orden_compras');
            $table->integer('id_bodega');
            $table->string('bodega', 255); // SKU debe ser único
            $table->string('unidad', 10)->nullable()->after('producto');
            // Campos de la imagen:
            $table->string('codigo_producto', 255); // SKU debe ser único
            $table->string('producto', 255);

            // Campos numéricos (float)
            $table->float('cantidad');
            $table->float('costo');
            $table->float('descuento');
            $table->float('impuesto');
            $table->float('valor_impuesto');
            $table->float('total');

            $table->text('detalle')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_orden_compras');
    }
};
