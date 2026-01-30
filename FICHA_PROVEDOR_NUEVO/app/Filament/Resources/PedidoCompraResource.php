<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoCompraResource\Pages;
use App\Filament\Resources\PedidoCompraResource\RelationManagers;
use App\Models\PedidoCompra;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Empresa;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Get;
use Filament\Forms\Set;

class PedidoCompraResource extends Resource
{
    protected static ?string $model = PedidoCompra::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getExternalConnectionName(int $empresaId): ?string
    {
        $empresa = Empresa::find($empresaId);
        if (!$empresa || !$empresa->status_conexion) {
            return null;
        }

        $connectionName = 'external_db_' . $empresaId;

        if (!Config::has("database.connections.{$connectionName}")) {
            $dbConfig = [
                'driver' => $empresa->motor,
                'host' => $empresa->host,
                'port' => $empresa->puerto,
                'database' => $empresa->nombre_base,
                'username' => $empresa->usuario,
                'password' => $empresa->clave,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'options' => [
                    \PDO::ATTR_PERSISTENT => true,
                ],
            ];
            Config::set("database.connections.{$connectionName}", $dbConfig);
        }

        return $connectionName;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('empresa.nombre_empresa')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pedi_cod_pedi')
                    ->label('Código Pedido')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amdg_id_empresa')
                    ->label('ID Empresa AMDG')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amdg_id_sucursal')
                    ->label('ID Sucursal AMDG')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('identificacion_proveedor')
                    ->label('Identificación Proveedor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre_proveedor')
                    ->label('Nombre Proveedor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_pedido')
                    ->label('Fecha Pedido')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_entrega')
                    ->label('Fecha Entrega')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_creacion')
                    ->label('Fecha Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPedidoCompras::route('/'),
            'reporte' => Pages\Reporte::route('/reporte'),
        ];
    }
}
