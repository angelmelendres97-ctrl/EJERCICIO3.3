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
    protected const AUXILIAR_LABEL = 'Código: ';
    protected const AUXILIAR_NOMBRE_LABEL = 'Nombre: ';
    protected const SERVICIO_CODIGO_LABEL = 'Código servicio: ';
    protected const SERVICIO_DESCRIPCION_LABEL = 'Descripción: ';

    protected function getListeners(): array
    {
        return [
            'pedidos_seleccionados' => 'onPedidosSeleccionados',
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['info_proveedor'] = $data['id_proveedor'] ?? null;

        $detallePorPedido = $this->resolveDetallePedidoData($data);

        if (isset($data['detalles']) && is_array($data['detalles'])) {
            foreach ($data['detalles'] as $index => $detalle) {
                $detalleData = $this->decodeDetalleData($detalle['detalle'] ?? null);
                $codigoProducto = $detalle['codigo_producto'] ?? null;
                $esServicio = $this->isServicioItem($codigoProducto);
                $esAuxiliar = !empty($detalleData['codigo'])
                    || !empty($detalleData['descripcion_auxiliar'])
                    || !empty($detalleData['descripcion']);

                $data['detalles'][$index]['es_auxiliar'] = $esAuxiliar;
                $data['detalles'][$index]['es_servicio'] = $esServicio;

                if ($esAuxiliar) {
                    $auxiliarNombre = $detalleData['descripcion_auxiliar']
                        ?? $detalleData['descripcion']
                        ?? null;

                    $data['detalles'][$index]['producto_auxiliar'] = trim(collect([
                        $detalleData['codigo'] ? self::AUXILIAR_LABEL . $detalleData['codigo'] : null,
                        $auxiliarNombre ? self::AUXILIAR_NOMBRE_LABEL . $auxiliarNombre : null,
                    ])->filter()->implode(' | '));
                }

                if ($esServicio) {
                    $servicioDescripcion = $detalleData['descripcion']
                        ?? $detalle['producto']
                        ?? null;

                    $data['detalles'][$index]['producto_servicio'] = trim(collect([
                        $codigoProducto ? self::SERVICIO_CODIGO_LABEL . $codigoProducto : null,
                        $servicioDescripcion ? self::SERVICIO_DESCRIPCION_LABEL . $servicioDescripcion : null,
                    ])->filter()->implode(' | '));
                }

                if (empty($detalle['detalle_pedido'])) {
                    $pedidoKey = $this->detallePedidoKey($detalle);
                    $detallePedido = $detallePorPedido[$pedidoKey] ?? null;
                    $detallePedido = $detallePedido
                        ?? $detalleData['descripcion']
                        ?? $detalleData['descripcion_auxiliar']
                        ?? null;

                    $data['detalles'][$index]['detalle_pedido'] = $detallePedido;
                }
            }
        }

        return $data;
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

    private function resolveImportadoPorDetalleEdit(array $pedidoDetallePairs): array
    {
        if (empty($pedidoDetallePairs)) {
            return [];
        }

        $pedidoCodigos = collect($pedidoDetallePairs)->pluck('pedido_codigo')->filter()->unique()->values()->all();
        $detalleIds    = collect($pedidoDetallePairs)->pluck('pedido_detalle_id')->filter()->unique()->values()->all();

        if (empty($pedidoCodigos) || empty($detalleIds)) {
            return [];
        }

        $idEmpresa   = $this->data['id_empresa'] ?? null;
        $amdgEmpresa = $this->data['amdg_id_empresa'] ?? null;
        $amdgSucu    = $this->data['amdg_id_sucursal'] ?? null;

        $currentOrdenId = $this->record?->id; // MUY IMPORTANTE: excluir esta misma OC

        $rows = \App\Models\DetalleOrdenCompra::query()
            ->select([
                'pedido_codigo',
                'pedido_detalle_id',
                DB::raw('SUM(cantidad) as cantidad_importada'),
            ])
            ->whereIn('pedido_codigo', $pedidoCodigos)
            ->whereIn('pedido_detalle_id', $detalleIds)
            ->whereHas('ordenCompra', function ($q) use ($idEmpresa, $amdgEmpresa, $amdgSucu, $currentOrdenId) {
                $q->where('anulada', false);

                if ($currentOrdenId) {
                    $q->where('id', '!=', $currentOrdenId);
                }

                if ($idEmpresa)    $q->where('id_empresa', $idEmpresa);
                if ($amdgEmpresa)  $q->where('amdg_id_empresa', $amdgEmpresa);
                if ($amdgSucu)     $q->where('amdg_id_sucursal', $amdgSucu);
            })
            ->groupBy('pedido_codigo', 'pedido_detalle_id')
            ->get();

        return $rows->mapWithKeys(function ($row) {
            $key = ((int) $row->pedido_codigo) . ':' . ((int) $row->pedido_detalle_id);
            return [$key => (float) $row->cantidad_importada];
        })->all();
    }


    public function onPedidosSeleccionados($pedidos, $connectionId, $motivo)
    {
        Log::info('Evento pedidos_seleccionados recibido en Edit', [
            'pedidos' => $pedidos,
            'connectionId' => $connectionId,
            'motivo' => $motivo,
        ]);

        // -----------------------------
        // 1) Normalizar pedidos (sin ceros a la izquierda)
        // -----------------------------
        $pedidosSeleccionados = $this->parsePedidosImportados($pedidos);

        $normalizePedido = fn($p) => (ltrim((string) $p, '0') === '') ? '0' : ltrim((string) $p, '0');

        $pedidosSeleccionadosNorm = array_values(array_unique(array_map($normalizePedido, $pedidosSeleccionados)));
        $pedidosUnicos = $pedidosSeleccionados;
        $pedidosUnicosNorm = $pedidosSeleccionadosNorm;

        $this->data['pedidos_importados'] = $pedidosUnicos;

        // -----------------------------
        // 2) Si quitaron pedidos: limpiar repeater (dejar manuales + los pedidos aún presentes)
        // -----------------------------
        $existingItems = $this->data['detalles'] ?? [];

        $existingItems = array_values(array_filter($existingItems, function ($row) use ($pedidosUnicosNorm, $normalizePedido) {
            $pedido = $row['pedido_codigo'] ?? null;

            // manual (sin pedido)
            if (empty($pedido)) {
                return true;
            }

            $pedidoNorm = $normalizePedido($pedido);

            return in_array($pedidoNorm, $pedidosUnicosNorm, true);
        }));

        // si no quedan pedidos, deja solo manuales
        if (empty($pedidosUnicosNorm)) {
            $existingItems = array_values(array_filter($existingItems, fn($r) => empty($r['pedido_codigo'] ?? null)));
        }

        $this->data['detalles'] = $existingItems;

        if (empty($pedidosUnicosNorm)) {
            $this->recalculateTotals();
            $this->form->fill($this->data);
            $this->dispatch('close-modal', id: 'importar_pedido');
            return;
        }

        // -----------------------------
        // 3) conexión externa
        // -----------------------------
        $connectionName = OrdenCompraResource::getExternalConnectionName($connectionId);
        if (!$connectionName) {
            return;
        }

        if (empty($this->data['uso_compra'])) {
            $this->data['uso_compra'] = $motivo;
        }

        // -----------------------------
        // 4) Traer detalles de SAE (línea por línea)
        //    OJO: aquí usamos pedidosSeleccionadosNorm (solo los recién elegidos)
        // -----------------------------
        $schema = DB::connection($connectionName)->getSchemaBuilder();

        $query = DB::connection($connectionName)
            ->table('saedped as d')
            ->whereIn(DB::raw("ltrim(d.dped_cod_pedi::text, '0')"), $pedidosSeleccionadosNorm);

        if ($schema->hasColumn('saedped', 'dped_cod_empr')) {
            $query->where('d.dped_cod_empr', $this->data['amdg_id_empresa']);
        }
        if ($schema->hasColumn('saedped', 'dped_cod_sucu')) {
            $query->where('d.dped_cod_sucu', $this->data['amdg_id_sucursal']);
        }

        // opcional: si quieres solo pendientes por ENTREGADO también, descomenta:
        // $query->whereColumn('d.dped_can_ped', '>', 'd.dped_can_ent');

        $detalles = $query->select('d.*')->get();

        if ($detalles->isEmpty()) {
            // igual actualiza solicitado_por y cierra modal
            $this->applySolicitadoPor($connectionName, $pedidosUnicos);
            $this->form->fill($this->data);
            $this->dispatch('close-modal', id: 'importar_pedido');
            return;
        }

        // -----------------------------
        // 5) Calcular "cuánto ya está importado" en otras OCs (excluyendo esta OC)
        // -----------------------------
        $pairs = $detalles->map(fn($d) => [
            'pedido_codigo'     => (int) $d->dped_cod_pedi,
            'pedido_detalle_id' => (int) $d->dped_cod_dped,
        ])->values()->all();

        $importadoPorDetalle = $this->resolveImportadoPorDetalleEdit($pairs);

        // -----------------------------
        // 6) Pendiente = pedido - importadoEnOtrasOCs
        // -----------------------------
        $detallesPendientes = $detalles->map(function ($detalle) use ($importadoPorDetalle) {
            $cantidadPedida = (float) ($detalle->dped_can_ped ?? 0);

            $key = ((int) $detalle->dped_cod_pedi) . ':' . ((int) $detalle->dped_cod_dped);
            $cantidadImportada = (float) ($importadoPorDetalle[$key] ?? 0);

            $detalle->cantidad_pendiente = $cantidadPedida - $cantidadImportada;

            return $detalle;
        })->filter(fn($d) => (float) $d->cantidad_pendiente > 0);

        if ($detallesPendientes->isEmpty()) {
            $this->applySolicitadoPor($connectionName, $pedidosUnicos);
            $this->form->fill($this->data);
            $this->dispatch('close-modal', id: 'importar_pedido');
            return;
        }

        // -----------------------------
        // 7) Construir items del repeater (SIN AGRUPAR)
        // -----------------------------
        $repeaterItems = $detallesPendientes->map(function ($detalle) use ($connectionName) {
            $id_bodega_item = $detalle->dped_cod_bode ?? null;

            $costo = 0;
            $impuesto = 0;
            $productoNombre = 'Producto no encontrado';
            $codigoProducto = $detalle->dped_cod_prod ?? null;

            if (!empty($codigoProducto) && !empty($id_bodega_item)) {
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

            $esAuxiliar = $this->isAuxiliarItem($detalle);
            $esServicio = $this->isServicioItem($codigoProducto);

            $valor_impuesto = ((float) $detalle->cantidad_pendiente * (float) $costo) * ((float) $impuesto / 100);

            $auxiliarDescripcion = null;
            $auxiliarData = null;

            if ($esAuxiliar) {
                $descripcionAuxiliar = $detalle->dped_desc_auxiliar ?? $detalle->dped_desc_axiliar ?? null;

                $auxiliarDescripcion = trim(collect([
                    $detalle->dped_cod_auxiliar ? 'Código auxiliar: ' . $detalle->dped_cod_auxiliar : null,
                    $detalle->dped_det_dped ? 'Descripción: ' . $detalle->dped_det_dped : null,
                    $descripcionAuxiliar ? 'Descripción auxiliar: ' . $descripcionAuxiliar : null,
                ])->filter()->implode(' | '));

                $auxiliarData = [
                    'codigo' => $detalle->dped_cod_auxiliar ?? null,
                    'descripcion' => $detalle->dped_det_dped ?? null,
                    'descripcion_auxiliar' => $descripcionAuxiliar,
                ];
            }

            $servicioDescripcion = null;
            if ($esServicio) {
                $servicioDescripcion = trim(collect([
                    $codigoProducto ? 'Código servicio: ' . $codigoProducto : null,
                    $detalle->dped_det_dped ? 'Descripción: ' . $detalle->dped_det_dped : null,
                ])->filter()->implode(' | '));
            }

            $productoLinea = $esServicio
                ? ($detalle->dped_det_dped ?? $productoNombre)
                : $productoNombre;

            return [
                'id_bodega' => $id_bodega_item,
                'codigo_producto' => $codigoProducto,
                'producto' => $productoLinea,

                'es_auxiliar' => $esAuxiliar,
                'es_servicio' => $esServicio,
                'detalle_pedido' => $detalle->dped_det_dped ?? null,

                'producto_auxiliar' => $auxiliarDescripcion,
                'producto_servicio' => $servicioDescripcion,
                'detalle' => $auxiliarData ? json_encode($auxiliarData, JSON_UNESCAPED_UNICODE) : null,

                // ✅ CLAVE ÚNICA por línea
                'pedido_codigo' => (int) $detalle->dped_cod_pedi,
                'pedido_detalle_id' => (int) $detalle->dped_cod_dped,

                // ✅ pendiente calculado
                'cantidad' => (float) $detalle->cantidad_pendiente,

                'costo' => $costo,
                'descuento' => 0,
                'impuesto' => $impuesto,
                'valor_impuesto' => number_format($valor_impuesto, 6, '.', ''),
            ];
        })->values()->toArray();

        // -----------------------------
        // 8) Merge + recalcular + fill
        // -----------------------------
        $existingItems = $this->data['detalles'] ?? [];
        $this->data['detalles'] = $this->mergeDetalleItems($existingItems, $repeaterItems);

        $this->recalculateTotals();

        $this->applySolicitadoPor($connectionName, $pedidosUnicos);
        $this->form->fill($this->data);

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

    private function resolveDetallePedidoData(array $data): array
    {
        $idEmpresa = $data['id_empresa'] ?? null;
        $amdgEmpresa = $data['amdg_id_empresa'] ?? null;
        $amdgSucursal = $data['amdg_id_sucursal'] ?? null;
        $detalles = $data['detalles'] ?? [];

        if (!$idEmpresa || !$amdgEmpresa || !$amdgSucursal || empty($detalles)) {
            return [];
        }

        $pares = collect($detalles)
            ->filter(fn($detalle) => empty($detalle['detalle_pedido'])
                && !empty($detalle['pedido_codigo'])
                && !empty($detalle['pedido_detalle_id']))
            ->map(fn($detalle) => [
                'pedido_codigo' => (int) $detalle['pedido_codigo'],
                'pedido_detalle_id' => (int) $detalle['pedido_detalle_id'],
            ])
            ->unique()
            ->values();

        if ($pares->isEmpty()) {
            return [];
        }

        $connectionName = OrdenCompraResource::getExternalConnectionName((int) $idEmpresa);
        if (!$connectionName) {
            return [];
        }

        $schema = DB::connection($connectionName)->getSchemaBuilder();
        $pedidoCodigos = $pares->pluck('pedido_codigo')->unique()->values()->all();
        $detalleIds = $pares->pluck('pedido_detalle_id')->unique()->values()->all();

        $builder = DB::connection($connectionName)
            ->table('saedped')
            ->whereIn('dped_cod_pedi', $pedidoCodigos)
            ->whereIn('dped_cod_dped', $detalleIds)
            ->select('dped_cod_pedi', 'dped_cod_dped', 'dped_det_dped');

        if ($schema->hasColumn('saedped', 'dped_cod_empr') && $schema->hasColumn('saedped', 'dped_cod_sucu')) {
            $builder->where(function ($query) use ($amdgEmpresa, $amdgSucursal) {
                $query->where('dped_cod_empr', $amdgEmpresa)
                    ->where('dped_cod_sucu', $amdgSucursal);

                if ($amdgEmpresa !== $amdgSucursal) {
                    $query->orWhere(function ($subQuery) use ($amdgEmpresa, $amdgSucursal) {
                        $subQuery->where('dped_cod_empr', $amdgSucursal)
                            ->where('dped_cod_sucu', $amdgEmpresa);
                    });
                }
            });
        }

        $rows = $builder->get();

        return $rows->mapWithKeys(function ($row) {
            $key = ((int) $row->dped_cod_pedi) . ':' . ((int) $row->dped_cod_dped);
            $detalle = trim((string) ($row->dped_det_dped ?? ''));
            return [$key => $detalle !== '' ? $detalle : null];
        })->all();
    }

    private function detallePedidoKey(array $detalle): ?string
    {
        $pedidoCodigo = $detalle['pedido_codigo'] ?? null;
        $pedidoDetalleId = $detalle['pedido_detalle_id'] ?? null;

        if (!$pedidoCodigo || !$pedidoDetalleId) {
            return null;
        }

        return sprintf('%s:%s', (int) $pedidoCodigo, (int) $pedidoDetalleId);
    }

    private function mergeDetalleItems(array $existingItems, array $newItems): array
    {
        $merged = [];
        $usedKeys = [];

        foreach ($existingItems as $index => $item) {
            $key = $this->detalleKey($item, $index);
            $usedKeys[$key] = true;
            $merged[] = $item;
        }

        foreach ($newItems as $index => $item) {
            $key = $this->detalleKey($item, $index);
            if (isset($usedKeys[$key])) {
                continue;
            }
            $usedKeys[$key] = true;
            $merged[] = $item;
        }

        return $merged;
    }

    private function detalleKey(array $item, int|string $index): string
    {
        $index = (string) $index;
        $pedidoCodigo = $item['pedido_codigo'] ?? null;
        $pedidoDetalleId = $item['pedido_detalle_id'] ?? null;

        if ($pedidoCodigo && $pedidoDetalleId) {
            return sprintf('pedido:%s:%s', $pedidoCodigo, $pedidoDetalleId);
        }

        $codigoProducto = $item['codigo_producto'] ?? null;
        $bodega = $item['id_bodega'] ?? null;
        $detalle = $item['detalle'] ?? null;
        $descripcion = $item['producto'] ?? null;

        return sprintf(
            'manual:%s:%s:%s:%s:%s',
            $index,
            $codigoProducto,
            $bodega,
            $detalle,
            $descripcion
        );
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

    private function parsePedidosImportados(array|string|null $value): array
    {
        return OrdenCompraResource::normalizePedidosImportados($value);
    }

    private function decodeDetalleData(null|string|array $detalle): array
    {
        if (is_array($detalle)) {
            return $detalle;
        }

        if (!$detalle) {
            return [];
        }

        $decoded = json_decode($detalle, true);

        return is_array($decoded) ? $decoded : [];
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
        return parent::getSaveFormAction();
    }
}
