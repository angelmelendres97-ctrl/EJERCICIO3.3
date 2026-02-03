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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListProductos extends ListRecords
{
    protected static string $resource = ProductoResource::class;

    protected ?string $jirehSyncKey = null;

    public function getTabs(): array
    {
        return [
            'local' => Tab::make('Laravel'),
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
        $query = parent::getTableQuery();

        if ($this->activeTab === 'jireh') {
            $context = $this->getJirehContext();
            $this->syncJirehProductos($context);

            if (empty($context['id_empresa']) || empty($context['amdg_id_empresa']) || empty($context['amdg_id_sucursal'])) {
                return $query->whereRaw('1 = 0');
            }

            return $query
                ->where('id_empresa', $context['id_empresa'])
                ->where('amdg_id_empresa', $context['amdg_id_empresa'])
                ->where('amdg_id_sucursal', $context['amdg_id_sucursal']);
        }

        return $query;
    }

    private function getJirehContext(): array
    {
        $filters = $this->tableFilters ?? [];
        $context = data_get($filters, 'jireh_context');

        return is_array($context) ? $context : [];
    }

    private function syncJirehProductos(array $context): void
    {
        $empresaId = isset($context['id_empresa']) ? (int) $context['id_empresa'] : null;
        $empresaCodigo = isset($context['amdg_id_empresa']) ? (int) $context['amdg_id_empresa'] : null;
        $sucursalCodigo = isset($context['amdg_id_sucursal']) ? (int) $context['amdg_id_sucursal'] : null;

        if (!$empresaId || !$empresaCodigo || !$sucursalCodigo) {
            return;
        }

        $syncKey = "{$empresaId}|{$empresaCodigo}|{$sucursalCodigo}";
        if ($this->jirehSyncKey === $syncKey) {
            return;
        }
        $this->jirehSyncKey = $syncKey;

        $connectionName = ProductoResource::getExternalConnectionName($empresaId);
        if (!$connectionName) {
            return;
        }

        try {
            $productos = DB::connection($connectionName)
                ->table('saeprod as p')
                ->leftJoin('saeprbo as pr', function ($join) {
                    $join->on('pr.prbo_cod_prod', '=', 'p.prod_cod_prod')
                        ->on('pr.prbo_cod_empr', '=', 'p.prod_cod_empr')
                        ->on('pr.prbo_cod_sucu', '=', 'p.prod_cod_sucu');
                })
                ->leftJoin('saeunid as u', 'u.unid_cod_unid', '=', 'pr.prbo_cod_unid')
                ->where('p.prod_cod_empr', $empresaCodigo)
                ->where('p.prod_cod_sucu', $sucursalCodigo)
                ->groupBy(
                    'p.prod_cod_prod',
                    'p.prod_cod_empr',
                    'p.prod_cod_sucu',
                    'p.prod_nom_prod',
                    'p.prod_det_prod',
                    'p.prod_des_prod',
                    'p.prod_cod_tpro',
                    'p.prod_cod_linp',
                    'p.prod_cod_grpr',
                    'p.prod_cod_cate',
                    'p.prod_cod_marc',
                    'u.unid_nom_unid'
                )
                ->select([
                    'p.prod_cod_prod',
                    'p.prod_cod_empr',
                    'p.prod_cod_sucu',
                    'p.prod_nom_prod',
                    DB::raw('COALESCE(p.prod_det_prod, p.prod_des_prod) as prod_det_prod'),
                    'p.prod_cod_tpro',
                    'p.prod_cod_linp',
                    'p.prod_cod_grpr',
                    'p.prod_cod_cate',
                    'p.prod_cod_marc',
                    DB::raw("MAX(CASE WHEN pr.prbo_iva_sino = 'S' THEN 1 ELSE 0 END) as iva_sn"),
                    DB::raw('MAX(pr.prbo_iva_porc) as iva_porc'),
                    DB::raw('MAX(pr.prbo_smi_prod) as stock_minimo'),
                    DB::raw('MAX(pr.prbo_sma_prod) as stock_maximo'),
                    DB::raw('MAX(pr.prbo_cod_unid) as prbo_cod_unid'),
                    'u.unid_nom_unid',
                ])
                ->get();

            $empresa = Empresa::find($empresaId);
            $lineaNegocioId = $empresa?->linea_negocio_id;

            foreach ($productos as $producto) {
                $unidadId = $this->resolveUnidadMedidaId($producto->unid_nom_unid ?? null);

                $registro = Producto::updateOrCreate(
                    ['sku' => trim($producto->prod_cod_prod)],
                    [
                        'id_empresa' => $empresaId,
                        'amdg_id_empresa' => $empresaCodigo,
                        'amdg_id_sucursal' => $sucursalCodigo,
                        'linea' => trim((string) $producto->prod_cod_linp),
                        'grupo' => trim((string) $producto->prod_cod_grpr),
                        'categoria' => trim((string) $producto->prod_cod_cate),
                        'marca' => trim((string) $producto->prod_cod_marc),
                        'nombre' => trim((string) $producto->prod_nom_prod),
                        'detalle' => $producto->prod_det_prod ? trim((string) $producto->prod_det_prod) : null,
                        'tipo' => (int) $producto->prod_cod_tpro,
                        'id_unidad_medida' => $unidadId,
                        'stock_minimo' => $producto->stock_minimo ?? 0,
                        'stock_maximo' => $producto->stock_maximo ?? 0,
                        'iva_sn' => (bool) ($producto->iva_sn ?? false),
                        'porcentaje_iva' => $producto->iva_porc ?? 0,
                    ]
                );

                if ($lineaNegocioId) {
                    $registro->lineasNegocio()->syncWithoutDetaching([$lineaNegocioId]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al sincronizar productos JIREH: ' . $e->getMessage());
        }
    }

    private function resolveUnidadMedidaId(?string $unidadNombre): int
    {
        $nombre = trim((string) $unidadNombre);

        if ($nombre === '') {
            return UnidadMedida::query()->value('id') ?? 1;
        }

        $unidad = UnidadMedida::firstOrCreate(
            ['nombre' => $nombre],
            [
                'siglas' => $nombre,
                'id_usuario' => auth()->id() ?? 1,
                'fecha_creacion' => now(),
            ]
        );

        return $unidad->id;
    }
}
