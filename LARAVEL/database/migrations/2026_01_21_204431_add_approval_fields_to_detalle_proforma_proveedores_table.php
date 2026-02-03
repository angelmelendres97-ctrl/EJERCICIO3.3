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
            //
            $table->boolean('es_aprobado')->default(false);
            $table->decimal('cantidad_aprobada', 10, 2)->nullable();
            $table->decimal('precio_aprobado', 10, 2)->nullable();
            $table->text('observacion_aprobacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_proforma_proveedores', function (Blueprint $table) {
            $table->dropColumn(['es_aprobado', 'cantidad_aprobada', 'precio_aprobado', 'observacion_aprobacion']);
        });
    }
};
