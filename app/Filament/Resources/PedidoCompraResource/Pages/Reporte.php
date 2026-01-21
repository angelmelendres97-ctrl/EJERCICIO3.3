<?php

namespace App\Filament\Resources\PedidoCompraResource\Pages;

use App\Filament\Resources\PedidoCompraResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\Empresa;
use App\Models\PedidoCompra;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = PedidoCompraResource::class;

    protected static string $view = 'filament.resources.pedido-compra-resource.pages.reporte';

    protected static ?string $title = 'Reporte de Pedidos de Compra';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'fecha_desde' => now()->startOfDay(),
            'fecha_hasta' => now()->endOfDay(),
        ]);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->makeForm()
                ->statePath('data')
                ->schema([
                    Forms\Components\Section::make('Filtros del Reporte')
                        ->schema([
                            Forms\Components\Select::make('conexion')
                                ->label('Conexion')
                                ->options(Empresa::query()->pluck('nombre_empresa', 'id'))
                                ->searchable()
                                ->preload()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(callable $set) => $set('empresa_id', null)),
                            Forms\Components\Select::make('empresa_id')
                                ->label('Empresa')
                                ->options(function (Get $get) {
                                    $conexion = $get('conexion');
                                    if (!$conexion)
                                        return [];
                                    $connectionName = PedidoCompraResource::getExternalConnectionName($conexion);
                                    if (!$connectionName)
                                        return [];
                                    try {
                                        return DB::connection($connectionName)->table('saeempr')->pluck('empr_nom_empr', 'empr_cod_empr')->all();
                                    } catch (\Exception $e) {
                                        return [];
                                    }
                                })
                                ->searchable()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(callable $set) => $set('sucursal_id', null)),
                            Forms\Components\Select::make('sucursal_id')
                                ->label('Sucursal')
                                ->options(function (Get $get) {
                                    $conexion = $get('conexion');
                                    $empresaId = $get('empresa_id');
                                    if (!$conexion || !$empresaId)
                                        return [];
                                    $connectionName = PedidoCompraResource::getExternalConnectionName($conexion);
                                    if (!$connectionName)
                                        return [];
                                    try {
                                        return DB::connection($connectionName)->table('saesucu')->where('sucu_cod_empr', $empresaId)->pluck('sucu_nom_sucu', 'sucu_cod_sucu')->all();
                                    } catch (\Exception $e) {
                                        return [];
                                    }
                                })
                                ->searchable()
                                ->live(onBlur: true),

                            Forms\Components\DatePicker::make('fecha_desde')
                                ->label('Fecha Desde')
                                ->live(onBlur: true),
                            Forms\Components\DatePicker::make('fecha_hasta')
                                ->label('Fecha Hasta')
                                ->live(onBlur: true),
                        ])->columns(5),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $formData = $this->form->getState();

                if (empty($formData['conexion']) || empty($formData['empresa_id']) || empty($formData['sucursal_id'])) {
                    // Return a query with no results if the form is incomplete
                    return PedidoCompra::query()->whereRaw('1 = 0');
                }

                $connectionName = PedidoCompraResource::getExternalConnectionName($formData['conexion']);

                if (!$connectionName) {
                    return PedidoCompra::query()->whereRaw('1 = 0');
                }

                // The base query for the table
                $model = new PedidoCompra();
                $model->setConnection($connectionName);
                $model->setTable('saepedi');
                $model->setKeyName(key: 'pedi_cod_pedi');

                $query = $model->newQuery()
                    ->where('pedi_cod_empr', $formData['empresa_id'])
                    ->where('pedi_cod_sucu', $formData['sucursal_id']);

                if (!empty($formData['fecha_desde']) && !empty($formData['fecha_hasta'])) {
                    $query->whereBetween('pedi_fec_pedi', [$formData['fecha_desde'], $formData['fecha_hasta']]);
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('rowIndex')->label('Nro')->rowIndex(),
                Tables\Columns\TextColumn::make('pedi_cod_pedi')->label('Secuencial')->formatStateUsing(fn(string $state): string => str_pad($state, 8, "0", STR_PAD_LEFT))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('pedi_res_pedi')->label('Responsable')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('pedi_det_pedi')->label('Motivo')->searchable(),
                Tables\Columns\TextColumn::make('pedi_fec_pedi')->label('Fecha Pedido')->date()->sortable(),
                Tables\Columns\TextColumn::make('pedi_fec_entr')->label('Fecha Entrega')->date()->sortable(),
                Tables\Columns\TextColumn::make('pedi_lug_entr')->label('Lugar Entrega')->searchable()->formatStateUsing(fn($state) => strtoupper($state)),
                Tables\Columns\TextColumn::make('pedi_uso_pedi')->label('Para Uso De')->searchable()->formatStateUsing(fn($state) => strtoupper($state)),
                Tables\Columns\TextColumn::make('pedi_des_cons')->label('Observaciones')->searchable()->formatStateUsing(fn($state) => strtoupper($state)),
                Tables\Columns\TextColumn::make('pedi_tipo_pedi')->label('Tipo Pedido')->searchable()->formatStateUsing(fn(string $state): string => match ($state) {
                    'L' => 'LOGISTICA',
                    'M' => 'MENSUAL',
                    'S' => 'SEMANAL',
                    'D' => 'DIARIO',
                    default => $state, // Mantener el valor original si no coincide con ninguno
                }),
                Tables\Columns\TextColumn::make('pedi_est_prof')->label('Estado')->searchable()->formatStateUsing(fn(string $state): string => match ($state) {
                    'N' => 'PENDIENTE',
                    default => $state, // Mantener el valor original si no coincide con ninguno
                }),
            ])
            ->actions([

                /*
                     Tables\Actions\Action::make('view_details')
                    ->label('Ver Detalle')
                    ->icon('heroicon-o-eye')
                    ->modalContent(function (Model $record) {
                        $formData = $this->form->getState();
                        $connectionName = OrdenCompraResource::getExternalConnectionName($formData['conexion']);
                        if (!$connectionName) {
                            return view('livewire.pedido-compra-detail-view', ['details' => collect(), 'error' => 'No se puede establecer la conexión.']);
                        }
                        try {
                            $details = DB::connection($connectionName)
                                ->table('saedped')
                                ->where('dped_cod_pedi', $record->pedi_cod_pedi)
                                ->where('dped_cod_empr', $formData['empresa_id'])
                                ->get();
                            return view('livewire.pedido-compra-detail-view', ['details' => $details]);
                        } catch (\Exception $e) {
                            return view('livewire.pedido-compra-detail-view', ['details' => collect(), 'error' => 'Error al consultar detalles: ' . $e->getMessage()]);
                        }
                    }),
                    */

                Action::make('view_details')
                    ->label('Ver Detalle')
                    ->action(function (Model $record) {
                        $formData = $this->form->getState();
                        $this->dispatch('open-pedido-modal', pedi_cod_pedi: $record->pedi_cod_pedi, connectionId: $formData['conexion']);
                    })

            ])
            ->headerActions([
                ExportAction::make()->label('Exportar Excel/CSV'),
                Action::make('export_pdf')
                    ->label('Exportar PDF')
                    ->color('danger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $records = $this->getFilteredTableQuery()->get();
                        if ($records->isEmpty()) {
                            return;
                        }
                        return response()->streamDownload(function () use ($records) {
                            echo Pdf::loadView('pdfs.pedido-compra-report-pdf', ['records' => $records])->stream();
                        }, 'reporte-pedidos.pdf');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export_pdf')
                    ->label('Exportar a PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Collection $records) {
                        return response()->streamDownload(function () use ($records) {
                            echo Pdf::loadView('pdfs.pedido-compra-report-pdf', ['records' => $records])->stream();
                        }, 'reporte-pedidos.pdf');
                    }),
            ]);
    }

    public function getTableRecordKey(Model $record): string
    {
        return $record->pedi_cod_pedi;
    }
}
