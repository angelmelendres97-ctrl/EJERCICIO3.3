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
    public bool $canEdit = false;

    public function mount(ResumenPedidos $record)
    {
        $this->record = $record;
        $this->canEdit = auth()->id() !== null
            && (int) $record->id_usuario === (int) auth()->id()
            && ! $record->anulada;
    }

    public function removeDetalle($detalleId): void
    {
        if (! $this->canEdit) {
            Notification::make()
                ->title('No tienes permiso para editar este resumen.')
                ->danger()
                ->send();
            return;
        }

        $detalle = DetalleResumenPedidos::query()
            ->where('id_resumen_pedidos', $this->record->id)
            ->find($detalleId);

        if (! $detalle) {
            Notification::make()
                ->title('No se encontró la orden seleccionada.')
                ->danger()
                ->send();
            return;
        }

        if (! in_array($detalleId, $this->removedDetalleIds, true)) {
            $this->removedDetalleIds[] = $detalleId;
        }

        $this->hasChanges = true;
        $this->record->refresh();
    }

    public function saveChanges(): void
    {
        if (! $this->canEdit || empty($this->removedDetalleIds)) {
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
        $this->dispatch('close-modal', id: 'mountedFormComponentAction');
    }

    public function render()
    {
        $detalles = $this->record
            ? $this->record->detalles()
                ->whereHas('ordenCompra', fn($query) => $query->where('anulada', false))
                ->with('ordenCompra.empresa')
                ->get()
            : collect();

        if (! empty($this->removedDetalleIds)) {
            $detalles = $detalles->reject(fn($detalle) => in_array($detalle->id, $this->removedDetalleIds, true));
        }

        $groupedDetalles = $this->buildGroupedDetalles($detalles);

        return view('livewire.resumen-pedidos-detalles', [
            'groupedDetalles' => $groupedDetalles,
            'canEdit' => $this->canEdit,
            'hasChanges' => $this->hasChanges,
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
