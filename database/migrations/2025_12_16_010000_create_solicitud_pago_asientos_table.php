<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_pago_asientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_pago_id')->constrained('solicitud_pagos')->cascadeOnDelete();
            $table->string('conexion');
            $table->string('empresa_id');
            $table->string('sucursal_codigo');
            $table->string('ejercicio');
            $table->unsignedSmallInteger('periodo');
            $table->string('asto_cod_asto');
            $table->timestamps();

            $table->unique([
                'solicitud_pago_id',
                'conexion',
                'empresa_id',
                'sucursal_codigo',
                'ejercicio',
                'periodo',
                'asto_cod_asto',
            ], 'solicitud_pago_asiento_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_pago_asientos');
    }
};
