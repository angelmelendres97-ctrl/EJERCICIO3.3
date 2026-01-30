<?php

namespace App\Filament\Resources\ResumenPedidosResource\Pages;

use App\Filament\Resources\ResumenPedidosResource;
use App\Models\DetalleResumenPedidos;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use App\Models\ResumenPedidos;

class CreateResumenPedidos extends CreateRecord
{
    protected static string $resource = ResumenPedidosResource::class;
    protected static string $view = 'filament.resources.resumen-pedidos-resource.pages.create-resumen-pedidos';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Crear e Imprimir');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_usuario'] = $data['id_usuario'] ?? auth()->id();
        $conexiones = $this->data['conexiones'] ?? [];
        $empresasSeleccionadas = ResumenPedidosResource::groupOptionsByConnection($this->data['empresas'] ?? []);
        $sucursalesSeleccionadas = ResumenPedidosResource::groupOptionsByConnection($this->data['sucursales'] ?? []);

        $conexionPrincipal = $conexiones[0] ?? null;
        $empresaPrincipal = $conexionPrincipal ? ($empresasSeleccionadas[$conexionPrincipal][0] ?? null) : null;
        $sucursalPrincipal = $conexionPrincipal ? ($sucursalesSeleccionadas[$conexionPrincipal][0] ?? null) : null;

        if ($conexionPrincipal) {
            $data['id_empresa'] = $conexionPrincipal;
        }

        if ($empresaPrincipal) {
            $data['amdg_id_empresa'] = $empresaPrincipal;
        }

        if ($sucursalPrincipal) {
            $data['amdg_id_sucursal'] = $sucursalPrincipal;
        }

        // Calculate the next sequential number
        $nextSecuencial = (ResumenPedidos::max('codigo_secuencial') ?? 0) + 1;
        $data['codigo_secuencial'] = $nextSecuencial;

        // You might want to add other default fields here if necessary
        // For example, if 'tipo' or 'descripcion' are based on other fields
        $data['tipo'] = $data['tipo_presupuesto']; // Assuming this is the case from the form
        $data['descripcion'] = 'Resumen generado el ' . now()->toDateTimeString();

        unset($data['conexiones'], $data['empresas'], $data['sucursales']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Get the created record
        $resumenPedido = $this->getRecord();

        // Get the purchase orders from the form data
        $ordenesCompra = $this->data['ordenes_compra'] ?? [];

        // Filter for selected orders and create detail records
        foreach ($ordenesCompra as $orden) {
            if (!empty($orden['checkbox_oc'])) {
                DetalleResumenPedidos::create([
                    'id_resumen_pedidos' => $resumenPedido->id,
                    'id_orden_compra' => $orden['id_orden_compra'],
                ]);
            }
        }

        if ($resumenPedido) {
            $this->dispatch('open-resumen-pedidos-pdf', url: route('resumen-pedidos.pdf', $resumenPedido));
        }
    }
}
