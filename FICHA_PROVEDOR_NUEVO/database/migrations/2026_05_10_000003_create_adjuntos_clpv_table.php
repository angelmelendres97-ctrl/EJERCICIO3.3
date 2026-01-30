<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adjuntos_clpv', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa');
            $table->integer('id_sucursal');
            $table->integer('id_clpv');
            $table->unsignedBigInteger('id_archivo_uafe')->nullable();
            $table->string('titulo')->nullable();
            $table->string('estado', 2)->default('PE');
            $table->string('ruta')->nullable();
            $table->integer('user_web')->nullable();
            $table->timestamp('fecha_server')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->date('fecha_vencimiento_uafe')->nullable();
            $table->integer('periodo_uafe')->nullable();
            $table->timestamps();

            $table->foreign('id_archivo_uafe')->references('id')->on('archivos_uafe')->nullOnDelete();
            $table->index(['id_empresa', 'id_clpv', 'id_archivo_uafe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjuntos_clpv');
    }
};
