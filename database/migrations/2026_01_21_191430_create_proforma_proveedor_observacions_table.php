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
        Schema::create('proforma_proveedor_observacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_proforma')->constrained('proformas')->onDelete('cascade');
            $table->foreignId('id_proveedor')->constrained('proveedores')->onDelete('cascade');
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_proveedor_observacions');
    }
};
