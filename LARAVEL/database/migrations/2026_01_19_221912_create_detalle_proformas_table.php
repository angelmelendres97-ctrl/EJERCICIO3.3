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
        Schema::create('detalle_proformas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_proforma')->constrained('proformas')->cascadeOnDelete();
            $table->integer('id_bodega');
            $table->string('bodega', 255)->nullable();
            $table->string('unidad', 10)->nullable();

            // Campos de la imagen:
            $table->string('codigo_producto', 255);
            $table->string('producto', 255);

            // Campos numéricos (float)
            $table->float('cantidad');
            $table->float('costo')->nullable()->default(0);
            $table->float('descuento')->nullable()->default(0);
            $table->float('impuesto')->nullable()->default(0);
            $table->float('valor_impuesto')->nullable()->default(0);
            $table->float('total')->nullable()->default(0);

            $table->text('detalle')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_proformas');
    }
};
