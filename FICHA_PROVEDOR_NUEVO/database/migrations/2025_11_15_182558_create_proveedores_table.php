<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();

            // RELACIONES
            $table->unsignedBigInteger('id_empresa'); // FK con empresas
            $table->integer('admg_id_empresa');
            $table->integer('admg_id_sucursal');

            // DATOS DEL PROVEEDOR
            $table->string('tipo');
            $table->string('ruc');
            $table->string('nombre');
            $table->string('nombre_comercial');
            $table->string('grupo');
            $table->string('zona');
            $table->string('flujo_caja');
            $table->string('tipo_proveedor');
            $table->string('forma_pago');
            $table->string('destino_pago');
            $table->string('pais_pago');
            $table->integer('dias_pago');
            $table->float('limite_credito');

            $table->string('telefono')->nullable();
            $table->string('direcccion')->nullable();
            $table->string('correo')->nullable();

            // CHECKBOX BOOLEAN
            $table->boolean('aplica_retencion_sn')->default(false);

            $table->timestamps();

            // FOREIGN KEY
            $table->foreign('id_empresa')
                ->references('id')
                ->on('empresas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
