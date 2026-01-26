<?php

namespace App\Filament\Resources\OrdenCompraResource\Pages;

use App\Filament\Resources\OrdenCompraResource;
use App\Services\OrdenCompraSyncService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use App\Models\PedidoCompra;
use Illuminate\Support\Facades\Log;
use Filament\Actions\Action;
use Filament\Actions;

class EditOrdenCompra extends EditRecord
{
    protected static string $resource = OrdenCompraResource::class;
    protected array $pedidosOriginales = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['info_proveedor'] = $data['id_proveedor'] ?? null;
        $data['detalles'] = $this->hydrateDetallesForEdit($data);

        return $data;
    }

    protected function getListeners(): array
    {
        return [
            'pedidos_seleccionados' => 'onPedidosSeleccionados',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => OrdenCompraResource::canDelete($this->record))
                ->authorize(fn() => OrdenCompraResource::canDelete($this->record))
                ->disabled(fn() => $this->record->anulada)
                ->action(function () {
                    OrdenCompraSyncService::eliminar($this->record);
                    OrdenCompraSyncService::actualizarEstadoPedidos($this->record, null, 'Pendiente');
                    $this->record->delete();
                }),
        ];
    }

    public function onPedidosSeleccionados($pedidos, $connectionId, $motivo)
    {
        Log::info('Evento pedidos_seleccionados recibido en Edit', ['pedidos' => $pedidos, 'connectionId' => $connectionId, 'motivo' => $motivo]);

        if (empty($pedidos) || !$connectionId) {
            return;
        }

        $pedidosImportadosActuales = $this->parsePedidosImportados($this->data['pedidos_importados'] ?? null);
        $nuevosPedidos = $this->parsePedidosImportados($pedidos);
        $this->data['pedidos_importados'] = array_values(array_unique(array_merge($pedidosImportadosActuales, $nuevosPedidos)));

        $connectionName = OrdenCompraResource::getExternalConnectionName($connectionId);
        if (!$connectionName) {
            return;
        }

        if (empty($this->data['uso_compra'])) {
            $this->data['uso_compra'] = $motivo;
        }

        $detalles = DB::connection($connectionName)
            ->table('saedped')
            ->whereIn('dped_cod_pedi', $pedidos)
            ->whereColumn('dped_can_ped', '>', 'dped_can_ent')
            ->get();

        $detallesAgrupados = $detalles->values()->groupBy(function ($item, $key) {
            $codigoProducto = $item->dped_cod_prod ?? null;
            $codigoBodega = $item->dped_cod_bode ?? 'bode';
            $esServicio = $this->isServicioItem($codigoProducto);
            $esAuxiliar = $this->isAuxiliarItem($item);

            if ($esServicio) {
                return 'servicio-' . ($item->dped_cod_pedi ?? 'pedido') . '-' . $codigoBodega . '-' . uniqid('', true);
            }

            if ($esAuxiliar) {
                $auxKey = $item->dped_cod_auxiliar
                    ?? $item->dped_desc_auxiliar
                    ?? $item->dped_desc_axiliar
                    ?? $item->dped_det_dped
                    ?? 'aux';
                return 'aux-' . ($item->dped_cod_pedi ?? 'pedido') . '-' . $codigoBodega . '-' . $auxKey . '-' . $key;
            }

            if (!empty($codigoProducto)) {
                return $codigoProducto . '-' . $codigoBodega;
            }

            return 'aux-' . ($item->dped_det_dped ?? uniqid('', true));
        })->map(function ($group) {
            $first = $group->first();
            $cantidadPedida = $group->sum(fn($i) => (float)$i->dped_can_ped);
            $cantidadEntregada = $group->sum(fn($i) => (float)$i->dped_can_ent);
            $codigoProducto = $first->dped_cod_prod ?? null;
            return (object) [
                'dped_cod_prod' => $first->dped_cod_prod,
                'cantidad_pendiente' => $cantidadPedida - $cantidadEntregada,
                'dped_cod_bode' => $first->dped_cod_bode,
                'pedido_codigo' => $first->dped_cod_pedi ?? null,
                'pedido_detalle_id' => $first->dped_cod_dped ?? null,
                'es_auxiliar' => $this->isAuxiliarItem($first),
                'es_servicio' => $this->isServicioItem($first->dped_cod_prod ?? null),
                'auxiliar_codigo' => $first->dped_cod_auxiliar ?? null,
                'auxiliar_descripcion' => $first->dped_desc_auxiliar
                    ?? $first->dped_desc_axiliar
                    ?? null,
                'auxiliar_nombre' => $first->dped_det_dped
                    ?? $first->dped_desc_auxiliar
                    ?? $first->dped_desc_axiliar
                    ?? $first->deped_prod_nom
                    ?? null,
                'servicio_nombre' => $first->dped_det_dped
                    ?? $first->dped_desc_auxiliar
                    ?? $first->dped_desc_axiliar
                    ?? $first->deped_prod_nom
                    ?? null,
                'detalle_pedido' => $first->dped_det_dped ?? null,
                'codigo_producto_estandar' => $codigoProducto,
            ];
        })->where('cantidad_pendiente', '>', 0);

        if ($detallesAgrupados->isNotEmpty()) {
            $repeaterItems = $detallesAgrupados->map(function ($detalle) use ($connectionName) {
                $id_bodega_item = $detalle->dped_cod_bode;

                $costo = 0;
                $impuesto = 0;
                $productoNombre = 'Producto no encontrado';
                $codigoProducto = $detalle->codigo_producto_estandar ?? $detalle->dped_cod_prod;

                if (!empty($codigoProducto)) {
                    $productData = DB::connection($connectionName)
                        ->table('saeprod')
                        ->join('saeprbo', 'prbo_cod_prod', '=', 'prod_cod_prod')
                        ->where('prod_cod_empr', $this->data['amdg_id_empresa'])
                        ->where('prod_cod_sucu', $this->data['amdg_id_sucursal'])
                        ->where('prbo_cod_empr', $this->data['amdg_id_empresa'])
                        ->where('prbo_cod_sucu', $this->data['amdg_id_sucursal'])
                        ->where('prbo_cod_bode', $id_bodega_item)
                        ->where('prod_cod_prod', $codigoProducto)
                        ->select('prbo_uco_prod', 'prbo_iva_porc', 'prod_nom_prod')
                        ->first();

                    if ($productData) {
                        $costo = number_format($productData->prbo_uco_prod, 6, '.', '');
                        $impuesto = round($productData->prbo_iva_porc, 2);
                        $productoNombre = $productData->prod_nom_prod . ' (' . $codigoProducto . ')';
                    }
                }

                $valor_impuesto = (floatval($detalle->cantidad_pendiente) * floatval($costo)) * (floatval($impuesto) / 100);

                $auxiliarDescripcion = null;
                $auxiliarData = null;
                if ($detalle->es_auxiliar) {
                    $auxiliarDescripcion = trim(collect([
                        $detalle->auxiliar_codigo ? 'Código: ' . $detalle->auxiliar_codigo : null,
                        $detalle->auxiliar_descripcion ? 'Nombre: ' . $detalle->auxiliar_descripcion : null,
                    ])->filter()->implode(' | '));

                    $auxiliarData = [
                        'codigo' => $detalle->auxiliar_codigo,
                        'descripcion' => $detalle->auxiliar_nombre,
                        'descripcion_auxiliar' => $detalle->auxiliar_descripcion,
                    ];
                }

                $servicioDescripcion = null;
                if ($detalle->es_servicio) {
                    $servicioDescripcion = trim(collect([
                        $detalle->dped_cod_prod ? 'Código servicio: ' . $detalle->dped_cod_prod : null,
                        $detalle->servicio_nombre ? 'Descripción: ' . $detalle->servicio_nombre : null,
                    ])->filter()->implode(' | '));
                }

                $productoLinea = $detalle->es_servicio
                    ? ($detalle->servicio_nombre ?? $productoNombre)
                    : $productoNombre;

                return [
                    'id_bodega' => $id_bodega_item,
                    'codigo_producto' => $codigoProducto,
                    'producto' => $productoLinea,
                    'es_auxiliar' => $detalle->es_auxiliar,
                    'es_servicio' => $detalle->es_servicio,
                    'detalle_pedido' => $detalle->detalle_pedido,
                    'producto_auxiliar' => $auxiliarDescripcion,
                    'producto_servicio' => $servicioDescripcion,
                    'detalle' => $auxiliarData ? json_encode($auxiliarData, JSON_UNESCAPED_UNICODE) : null,
                    'pedido_codigo' => $detalle->pedido_codigo,
                    'pedido_detalle_id' => $detalle->pedido_detalle_id,
                    'cantidad' => $detalle->cantidad_pendiente,
                    'costo' => $costo,
                    'descuento' => 0,
                    'impuesto' => $impuesto,
                    'valor_impuesto' => number_format($valor_impuesto, 6, '.', ''),
                ];
            })->values()->toArray();

            // Filter out blank rows from existing details before merging
            $existingItems = array_filter($this->data['detalles'] ?? [], fn($item) => !empty($item['codigo_producto']));
            $this->data['detalles'] = array_merge($existingItems, $repeaterItems);
            
            // Recalculate totals after merging
            $this->recalculateTotals();
        }

        $this->applySolicitadoPor($connectionName, $this->parsePedidosImportados($this->data['pedidos_importados'] ?? ''));

        $this->dispatch('close-modal', id: 'importar_pedido');
    }

    private function recalculateTotals()
    {
        $subtotalGeneral = 0;
        $descuentoGeneral = 0;
        $impuestoGeneral = 0;

        foreach ($this->data['detalles'] as $detalle) {
            $cantidad = floatval($detalle['cantidad'] ?? 0);
            $costo = floatval($detalle['costo'] ?? 0);
            $descuento = floatval($detalle['descuento'] ?? 0);
            $porcentajeIva = floatval($detalle['impuesto'] ?? 0);
            $subtotalItem = $cantidad * $costo;
            $impuestoGeneral += ($subtotalItem - $descuento) * ($porcentajeIva / 100);
            $subtotalGeneral += $subtotalItem;
            $descuentoGeneral += $descuento;
        }

        $totalGeneral = ($subtotalGeneral - $descuentoGeneral) + $impuestoGeneral;

        $this->data['subtotal'] = number_format($subtotalGeneral, 2, '.', '');
        $this->data['total_descuento'] = number_format($descuentoGeneral, 2, '.', '');
        $this->data['total_impuesto'] = number_format($impuestoGeneral, 2, '.', '');
        $this->data['total'] = number_format($totalGeneral, 2, '.', '');
        
        // This is crucial to make the form's total display update in real-time
        $this->form->fill($this->data);
    }

    private function isServicioItem(?string $codigoProducto): bool
    {
        if (!$codigoProducto) {
            return false;
        }

        return (bool) preg_match('/^SP[-\\s]*SP[-\\s]*SP/i', $codigoProducto);
    }

    private function isAuxiliarItem(object $item): bool
    {
        return !empty($item->dped_cod_auxiliar)
            || !empty($item->dped_desc_auxiliar)
            || !empty($item->dped_desc_axiliar);
    }

    private function hydrateDetallesForEdit(array $data): array
    {
        $detalles = $data['detalles'] ?? [];
        if (!is_array($detalles) || $detalles === []) {
            return $detalles;
        }

        $empresaId = $data['id_empresa'] ?? null;
        $amdgEmpresa = $data['amdg_id_empresa'] ?? null;
        $amdgSucursal = $data['amdg_id_sucursal'] ?? null;
        $connectionName = $empresaId ? OrdenCompraResource::getExternalConnectionName($empresaId) : null;

        $pedidoDetalleMap = [];
        if ($connectionName && $amdgEmpresa && $amdgSucursal) {
            $detalleIds = collect($detalles)
                ->pluck('pedido_detalle_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($detalleIds)) {
                $pedidoDetalleMap = DB::connection($connectionName)
                    ->table('saedped')
                    ->where('dped_cod_empr', $amdgEmpresa)
                    ->where('dped_cod_sucu', $amdgSucursal)
                    ->whereIn('dped_cod_dped', $detalleIds)
                    ->select([
                        'dped_cod_dped',
                        'dped_cod_pedi',
                        'dped_det_dped',
                        'dped_cod_auxiliar',
                        'dped_desc_auxiliar',
                        'dped_desc_axiliar',
                    ])
                    ->get()
                    ->mapWithKeys(function ($row) {
                        $key = ((int) ($row->dped_cod_pedi ?? 0)) . ':' . ((int) ($row->dped_cod_dped ?? 0));
                        return [$key => $row];
                    })
                    ->all();
            }
        }

        return collect($detalles)->map(function (array $detalle) use ($pedidoDetalleMap) {
            $pedidoCodigo = $detalle['pedido_codigo'] ?? 0;
            $pedidoDetalleId = $detalle['pedido_detalle_id'] ?? 0;
            $lookupKey = ((int) $pedidoCodigo) . ':' . ((int) $pedidoDetalleId);
            $pedidoRow = $pedidoDetalleMap[$lookupKey] ?? null;

            $detalleJson = null;
            if (!empty($detalle['detalle']) && is_string($detalle['detalle'])) {
                $detalleJson = json_decode($detalle['detalle'], true);
            }

            $detallePedido = $detalle['detalle_pedido'] ?? null;
            if (!$detallePedido && $pedidoRow?->dped_det_dped) {
                $detallePedido = $pedidoRow->dped_det_dped;
            }

            $auxCodigo = $pedidoRow?->dped_cod_auxiliar
                ?? ($detalleJson['codigo'] ?? null);
            $auxNombre = $pedidoRow?->dped_desc_auxiliar
                ?? $pedidoRow?->dped_desc_axiliar
                ?? ($detalleJson['descripcion_auxiliar'] ?? null);

            $esAuxiliar = !empty($auxCodigo) || !empty($auxNombre);
            $auxiliarDescripcion = null;
            if ($esAuxiliar) {
                $auxiliarDescripcion = trim(collect([
                    $auxCodigo ? 'Código: ' . $auxCodigo : null,
                    $auxNombre ? 'Nombre: ' . $auxNombre : null,
                ])->filter()->implode(' | '));
            }

            $codigoProducto = $detalle['codigo_producto'] ?? null;
            $esServicio = $this->isServicioItem($codigoProducto);
            $servicioDescripcion = null;
            if ($esServicio) {
                $servicioNombre = $detallePedido ?: ($detalle['producto'] ?? null);
                $servicioDescripcion = trim(collect([
                    $codigoProducto ? 'Código servicio: ' . $codigoProducto : null,
                    $servicioNombre ? 'Descripción: ' . $servicioNombre : null,
                ])->filter()->implode(' | '));
            }

            $detalle['detalle_pedido'] = $detallePedido;
            $detalle['es_auxiliar'] = $esAuxiliar;
            $detalle['es_servicio'] = $esServicio;
            $detalle['producto_auxiliar'] = $auxiliarDescripcion;
            $detalle['producto_servicio'] = $servicioDescripcion;

            return $detalle;
        })->values()->all();
    }

    private function parsePedidosImportados(array|string|null $value): array
    {
        return OrdenCompraResource::normalizePedidosImportados($value);
    }

    private function applySolicitadoPor(string $connectionName, array $pedidos): void
    {
        if (empty($pedidos)) {
            return;
        }

        $solicitantes = DB::connection($connectionName)
            ->table('saepedi')
            ->whereIn('pedi_cod_pedi', $pedidos)
            ->pluck('pedi_res_pedi')
            ->filter(fn($value) => !empty(trim((string) $value)))
            ->map(fn($value) => trim((string) $value))
            ->unique()
            ->values();

        if ($solicitantes->isNotEmpty()) {
            $this->data['solicitado_por'] = $solicitantes->implode(', ');
            $this->form->fill($this->data);
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $newDetalles = [];
        if (isset($data['detalles']) && is_array($data['detalles'])) {
            foreach ($data['detalles'] as $detalle) {
                if (!isset($detalle['valor_impuesto'])) {
                    $cantidad = floatval($detalle['cantidad'] ?? 0);
                    $costo = floatval($detalle['costo'] ?? 0);
                    $descuento = floatval($detalle['descuento'] ?? 0);
                    $porcentajeIva = floatval($detalle['impuesto'] ?? 0);

                    $subtotalItem = $cantidad * $costo;
                    $baseImponible = $subtotalItem - $descuento;
                    $valorIva = $baseImponible * ($porcentajeIva / 100);

                    $detalle['valor_impuesto'] = number_format($valorIva, 6, '.', '');
                }
                $newDetalles[] = $detalle;
            }
            $data['detalles'] = $newDetalles;
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->pedidosOriginales = OrdenCompraResource::normalizePedidosImportados($this->record->pedidos_importados);
    }

    protected function afterSave(): void
    {
        $pedidosActuales = OrdenCompraResource::normalizePedidosImportados($this->record->pedidos_importados);
        $agregados = array_values(array_diff($pedidosActuales, $this->pedidosOriginales));
        $eliminados = array_values(array_diff($this->pedidosOriginales, $pedidosActuales));

        if (!empty($agregados)) {
            OrdenCompraSyncService::actualizarEstadoPedidos($this->record, $agregados, 'Atendido');
        }

        if (!empty($eliminados)) {
            OrdenCompraSyncService::actualizarEstadoPedidos($this->record, $eliminados, 'Pendiente');
        }
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->hidden();
    }
}
