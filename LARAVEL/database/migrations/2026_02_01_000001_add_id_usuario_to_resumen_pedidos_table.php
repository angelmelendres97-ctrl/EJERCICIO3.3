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
        Schema::table('resumen_pedidos', function (Blueprint $table) {
            $table->foreignId('id_usuario')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('id_empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resumen_pedidos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_usuario');
        });
    }
};
