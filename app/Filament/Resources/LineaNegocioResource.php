<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LineaNegocioResource\Pages;
use App\Filament\Resources\LineaNegocioResource\RelationManagers;
use App\Models\LineaNegocio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LineaNegocioResource extends Resource
{
    protected static ?string $model = LineaNegocio::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Líneas de Negocio';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $pluralLabel = 'Líneas de Negocio';
    protected static ?string $label = 'Línea de Negocio';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Línea de Negocio')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Retail, Manufactura, Servicios'),
                        Forms\Components\Textarea::make('descripcion')
                            ->maxLength(65535)
                            ->rows(3)
                            ->placeholder('Descripción detallada de la línea de negocio'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListLineaNegocios::route('/'),
            'create' => Pages\CreateLineaNegocio::route('/create'),
            'edit' => Pages\EditLineaNegocio::route('/{record}/edit'),
        ];
    }
}
