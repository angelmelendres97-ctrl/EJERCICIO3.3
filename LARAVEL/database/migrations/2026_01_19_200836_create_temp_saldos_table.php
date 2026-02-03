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
        // Aseguramos que se cree en la conexión correcta si se corre especificamente,
        // pero mejor lo definimos en el comando migrate.
        // Ojo: Si corres migrate normal, se creará en la conexión default.
        // Para evitar errores en la DB principal, podemos chequear conexión o dejarlo así 
        // y el usuario debe correrlo con --database=sqlite_memory.

        Schema::create('temp_saldos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo_proveedor')->nullable();
            $table->string('codigo_ciudad')->nullable();
            $table->string('ruc')->nullable();
            $table->string('proveedor')->nullable();
            $table->string('numero_factura')->nullable();
            $table->string('codigo_sucursal')->nullable();
            $table->string('codigo_factura')->nullable();
            $table->date('emision')->nullable();
            $table->date('vencimiento')->nullable();
            $table->decimal('abono', 18, 2)->nullable();
            $table->decimal('total_factura', 18, 2)->nullable();
            $table->decimal('saldo', 18, 2)->nullable();
            $table->string('empresa_origen')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_saldos');
    }
};
