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
        Schema::table('proformas', function (Blueprint $table) {
            $table->string('estado')->default('Pendiente');
        });

        Schema::table('detalle_proformas', function (Blueprint $table) {
            $table->decimal('cantidad_aprobada', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        Schema::table('detalle_proformas', function (Blueprint $table) {
            $table->dropColumn('cantidad_aprobada');
        });
    }
};
