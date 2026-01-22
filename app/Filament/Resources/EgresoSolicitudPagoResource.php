<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EgresoSolicitudPagoResource\Pages;
use App\Models\SolicitudPago;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Services\SolicitudPagoReportService;
use Illuminate\Database\Eloquent\Builder;

class EgresoSolicitudPagoResource extends Resource
{
    protected static ?string $model = SolicitudPago::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Egresos';

    protected static ?string $modelLabel = 'Solicitud aprobada';

    protected static ?string $pluralModelLabel = 'Solicitudes aprobadas';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereRaw('upper(estado) in (?, ?)', ['APROBADA', strtoupper(SolicitudPago::ESTADO_APROBADA_ANULADA)]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('empresa.nombre_empresa')
                    ->label('Conexión')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('creadoPor.name')
                    ->label('Creado por')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->limit(40),
                TextColumn::make('monto_utilizado')
                    ->label('Abono a pagar')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('estado')
                    ->badge()
                    ->formatStateUsing(function (string $state): string {
                        return match (strtoupper($state)) {
                            'APROBADA' => 'Aprobada y pendiente de egreso',
                            strtoupper(SolicitudPago::ESTADO_APROBADA_ANULADA) => 'Solicitud Aprobada Anulada',
                            default => $state,
                        };
                    })
                    ->color(fn(string $state) => match (strtoupper($state)) {
                        'APROBADA' => 'warning',
                        strtoupper(SolicitudPago::ESTADO_APROBADA_ANULADA) => 'danger',
                        default => 'success',
                    })
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\Action::make('registrarEgreso')
                    ->label('Registrar egreso')
                    ->icon('heroicon-o-arrow-up-right')
                    ->color('primary')
                    ->url(fn(SolicitudPago $record) => self::getUrl('registrar', ['record' => $record]))
                    ->openUrlInNewTab()
                    ->visible(fn(SolicitudPago $record) => strtoupper((string) $record->estado) === 'APROBADA')
                    ->button()
                    ->size('sm'),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('descargarPdf')
                        ->label('Solicitud PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('danger')
                        ->action(fn(SolicitudPago $record) => app(SolicitudPagoReportService::class)->exportPdf($record)),

                    Tables\Actions\Action::make('descargarPdfDetallado')
                        ->label('Solicitud PDF Detallado')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->color('danger')
                        ->action(fn(SolicitudPago $record) => app(SolicitudPagoReportService::class)->exportDetailedPdf($record)),

                    Tables\Actions\Action::make('descargarExcel')
                        ->label('Solicitud EXCEL')
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->action(fn(SolicitudPago $record) => app(SolicitudPagoReportService::class)->exportExcel($record)),
                ])
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->button()
                    ->size('sm'),

                Tables\Actions\Action::make('anularSolicitud')
                    ->label('Anular')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(SolicitudPago $record) => strtoupper((string) $record->estado) === 'APROBADA')
                    ->action(fn(SolicitudPago $record) => $record->update(['estado' => SolicitudPago::ESTADO_APROBADA_ANULADA]))
                    ->button()
                    ->size('sm'),
            ])

            ->filters([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEgresoSolicitudPagos::route('/'),
            'registrar' => Pages\RegistrarEgreso::route('/{record}/registro'),
        ];
    }
}
