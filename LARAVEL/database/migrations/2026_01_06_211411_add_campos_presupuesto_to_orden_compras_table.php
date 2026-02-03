<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orden_compras', function (Blueprint $table) {
            $table->string('numero_factura_proforma', 255)->nullable()->after('formato');
            $table->string('nombre_reembolso', 255)->nullable()->after('tipo_oc');
        });
    }

    public function down(): void
    {
        Schema::table('orden_compras', function (Blueprint $table) {
            $table->dropColumn(['numero_factura_proforma', 'nombre_reembolso']);
        });
    }
};
