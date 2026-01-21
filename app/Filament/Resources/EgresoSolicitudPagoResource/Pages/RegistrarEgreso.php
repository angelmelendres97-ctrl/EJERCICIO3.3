<?php

namespace App\Filament\Resources\EgresoSolicitudPagoResource\Pages;

use App\Filament\Resources\EgresoSolicitudPagoResource;
use App\Filament\Resources\SolicitudPagoResource;
use App\Models\SolicitudPago;
use App\Models\SolicitudPagoDetalle;
use Filament\Actions\Action;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class RegistrarEgreso extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = EgresoSolicitudPagoResource::class;

    protected static string $view = 'filament.resources.egreso-solicitud-pago-resource.pages.registrar-egreso';

    protected static ?string $title = 'Registrar egreso';

    protected static bool $shouldRegisterNavigation = false;

    public array $facturasByProvider = [];

    public array $providerContexts = [];

    public array $directorioEntries = [];

    public array $diarioEntries = [];

    public array $paymentMappings = [];

    protected array $catalogCache = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->hydrateProviderData();
    }

    public function getSolicitudProperty(): SolicitudPago
    {
        return $this->record;
    }

    protected function hydrateProviderData(): void
    {
        $detalles = $this->record->loadMissing('detalles')->detalles;

        $this->facturasByProvider = [];
        $this->providerContexts = [];

        foreach ($detalles->groupBy(fn(SolicitudPagoDetalle $detalle) => $this->buildProviderKey($detalle)) as $key => $items) {
            $first = $items->first();
            $context = [
                'conexion' => (int) ($first?->erp_conexion ?? 0),
                'empresa' => $first?->erp_empresa_id,
                'sucursal' => $first?->erp_sucursal,
            ];
            $monedaBase = $this->getMonedaBase($context);
            $cotizacionExterna = $this->getCotizacionExterna($context);

            $this->providerContexts[$key] = $context;

            $this->facturasByProvider[$key] = $items
                ->map(function (SolicitudPagoDetalle $detalle) use ($monedaBase, $cotizacionExterna) {
                    $abonoProgramado = (float) ($detalle->abono_aplicado ?? 0);
                    $saldo = (float) ($detalle->saldo_al_crear ?? 0);
                    $montos = $this->calculateMontos($abonoProgramado, 0, $monedaBase, $monedaBase, 1.0, $cotizacionExterna);

                    return [
                        'numero' => $detalle->numero_factura,
                        'fecha_emision' => $detalle->fecha_emision,
                        'fecha_vencimiento' => $detalle->fecha_vencimiento,
                        'saldo' => $saldo,
                        'abono_programado' => $abonoProgramado,
                        'abono_pagado' => 0,
                        'tipo' => $this->resolveFacturaTipo($detalle),
                        'detalle' => $this->buildDetalleFactura($detalle),
                        'moneda' => $monedaBase,
                        'cotizacion' => 1.0,
                        'debito_local' => $montos['debito_local'],
                        'credito_local' => $montos['credito_local'],
                        'debito_extranjera' => $montos['debito_extranjera'],
                        'credito_extranjera' => $montos['credito_extranjera'],
                        'abono_total' => $abonoProgramado,
                        'saldo_pendiente' => max(0, $abonoProgramado),
                    ];
                })
                ->values()
                ->all();
        }
    }

    protected function buildProviderKey(SolicitudPagoDetalle $detalle): string
    {
        return $this->buildProviderKeyFromValues($detalle->proveedor_codigo, $detalle->proveedor_ruc);
    }

    protected function buildProviderKeyFromValues(?string $codigo, ?string $ruc): string
    {
        return trim((string) $codigo) . '|' . trim((string) $ruc);
    }

    protected function resolveFacturaTipo(SolicitudPagoDetalle $detalle): string
    {
        return strtoupper((string) $detalle->erp_tabla) === 'COMPRA' ? 'CAN' : 'FACTURAS';
    }

    protected function buildDetalleFactura(SolicitudPagoDetalle $detalle): string
    {
        $motivo = $this->record->motivo ?? '';

        return $motivo !== '' ? $motivo : 'Pago factura ' . ($detalle->numero_factura ?? '');
    }

    


    protected function calculateMontos(
        float $debito,
        float $credito,
        ?string $moneda,
        ?string $monedaBase,
        float $cotizacion,
        float $cotizacionExterna
    ): array {
        $debitoLocal = 0.0;
        $creditoLocal = 0.0;
        $debitoExtranjera = 0.0;
        $creditoExtranjera = 0.0;

        if ($moneda && $monedaBase && $moneda !== $monedaBase) {
            $debitoLocal = round($debito * $cotizacion, 2);
            $creditoLocal = round($credito * $cotizacion, 2);
            $debitoExtranjera = round($debito, 2);
            $creditoExtranjera = round($credito, 2);
        } else {
            $debitoLocal = round($debito, 2);
            $creditoLocal = round($credito, 2);
            $debitoExtranjera = $cotizacionExterna > 0 ? round($debito / $cotizacionExterna, 2) : 0.0;
            $creditoExtranjera = $cotizacionExterna > 0 ? round($credito / $cotizacionExterna, 2) : 0.0;
        }

        return [
            'debito_local' => $debitoLocal,
            'credito_local' => $creditoLocal,
            'debito_extranjera' => $debitoExtranjera,
            'credito_extranjera' => $creditoExtranjera,
        ];
    }

    protected function getProvidersQuery(): Builder
    {
        return SolicitudPagoDetalle::query()
            ->selectRaw('
                MIN(id) as id,
                proveedor_codigo,
                proveedor_nombre,
                proveedor_ruc,
                MIN(erp_conexion) as erp_conexion,
                MIN(erp_empresa_id) as erp_empresa_id,
                MIN(erp_sucursal) as erp_sucursal,
                SUM(COALESCE(abono_aplicado, 0)) as total_abono,
                COUNT(*) as facturas_count
            ')
            ->where('solicitud_pago_id', $this->record->getKey())
            ->groupBy('proveedor_codigo', 'proveedor_nombre', 'proveedor_ruc')
            ->orderBy('proveedor_nombre');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getProvidersQuery())
            ->paginated(false)
            ->columns([
                TextColumn::make('proveedor_nombre')
                    ->label('Proveedor')
                    ->description(function (SolicitudPagoDetalle $record): string {
                        $ruc = $record->proveedor_ruc ? 'RUC: ' . $record->proveedor_ruc : null;
                        $codigo = $record->proveedor_codigo ? 'Código: ' . $record->proveedor_codigo : null;
                        $facturas = $record->facturas_count ? $record->facturas_count . ' factura(s)' : null;
                        return collect([$codigo, $ruc, $facturas])->filter()->implode(' · ');
                    })
                    ->searchable(),
                TextColumn::make('total_abono')
                    ->label('Total a pagar')
                    ->formatStateUsing(fn($state) => '$' . number_format((float) $state, 2, '.', ','))
                    ->alignRight(),
                TextColumn::make('saldo_pendiente_actual')
                    ->label('Saldo pendiente actual')
                    ->state(function (SolicitudPagoDetalle $record): float {
                        $key = $this->buildProviderKeyFromValues($record->proveedor_codigo, $record->proveedor_ruc);
                        return $this->getProviderSaldoPendiente($key);
                    })
                    ->formatStateUsing(fn($state) => '$' . number_format((float) $state, 2, '.', ','))
                    ->alignRight(),
                ViewColumn::make('facturas')
                    ->label('Facturas')
                    ->state(function (SolicitudPagoDetalle $record): array {
                        $key = $this->buildProviderKeyFromValues($record->proveedor_codigo, $record->proveedor_ruc);
                        return $this->facturasByProvider[$key] ?? [];
                    })
                    ->view('filament.tables.columns.egreso-facturas'),
            ])
            ->actions([
                Tables\Actions\Action::make('generarDirectorio')
                    ->label('Generar Directorio y Diario')
                    ->icon('heroicon-o-document-text')
                    ->modalHeading(fn(SolicitudPagoDetalle $record) => 'Generar Directorio y Diario - ' . ($record->proveedor_nombre ?? 'Proveedor'))
                    ->form(fn(SolicitudPagoDetalle $record) => $this->getDirectorioFormSchema($record))
                    ->action(function (SolicitudPagoDetalle $record, array $data): void {
                        $this->registrarDirectorioYDiario($record, $data);

                        Notification::make()
                            ->title('Directorio y diario generados')
                            ->body('Proveedor: ' . ($record->proveedor_nombre ?? 'N/D'))
                            ->success()
                            ->send();
                    })
                    ->visible(function (SolicitudPagoDetalle $record): bool {
                        $key = $this->buildProviderKeyFromValues($record->proveedor_codigo, $record->proveedor_ruc);
                        return $this->getProviderSaldoPendiente($key) > 0;
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn(StaticAction $action) => $action->label('Cancelar')),
            ])
            ->actionsColumnLabel('Acciones');
    }

    protected function registrarDirectorioYDiario(SolicitudPagoDetalle $record, array $data): void
    {
        $context = $this->resolveProviderContext($record);
        $providerKey = $this->buildProviderKeyFromValues($record->proveedor_codigo, $record->proveedor_ruc);
        $monedaBase = $this->getMonedaBase($context);
        $tipoEgreso = $data['tipo_egreso'] ?? 'cheque';
        $valor = (float) ($data['valor'] ?? 0);
        $formatos = $this->getFormatosOptions($context);
        $cuentasBancarias = $this->getCuentasBancariasOptions($context);
        $cuentasBancoContables = $this->getCuentasBancoContablesOptions($context);

        $facturas = collect($this->facturasByProvider[$providerKey] ?? [])
            ->filter(fn(array $factura) => (float) ($factura['saldo_pendiente'] ?? 0) > 0)
            ->values();

        $totalPendiente = $facturas->sum(fn(array $factura) => (float) ($factura['saldo_pendiente'] ?? 0));

        if ($valor <= 0 || $valor > $totalPendiente) {
            Notification::make()
                ->title('Valor inválido')
                ->body('El valor debe ser mayor a cero y no puede superar el saldo pendiente.')
                ->danger()
                ->send();
            return;
        }

        $moneda = $data['moneda'] ?? $monedaBase;
        $cotizacion = (float) ($data['cotizacion'] ?? 1);
        $cotizacionExterna = (float) ($data['cotizacion_externa'] ?? $this->getCotizacionExterna($context));
        $detalle = $data['detalle'] ?? null;
        $cuentaBancaria = $data['cuenta_bancaria'] ?? null;
        $documento = $tipoEgreso === 'cuenta_bancaria'
            ? ($data['documento'] ?? '')
            : ($data['numero_cheque'] ?? ($facturas->first()['numero'] ?? ''));
        $centroCosto = $data['centro_costo'] ?? null;
        $centroActividad = $data['centro_actividad'] ?? null;

        $cuentaProveedor = $this->getCuentaProveedor($context, $record->proveedor_codigo);
        $cuentaProveedorNombre = $this->getCuentaContableNombre($context, $cuentaProveedor);
        $cuentaBanco = $tipoEgreso === 'cuenta_bancaria'
            ? ($data['cuenta_contable'] ?? $cuentaBancaria)
            : ($data['cuenta_contable'] ?? null);
        $cuentaBancoNombre = $this->getCuentaContableNombre($context, $cuentaBanco);
        $cuentaBancariaNombre = $tipoEgreso === 'cuenta_bancaria'
            ? ($cuentasBancoContables[$cuentaBancaria] ?? null)
            : ($cuentasBancarias[$cuentaBancaria] ?? null);
        $formatoCheque = $data['formato_cheque'] ?? null;
        $formatoChequeNombre = $formatoCheque ? ($formatos[$formatoCheque] ?? null) : null;
        $cuentaBancariaInfo = $tipoEgreso === 'cheque' && $cuentaBancaria
            ? $this->getCuentaBancariaInfo($context, $cuentaBancaria)
            : null;
        $cuentaBancariaNumero = $cuentaBancariaInfo['numero_cuenta'] ?? null;

        $directorio = [];
        $facturaLines = [];
        $facturasActualizadas = $this->facturasByProvider[$providerKey] ?? [];
        $restante = $valor;

        foreach ($facturasActualizadas as $index => $factura) {
            if ($restante <= 0) {
                break;
            }

            $saldoPendiente = (float) ($factura['saldo_pendiente'] ?? 0);
            if ($saldoPendiente <= 0) {
                continue;
            }

            $aplicar = min($saldoPendiente, $restante);
            if ($aplicar <= 0) {
                continue;
            }

            $restante -= $aplicar;
            $factura['abono_pagado'] = (float) ($factura['abono_pagado'] ?? 0) + $aplicar;
            $factura['saldo_pendiente'] = max(0, $saldoPendiente - $aplicar);

            $montosActualizados = $this->calculateMontos(
                (float) ($factura['abono_pagado'] ?? 0),
                0,
                $moneda,
                $monedaBase,
                $cotizacion,
                $cotizacionExterna
            );
            $factura['moneda'] = $moneda;
            $factura['cotizacion'] = $cotizacion;
            $factura['debito_local'] = $montosActualizados['debito_local'];
            $factura['credito_local'] = $montosActualizados['credito_local'];
            $factura['debito_extranjera'] = $montosActualizados['debito_extranjera'];
            $factura['credito_extranjera'] = $montosActualizados['credito_extranjera'];
            $facturasActualizadas[$index] = $factura;

            $montosDirectorio = $this->calculateMontos(
                $aplicar,
                0,
                $moneda,
                $monedaBase,
                $cotizacion,
                $cotizacionExterna
            );
            $programado = (float) ($factura['abono_programado'] ?? 0);

            $directorio[] = [
                'proveedor' => $record->proveedor_nombre ?? $record->proveedor_codigo ?? null,
                'tipo' => $factura['tipo'] ?? null,
                'factura' => $factura['numero'] ?? '',
                'fecha_vencimiento' => $factura['fecha_vencimiento'] ?? null,
                'abono' => $aplicar,
                'saldo_pendiente' => max(0, $programado - $factura['abono_pagado']),
                'moneda' => $moneda,
                'cotizacion' => $cotizacion,
                'cotizacion_externa' => $cotizacionExterna,
                'detalle' => $detalle,
                'debito_local' => $montosDirectorio['debito_local'],
                'credito_local' => $montosDirectorio['credito_local'],
                'debito_extranjera' => $montosDirectorio['debito_extranjera'],
                'credito_extranjera' => $montosDirectorio['credito_extranjera'],
                'diario_generado' => true,
            ];

            $facturaLines[] = [
                'tipo_linea' => 'factura',
                'factura' => $factura['numero'] ?? null,
                'cuenta' => $cuentaProveedor,
                'cuenta_nombre' => $cuentaProveedorNombre,
                'documento' => $factura['numero'] ?? $documento,
                'cotizacion' => $cotizacion,
                'debito' => $montosDirectorio['debito_local'],
                'credito' => $montosDirectorio['credito_local'],
                'debito_extranjera' => $montosDirectorio['debito_extranjera'],
                'credito_extranjera' => $montosDirectorio['credito_extranjera'],
                'beneficiario' => $record->proveedor_nombre ?? null,
                'cuenta_bancaria' => null,
                'cuenta_bancaria_nombre' => null,
                'banco_cheque' => null,
                'fecha_vencimiento' => $factura['fecha_vencimiento'] ?? null,
                'formato_cheque' => null,
                'formato_cheque_nombre' => null,
                'codigo_contable' => $cuentaProveedor,
                'detalle' => $factura['detalle'] ?? $detalle,
                'centro_costo' => $centroCosto,
                'centro_actividad' => $centroActividad,
                'directorio' => $factura['numero'] ?? $documento,
            ];
        }

        $diario = [];

        $montosPago = $this->calculateMontos(0, $valor, $moneda, $monedaBase, $cotizacion, $cotizacionExterna);
        $diario[] = [
            'tipo_linea' => 'egreso',
            'es_banco' => true,
            'cuenta' => $cuentaBanco,
            'cuenta_nombre' => $cuentaBancoNombre,
            'documento' => $documento,
            'cotizacion' => $cotizacion,
            'debito' => $montosPago['debito_local'],
            'credito' => $montosPago['credito_local'],
            'debito_extranjera' => $montosPago['debito_extranjera'],
            'credito_extranjera' => $montosPago['credito_extranjera'],
            'beneficiario' => null,
            'cuenta_bancaria' => $cuentaBancaria,
            'cuenta_bancaria_nombre' => $cuentaBancariaNombre,
            'cuenta_bancaria_numero' => $cuentaBancariaNumero,
            'banco_cheque' => $tipoEgreso === 'cheque' ? ($data['numero_cheque'] ?? null) : $documento,
            'fecha_vencimiento' => null,
            'formato_cheque' => $formatoCheque,
            'formato_cheque_nombre' => $formatoChequeNombre,
            'codigo_contable' => $cuentaBanco,
            'detalle' => $tipoEgreso === 'cheque' ? ('Pago bancario ' . ($cuentaBancaria ?? '')) : $detalle,
            'centro_costo' => $centroCosto,
            'centro_actividad' => $centroActividad,
            'directorio' => $documento,
            'tipo_pago' => $tipoEgreso,
            'fecha_cheque' => $data['fecha_cheque'] ?? null,
        ];

        $existingEntries = $this->diarioEntries[$providerKey] ?? [];
        $merged = array_merge($existingEntries, $facturaLines, $diario);
        $fila = 1;
        foreach ($merged as $index => $linea) {
            $linea['fila'] = $fila++;
            $merged[$index] = $linea;
        }

        $this->directorioEntries[$providerKey] = array_merge($this->directorioEntries[$providerKey] ?? [], $directorio);
        $this->diarioEntries[$providerKey] = $merged;
        $this->paymentMappings[$providerKey][] = [
            'tipo_egreso' => $tipoEgreso,
            'moneda' => $moneda,
            'formato' => $data['formato'] ?? null,
            'detalle' => $detalle,
            'cotizacion' => $cotizacion,
            'cotizacion_externa' => $cotizacionExterna,
            'cuenta_bancaria' => $cuentaBancaria,
            'cuenta_contable' => $cuentaBanco,
            'numero_cheque' => $data['numero_cheque'] ?? null,
            'formato_cheque' => $data['formato_cheque'] ?? null,
            'fecha_cheque' => $data['fecha_cheque'] ?? null,
            'documento' => $documento,
            'centro_costo' => $centroCosto,
            'centro_actividad' => $centroActividad,
            'moneda_base' => $monedaBase,
        ];

        $this->facturasByProvider[$providerKey] = $facturasActualizadas;
    }

    protected function syncFacturaDisplayFromPago(string $providerKey, array $data, ?string $monedaBase): void
    {
        $cotizacion = (float) ($data['cotizacion'] ?? 1);
        $cotizacionExterna = (float) ($data['cotizacion_externa'] ?? 1);
        $moneda = $data['moneda'] ?? $monedaBase;

        $this->facturasByProvider[$providerKey] = collect($this->facturasByProvider[$providerKey] ?? [])
            ->map(function (array $factura) use ($data, $moneda, $monedaBase, $cotizacion, $cotizacionExterna) {
                $abono = (float) ($factura['abono_pagado'] ?? 0);
                $programado = (float) ($factura['abono_programado'] ?? 0);
                $montos = $this->calculateMontos($abono, 0, $moneda, $monedaBase, $cotizacion, $cotizacionExterna);

                return array_merge($factura, [
                    'detalle' => $data['detalle'] ?? $factura['detalle'] ?? null,
                    'moneda' => $moneda,
                    'cotizacion' => $cotizacion,
                    'debito_local' => $montos['debito_local'],
                    'credito_local' => $montos['credito_local'],
                    'debito_extranjera' => $montos['debito_extranjera'],
                    'credito_extranjera' => $montos['credito_extranjera'],
                    'abono_total' => $factura['abono_total'] ?? $programado,
                    'saldo_pendiente' => max(0, $programado - $abono),
                ]);
            })
            ->values()
            ->all();
    }

    protected function getDirectorioFormSchema(SolicitudPagoDetalle $record): array
    {
        $context = $this->resolveProviderContext($record);
        $monedas = $this->getMonedasOptions($context);
        $formatos = $this->getFormatosOptions($context);
        $cuentas = $this->getCuentasBancariasOptions($context);
        $cuentasBancoContables = $this->getCuentasBancoContablesOptions($context);
        $cuentasContables = $this->getCuentasContablesOptions($context);
        $centrosCosto = $this->getCentrosCostoOptions($context);
        $centrosActividad = $this->getCentrosActividadOptions($context);
        $providerKey = $this->buildProviderKeyFromValues($record->proveedor_codigo, $record->proveedor_ruc);
        $saldoPendiente = $this->getProviderSaldoPendiente($providerKey);

        $monedaBase = $this->getMonedaBase($context);
        $cotizacionExterna = $this->getCotizacionExterna($context);

        return [
            Wizard::make([
                Step::make('Tipo de egreso')
                    ->schema([
                        Select::make('tipo_egreso')
                            ->label('Tipo de egreso')
                            ->options([
                                'cheque' => 'Cheque',
                                'cuenta_bancaria' => 'Cuenta bancaria',
                            ])
                            ->required()
                            ->reactive(),
                    ]),
                Step::make('Datos contables')
                    ->schema([
                        Select::make('moneda')
                            ->label('Moneda')
                            ->options($monedas)
                            ->searchable()
                            ->required()
                            ->default($monedaBase)
                            ->visible(fn(Get $get) => $get('tipo_egreso') === 'cheque'),
                        Select::make('formato')
                            ->label('Formato')
                            ->options($formatos)
                            ->searchable()
                            ->required(fn(Get $get) => $get('tipo_egreso') === 'cheque')
                            ->visible(fn(Get $get) => $get('tipo_egreso') === 'cheque'),
                        Textarea::make('detalle')
                            ->label('Detalle')
                            ->rows(2)
                            ->default('Egreso de solicitud #' . $this->record->getKey())
                            ->required(),
                        TextInput::make('cotizacion')
                            ->label('Cotización')
                            ->numeric()
                            ->default(1)
                            ->required(fn(Get $get) => $get('tipo_egreso') === 'cheque')
                            ->visible(fn(Get $get) => $get('tipo_egreso') === 'cheque'),
                        TextInput::make('cotizacion_externa')
                            ->label('Cotización externa')
                            ->numeric()
                            ->default($cotizacionExterna)
                            ->required(fn(Get $get) => $get('tipo_egreso') === 'cheque')
                            ->visible(fn(Get $get) => $get('tipo_egreso') === 'cheque'),
                        TextInput::make('valor')
                            ->label('Valor')
                            ->numeric()
                            ->minValue(0.01)
                            ->maxValue($saldoPendiente)
                            ->default($saldoPendiente)
                            ->helperText('Disponible: $' . number_format($saldoPendiente, 2, '.', ','))
                            ->required(),
                    ])
                    ->columns(2),
                Step::make('Opciones de pago')
                    ->schema([
                        Select::make('cuenta_bancaria')
                            ->label('Cuenta bancaria')
                            ->options(function (Get $get) use ($cuentas, $cuentasBancoContables): array {
                                return $get('tipo_egreso') === 'cuenta_bancaria'
                                    ? $cuentasBancoContables
                                    : $cuentas;
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, Get $get) use ($context): void {
                                if ($get('tipo_egreso') === 'cheque') {
                                    $info = $this->getCuentaBancariaInfo($context, $state);
                                    if ($info) {
                                        $set('numero_cheque', $info['numero_cheque']);
                                        $set('formato_cheque', $info['formato_cheque']);
                                        $set('cuenta_contable', $info['cuenta_contable']);
                                    }
                                }

                                if ($get('tipo_egreso') === 'cuenta_bancaria') {
                                    $set('cuenta_contable', $state);
                                }
                            })
                            ->visible(fn(Get $get) => in_array($get('tipo_egreso'), ['cheque', 'cuenta_bancaria'], true)),
                        TextInput::make('numero_cheque')
                            ->label('N° cheque')
                            ->maxLength(50)
                            ->required(fn(Get $get) => $get('tipo_egreso') === 'cheque')
                            ->visible(fn(Get $get) => $get('tipo_egreso') === 'cheque'),
                        Select::make('formato_cheque')
                            ->label('Formato cheque')
                            ->options($formatos)
                            ->searchable()
                            ->required(fn(Get $get) => $get('tipo_egreso') === 'cheque')
                            ->visible(fn(Get $get) => $get('tipo_egreso') === 'cheque'),
                        DatePicker::make('fecha_cheque')
                            ->label('Fecha de cheque')
                            ->default(Carbon::now())
                            ->required(fn(Get $get) => $get('tipo_egreso') === 'cheque')
                            ->visible(fn(Get $get) => $get('tipo_egreso') === 'cheque'),
                        Select::make('cuenta_contable')
                            ->label('Cuenta contable')
                            ->options($cuentasContables)
                            ->searchable()
                            ->required(fn(Get $get) => $get('tipo_egreso') === 'cheque')
                            ->visible(fn(Get $get) => $get('tipo_egreso') === 'cheque')
                            ->dehydrated(),
                        TextInput::make('documento')
                            ->label('Documento')
                            ->maxLength(50)
                            ->required(fn(Get $get) => $get('tipo_egreso') === 'cuenta_bancaria')
                            ->visible(fn(Get $get) => $get('tipo_egreso') === 'cuenta_bancaria'),
                        Select::make('centro_costo')
                            ->label('Centro de costo')
                            ->options($centrosCosto)
                            ->searchable()
                            ->required(fn(Get $get) => $get('tipo_egreso') === 'cuenta_bancaria')
                            ->visible(fn(Get $get) => $get('tipo_egreso') === 'cuenta_bancaria'),
                        Select::make('centro_actividad')
                            ->label('Centro de actividad')
                            ->options($centrosActividad)
                            ->searchable()
                            ->required(fn(Get $get) => $get('tipo_egreso') === 'cuenta_bancaria')
                            ->visible(fn(Get $get) => $get('tipo_egreso') === 'cuenta_bancaria'),
                    ])
                    ->columns(2),
            ])
                ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                    <x-filament::button type="submit" size="sm">
                        Generar
                    </x-filament::button>
                BLADE)))
                ->skippable(false),
        ];
    }

    protected function resolveProviderContext(SolicitudPagoDetalle $record): array
    {
        $key = $this->buildProviderKeyFromValues($record->proveedor_codigo, $record->proveedor_ruc);

        return $this->providerContexts[$key] ?? [
            'conexion' => (int) ($record->erp_conexion ?? 0),
            'empresa' => $record->erp_empresa_id,
            'sucursal' => $record->erp_sucursal,
        ];
    }

    protected function getCatalogCacheKey(array $context, string $type): string
    {
        return implode('|', [
            $type,
            $context['conexion'] ?? '0',
            $context['empresa'] ?? '0',
            $context['sucursal'] ?? '0',
        ]);
    }

    protected function getExternalConnection(array $context): ?string
    {
        $conexionId = (int) ($context['conexion'] ?? 0);

        if (! $conexionId) {
            return null;
        }

        return SolicitudPagoResource::getExternalConnectionName($conexionId);
    }

    protected function getMonedasOptions(array $context): array
    {
        $cacheKey = $this->getCatalogCacheKey($context, 'monedas');

        if (array_key_exists($cacheKey, $this->catalogCache)) {
            return $this->catalogCache[$cacheKey];
        }

        $connection = $this->getExternalConnection($context);
        $empresa = $context['empresa'] ?? null;

        if (! $connection || ! $empresa) {
            return $this->catalogCache[$cacheKey] = [];
        }

        try {
            $options = DB::connection($connection)
                ->table('saemone')
                ->where('mone_cod_empr', $empresa)
                ->pluck('mone_des_mone', 'mone_cod_mone')
                ->all();
        } catch (\Throwable $e) {
            $options = [];
        }

        return $this->catalogCache[$cacheKey] = $options;
    }

    protected function getFormatosOptions(array $context): array
    {
        $cacheKey = $this->getCatalogCacheKey($context, 'formatos');

        if (array_key_exists($cacheKey, $this->catalogCache)) {
            return $this->catalogCache[$cacheKey];
        }

        $connection = $this->getExternalConnection($context);
        $empresa = $context['empresa'] ?? null;

        if (! $connection || ! $empresa) {
            return $this->catalogCache[$cacheKey] = [];
        }

        try {
            $options = DB::connection($connection)
                ->table('saeftrn')
                ->where('ftrn_cod_empr', $empresa)
                ->where('ftrn_cod_modu', 5)
                ->where('ftrn_tip_movi', 'EG')
                ->pluck('ftrn_des_ftrn', 'ftrn_cod_ftrn')
                ->all();
        } catch (\Throwable $e) {
            $options = [];
        }

        return $this->catalogCache[$cacheKey] = $options;
    }

    protected function getCuentasBancariasOptions(array $context): array
    {
        $cacheKey = $this->getCatalogCacheKey($context, 'cuentas-bancarias');

        if (array_key_exists($cacheKey, $this->catalogCache)) {
            return $this->catalogCache[$cacheKey];
        }

        $connection = $this->getExternalConnection($context);
        $empresa = $context['empresa'] ?? null;

        if (! $connection || ! $empresa) {
            return $this->catalogCache[$cacheKey] = [];
        }

        try {
            $rows = DB::connection($connection)
                ->table('saectab')
                ->join('saebanc', function ($join) {
                    $join->on('banc_cod_empr', '=', 'ctab_cod_empr')
                        ->on('banc_cod_banc', '=', 'ctab_cod_banc');
                })
                ->where('ctab_cod_empr', $empresa)
                ->whereIn('ctab_tip_ctab', ['C', 'B'])
                ->select([
                    'ctab_cod_ctab',
                    'ctab_cod_cuen',
                    'banc_nom_banc',
                    'ctab_num_ctab',
                ])
                ->orderBy('banc_nom_banc')
                ->get();

            $options = $rows
                ->mapWithKeys(fn($row) => [
                    $row->ctab_cod_ctab => $row->ctab_cod_cuen . ' - ' . $row->banc_nom_banc . ' - ' . $row->ctab_num_ctab,
                ])
                ->all();
        } catch (\Throwable $e) {
            $options = [];
        }

        return $this->catalogCache[$cacheKey] = $options;
    }

    protected function getCuentasBancoContablesOptions(array $context): array
    {
        $cacheKey = $this->getCatalogCacheKey($context, 'cuentas-banco-contables');

        if (array_key_exists($cacheKey, $this->catalogCache)) {
            return $this->catalogCache[$cacheKey];
        }

        $connection = $this->getExternalConnection($context);
        $empresa = $context['empresa'] ?? null;

        if (! $connection || ! $empresa) {
            return $this->catalogCache[$cacheKey] = [];
        }

        try {
            $rows = DB::connection($connection)
                ->table('saecuen')
                ->where('cuen_cod_empr', $empresa)
                ->where('cuen_mov_cuen', 1)
                ->orderBy('cuen_cod_cuen')
                ->get(['cuen_cod_cuen', 'cuen_nom_cuen']);

            $options = $rows
                ->mapWithKeys(fn($row) => [
                    $row->cuen_cod_cuen => $row->cuen_cod_cuen . ' - ' . $row->cuen_nom_cuen,
                ])
                ->all();
        } catch (\Throwable $e) {
            $options = [];
        }

        return $this->catalogCache[$cacheKey] = $options;
    }

    protected function getCuentasContablesOptions(array $context): array
    {
        $cacheKey = $this->getCatalogCacheKey($context, 'cuentas-contables');

        if (array_key_exists($cacheKey, $this->catalogCache)) {
            return $this->catalogCache[$cacheKey];
        }

        $connection = $this->getExternalConnection($context);
        $empresa = $context['empresa'] ?? null;

        if (! $connection || ! $empresa) {
            return $this->catalogCache[$cacheKey] = [];
        }

        try {
            $options = DB::connection($connection)
                ->table('saecuen')
                ->where('cuen_cod_empr', $empresa)
                ->pluck('cuen_nom_cuen', 'cuen_cod_cuen')
                ->all();
        } catch (\Throwable $e) {
            $options = [];
        }

        return $this->catalogCache[$cacheKey] = $options;
    }

    protected function getCentrosCostoOptions(array $context): array
    {
        $cacheKey = $this->getCatalogCacheKey($context, 'centros-costo');

        if (array_key_exists($cacheKey, $this->catalogCache)) {
            return $this->catalogCache[$cacheKey];
        }

        $connection = $this->getExternalConnection($context);
        $empresa = $context['empresa'] ?? null;

        if (! $connection || ! $empresa) {
            return $this->catalogCache[$cacheKey] = [];
        }

        try {
            $options = DB::connection($connection)
                ->table('saeccosn')
                ->where('ccosn_cod_empr', $empresa)
                ->where('ccosn_mov_ccosn', 1)
                ->orderBy('ccosn_nom_ccosn')
                ->get()
                ->mapWithKeys(fn($row) => [
                    $row->ccosn_cod_ccosn => $row->ccosn_nom_ccosn . ' - ' . $row->ccosn_cod_ccosn,
                ])
                ->all();
        } catch (\Throwable $e) {
            $options = [];
        }

        return $this->catalogCache[$cacheKey] = $options;
    }

    protected function getCentrosActividadOptions(array $context): array
    {
        $cacheKey = $this->getCatalogCacheKey($context, 'centros-actividad');

        if (array_key_exists($cacheKey, $this->catalogCache)) {
            return $this->catalogCache[$cacheKey];
        }

        $connection = $this->getExternalConnection($context);
        $empresa = $context['empresa'] ?? null;

        if (! $connection || ! $empresa) {
            return $this->catalogCache[$cacheKey] = [];
        }

        try {
            $options = DB::connection($connection)
                ->table('saecact')
                ->where('cact_cod_empr', $empresa)
                ->orderBy('cact_nom_cact')
                ->get()
                ->mapWithKeys(fn($row) => [
                    $row->cact_cod_cact => $row->cact_nom_cact . ' - ' . $row->cact_cod_cact,
                ])
                ->all();
        } catch (\Throwable $e) {
            $options = [];
        }

        return $this->catalogCache[$cacheKey] = $options;
    }

    protected function getCuentaBancariaInfo(array $context, $cta): ?array
    {
        if (! $cta) {
            return null;
        }

        $cacheKey = $this->getCatalogCacheKey($context, 'cuenta-info-' . $cta);

        if (array_key_exists($cacheKey, $this->catalogCache)) {
            return $this->catalogCache[$cacheKey];
        }

        $connection = $this->getExternalConnection($context);
        $empresa = $context['empresa'] ?? null;

        if (! $connection || ! $empresa) {
            return $this->catalogCache[$cacheKey] = null;
        }

        try {
            $row = DB::connection($connection)
                ->table('saectab')
                ->where('ctab_cod_empr', $empresa)
                ->where('ctab_cod_ctab', $cta)
                ->select(['ctab_num_cheq', 'ctab_for_cheq', 'ctab_cod_cuen', 'ctab_num_ctab'])
                ->first();

            if (! $row) {
                return $this->catalogCache[$cacheKey] = null;
            }

            return $this->catalogCache[$cacheKey] = [
                'numero_cheque' => (string) $row->ctab_num_cheq,
                'formato_cheque' => $row->ctab_for_cheq,
                'cuenta_contable' => $row->ctab_cod_cuen,
                'numero_cuenta' => $row->ctab_num_ctab,
            ];
        } catch (\Throwable $e) {
            return $this->catalogCache[$cacheKey] = null;
        }
    }

    protected function getCuentaProveedor(array $context, ?string $proveedorCodigo): ?string
    {
        if (! $proveedorCodigo) {
            return null;
        }

        $cacheKey = $this->getCatalogCacheKey($context, 'cuenta-proveedor-' . $proveedorCodigo);

        if (array_key_exists($cacheKey, $this->catalogCache)) {
            return $this->catalogCache[$cacheKey];
        }

        $connection = $this->getExternalConnection($context);
        $empresa = $context['empresa'] ?? null;

        if (! $connection || ! $empresa) {
            return $this->catalogCache[$cacheKey] = null;
        }

        try {
            $cuenta = DB::connection($connection)
                ->table('saeclpv')
                ->where('clpv_cod_empr', $empresa)
                ->where('clpv_cod_clpv', $proveedorCodigo)
                ->value('clpv_cod_cuen');
        } catch (\Throwable $e) {
            $cuenta = null;
        }

        return $this->catalogCache[$cacheKey] = $cuenta;
    }

    protected function getCuentaContableNombre(array $context, ?string $cuenta): ?string
    {
        if (! $cuenta) {
            return null;
        }

        $cacheKey = $this->getCatalogCacheKey($context, 'cuenta-nombre-' . $cuenta);

        if (array_key_exists($cacheKey, $this->catalogCache)) {
            return $this->catalogCache[$cacheKey];
        }

        $connection = $this->getExternalConnection($context);
        $empresa = $context['empresa'] ?? null;

        if (! $connection || ! $empresa) {
            return $this->catalogCache[$cacheKey] = null;
        }

        try {
            $nombre = DB::connection($connection)
                ->table('saecuen')
                ->where('cuen_cod_empr', $empresa)
                ->where('cuen_cod_cuen', $cuenta)
                ->value('cuen_nom_cuen');
        } catch (\Throwable $e) {
            $nombre = null;
        }

        return $this->catalogCache[$cacheKey] = $nombre;
    }

    protected function getMonedaBase(array $context): ?string
    {
        $cacheKey = $this->getCatalogCacheKey($context, 'moneda-base');

        if (array_key_exists($cacheKey, $this->catalogCache)) {
            return $this->catalogCache[$cacheKey];
        }

        $connection = $this->getExternalConnection($context);
        $empresa = $context['empresa'] ?? null;

        if (! $connection || ! $empresa) {
            return $this->catalogCache[$cacheKey] = null;
        }

        try {
            $moneda = DB::connection($connection)
                ->table('saepcon')
                ->where('pcon_cod_empr', $empresa)
                ->value('pcon_mon_base');
        } catch (\Throwable $e) {
            $moneda = null;
        }

        return $this->catalogCache[$cacheKey] = $moneda;
    }

    protected function getCotizacionExterna(array $context): float
    {
        $cacheKey = $this->getCatalogCacheKey($context, 'cotizacion-externa');

        if (array_key_exists($cacheKey, $this->catalogCache)) {
            return $this->catalogCache[$cacheKey];
        }

        $connection = $this->getExternalConnection($context);
        $empresa = $context['empresa'] ?? null;

        if (! $connection || ! $empresa) {
            return $this->catalogCache[$cacheKey] = 1.0;
        }

        try {
            $monedaExtra = DB::connection($connection)
                ->table('saepcon')
                ->where('pcon_cod_empr', $empresa)
                ->value('pcon_seg_mone');

            if (! $monedaExtra) {
                return $this->catalogCache[$cacheKey] = 1.0;
            }

            $cotizacion = DB::connection($connection)
                ->table('saetcam')
                ->where('mone_cod_empr', $empresa)
                ->where('tcam_cod_mone', $monedaExtra)
                ->orderByDesc('tcam_fec_tcam')
                ->value('tcam_val_tcam');
        } catch (\Throwable $e) {
            $cotizacion = null;
        }

        return $this->catalogCache[$cacheKey] = (float) ($cotizacion ?? 1.0);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver al listado')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(EgresoSolicitudPagoResource::getUrl()),
            Action::make('registrarEgreso')
                ->label('Registrar egreso')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->disabled(fn() => $this->totalDiferencia !== 0.0 || empty($this->diarioEntries))
                ->visible(fn() => strtoupper((string) $this->record->estado) === 'APROBADA')
                ->action(function (): void {
                    try {
                        $reportContext = $this->registrarEgresoContable();
                        $this->record->update(['estado' => 'FINALIZADO']);
                        $token = (string) Str::uuid();

                        Cache::put(
                            $this->buildReportCacheKey($token),
                            [
                                'solicitud_id' => $this->record->getKey(),
                                'contexts' => $reportContext,
                            ],
                            now()->addMinutes(10)
                        );

                        Notification::make()
                            ->title('Egreso registrado')
                            ->body('El egreso quedó registrado en las tablas contables.')
                            ->success()
                            ->send();

                        $this->dispatchBrowserEvent('egreso-reporte', [
                            'url' => route('egresos.solicitud-pago.reporte', ['token' => $token]),
                        ]);

                        $this->redirect(EgresoSolicitudPagoResource::getUrl());
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('Error al registrar')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function getTotalDebitoProperty(): float
    {
        return collect($this->diarioEntries)
            ->flatten(1)
            ->sum(fn(array $linea) => (float) ($linea['debito'] ?? 0));
    }

    public function getTotalCreditoProperty(): float
    {
        return collect($this->diarioEntries)
            ->flatten(1)
            ->sum(fn(array $linea) => (float) ($linea['credito'] ?? 0));
    }

    public function getTotalDiferenciaProperty(): float
    {
        return round($this->totalDebito - $this->totalCredito, 2);
    }

    public function getTotalAbonoProperty(): float
    {
        return (float) ($this->record->detalles?->sum('abono_aplicado') ?? 0);
    }

    public function getTotalFacturasProperty(): int
    {
        return (int) ($this->record->detalles?->count() ?? 0);
    }

    public function getTotalSaldoProperty(): float
    {
        return (float) ($this->record->detalles?->sum('saldo_al_crear') ?? 0);
    }

    public function getTotalAbonoHtmlProperty(): HtmlString
    {
        return new HtmlString('$' . number_format($this->totalAbono, 2, '.', ','));
    }

    protected function getProviderSaldoPendiente(string $providerKey): float
    {
        return collect($this->facturasByProvider[$providerKey] ?? [])
            ->sum(fn(array $factura) => (float) ($factura['saldo_pendiente'] ?? 0));
    }

    protected function registrarEgresoContable(): array
    {
        if (empty($this->diarioEntries)) {
            throw new \RuntimeException('No hay movimientos en el diario para registrar.');
        }

        if (strtoupper((string) $this->record->estado) !== 'APROBADA') {
            throw new \RuntimeException('La solicitud ya fue procesada y no puede registrarse nuevamente.');
        }

        $reportContext = [];

        foreach ($this->diarioEntries as $providerKey => $entries) {
            $context = $this->providerContexts[$providerKey] ?? null;

            if (! $context) {
                throw new \RuntimeException('No se encontró el contexto contable del proveedor.');
            }

            $reportContext[] = $this->registrarContabilidadProveedor(
                $providerKey,
                $entries,
                $this->directorioEntries[$providerKey] ?? [],
                $context
            );
        }

        return $reportContext;
    }

    protected function registrarContabilidadProveedor(
        string $providerKey,
        array $entries,
        array $directorioEntries,
        array $context
    ): array {
        $connection = $this->getExternalConnection($context);
        $empresa = $context['empresa'] ?? null;
        $sucursal = $context['sucursal'] ?? null;

        if (! $connection || ! $empresa || ! $sucursal) {
            throw new \RuntimeException('No existe conexión o empresa/sucursal válida para el registro contable.');
        }

        return DB::connection($connection)->transaction(function () use (
            $connection,
            $empresa,
            $sucursal,
            $providerKey,
            $entries,
            $directorioEntries,
            $context
        ): array {
            $fechaMovimiento = $this->record->fecha
                ? Carbon::parse($this->record->fecha)
                : Carbon::now();

            $moneda = $this->getMonedaBase($context);
            $tidu = DB::connection($connection)
                ->table('saetidu')
                ->where('tidu_cod_empr', $empresa)
                ->where('tidu_cod_modu', 5)
                ->where('tidu_tip_tidu', 'EG')
                ->orderBy('tidu_cod_tidu')
                ->value('tidu_cod_tidu');

            if (! $tidu) {
                throw new \RuntimeException('No se encontró el tipo de documento EG para la empresa.');
            }

            $ejercicio = DB::connection($connection)
                ->table('saeejer')
                ->where('ejer_cod_empr', $empresa)
                ->whereDate('ejer_fec_finl', $fechaMovimiento->copy()->endOfYear()->toDateString())
                ->value('ejer_cod_ejer');

            if (! $ejercicio) {
                throw new \RuntimeException('No se encontró el ejercicio contable para la fecha indicada.');
            }

            $periodo = (int) $fechaMovimiento->format('m');

            $secuencia = DB::connection($connection)
                ->table('saesecu')
                ->where('secu_cod_empr', $empresa)
                ->where('secu_cod_sucu', $sucursal)
                ->where('secu_cod_tidu', $tidu)
                ->where('secu_cod_modu', 5)
                ->where('secu_cod_ejer', $ejercicio)
                ->where('secu_num_prdo', $periodo)
                ->first();

            if (! $secuencia) {
                throw new \RuntimeException('No se encontró la secuencia contable para el período.');
            }

            $secuDia = $this->incrementarSecuencia($secuencia->secu_egr_comp ?? '');
            $secuAsto = $this->incrementarSecuencia($secuencia->secu_asi_comp ?? '');

            DB::connection($connection)
                ->table('saesecu')
                ->where('secu_cod_empr', $empresa)
                ->where('secu_cod_sucu', $sucursal)
                ->where('secu_cod_tidu', $tidu)
                ->where('secu_cod_modu', 5)
                ->where('secu_cod_ejer', $ejercicio)
                ->where('secu_num_prdo', $periodo)
                ->update([
                    'secu_egr_comp' => $secuDia,
                    'secu_asi_comp' => $secuAsto,
                ]);

            $userId = auth()->id() ?? 0;
            $userName = auth()->user()?->name ?? 'Sistema';
            $beneficiario = $this->resolveBeneficiarioProveedor($providerKey, $entries);
            $detalleAsto = $this->resolveDetalleAsto($providerKey);

            DB::connection($connection)
                ->table('saeasto')
                ->insert([
                    'asto_cod_asto' => $secuAsto,
                    'asto_cod_empr' => $empresa,
                    'asto_cod_sucu' => $sucursal,
                    'asto_cod_ejer' => $ejercicio,
                    'asto_num_prdo' => $periodo,
                    'asto_cod_mone' => $moneda,
                    'asto_cod_usua' => $userId,
                    'asto_cod_modu' => 5,
                    'asto_cod_tdoc' => '',
                    'asto_ben_asto' => $beneficiario,
                    'asto_vat_asto' => 0,
                    'asto_fec_asto' => $fechaMovimiento->toDateString(),
                    'asto_det_asto' => $detalleAsto,
                    'asto_est_asto' => 'PE',
                    'asto_num_mayo' => $secuAsto,
                    'asto_fec_emis' => $fechaMovimiento->toDateString(),
                    'asto_tipo_mov' => 'EG',
                    'asto_cot_asto' => 1,
                    'asto_for_impr' => 8,
                    'asto_cod_tidu' => $tidu,
                    'asto_usu_asto' => 1,
                    'asto_fec_serv' => DB::raw('CURRENT_DATE'),
                    'asto_user_web' => $userId,
                    'asto_fec_fina' => $fechaMovimiento->toDateString(),
                ]);

            $dirCodigo = 0;
            foreach ($directorioEntries as $entry) {
                $dirCodigo++;
                $fechaVence = $entry['fecha_vencimiento'] ?? null;

                DB::connection($connection)
                    ->table('saedir')
                    ->insert([
                        'dir_cod_dir' => $dirCodigo,
                        'dire_cod_asto' => $secuAsto,
                        'dire_cod_empr' => $empresa,
                        'dire_cod_sucu' => $sucursal,
                        'asto_cod_ejer' => $ejercicio,
                        'asto_num_prdo' => $periodo,
                        'dir_cod_cli' => $this->resolveProveedorCodigo($providerKey),
                        'tran_cod_modu' => 4,
                        'dir_cod_tran' => 'CAN',
                        'dir_num_fact' => $entry['factura'] ?? null,
                        'dir_fec_venc' => $fechaVence ? Carbon::parse($fechaVence)->toDateString() : null,
                        'dir_detalle' => $entry['detalle'] ?? null,
                        'dire_tip_camb' => (float) ($entry['cotizacion'] ?? 1),
                        'dir_deb_ml' => (float) ($entry['debito_local'] ?? 0),
                        'dir_cre_ml' => (float) ($entry['credito_local'] ?? 0),
                        'dir_deb_mex' => (float) ($entry['debito_extranjera'] ?? 0),
                        'dir_cred_mex' => (float) ($entry['credito_extranjera'] ?? 0),
                        'bandera_cr' => 'DB',
                        'dir_aut_usua' => '',
                        'dir_aut_impr' => '',
                        'dir_fac_inic' => '',
                        'dir_fac_fina' => '',
                        'dir_ser_docu' => '',
                        'dir_fec_vali' => null,
                        'dire_suc_clpv' => $sucursal,
                        'dir_user_web' => $userId,
                        'dire_nom_clpv' => $entry['proveedor'] ?? $beneficiario,
                        'dir_cod_ccli' => null,
                    ]);
            }

            $totalDebito = 0.0;
            $dasiCodigo = 0;
            foreach ($entries as $entry) {
                $dasiCodigo++;
                $totalDebito += (float) ($entry['debito'] ?? 0);
                $ctaBancaria = $entry['cuenta_bancaria'] ?? null;
                $esCheque = ($entry['tipo_pago'] ?? null) === 'cheque';
                $numCheque = $entry['banco_cheque'] ?? ($entry['documento'] ?? '');
                $opBacn = $ctaBancaria ? 'S' : 'N';
                $opFlch = $ctaBancaria ? 1 : null; //verificar q se oase

                DB::connection($connection)
                    ->table('saedasi')
                    ->insert([
                        'asto_cod_asto' => $secuAsto,
                        'asto_cod_empr' => $empresa,
                        'asto_cod_sucu' => $sucursal,
                        'dasi_num_prdo' => $periodo,
                        'asto_cod_ejer' => $ejercicio,
                        'dasi_cod_cuen' => $entry['cuenta'] ?? null,
                        'ccos_cod_ccos' => $entry['centro_costo'] ?? '',
                        'dasi_dml_dasi' => (float) ($entry['debito'] ?? 0),
                        'dasi_cml_dasi' => (float) ($entry['credito'] ?? 0),
                        'dasi_dme_dasi' => (float) ($entry['debito_extranjera'] ?? 0),
                        'dasi_cme_dasi' => (float) ($entry['credito_extranjera'] ?? 0),
                        'dasi_tip_camb' => (float) ($entry['cotizacion'] ?? 1),
                        'dasi_det_asi' => $entry['detalle'] ?? null,
                        'dasi_nom_ctac' => $entry['cuenta_nombre'] ?? ($entry['nombre'] ?? null),
                        'dasi_cod_clie' => $this->resolveProveedorCodigo($providerKey),
                        'dasi_cod_tran' => '',
                        'dasi_user_web' => $userId,
                        'dasi_cod_ret' => null,
                        'dasi_cod_dir' => null,
                        'dasi_cta_ret' => null,
                        'dasi_cru_dasi' => 'AC',
                        'dasi_ban_dasi' => 'S',
                        'dasi_bca_dasi' => $opBacn,
                        'dasi_con_flch' => $opFlch,
                        'dasi_num_depo' => $numCheque,
                        'dasi_cod_cact' => $entry['centro_actividad'] ?? null,
                    ]);

                if ($esCheque && $ctaBancaria) {
                    $ctaNumero = $entry['cuenta_bancaria_numero'] ?? $ctaBancaria;
                    $fechaCheque = $entry['fecha_cheque'] ?? null;

                    DB::connection($connection)
                        ->table('saedchc')
                        ->insert([
                            'dchc_cod_ctab' => $ctaBancaria,
                            'dchc_cod_asto' => $secuAsto,
                            'asto_cod_empr' => $empresa,
                            'asto_cod_sucu' => $sucursal,
                            'asto_cod_ejer' => $ejercicio,
                            'asto_num_prdo' => $periodo,
                            'dchc_num_dchc' => $numCheque,
                            'dchc_val_dchc' => (float) ($entry['credito'] ?? 0),
                            'dchc_cta_dchc' => $ctaNumero,
                            'dchc_fec_dchc' => $fechaCheque ? Carbon::parse($fechaCheque)->toDateString() : null,
                            'dchc_benf_dchc' => $entry['beneficiario'] ?? '',
                            'dchc_cod_cuen' => $entry['cuenta'] ?? null,
                            'dchc_nom_banc' => $entry['cuenta_nombre'] ?? '',
                            'dchc_con_fila' => $dasiCodigo,
                        ]);

                    DB::connection($connection)
                        ->table('saectab')
                        ->where('ctab_cod_empr', $empresa)
                        ->where('ctab_cod_sucu', $sucursal)
                        ->where('ctab_cod_ctab', $ctaBancaria)
                        ->update(['ctab_num_cheq' => $numCheque]);
                }
            }

            DB::connection($connection)
                ->table('saeasto')
                ->where('asto_cod_empr', $empresa)
                ->where('asto_cod_sucu', $sucursal)
                ->where('asto_cod_asto', $secuAsto)
                ->where('asto_cod_ejer', $ejercicio)
                ->where('asto_num_prdo', $periodo)
                ->update([
                    'asto_est_asto' => 'MY',
                    'asto_vat_asto' => $totalDebito,
                ]);

            return [
                'connection' => $connection,
                'empresa' => $empresa,
                'sucursal' => $sucursal,
                'ejercicio' => $ejercicio,
                'periodo' => $periodo,
                'asto_cod_asto' => $secuAsto,
            ];
        });
    }

    protected function buildReportCacheKey(string $token): string
    {
        return 'egreso_report_' . $token;
    }

    protected function incrementarSecuencia(?string $secuencia): string
    {
        $secuencia = (string) $secuencia;
        $prefijo = substr($secuencia, 0, 5);
        $numero = (int) substr($secuencia, 5);
        $numero++;

        return $prefijo . str_pad((string) $numero, 8, '0', STR_PAD_LEFT);
    }

    protected function resolveProveedorCodigo(string $providerKey): ?string
    {
        return explode('|', $providerKey)[0] ?? null;
    }

    protected function resolveBeneficiarioProveedor(string $providerKey, array $entries): string
    {
        $beneficiario = collect($entries)
            ->pluck('beneficiario')
            ->filter()
            ->first();

        if ($beneficiario) {
            return (string) $beneficiario;
        }

        return (string) ($this->resolveProveedorCodigo($providerKey) ?? '');
    }

    protected function resolveDetalleAsto(string $providerKey): string
    {
        $detalle = collect($this->paymentMappings[$providerKey] ?? [])
            ->pluck('detalle')
            ->filter()
            ->first();

        if ($detalle) {
            return (string) $detalle;
        }

        return $this->record->motivo
            ? (string) $this->record->motivo
            : 'Egreso solicitud #' . $this->record->getKey();
    }
}
