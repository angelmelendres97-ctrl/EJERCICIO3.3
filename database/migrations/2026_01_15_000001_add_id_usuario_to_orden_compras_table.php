<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orden_compras', function (Blueprint $table) {
            $table->foreignId('id_usuario')
                ->nullable()
                ->after('id_empresa')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orden_compras', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_usuario');
        });
    }
};
