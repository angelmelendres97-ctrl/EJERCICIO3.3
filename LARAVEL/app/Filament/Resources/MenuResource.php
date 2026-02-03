<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Filament\Resources\MenuResource\RelationManagers;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Menú')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Usuarios, Productos, Reportes'),
                        Forms\Components\TextInput::make('grupo')
                            ->label('Grupo')
                            ->maxLength(255)
                            ->placeholder('Ej: Administración, Compras, Reportes')
                            ->helperText('Define el grupo para organizar el menú en el sidebar.'),
                        Forms\Components\TextInput::make('icono')
                            ->placeholder('heroicon-o-home, heroicon-o-users, heroicon-o-chart-bar')
                            ->helperText('Usa iconos de Heroicons. Visita: https://heroicons.com/'),
                        Forms\Components\TextInput::make('ruta')
                            ->placeholder('/admin/usuarios, /admin/productos')
                            ->helperText('Ruta completa incluyendo el prefijo /admin'),
                        Forms\Components\TextInput::make('orden')
                            ->numeric()
                            ->default(0)
                            ->helperText('Orden de aparición en el menú (menor número = más arriba)'),
                        Forms\Components\Toggle::make('activo')
                            ->default(true)
                            ->helperText('Activa o desactiva la visibilidad del menú'),
                    ])->columns(2),

                Forms\Components\Section::make('Roles con Acceso')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->helperText('Selecciona los roles que tendrán acceso a este menú'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grupo')
                    ->label('Grupo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ruta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('icono')
                    ->icon(fn(string $state): string => $state)
                    ->size(Tables\Columns\IconColumn\IconColumnSize::Medium),
                Tables\Columns\ToggleColumn::make('activo'),
                Tables\Columns\TextColumn::make('orden')
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->separator(','),
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
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
