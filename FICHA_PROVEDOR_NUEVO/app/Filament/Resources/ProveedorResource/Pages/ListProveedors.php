<?php

namespace App\Filament\Resources\ProveedorResource\Pages;

use App\Filament\Resources\ProveedorResource;
use App\Models\Proveedores;
use App\Models\SaeProveedor;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProveedors extends ListRecords
{
    protected static string $resource = ProveedorResource::class;

    public function getTabs(): array
    {
        return [
            'activos' => Tab::make('Activos')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('anulada', false)),
            'anuladas' => Tab::make('Anuladas')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('anulada', true)),
            'jireh' => Tab::make('JIREH'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        if (($this->activeTab ?? null) !== 'jireh') {
            return ProveedorResource::getEloquentQuery();
        }

        $filters = $this->getJirehFilters();
        $conexionId = $filters['conexion'] ?? null;
        $empresaCodigo = $filters['empresa'] ?? null;
        $sucursalCodigo = $filters['sucursal'] ?? null;

        if (! $conexionId || ! $empresaCodigo || ! $sucursalCodigo) {
            return Proveedores::query()->whereRaw('1 = 0');
        }

        $connectionName = ProveedorResource::getExternalConnectionName((int) $conexionId);
        if (! $connectionName) {
            return Proveedores::query()->whereRaw('1 = 0');
        }

        return SaeProveedor::on($connectionName)
            ->from('saeclpv as clpv')
            ->leftJoin('saegrpv as grpv', function ($join): void {
                $join->on('grpv.grpv_cod_grpv', '=', 'clpv.grpv_cod_grpv')
                    ->on('grpv.grpv_cod_empr', '=', 'clpv.clpv_cod_empr');
            })
            ->leftJoin('comercial.tipo_iden_clpv as tipo', 'tipo.tipo', '=', 'clpv.clv_con_clpv')
            ->where('clpv.clpv_cod_empr', $empresaCodigo)
            ->where('clpv.clpv_cod_sucu', $sucursalCodigo)
            ->where('clpv.clpv_clopv_clpv', 'PV')
            ->selectRaw('clpv.clpv_ruc_clpv as ruc')
            ->selectRaw('clpv.clpv_nom_clpv as nombre')
            ->selectRaw('clpv.clpv_nom_come as nombre_comercial')
            ->selectRaw('grpv.grpv_nom_grpv as grupo')
            ->selectRaw('clpv.clpv_fec_des as created_at')
            ->selectRaw('clpv.clpv_cod_empr as admg_id_empresa')
            ->selectRaw('clpv.clpv_cod_sucu as admg_id_sucursal')
            ->selectRaw('tipo.identificacion as tipo')
            ->selectRaw('? as id_empresa', [(int) $conexionId])
            ->selectRaw("concat(clpv.clpv_cod_empr, '-', clpv.clpv_cod_sucu, '-', clpv.clpv_cod_clpv) as record_key")
            ->selectRaw("CASE WHEN clpv.clpv_est_clpv = 'A' THEN 0 ELSE 1 END as anulada");
    }

    public function editJirehProveedor(SaeProveedor $record)
    {
        $conexionId = $this->getJirehFilters()['conexion'] ?? null;
        if (! $conexionId) {
            return;
        }

        $proveedor = Proveedores::firstOrNew([
            'ruc' => $record->ruc,
            'id_empresa' => (int) $conexionId,
            'admg_id_empresa' => $record->admg_id_empresa,
            'admg_id_sucursal' => $record->admg_id_sucursal,
        ]);

        $proveedor->fill([
            'id_empresa' => (int) $conexionId,
            'admg_id_empresa' => $record->admg_id_empresa,
            'admg_id_sucursal' => $record->admg_id_sucursal,
            'tipo' => $record->tipo,
            'ruc' => $record->ruc,
            'nombre' => $record->nombre,
            'nombre_comercial' => $record->nombre_comercial,
            'grupo' => $record->grupo,
            'anulada' => (bool) $record->anulada,
        ]);

        $proveedor->save();

        return redirect()->to(ProveedorResource::getUrl('edit', ['record' => $proveedor]));
    }

    protected function getJirehFilters(): array
    {
        return is_array($this->tableFilters['jireh_filters'] ?? null)
            ? $this->tableFilters['jireh_filters']
            : [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
