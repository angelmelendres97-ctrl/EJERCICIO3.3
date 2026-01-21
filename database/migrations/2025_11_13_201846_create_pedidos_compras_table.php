<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_compras', function (Blueprint $table) {
            $table->id();

            // Relaciones / referencias
            $table->unsignedBigInteger('id_empresa');
            $table->integer('pedi_cod_pedi');
            $table->integer('amdg_id_empresa');
            $table->integer('amdg_id_sucursal');

            // Proveedor
            $table->string('identificacion_proveedor');
            $table->string('nombre_proveedor');

            // Fechas
            $table->date('fecha_pedido');
            $table->date('fecha_entrega');

            // Observaciones y control
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();

            // Índices y llaves foráneas
            $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('cascade');
            $table->index('pedi_cod_pedi');
            $table->index('amdg_id_empresa');
            $table->index('amdg_id_sucursal');
            $table->index('identificacion_proveedor');
            $table->index('fecha_pedido');
            $table->index('fecha_entrega');
            $table->text('estado')->default('PE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_compras');
    }
};
