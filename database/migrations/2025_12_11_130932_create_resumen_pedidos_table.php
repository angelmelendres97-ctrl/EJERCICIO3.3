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
        Schema::create('resumen_pedidos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_empresa');
            $table->foreign('id_empresa')
                ->references('id')
                ->on('empresas')
                ->onDelete('cascade');

            $table->integer('amdg_id_empresa');
            $table->integer('amdg_id_sucursal');
            $table->integer('codigo_secuencial');
            $table->string('tipo');
            $table->text('descripcion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumen_pedidos');
    }
};
