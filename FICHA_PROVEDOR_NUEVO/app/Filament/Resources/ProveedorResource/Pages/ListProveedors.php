<?php

namespace App\Filament\Resources\ProveedorResource\Pages;

use App\Filament\Resources\ProveedorResource;
use App\Models\Empresa;
use App\Services\JirehProveedorImportService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListProveedors extends ListRecords
{
    protected static string $resource = ProveedorResource::class;

    public ?int $jirehConexionId = null;
    public ?string $jirehEmpresa = null;
    public ?string $jirehSucursal = null;
    public bool $jirehLoaded = false;

    public function getTabs(): array
    {
        return [
            'activos' => Tab::make('Activos')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('anulada', false)),
            'anuladas' => Tab::make('Anuladas')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('anulada', true)),
            'jireh' => Tab::make('JIREH')
                ->icon('heroicon-o-building-storefront')
                ->modifyQueryUsing(function (Builder $query) {
                    if (!$this->jirehLoaded || !$this->jirehConexionId || !$this->jirehEmpresa || !$this->jirehSucursal) {
                        return $query->whereRaw('1 = 0');
                    }

                    return $query
                        ->where('id_empresa', $this->jirehConexionId)
                        ->where('admg_id_empresa', $this->jirehEmpresa)
                        ->where('admg_id_sucursal', $this->jirehSucursal);
                }),
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('cargar_jireh')
                ->label('Cargar JIREH')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->form([
                    Select::make('id_empresa')
                        ->label('Conexion')
                        ->options(Empresa::query()->pluck('nombre_empresa', 'id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),
                    Select::make('admg_id_empresa')
                        ->label('Empresa')
                        ->options(function (Get $get) {
                            $empresaId = $get('id_empresa');
                            if (!$empresaId) {
                                return [];
                            }

                            $connectionName = ProveedorResource::getExternalConnectionName($empresaId);
                            if (!$connectionName) {
                                return [];
                            }

                            return DB::connection($connectionName)
                                ->table('saeempr')
                                ->pluck('empr_nom_empr', 'empr_cod_empr')
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),
                    Select::make('admg_id_sucursal')
                        ->label('Sucursal')
                        ->options(function (Get $get) {
                            $empresaId = $get('id_empresa');
                            $admgEmpresa = $get('admg_id_empresa');

                            if (!$empresaId || !$admgEmpresa) {
                                return [];
                            }

                            $connectionName = ProveedorResource::getExternalConnectionName($empresaId);
                            if (!$connectionName) {
                                return [];
                            }

                            return DB::connection($connectionName)
                                ->table('saesucu')
                                ->where('sucu_cod_empr', $admgEmpresa)
                                ->pluck('sucu_nom_sucu', 'sucu_cod_sucu')
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->mountUsing(function (Action $action): void {
                    $action->fillForm([
                        'id_empresa' => $this->jirehConexionId,
                        'admg_id_empresa' => $this->jirehEmpresa,
                        'admg_id_sucursal' => $this->jirehSucursal,
                    ]);
                })
                ->action(function (array $data): void {
                    $this->jirehConexionId = (int) $data['id_empresa'];
                    $this->jirehEmpresa = (string) $data['admg_id_empresa'];
                    $this->jirehSucursal = (string) $data['admg_id_sucursal'];
                    $this->jirehLoaded = true;

                    $importados = JirehProveedorImportService::importar(
                        $this->jirehConexionId,
                        $this->jirehEmpresa,
                        $this->jirehSucursal
                    );

                    $this->resetPage();

                    Notification::make()
                        ->title('Proveedores JIREH cargados')
                        ->body("Se sincronizaron {$importados} proveedores.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
