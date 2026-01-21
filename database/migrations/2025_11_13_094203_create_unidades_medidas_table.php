<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades_medidas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('siglas', 10);
            $table->foreignId('id_usuario')->constrained('users');
            $table->dateTime('fecha_creacion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_medidas');
    }
};