<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empresa')->constrained('empresas')->cascadeOnDelete();
            $table->date('fecha');
            $table->text('motivo')->nullable();
            $table->string('estado')->default('BORRADOR');
            $table->string('tipo_solicitud')->default('Pago de Facturas');
            $table->decimal('monto_aprobado', 15, 2)->default(0);
            $table->decimal('monto_estimado', 15, 2)->default(0);
            $table->decimal('monto_utilizado', 15, 2)->default(0);
            $table->foreignId('creado_por_id')->constrained('users');
            $table->foreignId('aprobado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('aprobada_at')->nullable();
            $table->timestamps();
        });

        Schema::create('solicitud_pago_contextos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_pago_id')->constrained('solicitud_pagos')->cascadeOnDelete();
            $table->string('conexion');
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('sucursal_codigo');
            $table->timestamps();

            $table->unique(['solicitud_pago_id', 'conexion', 'empresa_id', 'sucursal_codigo'], 'solicitud_pago_contexto_unique');
        });

        Schema::create('solicitud_pago_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_pago_id')->constrained('solicitud_pagos')->cascadeOnDelete();
            $table->string('erp_tabla')->default('SAEDMCP');
            $table->string('erp_conexion');
            $table->string('erp_empresa_id');
            $table->string('erp_sucursal');
            $table->string('erp_clave');
            $table->string('proveedor_ruc');
            $table->string('proveedor_codigo')->nullable();
            $table->string('proveedor_nombre');
            $table->string('numero_factura');
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('monto_factura', 15, 2);
            $table->decimal('saldo_al_crear', 15, 2);
            $table->decimal('abono_aplicado', 15, 2)->default(0);
            $table->string('estado_abono')->default('SIN_ABONO');
            $table->timestamps();

            $table->unique(['solicitud_pago_id', 'erp_clave'], 'solicitud_pago_detalles_unique');
            $table->index('proveedor_ruc');
            $table->index('numero_factura');
            $table->index('estado_abono');
            $table->index(['erp_conexion', 'erp_empresa_id', 'erp_sucursal'], 'solicitud_pago_detalles_contexto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_pago_detalles');
        Schema::dropIfExists('solicitud_pago_contextos');
        Schema::dropIfExists('solicitud_pagos');
    }
};
