<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpresaResource\Pages;
use App\Filament\Resources\EmpresaResource\RelationManagers;
use App\Models\Empresa;
use App\Models\LineaNegocio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmpresaResource extends Resource
{
    protected static ?string $model = Empresa::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\TextInput::make('ruc')
                            ->required()
                            ->maxLength(11)
                            ->placeholder('Ej: 20123456789')
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('nombre_empresa')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nombre de la empresa'),
                        Forms\Components\TextInput::make('nombre_pb')
                            ->label('Nombre PB')
                            ->maxLength(255)
                            ->placeholder('Nombre alterno para PB'),
                        Forms\Components\Select::make('tipo')
                            ->required()
                            ->options([
                                'azul' => 'Azul',
                                'plomo' => 'Plomo',
                            ])
                            ->default('azul'),
                        Forms\Components\Select::make('linea_negocio_id')
                            ->label('Línea de Negocio')
                            ->required()
                            ->options(LineaNegocio::pluck('nombre', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nombre')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('descripcion')
                                    ->maxLength(65535)
                                    ->rows(3),
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Configuración de Base de Datos')
                    ->schema([
                        Forms\Components\TextInput::make('motor')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: mysql, pgsql, sqlsrv'),
                        Forms\Components\TextInput::make('host')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: localhost, 192.168.1.100'),
                        Forms\Components\TextInput::make('puerto')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('Ej: 3306, 5432, 1433'),
                        Forms\Components\TextInput::make('nombre_base')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nombre de la base de datos'),
                        Forms\Components\TextInput::make('usuario')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Usuario de la base de datos'),
                        Forms\Components\TextInput::make('clave')
                            ->maxLength(255)
                            ->password()
                            ->placeholder('Contraseña de la base de datos'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ruc')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre_empresa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre_pb')
                    ->label('Nombre PB')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo'),
                Tables\Columns\TextColumn::make('lineaNegocio.nombre')
                    ->label('Línea de Negocio')
                    ->searchable()
                    ->sortable()
                    ->color('success')
                    ->badge(),
                Tables\Columns\TextColumn::make('motor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('puerto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('host')
                    ->searchable(),
                Tables\Columns\TextColumn::make('usuario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre_base')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_conexion')
                    ->badge()
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        0 => 'Sin Conexion',
                        1 => 'En linea',
                        default => 'Desconocido',
                    })
                    ->color(fn(int $state): string => match ($state) {
                        0 => 'warning',
                        1 => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('mensaje_conexion')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
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
            ])
            ->headerActions([
                Tables\Actions\Action::make('sincronizar_conexiones')
                    ->label('Sincronizar conexiones')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->action(function () {
                        $empresas = \App\Models\Empresa::all();
                        $conexionesExitosas = 0;
                        $conexionesFallidas = 0;

                        foreach ($empresas as $empresa) {
                            try {
                                $driver = strtolower($empresa->motor ?? 'mysql');
                                $host = $empresa->host ?? '127.0.0.1';
                                $port = $empresa->puerto ?? null;
                                $database = $empresa->nombre_base ?? '';
                                $username = $empresa->usuario ?? null;
                                $password = $empresa->clave ?? null;

                                // Construir DSN según el driver
                                switch ($driver) {
                                    case 'mysql':
                                        $portPart = $port ? ";port={$port}" : '';
                                        $dsn = "mysql:host={$host}{$portPart};dbname={$database};charset=utf8mb4";
                                        break;
                                    case 'pgsql':
                                    case 'postgres':
                                        $portPart = $port ? ";port={$port}" : '';
                                        $dsn = "pgsql:host={$host}{$portPart};dbname={$database}";
                                        break;
                                    case 'sqlsrv':
                                    case 'mssql':
                                        $portPart = $port ? ",{$port}" : '';
                                        $dsn = "sqlsrv:Server={$host}{$portPart};Database={$database}";
                                        break;
                                    case 'sqlite':
                                        // Para SQLite, nombre_base normalmente es la ruta al archivo
                                        $dsn = "sqlite:{$database}";
                                        $username = null;
                                        $password = null;
                                        break;
                                    default:
                                        throw new \Exception("Driver no soportado: {$driver}");
                                }

                                // Opciones PDO razonables
                                $options = [
                                    \PDO::ATTR_TIMEOUT => 5,
                                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                                ];

                                // Intentar crear instancia PDO (esto probará la conexión)
                                $pdo = new \PDO($dsn, $username, $password, $options);
                                // Ejecutar una consulta mínima opcional para confirmar la conexión en algunos drivers
                                if ($driver === 'mysql' || $driver === 'pgsql') {
                                    $pdo->query('SELECT 1');
                                }

                                // Si llegamos aquí, la conexión fue exitosa
                                $empresa->update([
                                    'status_conexion' => 1,
                                    'mensaje_conexion' => 'Conexión exitosa',
                                ]);
                                $conexionesExitosas++;
                            } catch (\Throwable $e) {
                                // Error de conexión: guardar mensaje y marcar como fallida
                                $mensaje = $e->getMessage();
                                // Limitar longitud del mensaje para evitar problemas con campos muy pequeños
                                if (is_string($mensaje) && strlen($mensaje) > 1000) {
                                    $mensaje = substr($mensaje, 0, 1000) . '...';
                                }

                                $empresa->update([
                                    'status_conexion' => 0,
                                    'mensaje_conexion' => $mensaje,
                                ]);
                                $conexionesFallidas++;
                            }
                        }

                        // Notificación de resultados
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Sincronización completada')
                            ->body("Conexiones exitosas: {$conexionesExitosas}, Fallidas: {$conexionesFallidas}")
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Sincronizar conexiones')
                    ->modalDescription('¿Está seguro de que desea sincronizar todas las conexiones de las empresas?')
                    ->modalSubmitActionLabel('Sí, sincronizar todas'),
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
            'index' => Pages\ListEmpresas::route('/'),
            'create' => Pages\CreateEmpresa::route('/create'),
            'edit' => Pages\EditEmpresa::route('/{record}/edit'),
        ];
    }
}
