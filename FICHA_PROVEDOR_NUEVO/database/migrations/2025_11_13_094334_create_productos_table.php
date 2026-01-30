<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresa');
            $table->integer('amdg_id_empresa');
            $table->integer('amdg_id_sucursal');
            $table->string('linea');
            $table->string('grupo');
            $table->string('categoria');
            $table->string('marca');
            $table->string('sku')->unique();
            $table->string('nombre');
            $table->text('detalle')->nullable();
            $table->tinyInteger('tipo')->comment('1=servicio, 2=producto');
            $table->foreignId('id_unidad_medida')->constrained('unidades_medidas');
            $table->decimal('stock_minimo', 10, 2)->default(0);
            $table->decimal('stock_maximo', 10, 2)->default(0);
            $table->boolean('iva_sn')->default(false);
            $table->decimal('porcentaje_iva', 5, 2)->default(0);

            // Índices y llaves foráneas
            $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
