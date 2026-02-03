<?php

namespace App\Filament\Resources\ProveedorResource\Pages;

use App\Filament\Resources\ProveedorResource;
use App\Models\Empresa;
use App\Models\Proveedores;
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
            return Proveedores::query()->whereRaw('1 = 0');
        }

        $connectionName = ProveedorResource::getExternalConnectionName($filters['conexion']);
        if (!$connectionName) {
            return Proveedores::query()->whereRaw('1 = 0');
        }

        $empresaNombre = Empresa::find($filters['conexion'])?->nombre_empresa ?? 'JIREH';

        $model = new Proveedores();
        $model->setConnection($connectionName);
        $model->setTable('saeclpv');
        $model->setKeyName('clpv_cod_clpv');

        return $model->newQuery()
            ->select([
                'saeclpv.clpv_ruc_clpv as ruc',
                'saeclpv.clpv_nom_clpv as nombre',
                'saeclpv.clpv_nom_come as nombre_comercial',
                'saeclpv.clpv_lim_cred as limite_credito',
                'saeclpv.clpv_pro_pago as dias_pago',
                'saeclpv.clpv_ret_sn as aplica_retencion_sn',
                'saeclpv.clpv_cod_empr as admg_id_empresa',
                'saeclpv.clpv_cod_sucu as admg_id_sucursal',
                'saeclpv.clpv_fec_des as created_at',
                'tipo.identificacion as tipo',
                'grpv.grpv_nom_grpv as grupo',
                'zona.zona_nom_zona as zona',
                'cact.cact_nom_cact as flujo_caja',
                'tprov.tprov_des_tprov as tipo_proveedor',
                'fpagop.fpagop_des_fpagop as forma_pago',
                'tpago.tpago_des_tpago as destino_pago',
                'paisp.paisp_des_paisp as pais_pago',
            ])
            ->selectRaw('? as id_empresa', [$filters['conexion']])
            ->selectRaw('? as empresa_nombre', [$empresaNombre])
            ->selectRaw("CASE WHEN saeclpv.clpv_est_clpv = 'A' THEN 0 ELSE 1 END as anulada")
            ->selectRaw('saeclpv.clpv_cod_clpv as id')
            ->leftJoin('comercial.tipo_iden_clpv as tipo', 'tipo.tipo', '=', 'saeclpv.clv_con_clpv')
            ->leftJoin('saegrpv as grpv', function ($join) {
                $join->on('grpv.grpv_cod_grpv', '=', 'saeclpv.grpv_cod_grpv')
                    ->on('grpv.grpv_cod_empr', '=', 'saeclpv.clpv_cod_empr');
            })
            ->leftJoin('saezona as zona', function ($join) {
                $join->on('zona.zona_cod_zona', '=', 'saeclpv.clpv_cod_zona')
                    ->on('zona.zona_cod_empr', '=', 'saeclpv.clpv_cod_empr');
            })
            ->leftJoin('saecact as cact', function ($join) {
                $join->on('cact.cact_cod_cact', '=', 'saeclpv.clpv_cod_cact')
                    ->on('cact.cact_cod_empr', '=', 'saeclpv.clpv_cod_empr');
            })
            ->leftJoin('saetprov as tprov', function ($join) {
                $join->on('tprov.tprov_cod_tprov', '=', 'saeclpv.clpv_cod_tprov')
                    ->on('tprov.tprov_cod_empr', '=', 'saeclpv.clpv_cod_empr');
            })
            ->leftJoin('saefpagop as fpagop', function ($join) {
                $join->on('fpagop.fpagop_cod_fpagop', '=', 'saeclpv.clpv_cod_fpagop')
                    ->on('fpagop.fpagop_cod_empr', '=', 'saeclpv.clpv_cod_empr');
            })
            ->leftJoin('saetpago as tpago', function ($join) {
                $join->on('tpago.tpago_cod_tpago', '=', 'saeclpv.clpv_cod_tpago')
                    ->on('tpago.tpago_cod_empr', '=', 'saeclpv.clpv_cod_empr');
            })
            ->leftJoin('saepaisp as paisp', 'paisp.paisp_cod_paisp', '=', 'saeclpv.clpv_cod_paisp')
            ->where('saeclpv.clpv_cod_empr', $filters['empresa'])
            ->where('saeclpv.clpv_cod_sucu', $filters['sucursal'])
            ->where('saeclpv.clpv_clopv_clpv', 'PV');
    }

    public function syncJirehProveedorToLocal(object $record): Proveedores
    {
        $filters = $this->getJirehFilterState();
        $conexionId = $filters['conexion'] ?? null;

        $retencion = $record->aplica_retencion_sn ?? false;
        if (is_string($retencion)) {
            $retencion = strtoupper($retencion) === 'S';
        }

        return Proveedores::updateOrCreate(
            ['ruc' => $record->ruc],
            [
                'id_empresa' => $conexionId,
                'admg_id_empresa' => $record->admg_id_empresa ?? $filters['empresa'],
                'admg_id_sucursal' => $record->admg_id_sucursal ?? $filters['sucursal'],
                'tipo' => $record->tipo ?? '',
                'nombre' => trim((string) ($record->nombre ?? '')),
                'nombre_comercial' => trim((string) ($record->nombre_comercial ?? $record->nombre ?? '')),
                'grupo' => $record->grupo ?? '',
                'zona' => $record->zona ?? '',
                'flujo_caja' => $record->flujo_caja ?? '',
                'tipo_proveedor' => $record->tipo_proveedor ?? '',
                'forma_pago' => $record->forma_pago ?? '',
                'destino_pago' => $record->destino_pago ?? '',
                'pais_pago' => $record->pais_pago ?? '',
                'dias_pago' => $record->dias_pago ?? 0,
                'limite_credito' => $record->limite_credito ?? 0,
                'aplica_retencion_sn' => $retencion,
                'telefono' => $record->telefono ?? '',
                'direcccion' => $record->direcccion ?? '',
                'correo' => $record->correo ?? '',
                'anulada' => (bool) ($record->anulada ?? false),
            ],
        );
    }

    protected function getJirehFilterState(): array
    {
        return $this->tableFilters['jireh'] ?? [];
    }
}
