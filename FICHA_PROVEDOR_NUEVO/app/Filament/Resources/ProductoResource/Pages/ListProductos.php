<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use App\Models\Empresa;
use App\Services\JirehProductoImportService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListProductos extends ListRecords
{
    protected static string $resource = ProductoResource::class;

    public ?int $jirehConexionId = null;
    public ?string $jirehEmpresa = null;
    public ?string $jirehSucursal = null;
    public bool $jirehLoaded = false;

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
                    Select::make('amdg_id_empresa')
                        ->label('Empresa')
                        ->options(function (Get $get) {
                            $empresaId = $get('id_empresa');
                            if (!$empresaId) {
                                return [];
                            }

                            $connectionName = ProductoResource::getExternalConnectionName($empresaId);
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
                    Select::make('amdg_id_sucursal')
                        ->label('Sucursal')
                        ->options(function (Get $get) {
                            $empresaId = $get('id_empresa');
                            $amdgEmpresa = $get('amdg_id_empresa');

                            if (!$empresaId || !$amdgEmpresa) {
                                return [];
                            }

                            $connectionName = ProductoResource::getExternalConnectionName($empresaId);
                            if (!$connectionName) {
                                return [];
                            }

                            return DB::connection($connectionName)
                                ->table('saesucu')
                                ->where('sucu_cod_empr', $amdgEmpresa)
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
                        'amdg_id_empresa' => $this->jirehEmpresa,
                        'amdg_id_sucursal' => $this->jirehSucursal,
                    ]);
                })
                ->action(function (array $data): void {
                    $this->jirehConexionId = (int) $data['id_empresa'];
                    $this->jirehEmpresa = (string) $data['amdg_id_empresa'];
                    $this->jirehSucursal = (string) $data['amdg_id_sucursal'];
                    $this->jirehLoaded = true;

                    $importados = JirehProductoImportService::importar(
                        $this->jirehConexionId,
                        $this->jirehEmpresa,
                        $this->jirehSucursal
                    );

                    $this->resetPage();

                    Notification::make()
                        ->title('Productos JIREH cargados')
                        ->body("Se sincronizaron {$importados} productos.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'locales' => Tab::make('Locales'),
            'jireh' => Tab::make('JIREH')
                ->icon('heroicon-o-building-storefront')
                ->modifyQueryUsing(function (Builder $query) {
                    if (!$this->jirehLoaded || !$this->jirehConexionId || !$this->jirehEmpresa || !$this->jirehSucursal) {
                        return $query->whereRaw('1 = 0');
                    }

                    return $query
                        ->where('id_empresa', $this->jirehConexionId)
                        ->where('amdg_id_empresa', $this->jirehEmpresa)
                        ->where('amdg_id_sucursal', $this->jirehSucursal);
                }),
        ];
    }
}
