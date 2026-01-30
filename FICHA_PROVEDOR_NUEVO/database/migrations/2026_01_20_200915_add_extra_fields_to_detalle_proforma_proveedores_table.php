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
        Schema::table('detalle_proforma_proveedores', function (Blueprint $table) {
            $table->string('correo')->nullable();
            $table->string('contacto')->nullable();
            $table->decimal('precio', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_proforma_proveedores', function (Blueprint $table) {
            $table->dropColumn(['correo', 'contacto', 'precio']);
        });
    }
};
