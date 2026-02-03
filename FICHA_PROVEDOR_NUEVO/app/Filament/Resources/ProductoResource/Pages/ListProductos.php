<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\UnidadMedida;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        if ($this->activeTab !== 'jireh') {
            return parent::getTableQuery();
        }

        $filters = $this->getJirehFilterState();
        if (empty($filters['conexion']) || empty($filters['empresa']) || empty($filters['sucursal'])) {
            return Producto::query()->whereRaw('1 = 0');
        }

        $connectionName = ProductoResource::getExternalConnectionName($filters['conexion']);
        if (!$connectionName) {
            return Producto::query()->whereRaw('1 = 0');
        }

        $empresaNombre = Empresa::find($filters['conexion'])?->nombre_empresa ?? 'JIREH';

        $model = new Producto();
        $model->setConnection($connectionName);
        $model->setTable('saeprod');
        $model->setKeyName('prod_cod_prod');

        return $model->newQuery()
            ->select([
                'saeprod.prod_cod_prod as sku',
                'saeprod.prod_nom_prod as nombre',
                'saeprod.prod_det_prod as detalle',
                'saeprod.prod_cod_tpro as tipo',
                'saeprod.prod_cod_empr as amdg_id_empresa',
                'saeprod.prod_cod_sucu as amdg_id_sucursal',
                'saeprod.prod_cod_linp as linea',
                'saeprod.prod_cod_grpr as grupo',
                'saeprod.prod_cod_cate as categoria',
                'saeprod.prod_cod_marc as marca',
                'prbo.prbo_smi_prod as stock_minimo',
                'prbo.prbo_sma_prod as stock_maximo',
                'prbo.prbo_iva_sino as iva_sn',
                'prbo.prbo_iva_porc as porcentaje_iva',
                'unid.unid_nom_unid as unidad_medida',
            ])
            ->selectRaw('? as id_empresa', [$filters['conexion']])
            ->selectRaw('? as empresa_nombre', [$empresaNombre])
            ->selectRaw('saeprod.prod_cod_prod as id')
            ->join('saeprbo as prbo', function ($join) {
                $join->on('prbo.prbo_cod_prod', '=', 'saeprod.prod_cod_prod')
                    ->on('prbo.prbo_cod_empr', '=', 'saeprod.prod_cod_empr')
                    ->on('prbo.prbo_cod_sucu', '=', 'saeprod.prod_cod_sucu');
            })
            ->leftJoin('saeunid as unid', 'unid.unid_cod_unid', '=', 'prbo.prbo_cod_unid')
            ->where('saeprod.prod_cod_empr', $filters['empresa'])
            ->where('saeprod.prod_cod_sucu', $filters['sucursal']);
    }

    public function syncJirehProductoToLocal(object $record): Producto
    {
        $filters = $this->getJirehFilterState();
        $conexionId = $filters['conexion'] ?? null;

        $unidadMedidaId = null;
        if (!empty($record->unidad_medida)) {
            $unidadMedidaId = UnidadMedida::query()
                ->where('nombre', $record->unidad_medida)
                ->value('id');
        }

        $unidadMedidaId ??= UnidadMedida::query()->value('id');

        $ivaSn = $record->iva_sn ?? false;
        if (is_string($ivaSn)) {
            $ivaSn = strtoupper($ivaSn) === 'S';
        }

        return Producto::updateOrCreate(
            ['sku' => $record->sku],
            [
                'id_empresa' => $conexionId,
                'amdg_id_empresa' => $record->amdg_id_empresa ?? $filters['empresa'],
                'amdg_id_sucursal' => $record->amdg_id_sucursal ?? $filters['sucursal'],
                'linea' => trim((string) ($record->linea ?? '')),
                'grupo' => trim((string) ($record->grupo ?? '')),
                'categoria' => trim((string) ($record->categoria ?? '')),
                'marca' => trim((string) ($record->marca ?? '')),
                'nombre' => trim((string) ($record->nombre ?? '')),
                'detalle' => $record->detalle ?? null,
                'tipo' => (int) ($record->tipo ?? 2),
                'id_unidad_medida' => $unidadMedidaId,
                'stock_minimo' => $record->stock_minimo ?? 0,
                'stock_maximo' => $record->stock_maximo ?? 0,
                'iva_sn' => $ivaSn,
                'porcentaje_iva' => $record->porcentaje_iva ?? 0,
            ],
        );
    }

    protected function getJirehFilterState(): array
    {
        return $this->tableFilters['jireh'] ?? [];
    }
}
