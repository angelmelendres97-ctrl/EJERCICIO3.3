<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('detalle_orden_compras', function (Blueprint $table) {
            $table->integer('pedido_codigo')->nullable()->after('id_orden_compra');
            $table->integer('pedido_detalle_id')->nullable()->after('pedido_codigo');

            $table->index('pedido_codigo');
            $table->index('pedido_detalle_id');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_orden_compras', function (Blueprint $table) {
            $table->dropIndex(['pedido_codigo']);
            $table->dropIndex(['pedido_detalle_id']);

            $table->dropColumn(['pedido_codigo', 'pedido_detalle_id']);
        });
    }
};
