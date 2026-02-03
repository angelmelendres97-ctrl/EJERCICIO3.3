<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use App\Models\Producto;
use App\Models\SaeProducto;
use App\Models\UnidadMedida;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProductos extends ListRecords
{
    protected static string $resource = ProductoResource::class;

    public function getTabs(): array
    {
        return [
            'local' => Tab::make('Local'),
            'jireh' => Tab::make('JIREH'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        if (($this->activeTab ?? null) !== 'jireh') {
            return ProductoResource::getEloquentQuery();
        }

        $filters = $this->getJirehFilters();
        $conexionId = $filters['conexion'] ?? null;
        $empresaCodigo = $filters['empresa'] ?? null;
        $sucursalCodigo = $filters['sucursal'] ?? null;

        if (! $conexionId || ! $empresaCodigo || ! $sucursalCodigo) {
            return Producto::query()->whereRaw('1 = 0');
        }

        $connectionName = ProductoResource::getExternalConnectionName((int) $conexionId);
        if (! $connectionName) {
            return Producto::query()->whereRaw('1 = 0');
        }

        return SaeProducto::on($connectionName)
            ->from('saeprod as prod')
            ->leftJoin('saeprbo as prbo', function ($join): void {
                $join->on('prbo.prbo_cod_prod', '=', 'prod.prod_cod_prod')
                    ->on('prbo.prbo_cod_empr', '=', 'prod.prod_cod_empr')
                    ->on('prbo.prbo_cod_sucu', '=', 'prod.prod_cod_sucu');
            })
            ->leftJoin('saeunid as unid', 'unid.unid_cod_unid', '=', 'prbo.prbo_cod_unid')
            ->where('prod.prod_cod_empr', $empresaCodigo)
            ->where('prod.prod_cod_sucu', $sucursalCodigo)
            ->groupBy(
                'prod.prod_cod_prod',
                'prod.prod_nom_prod',
                'prod.prod_det_prod',
                'prod.prod_cod_tpro',
                'prod.prod_cod_empr',
                'prod.prod_cod_sucu',
                'prod.prod_cod_linp',
                'prod.prod_cod_grpr',
                'prod.prod_cod_cate',
                'prod.prod_cod_marc'
            )
            ->selectRaw('prod.prod_cod_prod as sku')
            ->selectRaw('prod.prod_nom_prod as nombre')
            ->selectRaw('prod.prod_det_prod as detalle')
            ->selectRaw('prod.prod_cod_tpro as tipo')
            ->selectRaw('prod.prod_cod_empr as amdg_id_empresa')
            ->selectRaw('prod.prod_cod_sucu as amdg_id_sucursal')
            ->selectRaw('prod.prod_cod_linp as linea')
            ->selectRaw('prod.prod_cod_grpr as grupo')
            ->selectRaw('prod.prod_cod_cate as categoria')
            ->selectRaw('prod.prod_cod_marc as marca')
            ->selectRaw('? as id_empresa', [(int) $conexionId])
            ->selectRaw("concat(prod.prod_cod_empr, '-', prod.prod_cod_sucu, '-', prod.prod_cod_prod) as record_key")
            ->selectRaw('MIN(prbo.prbo_smi_prod) as stock_minimo')
            ->selectRaw('MAX(prbo.prbo_sma_prod) as stock_maximo')
            ->selectRaw("MAX(CASE WHEN prbo.prbo_iva_sino = 'S' THEN 1 ELSE 0 END) as iva_sn")
            ->selectRaw('MAX(prbo.prbo_iva_porc) as porcentaje_iva')
            ->selectRaw('MAX(unid.unid_nom_unid) as unidad_nombre');
    }

    public function editJirehProducto(SaeProducto $record)
    {
        $conexionId = $this->getJirehFilters()['conexion'] ?? null;
        if (! $conexionId) {
            return;
        }

        $unidadMedidaId = $this->resolveUnidadMedidaId($record->unidad_nombre ?? null);

        $producto = Producto::firstOrNew([
            'sku' => $record->sku,
            'id_empresa' => (int) $conexionId,
            'amdg_id_empresa' => $record->amdg_id_empresa,
            'amdg_id_sucursal' => $record->amdg_id_sucursal,
        ]);

        $producto->fill([
            'id_empresa' => (int) $conexionId,
            'amdg_id_empresa' => $record->amdg_id_empresa,
            'amdg_id_sucursal' => $record->amdg_id_sucursal,
            'linea' => $record->linea,
            'grupo' => $record->grupo,
            'categoria' => $record->categoria,
            'marca' => $record->marca,
            'sku' => $record->sku,
            'nombre' => $record->nombre,
            'detalle' => $record->detalle,
            'tipo' => $record->tipo,
            'id_unidad_medida' => $unidadMedidaId,
            'stock_minimo' => $record->stock_minimo ?? 0,
            'stock_maximo' => $record->stock_maximo ?? 0,
            'iva_sn' => (bool) $record->iva_sn,
            'porcentaje_iva' => $record->porcentaje_iva ?? 0,
        ]);

        $producto->save();

        return redirect()->to(ProductoResource::getUrl('edit', ['record' => $producto]));
    }

    protected function getJirehFilters(): array
    {
        return is_array($this->tableFilters['jireh_filters'] ?? null)
            ? $this->tableFilters['jireh_filters']
            : [];
    }

    protected function resolveUnidadMedidaId(?string $nombre): ?int
    {
        if ($nombre) {
            return UnidadMedida::firstOrCreate(['nombre' => $nombre])->id;
        }

        return UnidadMedida::query()->value('id');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
