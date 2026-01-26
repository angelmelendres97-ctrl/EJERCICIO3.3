<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrdenCompraResource;
use App\Models\PedidoCompra;
use App\Models\DetalleOrdenCompra;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Filament\Actions\StaticAction;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class BuscarPedidosCompra extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $id_empresa;
    public $amdg_id_empresa;
    public $amdg_id_sucursal;
    public ?string $pedidos_importados = null;

    public ?array $data = [];

    private function initializeForm(): void
    {
        if (!isset($this->form)) {
            $this->form = $this->form($this->makeForm());
        }
    }

    public function mount($id_empresa, $amdg_id_empresa, $amdg_id_sucursal, $pedidos_importados = null): void
    {
        $this->initializeForm();
        $this->id_empresa = $id_empresa;
        $this->amdg_id_empresa = $amdg_id_empresa;
        $this->amdg_id_sucursal = $amdg_id_sucursal;
        $this->pedidos_importados = $pedidos_importados;

        $this->form->fill([
            'fecha_desde' => Carbon::create(2026, 1, 1)->startOfDay(),
            'fecha_hasta' => now()->endOfDay(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('fecha_desde')
                            ->label('Fecha Desde')
                            ->live(onBlur: true),
                        Forms\Components\DatePicker::make('fecha_hasta')
                            ->label('Fecha Hasta')
                            ->live(onBlur: true),
                    ])
            ]);
    }

    protected function getTableQuery(): Builder
    {
        $this->initializeForm();
        $formData = $this->form->getState();

        if (empty($this->id_empresa) || empty($this->amdg_id_empresa) || empty($this->amdg_id_sucursal)) {
            return PedidoCompra::query()->whereRaw('1 = 0');
        }

        $connectionName = OrdenCompraResource::getExternalConnectionName($this->id_empresa);

        if (!$connectionName) {
            return PedidoCompra::query()->whereRaw('1 = 0');
        }


        /*
        $model = new PedidoCompra();
        $model->setConnection($connectionName);
        $model->setTable('saepedi');
        $model->setKeyName('pedi_cod_pedi');

        $query = $model->newQuery()
            ->where('pedi_cod_empr', $this->amdg_id_empresa)
            ->where('pedi_cod_sucu', $this->amdg_id_sucursal);

        if (!empty($formData['fecha_desde']) && !empty($formData['fecha_hasta'])) {
            $query->whereBetween('pedi_fec_pedi', [$formData['fecha_desde'], $formData['fecha_hasta']]);
        }
        */


        $model = new PedidoCompra();

        $model->setConnection($connectionName);
        $model->setTable('saepedi');
        $model->setKeyName('pedi_cod_pedi');

        $query = $model->newQuery()
            ->select('saepedi.*')
            ->distinct()
            ->leftJoin('saedped', function ($join) {
                $join->on('saedped.dped_cod_pedi', '=', 'saepedi.pedi_cod_pedi');
                // Si tu tabla de detalle tiene empresa/sucursal, puedes amarrarlo aquí también si aplica:
                $join->on('saedped.dped_cod_empr', '=', 'saepedi.pedi_cod_empr');
                $join->on('saedped.dped_cod_sucu', '=', 'saepedi.pedi_cod_sucu');
            })
            ->where('saepedi.pedi_cod_empr', $this->amdg_id_empresa)
            ->where('saepedi.pedi_cod_sucu', $this->amdg_id_sucursal);

        if (!empty($formData['fecha_desde']) && !empty($formData['fecha_hasta'])) {
            $query->whereBetween('saepedi.pedi_fec_pedi', [
                $formData['fecha_desde'],
                $formData['fecha_hasta']
            ]);
        }

        $pedidosDisponibles = $this->resolvePedidosDisponibles($connectionName, $formData);
        if (empty($pedidosDisponibles)) {
            return PedidoCompra::query()->whereRaw('1 = 0');
        }

        $query->whereIn('saepedi.pedi_cod_pedi', $pedidosDisponibles);

        $pedidosImportados = $this->resolvePedidosImportados();
        if (!empty($pedidosImportados)) {
            $query->whereNotIn('saepedi.pedi_cod_pedi', $pedidosImportados);
        }

        return $query;
    }

    private function resolvePedidosImportados(): array
    {
        $fromForm = $this->parsePedidosImportados($this->pedidos_importados);

        return array_values(array_unique(array_filter($fromForm)));
    }

    private function resolvePedidosDisponibles(string $connectionName, array $formData): array
    {
        $pedidoQuery = DB::connection($connectionName)
            ->table('saepedi')
            ->select('pedi_cod_pedi', 'pedi_est_pedi')
            ->where('pedi_cod_empr', $this->amdg_id_empresa)
            ->where('pedi_cod_sucu', $this->amdg_id_sucursal);

        if (!empty($formData['fecha_desde']) && !empty($formData['fecha_hasta'])) {
            $pedidoQuery->whereBetween('pedi_fec_pedi', [
                $formData['fecha_desde'],
                $formData['fecha_hasta']
            ]);
        }

        $pedidos = $pedidoQuery->get();
        if ($pedidos->isEmpty()) {
            return [];
        }

        $pedidoIds = $pedidos->pluck('pedi_cod_pedi')->map(fn($pedido) => (int) $pedido)->all();
        $pedidoAnulados = $pedidos
            ->filter(fn($pedido) => $pedido->pedi_est_pedi === 'ANU')
            ->pluck('pedi_cod_pedi')
            ->map(fn($pedido) => (int) $pedido)
            ->all();

        $pedidosPendientes = $this->resolvePedidosConPendientes($connectionName, $pedidoIds);

        return array_values(array_unique(array_merge($pedidosPendientes, $pedidoAnulados)));
    }

    private function resolvePedidosConPendientes(string $connectionName, array $pedidoIds): array
    {
        if (empty($pedidoIds)) {
            return [];
        }

        $detalles = DB::connection($connectionName)
            ->table('saedped')
            ->select('dped_cod_pedi', 'dped_cod_dped', 'dped_can_ped')
            ->where('dped_cod_empr', $this->amdg_id_empresa)
            ->where('dped_cod_sucu', $this->amdg_id_sucursal)
            ->whereIn('dped_cod_pedi', $pedidoIds)
            ->get();

        if ($detalles->isEmpty()) {
            return [];
        }

        $pedidoDetallePairs = $detalles->map(fn($detalle) => [
            'pedido_codigo' => (int) $detalle->dped_cod_pedi,
            'pedido_detalle_id' => (int) $detalle->dped_cod_dped,
        ])->values()->all();
        $importadoPorDetalle = $this->resolveImportadoPorDetalle($pedidoDetallePairs);

        $pendientes = [];
        foreach ($detalles as $detalle) {
            $cantidadPedida = (float) ($detalle->dped_can_ped ?? 0);
            $key = ((int) $detalle->dped_cod_pedi) . ':' . ((int) $detalle->dped_cod_dped);
            $cantidadImportada = (float) ($importadoPorDetalle[$key] ?? 0);
            $cantidadPendiente = $cantidadPedida - $cantidadImportada;

            if ($cantidadPendiente > 0) {
                $pendientes[(int) $detalle->dped_cod_pedi] = true;
            }
        }

        return array_keys($pendientes);
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

        $rows = DetalleOrdenCompra::query()
            ->select([
                'pedido_codigo',
                'pedido_detalle_id',
                DB::raw('SUM(cantidad) as cantidad_importada'),
            ])
            ->whereIn('pedido_codigo', $pedidoCodigos)
            ->whereIn('pedido_detalle_id', $detalleIds)
            ->whereHas('ordenCompra', function ($q) {
                $q->where('anulada', false)
                    ->where('id_empresa', $this->id_empresa)
                    ->where('amdg_id_empresa', $this->amdg_id_empresa)
                    ->where('amdg_id_sucursal', $this->amdg_id_sucursal);
            })
            ->groupBy('pedido_codigo', 'pedido_detalle_id')
            ->get();

        return $rows->mapWithKeys(function ($row) {
            $key = ((int) $row->pedido_codigo) . ':' . ((int) $row->pedido_detalle_id);
            return [$key => (float) $row->cantidad_importada];
        })->all();
    }


    private function parsePedidosImportados(array|string|null $value): array
    {
        if (empty($value)) {
            return [];
        }

        $lista = is_array($value) ? $value : preg_split('/\\s*,\\s*/', trim((string) $value));

        return collect($lista)
            ->filter()
            ->map(fn($pedido) => (int) ltrim((string) $pedido, '0'))
            ->filter(fn($pedido) => $pedido > 0)
            ->all();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => $this->getTableQuery())
            ->defaultSort('pedi_fec_pedi', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('pedi_cod_pedi')
                    ->label('Secuencial')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(
                        fn($state, $record) =>
                        str_pad($record->pedi_cod_pedi, 8, "0", STR_PAD_LEFT)
                    ),
                Tables\Columns\TextColumn::make('pedi_res_pedi')->label('Responsable')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('pedi_det_pedi')->label('Motivo')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('pedi_fec_pedi')->label('Fecha Pedido')->date()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('Ver Detalle')
                    ->icon('heroicon-o-eye')
                    ->modalContent(function (Model $record) {
                        $connectionName = OrdenCompraResource::getExternalConnectionName($this->id_empresa);
                        if (!$connectionName) {
                            return view('livewire.pedido-compra-detail-view', ['details' => collect(), 'error' => 'No se puede establecer la conexión.']);
                        }
                        try {
                            $details = DB::connection($connectionName)
                                ->table('saedped')
                                ->where('dped_cod_pedi', $record->pedi_cod_pedi)
                                ->where('dped_cod_empr', $this->amdg_id_empresa)
                                ->where('dped_cod_sucu', $this->amdg_id_sucursal)
                                ->get();

                            $pedidoDetallePairs = $details->map(fn($detail) => [
                                'pedido_codigo' => (int) $detail->dped_cod_pedi,
                                'pedido_detalle_id' => (int) $detail->dped_cod_dped,
                            ])->values()->all();
                            $importadoPorDetalle = $this->resolveImportadoPorDetalle($pedidoDetallePairs);

                            $details = $details->map(function ($detail) use ($importadoPorDetalle) {
                                $cantidadPedida = (float) ($detail->dped_can_ped ?? 0);
                                $key = ((int) $detail->dped_cod_pedi) . ':' . ((int) $detail->dped_cod_dped);
                                $cantidadImportada = (float) ($importadoPorDetalle[$key] ?? 0);
                                $cantidadPendiente = $cantidadPedida - $cantidadImportada;

                                $detail->cantidad_importada = $cantidadImportada;
                                $detail->cantidad_pendiente = $cantidadPendiente;

                                return $detail;
                            })->filter(fn($detail) => $detail->cantidad_pendiente > 0);

                            return view('livewire.pedido-compra-detail-view', ['details' => $details]);
                        } catch (\Exception $e) {
                            return view('livewire.pedido-compra-detail-view', ['details' => collect(), 'error' => 'Error al consultar detalles: ' . $e->getMessage()]);
                        }
                    })
                    ->modalHeading('Detalles del Pedido')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar')),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('importar')
                    ->label('Importar Pedidos')
                    ->requiresConfirmation()
                    ->action(function (Collection $records, Tables\Actions\BulkAction $action) {

                        if ($records->isEmpty()) {
                            return;
                        }

                        $motivo = $records->first()->pedi_det_pedi;

                        $this->dispatch(
                            'pedidos_seleccionados',
                            $records->pluck('pedi_cod_pedi')->toArray(),
                            $this->id_empresa,
                            $motivo
                        );

                        $pedidosActuales = $this->parsePedidosImportados($this->pedidos_importados);
                        $pedidosSeleccionados = $records->pluck('pedi_cod_pedi')
                            ->map(fn($pedido) => (int) ltrim((string) $pedido, '0'))
                            ->filter(fn($pedido) => $pedido > 0)
                            ->all();

                        $pedidosUnicos = array_values(array_unique(array_merge($pedidosActuales, $pedidosSeleccionados)));
                        $this->pedidos_importados = implode(', ', array_map(
                            fn($pedido) => str_pad($pedido, 8, "0", STR_PAD_LEFT),
                            $pedidosUnicos
                        ));

                        $this->resetTable();

                        // 🔥 CERRAR MODAL 100% SEGURO
                        $action->cancel();
                        //$this->dispatch('close-modal', id: 'importar_pedido');
                        $this->dispatch('close-modal', id: 'mountedFormComponentAction');

                        //$this->dispatch('close-modal', id: 'mountedAction');
                    })
            ]);
    }

    public function getTableRecordKey(Model $record): string
    {
        return $record->pedi_cod_pedi;
    }

    public function deleteDetail($pedi_cod_pedi, $dped_cod_prod)
    {
        $connectionName = OrdenCompraResource::getExternalConnectionName($this->id_empresa);

        if (!$connectionName) {
            return PedidoCompra::query()->whereRaw('1 = 0');
        }

        DB::connection($connectionName)
            ->table('saedped')
            ->where('dped_cod_pedi', $pedi_cod_pedi)
            ->where('dped_cod_prod', $dped_cod_prod)
            ->update([
                'dped_can_ent' => DB::raw('dped_can_ped')
            ]);

        // 🔥 Mostrar notificación
        Notification::make()
            ->title('Producto finalizado correctamente')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.buscar-pedidos-compra');
    }
}
