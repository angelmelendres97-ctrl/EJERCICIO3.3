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
    public array $removedDetalleIds = [];
    public bool $hasChanges = false;

    public function mount(ResumenPedidos $record)
    {
        $this->record = $record;
    }

    public function removeDetalle($detalleId)
    {
        if (! $this->canManage()) {
            Notification::make()
                ->title('No tienes permisos para modificar este resumen')
                ->danger()
                ->send();

            return;
        }

        if (! $detalleId || in_array($detalleId, $this->removedDetalleIds, true)) {
            return;
        }

        $this->removedDetalleIds[] = (int) $detalleId;
        $this->hasChanges = true;

        Notification::make()
            ->title('Orden marcada para quitar')
            ->success()
            ->send();
    }

    public function saveChanges(): void
    {
        if (! $this->canManage()) {
            Notification::make()
                ->title('No tienes permisos para guardar cambios')
                ->danger()
                ->send();

            return;
        }

        if (empty($this->removedDetalleIds)) {
            return;
        }

        DetalleResumenPedidos::query()
            ->where('id_resumen_pedidos', $this->record->id)
            ->whereIn('id', $this->removedDetalleIds)
            ->delete();

        $this->removedDetalleIds = [];
        $this->hasChanges = false;
        $this->record->refresh();

        Notification::make()
            ->title('Resumen actualizado correctamente')
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'mountedAction');
    }

    public function render()
    {
        $detallesQuery = $this->record
            ? $this->record->detalles()
                ->whereHas('ordenCompra', fn($query) => $query->where('anulada', false))
                ->with('ordenCompra.empresa')
            : null;

        if (! $detallesQuery) {
            $detalles = collect();
        } else {
            if (! empty($this->removedDetalleIds)) {
                $detallesQuery->whereNotIn('id', $this->removedDetalleIds);
            }

            $detalles = $detallesQuery->get();
        }

        $groupedDetalles = $this->buildGroupedDetalles($detalles);

        return view('livewire.resumen-pedidos-detalles', [
            'groupedDetalles' => $groupedDetalles,
            'canManage' => $this->canManage(),
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

    protected function canManage(): bool
    {
        $userId = auth()->id();

        return $userId !== null && (int) $this->record->id_usuario === (int) $userId;
    }
}
