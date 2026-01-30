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
        Schema::create('detalle_proforma_proveedores', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('id_detalle_proforma')
                ->constrained('detalle_proformas')
                ->cascadeOnDelete();

            $table->foreignId('id_proveedor')
                ->constrained('proveedores')
                ->cascadeOnDelete();

            // Attributes
            $table->boolean('seleccionado')->default(true);
            $table->decimal('costo', 10, 2)->nullable();

            // Allow multiple providers per item, but unique pair
            $table->unique(['id_detalle_proforma', 'id_proveedor'], 'unique_prod_prov');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_proforma_proveedores');
    }
};
