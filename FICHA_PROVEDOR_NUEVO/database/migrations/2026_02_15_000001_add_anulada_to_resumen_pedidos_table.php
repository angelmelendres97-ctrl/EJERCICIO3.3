<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('resumen_pedidos', function (Blueprint $table) {
            $table->boolean('anulada')->default(false)->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('resumen_pedidos', function (Blueprint $table) {
            $table->dropColumn('anulada');
        });
    }
};
