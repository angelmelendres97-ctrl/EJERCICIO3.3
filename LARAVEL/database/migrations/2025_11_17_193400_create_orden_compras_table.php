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
        Schema::create('orden_compras', function (Blueprint $table) {
            $table->id();

            // RELACIONES
            $table->unsignedBigInteger('id_empresa'); // FK con empresas

            // Claves Foráneas (Asumo que son de tipo INT)
            $table->integer('amdg_id_empresa');
            $table->integer('amdg_id_sucursal');

            // Columna del Presupuesto
            $table->string('uso_compra', 2550);
            $table->string('solicitado_por', 2550);
            $table->string('formato', 25);
            $table->string('tipo_oc', length: 25);
            $table->string('presupuesto', length: 10);


            // Información General
            $table->integer('id_proveedor');
            $table->string('identificacion', 20);
            $table->string('proveedor', 255);

            $table->string('trasanccion', 255);
            $table->date('fecha_pedido')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('pedidos_importados')->nullable();


            $table->float('subtotal');
            $table->float('total_descuento');
            $table->float('total_impuesto');
            $table->float('total');


            // FOREIGN KEY
            $table->foreign('id_empresa')
                ->references('id')
                ->on('empresas')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_compras');
    }
};
