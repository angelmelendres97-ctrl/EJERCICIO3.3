<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('solicitud_pago_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_pago_id')->constrained('solicitud_pagos')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('archivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_pago_adjuntos');
    }
};
