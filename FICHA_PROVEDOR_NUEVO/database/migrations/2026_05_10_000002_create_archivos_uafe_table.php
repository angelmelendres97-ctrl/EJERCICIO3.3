<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivos_uafe', function (Blueprint $table) {
            $table->id();
            $table->integer('empr_cod_empr');
            $table->string('titulo');
            $table->string('estado', 2)->default('AC');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos_uafe');
    }
};
