<?php

namespace App\Livewire;

use App\Models\DetalleResumenPedidos;
use App\Models\ResumenPedidos;
use App\Filament\Resources\ResumenPedidosResource;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ResumenPedidosDetalles extends Component
{
    public ResumenPedidos $record;

    public function mount(ResumenPedidos $record)
    {
        $this->record = $record;
    }

    public function deleteDetalle($detalleId)
    {
        $detalle = DetalleResumenPedidos::find($detalleId);
        if ($detalle) {
            $detalle->delete();
            Notification::make()
                ->title('Detalle eliminado correctamente')
                ->success()
                ->send();
            
            // Refresh the record data
            $this->record->refresh();
        } else {
            Notification::make()
                ->title('Error al eliminar el detalle')
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        $detalles = $this->record
            ? $this->record->detalles()
                ->whereHas('ordenCompra', fn($query) => $query->where('anulada', false))
                ->with('ordenCompra.empresa')
                ->get()
            : collect();

        $groupedDetalles = $this->buildGroupedDetalles($detalles);

        return view('livewire.resumen-pedidos-detalles', [
            'groupedDetalles' => $groupedDetalles,
        ]);
    }

    protected function buildGroupedDetalles(Collection $detalles): array
    {
        $nombresExternos = $this->buildExternalNames($detalles);

        return $detalles
            ->groupBy(function ($detalle) {
                $orden = $detalle->ordenCompra;
                return $orden->id_empresa . '|' . $orden->amdg_id_empresa . '|' . $orden->amdg_id_sucursal;
            })
            ->map(function ($items, $key) use ($nombresExternos) {
                [$conexionId, $empresaId, $sucursalId] = array_pad(explode('|', (string) $key, 3), 3, null);
                $orden = $items->first()->ordenCompra;
                $conexionNombre = $orden->empresa->nombre_empresa ?? '';
                $empresaNombre = $nombresExternos['empresas'][$conexionId][$empresaId] ?? $empresaId;
                $sucursalNombre = $nombresExternos['sucursales'][$conexionId][$empresaId][$sucursalId] ?? $sucursalId;

                return [
                    'conexion_id' => $conexionId,
                    'empresa_id' => $empresaId,
                    'sucursal_id' => $sucursalId,
                    'conexion_nombre' => $conexionNombre,
                    'empresa_nombre' => $empresaNombre,
                    'sucursal_nombre' => $sucursalNombre,
                    'detalles' => $items,
                    'total' => $items->sum(fn($detalle) => (float) ($detalle->ordenCompra->total ?? 0)),
                ];
            })
            ->values()
            ->all();
    }

    protected function buildExternalNames(Collection $detalles): array
    {
        $empresaNombrePorConexion = [];
        $sucursalNombrePorConexion = [];

        $detalles->groupBy(fn($detalle) => $detalle->ordenCompra->id_empresa)
            ->each(function (Collection $items, $conexionId) use (&$empresaNombrePorConexion, &$sucursalNombrePorConexion) {
                $connectionName = ResumenPedidosResource::getExternalConnectionName((int) $conexionId);

                if (! $connectionName) {
                    return;
                }

                $empresaCodes = $items->pluck('ordenCompra.amdg_id_empresa')->filter()->unique()->values()->all();
                $sucursalCodes = $items->pluck('ordenCompra.amdg_id_sucursal')->filter()->unique()->values()->all();

                if (! empty($empresaCodes)) {
                    try {
                        $empresaNombrePorConexion[$conexionId] = DB::connection($connectionName)
                            ->table('saeempr')
                            ->whereIn('empr_cod_empr', $empresaCodes)
                            ->pluck('empr_nom_empr', 'empr_cod_empr')
                            ->all();
                    } catch (\Exception $e) {
                        $empresaNombrePorConexion[$conexionId] = [];
                    }
                }

                if (! empty($empresaCodes) && ! empty($sucursalCodes)) {
                    try {
                        $sucursales = DB::connection($connectionName)
                            ->table('saesucu')
                            ->whereIn('sucu_cod_empr', $empresaCodes)
                            ->whereIn('sucu_cod_sucu', $sucursalCodes)
                            ->get(['sucu_cod_empr', 'sucu_cod_sucu', 'sucu_nom_sucu']);

                        foreach ($sucursales as $sucursal) {
                            $sucursalNombrePorConexion[$conexionId][$sucursal->sucu_cod_empr][$sucursal->sucu_cod_sucu] = $sucursal->sucu_nom_sucu;
                        }
                    } catch (\Exception $e) {
                        $sucursalNombrePorConexion[$conexionId] = [];
                    }
                }
            });

        return [
            'empresas' => $empresaNombrePorConexion,
            'sucursales' => $sucursalNombrePorConexion,
        ];
    }
}
