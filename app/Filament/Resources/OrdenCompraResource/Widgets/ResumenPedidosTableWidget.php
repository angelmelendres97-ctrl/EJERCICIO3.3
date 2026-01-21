<?php

namespace App\Filament\Resources\OrdenCompraResource\Widgets;

use App\Models\ResumenPedidos;
use Filament\Actions\StaticAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ResumenPedidosTableWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Resúmenes de pedidos')
            ->query(ResumenPedidos::query())
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('codigo_secuencial')
                    ->label('Secuencial')
                    ->formatStateUsing(fn(string $state): string => str_pad($state, 8, '0', STR_PAD_LEFT))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('empresa.nombre_empresa')
                    ->label('Conexión')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Presupuesto')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_ordenes')
                    ->label('Ver Órdenes')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn($record) => view(
                        'filament.resources.resumen-pedidos-resource.widgets.ordenes-compra-modal',
                        ['record' => $record]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar')),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn(ResumenPedidos $record) => route('resumen-pedidos.pdf', $record))
                    ->openUrlInNewTab(),
            ]);
    }
}
