<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnidadMedidaResource\Pages;
use App\Filament\Resources\UnidadMedidaResource\RelationManagers;
use App\Models\UnidadMedida;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class UnidadMedidaResource extends Resource
{
    protected static ?string $model = UnidadMedida::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('siglas')
                    ->required()
                    ->maxLength(10),

                // Ocultamos el usuario y la fecha en el formulario y los establecemos automáticamente .
                Forms\Components\Hidden::make('id_usuario')
                    ->default(fn () => Auth::id()),
                Forms\Components\Hidden::make('fecha_creacion')
                    ->default(fn () => now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('siglas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_creacion')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListUnidadMedidas::route('/'),
            'create' => Pages\CreateUnidadMedida::route('/create'),
            'edit' => Pages\EditUnidadMedida::route('/{record}/edit'),
        ];
    }
}
