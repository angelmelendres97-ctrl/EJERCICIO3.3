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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('ruc')->unique();
            $table->string('nombre_empresa');
            $table->enum('tipo', ['azul', 'plomo'])->default('azul');
            $table->foreignId('linea_negocio_id')->constrained('linea_negocios')->onDelete('cascade');
            $table->string('motor');
            $table->string('puerto');
            $table->string('host');
            $table->string('usuario');
            $table->string('clave')->nullable();
            $table->string('nombre_base');
            $table->boolean('status_conexion')->default(false);
            $table->text('mensaje_conexion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
