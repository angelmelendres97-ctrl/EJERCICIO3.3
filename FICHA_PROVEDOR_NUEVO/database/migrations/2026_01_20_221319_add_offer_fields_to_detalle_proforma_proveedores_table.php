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
        Schema::table('detalle_proforma_proveedores', function (Blueprint $table) {
            $table->decimal('cantidad_oferta', 10, 2)->default(0);
            $table->decimal('valor_unitario_oferta', 10, 2)->default(0);
            $table->decimal('subtotal_oferta', 10, 2)->default(0);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('iva_porcentaje', 5, 2)->default(0);
            $table->decimal('otros_cargos', 10, 2)->default(0);
            $table->decimal('total_oferta', 10, 2)->default(0);
            $table->text('observacion_oferta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_proforma_proveedores', function (Blueprint $table) {
            $table->dropColumn([
                'cantidad_oferta',
                'valor_unitario_oferta',
                'subtotal_oferta',
                'descuento_porcentaje',
                'iva_porcentaje',
                'otros_cargos',
                'total_oferta',
                'observacion_oferta'
            ]);
        });
    }
};
