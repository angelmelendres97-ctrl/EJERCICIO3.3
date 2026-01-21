<?php

namespace App\Filament\Resources\SolicitudPagoResource\RelationManagers;

use App\Filament\Resources\SolicitudPagoResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Grouping\Group;

class DetallesRelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';

    protected static ?string $title = 'Facturas solicitadas';

    public function table(Table $table): Table
    {
        $empresaOptionsCache = [];
        $sucursalOptionsCache = [];

        $empresaLabel = function ($record) use (&$empresaOptionsCache) {
            $conexionId = (int) ($record->erp_conexion ?? $this->getOwnerRecord()->id_empresa);
            if (! isset($empresaOptionsCache[$conexionId])) {
                $empresaOptionsCache[$conexionId] = SolicitudPagoResource::getEmpresasOptions($conexionId);
            }

            return $empresaOptionsCache[$conexionId][$record->erp_empresa_id] ?? $record->erp_empresa_id;
        };

        $sucursalLabel = function ($record) use (&$sucursalOptionsCache) {
            $conexionId = (int) ($record->erp_conexion ?? $this->getOwnerRecord()->id_empresa);
            $empresaCodigo = (string) ($record->erp_empresa_id ?? '');
            if (! isset($sucursalOptionsCache[$conexionId][$empresaCodigo])) {
                $sucursalOptionsCache[$conexionId][$empresaCodigo] = SolicitudPagoResource::getSucursalesOptions($conexionId, array_filter([$empresaCodigo]));
            }

            return $sucursalOptionsCache[$conexionId][$empresaCodigo][$record->erp_sucursal] ?? $record->erp_sucursal;
        };

        return $table
            ->columns([
                TextColumn::make('erp_empresa_id')
                    ->label('Empresa')
                    ->formatStateUsing(fn($state, $record) => $empresaLabel($record)),

                TextColumn::make('erp_sucursal')
                    ->label('Sucursal')
                    ->formatStateUsing(fn($state, $record) => $sucursalLabel($record))
                    ->toggleable(),

                TextColumn::make('proveedor_nombre')
                    ->label('Proveedor')
                    ->searchable(),

                TextColumn::make('numero_factura')
                    ->label('N° Factura')
                    ->searchable(),

                TextColumn::make('fecha_emision')
                    ->label('Emisión')
                    ->date('Y-m-d'),

                TextColumn::make('fecha_vencimiento')
                    ->label('Vence')
                    ->date('Y-m-d'),

                TextColumn::make('monto_factura')
                    ->label('Total')
                    ->money('USD')
                    ->summarize([
                        Sum::make()->label('Total'),
                    ]),

                TextColumn::make('abono_aplicado')
                    ->label('Monto abonado')
                    ->money('USD')
                    ->summarize([
                        Sum::make()->label('Total abonado'),
                    ]),

                TextColumn::make('saldo_pendiente')
                    ->label('Saldo pendiente')
                    ->state(fn($record) => max(0, (float) ($record->monto_factura ?? 0) - (float) ($record->abono_aplicado ?? 0)))
                    ->money('USD')
                    ->summarize([
                        Sum::make()->label('Saldo pendiente'),
                    ]),

                TextColumn::make('estado_abono')
                    ->label('Estado')
                    ->formatStateUsing(function ($state, $record) {
                        $total = (float) ($record->monto_factura ?? 0);
                        $abono = (float) ($record->abono_aplicado ?? 0);
                        $saldoPendiente = max(0, $total - $abono);

                        if ($abono <= 0) {
                            return 'Sin abonos realizados';
                        }

                        if ($saldoPendiente <= 0) {
                            return 'Abonado';
                        }

                        return 'Pendiente de abono';
                    }),

                TextColumn::make('solicitudPago.creador.name')
                    ->label('Creado por')
                    ->formatStateUsing(fn($state, $record) => $record->solicitudPago->creador->name ?? 'N/A'),
            ])
            ->groups([
                Group::make('erp_empresa_id')
                    ->label('Empresa')
                    ->getTitleFromRecordUsing(fn($record) => $empresaLabel($record))
                    ->collapsible(),
                Group::make('erp_sucursal')
                    ->label('Sucursal')
                    ->getTitleFromRecordUsing(fn($record) => $sucursalLabel($record))
                    ->collapsible(),
                Group::make('proveedor_ruc')
                    ->label('Proveedor')
                    ->getTitleFromRecordUsing(fn($record) => $record->proveedor_nombre ?? $record->proveedor_ruc ?? $record->proveedor_codigo)
                    ->collapsible(),
            ])
            ->defaultGroup('erp_empresa_id')
            // Solo lectura (ver)
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
