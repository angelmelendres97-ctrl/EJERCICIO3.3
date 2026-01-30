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
        Schema::create('proformas', function (Blueprint $table) {
            $table->id();

            // RELACIONES
            $table->unsignedBigInteger('id_empresa'); // FK con empresas
            $table->unsignedBigInteger('id_usuario'); // FK con usuarios

            // Claves Foráneas (Asumo que son de tipo INT)
            $table->integer('amdg_id_empresa');
            $table->integer('amdg_id_sucursal');

            // Columna del Presupuesto
            $table->string('uso_compra', 2550)->nullable(); // Made nullable compatible
            $table->string('solicitado_por', 2550)->nullable();
            $table->string('formato', 25)->nullable();
            $table->string('tipo_oc', length: 25)->nullable();
            $table->string('presupuesto', length: 10)->nullable();


            // Información General
            $table->integer('id_proveedor')->nullable();
            $table->string('identificacion', 20)->nullable();
            $table->string('proveedor', 255)->nullable();

            $table->string('trasanccion', 255)->nullable();
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

            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proformas');
    }
};
