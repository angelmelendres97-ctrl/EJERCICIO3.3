<?php

namespace App\Filament\Resources\OrdenCompraResource\Pages;

use App\Filament\Resources\OrdenCompraResource;
use App\Services\OrdenCompraSyncService;
use App\Models\DetalleOrdenCompra;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CreateOrdenCompra extends CreateRecord
{
    protected static string $resource = OrdenCompraResource::class;
    protected static string $view = 'filament.resources.orden-compra-resource.pages.create-orden-compra';


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Crear e Imprimir');
    }

    protected function getListeners(): array
    {
        return [
            'pedidos_seleccionados' => 'onPedidosSeleccionados',
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_usuario'] = $data['id_usuario'] ?? auth()->id();
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

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {

            // Si por alguna razón Filament no envió 'detalles', lo tomamos del estado actual
            if (!array_key_exists('detalles', $data)) {
                $data['detalles'] = $this->data['detalles'] ?? [];
            }

            $record = static::getModel()::create($data);

            OrdenCompraSyncService::sincronizar($record, $data);

            return $record;
        });
    }


    protected function afterCreate(): void
    {
        if ($this->record) {
            $this->dispatch('open-orden-compra-pdf', url: route('orden-compra.pdf', $this->record));
        }
    }

    public function onPedidosSeleccionados($pedidos, $connectionId, $motivo)
    {
        Log::info('Evento pedidos_seleccionados recibido', ['pedidos' => $pedidos, 'connectionId' => $connectionId, 'motivo' => $motivo]);

        if (empty($pedidos) || !$connectionId) {
            return;
        }

        $pedidosNormalizados = $this->normalizePedidosImportados($pedidos);
        $pedidosExistentes = $this->normalizePedidosImportados($this->data['pedidos_importados'] ?? null);

        $pedidosUnicos = array_values(array_unique(array_merge($pedidosExistentes, $pedidosNormalizados)));

        $this->data['pedidos_importados'] = $pedidosUnicos;

        $connectionName = OrdenCompraResource::getExternalConnectionName($connectionId);
        if (!$connectionName) {
            return;
        }

        $this->data['uso_compra'] = $motivo;

        $unidades = DB::connection($connectionName)
            ->table('saeunid')
            ->select([
                'unid_cod_unid',
                DB::raw('MAX(unid_nom_unid) as unid_nom_unid'),
                DB::raw('MAX(unid_sigl_unid) as unid_sigl_unid'),
            ])
            ->when(
                DB::connection($connectionName)->getSchemaBuilder()->hasColumn('saeunid', 'unid_cod_empr'),
                fn($q) => $q->where('unid_cod_empr', $this->data['amdg_id_empresa'])
            )
            ->groupBy('unid_cod_unid');

        $detalles = DB::connection($connectionName)
            ->table('saedped as d')
            ->leftJoinSub($unidades, 'u', function ($join) {
                $join->on('u.unid_cod_unid', '=', 'd.dped_cod_unid');
            })
            ->whereIn('d.dped_cod_pedi', $pedidos)
            ->where('d.dped_cod_empr', $this->data['amdg_id_empresa'])
            ->where('d.dped_cod_sucu', $this->data['amdg_id_sucursal'])
            ->orderBy('d.dped_cod_pedi')
            ->orderBy('d.dped_cod_dped')
            ->select([
                'd.*',
                'u.unid_cod_unid',
                'u.unid_nom_unid',
                'u.unid_sigl_unid',
            ])
            ->get();


        $pairs = $detalles->map(fn($d) => [
            'pedido_codigo'      => (int) $d->dped_cod_pedi,
            'pedido_detalle_id'  => (int) $d->dped_cod_dped,
        ])->values()->all();

        $importadoPorDetalle = $this->resolveImportadoPorDetalle($pairs);

        $detallesPendientes = $detalles->map(function ($detalle) use ($importadoPorDetalle) {
            $cantidadPedida = (float) ($detalle->dped_can_ped ?? 0);
            $key = ((int) $detalle->dped_cod_pedi) . ':' . ((int) $detalle->dped_cod_dped);
            $cantidadImportada = (float) ($importadoPorDetalle[$key] ?? 0);
            $cantidadPendiente = $cantidadPedida - $cantidadImportada;


            $detalle->cantidad_pendiente = $cantidadPendiente;

            return $detalle;
        })->filter(fn($detalle) => $detalle->cantidad_pendiente > 0);

        if ($detallesPendientes->isNotEmpty()) {
            $repeaterItems = $detallesPendientes->map(function ($detalle) use ($connectionName) {
                // Use the specific warehouse code for this group
                $id_bodega_item = $detalle->dped_cod_bode;

                $costo = 0;
                $impuesto = 0;
                $productoNombre = 'Producto no encontrado';
                $codigoProducto = $detalle->dped_cod_prod ?? null;

                if (!empty($codigoProducto)) {
                    $productData = DB::connection($connectionName)
                        ->table('saeprod')
                        ->join('saeprbo', 'prbo_cod_prod', '=', 'prod_cod_prod')
                        ->where('prod_cod_empr', $this->data['amdg_id_empresa'])
                        ->where('prod_cod_sucu', $this->data['amdg_id_sucursal'])
                        ->where('prbo_cod_empr', $this->data['amdg_id_empresa'])
                        ->where('prbo_cod_sucu', $this->data['amdg_id_sucursal'])
                        ->where('prbo_cod_bode', $id_bodega_item) // Use the item-specific warehouse
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

                $esAuxiliar = $this->isAuxiliarItem($detalle);
                if ($esAuxiliar) {
                    $descripcionAuxiliar = $detalle->dped_desc_auxiliar ?? $detalle->dped_desc_axiliar;
                    $auxiliarDescripcion = trim(collect([
                        $detalle->dped_cod_auxiliar ? 'Código: ' . $detalle->dped_cod_auxiliar : null,
                        $descripcionAuxiliar ? 'Nombre: ' . $descripcionAuxiliar : null,
                    ])->filter()->implode(' | '));

                    $auxiliarData = [
                        'codigo' => $detalle->dped_cod_auxiliar,
                        'descripcion' => $detalle->dped_det_dped,
                        'descripcion_auxiliar' => $descripcionAuxiliar,
                    ];
                }

                $servicioDescripcion = null;

                $esServicio = $this->isServicioItem($codigoProducto);
                if ($esServicio) {
                    $servicioDescripcion = trim(collect([
                        $detalle->dped_cod_prod ? 'Código servicio: ' . $detalle->dped_cod_prod : null,
                        $detalle->dped_det_dped
                            ? 'Descripción: ' . $detalle->dped_det_dped
                            : null,
                    ])->filter()->implode(' | '));
                }

                $detallePedido = trim((string) ($detalle->dped_det_dped ?? ''));
                $detallePedido = $detallePedido !== '' ? $detallePedido : null;

                $productoLinea = $esServicio
                    ? ($detalle->dped_det_dped ?? $productoNombre)
                    : $productoNombre;
                $unidadItem = $detalle->unid_abr_unid
                    ?? $detalle->unid_nom_unid
                    ?? 'UN';


                return [
                    'id_bodega' => $id_bodega_item, // Set the correct warehouse for this line
                    'codigo_producto' => $codigoProducto,
                    'producto' => $productoLinea,
                    'unidad' => $unidadItem,
                    'es_auxiliar' => $esAuxiliar,
                    'es_servicio' => $esServicio,
                    'detalle_pedido' => $detallePedido,
                    'producto_auxiliar' => $auxiliarDescripcion,
                    'producto_servicio' => $servicioDescripcion,
                    'detalle' => $auxiliarData ? json_encode($auxiliarData, JSON_UNESCAPED_UNICODE) : null,
                    'pedido_codigo' => $detalle->dped_cod_pedi,
                    'pedido_detalle_id' => $detalle->dped_cod_dped,

                    'cantidad' => $detalle->cantidad_pendiente,
                    'costo' => $costo,
                    'descuento' => 0,
                    'impuesto' => $impuesto,
                    'valor_impuesto' => number_format($valor_impuesto, 6, '.', ''),
                ];
            })->values()->toArray();

            $existingItems = $this->data['detalles'] ?? [];
            $mergedItems = $this->mergeDetalleItems($existingItems, $repeaterItems);
            $this->data['detalles'] = $mergedItems;

            // Force recalculation of totals
            $subtotalGeneral = 0;
            $descuentoGeneral = 0;
            $impuestoGeneral = 0;

            foreach ($this->data['detalles'] as $detalle) {
                $cantidad = floatval($detalle['cantidad'] ?? 0);
                $costo = floatval($detalle['costo'] ?? 0);
                $descuento = floatval($detalle['descuento'] ?? 0);
                $porcentajeIva = floatval($detalle['impuesto'] ?? 0);
                $subtotalItem = $cantidad * $costo;
                $impuestoGeneral += $subtotalItem * ($porcentajeIva / 100);
                $subtotalGeneral += $subtotalItem;
                $descuentoGeneral += $descuento;
            }

            $totalGeneral = ($subtotalGeneral - $descuentoGeneral) + $impuestoGeneral;

            $this->data['subtotal'] = number_format($subtotalGeneral, 2, '.', '');
            $this->data['total_descuento'] = number_format($descuentoGeneral, 2, '.', '');
            $this->data['total_impuesto'] = number_format($impuestoGeneral, 2, '.', '');
            $this->data['total'] = number_format($totalGeneral, 2, '.', '');
        }

        $this->applySolicitadoPor($connectionName, $pedidosUnicos);

        // Use a more specific event name if needed, or just close the generic modal
        $this->dispatch('close-modal', id: 'importar_pedido');
    }

    protected function beforeCreate(): void
    {
        $detalles = $this->data['detalles'] ?? [];

        $faltantes = collect($detalles)->filter(function ($detalle) {
            $requiereProducto = !empty($detalle['es_auxiliar']) || !empty($detalle['es_servicio']);

            return $requiereProducto && empty($detalle['codigo_producto']);
        });

        if ($faltantes->isNotEmpty()) {
            throw ValidationException::withMessages([
                'detalles' => 'Debe seleccionar un producto del inventario para cada ítem auxiliar o de servicio.',
            ]);
        }
    }

    private function normalizePedidosImportados(array|string|null $pedidos): array
    {
        return OrdenCompraResource::normalizePedidosImportados($pedidos);
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

    private function detalleKey(array $item, int $index): string
    {
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

    private function resolveImportadoPorDetalle(array $pedidoDetallePairs): array
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

        $rows = DetalleOrdenCompra::query()
            ->select([
                'pedido_codigo',
                'pedido_detalle_id',
                DB::raw('SUM(cantidad) as cantidad_importada'),
            ])
            ->whereIn('pedido_codigo', $pedidoCodigos)
            ->whereIn('pedido_detalle_id', $detalleIds)
            ->whereHas('ordenCompra', function ($q) use ($idEmpresa, $amdgEmpresa, $amdgSucu) {
                $q->where('anulada', false);

                if ($idEmpresa)   $q->where('id_empresa', $idEmpresa);
                if ($amdgEmpresa) $q->where('amdg_id_empresa', $amdgEmpresa);
                if ($amdgSucu)    $q->where('amdg_id_sucursal', $amdgSucu);
            })
            ->groupBy('pedido_codigo', 'pedido_detalle_id')
            ->get();

        return $rows->mapWithKeys(function ($row) {
            $key = ((int) $row->pedido_codigo) . ':' . ((int) $row->pedido_detalle_id);
            return [$key => (float) $row->cantidad_importada];
        })->all();
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
        }
    }
}
