<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SolicitudPagoResource;
use App\Models\SolicitudPago;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\WithPagination;

class SolicitudPagoFacturas extends Page implements HasForms
{
    use InteractsWithForms;
    use WithPagination;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.solicitud-pago-facturas';

    protected static ?string $title = 'Solicitud de Pagos';

    public ?array $filters = [];

    public array $facturasDisponibles = [];

    public array $selectedInvoices = [];

    public array $openProviders = [];

    public array $invoiceAbonos = [];

    public array $providerAbonos = [];

    public array $providerDescriptions = [];

    public ?SolicitudPago $solicitud = null;

    public array $compraForm = [
        'conexion_id' => null,
        'empresa_codigo' => null,
        'descripcion_proveedor' => '',
        'valor_pagar' => null,
        'abono' => null,
    ];

    public bool $showCompraModal = false;

    public int $perPage = 10;

    public string $search = '';

    public ?string $sortField = 'proveedor_nombre';

    public string $sortDirection = 'asc';

    public function mount(): void
    {
        $recordId = request()->integer('record');
        if ($recordId) {
            $this->solicitud = SolicitudPago::with(['detalles', 'aprobador', 'contextos'])->find($recordId);
        }

        if ($this->solicitud) {
            $this->hydrateFromRecord();
        }

        if (! $this->solicitud) {
            $this->form->fill([
                'fecha_desde' => Carbon::now()->subYears(5)->startOfDay(),
                'fecha_hasta' => Carbon::now()->endOfDay(),
                'monto_aprobado' => null,
                'motivo' => null,
                'conexiones' => [],
                'monto_estimado' => 0,
            ]);
        }
    }

    public function montoAprobadoValue(): float
    {
        $value = $this->filters['monto_aprobado'] ?? 0;

        $s = (string) $value;
        $s = str_replace(['$', ' '], '', $s);

        // ES: 2.000,00
        if (preg_match('/^\d{1,3}(\.\d{3})*(,\d+)?$/', $s)) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            // EN: 2,000.00
            $s = str_replace(',', '', $s);
        }

        return (float) $s;
    }

    public function openCompraModal(): void
    {
        $this->resetCompraForm();
        $this->showCompraModal = true;
    }

    public function closeCompraModal(): void
    {
        $this->showCompraModal = false;
    }

    protected function resetCompraForm(): void
    {
        $conexionId = $this->solicitud?->id_empresa
            ?? collect($this->filters['conexiones'] ?? [])->first();

        $this->compraForm = [
            'conexion_id' => $conexionId,
            'empresa_codigo' => null,
            'descripcion_proveedor' => '',
            'valor_pagar' => null,
            'abono' => null,
        ];
    }

    public function updatedCompraFormConexionId($value): void
    {
        $this->compraForm['empresa_codigo'] = null;
    }

    public function guardarCompra(): void
    {
        if ($this->isSolicitudAprobada()) {
            Notification::make()
                ->title('La solicitud ya fue aprobada y no puede modificarse.')
                ->warning()
                ->send();

            return;
        }

        $data = $this->validate([
            'compraForm.conexion_id' => ['required', 'integer'],
            'compraForm.empresa_codigo' => ['required', 'string'],
            'compraForm.descripcion_proveedor' => ['required', 'string', 'max:255'],
            'compraForm.valor_pagar' => ['required', 'numeric', 'min:0.01'],
            'compraForm.abono' => ['nullable', 'numeric', 'min:0'],
        ]);

        $conexion = (int) $data['compraForm']['conexion_id'];
        $empresaCodigo = (string) $data['compraForm']['empresa_codigo'];

        $valor = (float) $data['compraForm']['valor_pagar'];
        $abono = (float) ($data['compraForm']['abono'] ?? 0);
        $abono = min($abono, $valor);

        $descripcionProveedor = trim((string) $data['compraForm']['descripcion_proveedor']);
        $compraCodigo = 'COMPRA-' . Str::upper(Str::random(8));
        $facturaNumero = $compraCodigo;

        $contexto = $this->resolveCompraContext($conexion, $empresaCodigo);

        $facturaKey = $this->buildFacturaKey(
            (string) $conexion,
            $contexto['empresa_codigo'],
            $contexto['sucursal_codigo'],
            $compraCodigo,
            $facturaNumero,
            null
        );

        $factura = [
            'key' => $facturaKey,
            'numero' => $facturaNumero,
            'fecha_emision' => Carbon::now()->format('Y-m-d'),
            'fecha_vencimiento' => null,
            'saldo' => $valor,
            'total' => $valor,
            'monto' => $valor,
            'empresa_codigo' => $contexto['empresa_codigo'],
            'empresa_nombre' => $contexto['empresa_nombre'],
            'sucursal_codigo' => $contexto['sucursal_codigo'],
            'sucursal_nombre' => $contexto['sucursal_nombre'],
            'conexion_id' => $conexion,
            'conexion_nombre' => $contexto['conexion_nombre'],
            'tipo' => 'compra',
        ];

        $providerKey = $this->buildProveedorKey($compraCodigo, '', '');

        $nuevoProveedor = [
            'key' => $providerKey,
            'proveedor_codigo' => $compraCodigo,
            'proveedor_nombre' => $descripcionProveedor,
            'proveedor_ruc' => null,
            'proveedor_actividad' => $descripcionProveedor,
            'total' => $valor,
            'facturas_count' => 1,
            'es_compra' => true,
            'empresas' => [[
                'conexion_id' => $conexion,
                'conexion_nombre' => $contexto['conexion_nombre'],
                'empresa_codigo' => $contexto['empresa_codigo'],
                'empresa_nombre' => $contexto['empresa_nombre'],
                'sucursales' => [[
                    'sucursal_codigo' => $contexto['sucursal_codigo'],
                    'sucursal_nombre' => $contexto['sucursal_nombre'],
                    'facturas' => [$factura],
                ]],
            ]],
        ];

        $this->facturasDisponibles[] = $nuevoProveedor;
        $this->providerDescriptions[$providerKey] = $descripcionProveedor;

        $abonoAjustado = $this->resolveAbonoPermitido($facturaKey, $abono, $valor);

        if ($abonoAjustado > 0) {
            $this->invoiceAbonos[$facturaKey] = $abonoAjustado;
            $this->selectedInvoices[] = $facturaKey;
        }

        $this->syncProviderAbonoFromInvoices($providerKey);
        $this->filters['monto_estimado'] = $this->montoEsperado;
        $this->updatedSelectedInvoices();

        $this->showCompraModal = false;

        Notification::make()
            ->title('Compra agregada')
            ->success()
            ->send();
    }



    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function generateReport(): void
    {
        if ($this->solicitud) {
            return;
        }

        $this->resetPage();
        $this->loadFacturas();
    }

    public function getAbonoEnUsoProperty(): float
    {
        return collect($this->invoiceAbonos)->sum(fn($v) => max(0, (float) $v));
    }

    public function getTotalFacturasProperty(): float
    {
        return collect($this->facturasDisponibles)
            ->flatMap(fn(array $proveedor) => collect($proveedor['empresas'] ?? [])->flatMap(fn(array $empresa) => collect($empresa['sucursales'] ?? [])->flatMap(fn(array $sucursal) => collect($sucursal['facturas'] ?? []))))
            ->sum(fn(array $factura) => (float) ($factura['saldo'] ?? 0));
    }

    protected function resolveCompraContext(int $conexion, ?string $empresaCodigo = null): array
    {
        $conexionNombre = \App\Models\Empresa::query()
            ->where('id', $conexion)
            ->value('nombre_empresa') ?? '';

        $empresasSeleccionadas = $this->groupOptionsByConnection($this->filters['empresas'] ?? []);
        $empresaCodigo = $empresaCodigo ?? ($empresasSeleccionadas[$conexion][0] ?? '');
        $empresaNombre = $empresaCodigo !== ''
            ? (SolicitudPagoResource::getEmpresasOptions($conexion)[$empresaCodigo] ?? $empresaCodigo)
            : '';

        $sucursalesDisponibles = $empresaCodigo !== ''
            ? SolicitudPagoResource::getSucursalesOptions($conexion, [$empresaCodigo])
            : [];

        $sucursalesSeleccionadas = $this->groupOptionsByConnection($this->filters['sucursales'] ?? []);
        $sucursalCodigo = $sucursalesSeleccionadas[$conexion][0] ?? '';
        if ($sucursalCodigo !== '' && ! array_key_exists($sucursalCodigo, $sucursalesDisponibles)) {
            $sucursalCodigo = '';
        }
        if ($sucursalCodigo === '') {
            $sucursalCodigo = (string) array_key_first($sucursalesDisponibles);
        }
        $sucursalNombre = $sucursalCodigo !== '' && $empresaCodigo !== ''
            ? ($sucursalesDisponibles[$sucursalCodigo] ?? $sucursalCodigo)
            : ($sucursalCodigo !== '' ? $sucursalCodigo : '');

        return [
            'conexion_nombre' => $conexionNombre,
            'empresa_codigo' => $empresaCodigo,
            'empresa_nombre' => $empresaNombre,
            'sucursal_codigo' => $sucursalCodigo,
            'sucursal_nombre' => $sucursalNombre,
        ];
    }

    public function getCompraConexionesOptionsProperty(): array
    {
        return \App\Models\Empresa::query()->pluck('nombre_empresa', 'id')->all();
    }

    public function getCompraEmpresasOptionsProperty(): array
    {
        $conexionId = $this->compraForm['conexion_id'] ?? null;

        if (! $conexionId) {
            return [];
        }

        return SolicitudPagoResource::getEmpresasOptions((int) $conexionId);
    }

    protected function hydrateFromRecord(): void
    {
        $conexiones = $this->solicitud->contextos->pluck('empresa_id')->filter()->unique()->values()->all();
        if (empty($conexiones)) {
            $conexiones = collect($this->solicitud->detalles ?? [])
                ->pluck('erp_conexion')
                ->filter()
                ->map(fn($value) => (int) $value)
                ->unique()
                ->values()
                ->all();
        }

        $empresasSeleccionadas = collect($this->solicitud->detalles ?? [])
            ->map(fn($detalle) => ($detalle->erp_conexion ?? '') . '|' . ($detalle->erp_empresa_id ?? ''))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $sucursalesSeleccionadas = collect($this->solicitud->detalles ?? [])
            ->map(fn($detalle) => ($detalle->erp_conexion ?? '') . '|' . ($detalle->erp_sucursal ?? ''))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->filters = [
            'conexiones' => ! empty($conexiones) ? $conexiones : [$this->solicitud->id_empresa],
            'empresas' => $empresasSeleccionadas,
            'sucursales' => $sucursalesSeleccionadas,
            'fecha_desde' => $this->solicitud->fecha?->copy()->subMonth()->startOfDay(),
            'fecha_hasta' => $this->solicitud->fecha?->copy()->addMonth()->endOfDay(),
            'monto_aprobado' => $this->solicitud->monto_aprobado,
            'motivo' => $this->solicitud->motivo,
        ];

        $this->facturasDisponibles = $this->buildFacturasDesdeSolicitud($this->solicitud);
        $this->syncProviderMetadata($this->facturasDisponibles);
        $this->selectedInvoices = collect($this->solicitud->detalles ?? [])
            ->filter(fn($detalle) => (float) ($detalle->abono_aplicado ?? 0) > 0)
            ->map(fn($detalle) => $detalle->erp_clave ?? $this->buildFacturaKey(
                (string) ($detalle->erp_conexion ?? ''),
                (string) ($detalle->erp_empresa_id ?? ''),
                (string) ($detalle->erp_sucursal ?? ''),
                (string) ($detalle->proveedor_codigo ?? ''),
                (string) ($detalle->numero_factura ?? ''),
                (string) ($detalle->proveedor_ruc ?? '')
            ))
            ->filter()
            ->values()
            ->all();

        $this->invoiceAbonos = collect($this->solicitud->detalles ?? [])
            ->mapWithKeys(function ($detalle) {
                $abono = (float) ($detalle->abono_aplicado ?? 0);
                if ($abono <= 0) {
                    return [];
                }

                $key = $detalle->erp_clave ?? $this->buildFacturaKey(
                    (string) ($detalle->erp_conexion ?? ''),
                    (string) ($detalle->erp_empresa_id ?? ''),
                    (string) ($detalle->erp_sucursal ?? ''),
                    (string) ($detalle->proveedor_codigo ?? ''),
                    (string) ($detalle->numero_factura ?? ''),
                    (string) ($detalle->proveedor_ruc ?? '')
                );

                return [
                    $key => $abono,
                ];
            })
            ->all();

        $this->syncAllProviderAbonosFromInvoices();

        $this->filters['monto_estimado'] = $this->montoEsperado;

        $this->form->fill($this->filters);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('filters')
            ->schema([
                Section::make('Datos de la solicitud')
                    ->hidden()
                    ->columns(3)

                    ->schema([
                        Select::make('conexiones')
                            ->label('Conexiones')
                            ->multiple()
                            ->options(\App\Models\Empresa::query()->pluck('nombre_empresa', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->hidden()
                            ->dehydrated(true)
                            ->afterStateUpdated(function (Forms\Set $set, ?array $state): void {
                                $empresas = $this->buildDefaultEmpresasSelection($state ?? []);
                                $sucursales = $this->buildDefaultSucursalesSelection($state ?? [], $empresas);

                                $set('empresas', $empresas);
                                $set('sucursales', $sucursales);
                                $this->resetPage();
                                $this->resetFacturasData();
                            }),

                        Select::make('empresas')
                            ->label('Empresa')
                            ->multiple()
                            ->options(fn(Forms\Get $get): array => $this->getEmpresasOptionsByConnections($get('conexiones') ?? []))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->hidden(fn() => (bool) $this->solicitud)
                            ->afterStateUpdated(function (): void {
                                $this->syncSucursales();
                                $this->resetPage();
                                $this->resetFacturasData();
                            }),
                        Select::make('sucursales')
                            ->label('Sucursal')
                            ->multiple()
                            ->options(fn(Forms\Get $get): array => $this->getSucursalesOptionsByConnections(
                                $get('conexiones') ?? [],
                                $this->groupOptionsByConnection($get('empresas') ?? []),
                            ))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->hidden(fn() => (bool) $this->solicitud)
                            ->afterStateUpdated(function (): void {
                                $this->resetPage();
                                $this->resetFacturasData();
                            }),
                    ]),
                Section::make('Filtros de búsqueda')
                    ->hidden(fn() => (bool) $this->solicitud)
                    ->columns(4)
                    ->schema([
                        DatePicker::make('fecha_desde')
                            ->label('Fecha desde')
                            ->default(Carbon::now()->subYears(5)->startOfDay())
                            ->live()
                            ->afterStateUpdated(function (): void {
                                $this->resetPage();
                                $this->resetFacturasData();
                            }),
                        DatePicker::make('fecha_hasta')
                            ->label('Fecha hasta')
                            ->default(Carbon::now()->endOfDay())
                            ->live()
                            ->afterStateUpdated(function (): void {
                                $this->resetPage();
                                $this->resetFacturasData();
                            }),
                        Actions::make([
                            FormAction::make('generateReport')
                                ->label('Generar reporte')
                                ->icon('heroicon-o-document-arrow-down')
                                ->color('primary')
                                ->action(fn() => $this->generateReport()),
                        ])->columnSpan(1),
                    ]),
                Section::make('Resumen y aprobación')
                    ->hidden()
                    ->columns(3)
                    ->schema([
                        // Monto estimado (SOLO VISTA, viene de monto_esperado)
                        TextInput::make('monto_estimado')
                            ->label('Monto esperado')
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (TextInput $component, $state): void {
                                $component->state($this->filters['monto_estimado'] ?? $state ?? 0);
                            })
                            ->formatStateUsing(function ($state) {
                                $value = $this->filters['monto_estimado'] ?? $state ?? 0;

                                return number_format((float) $value, 2, '.', ',');
                            }),

                        // Monto aprobado (VALOR REAL)
                        TextInput::make('monto_aprobado')
                            ->label('Monto aprobado')
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (TextInput $component, $state): void {
                                $component->state($state ?? ($this->filters['monto_aprobado'] ?? 0));
                            })
                            ->formatStateUsing(
                                fn($state) =>
                                number_format((float) ($state ?? 0), 2, '.', ',')
                            ),

                        Textarea::make('motivo')
                            ->label('Motivo')
                            ->rows(2)
                            ->maxLength(1000)
                            ->placeholder('Ingrese el motivo...'),
                    ]),
            ]);
    }

    protected function syncSucursales(): void
    {
        $conexiones = $this->filters['conexiones'] ?? [];
        $empresas = $this->filters['empresas'] ?? [];

        $this->filters['sucursales'] = $this->buildDefaultSucursalesSelection($conexiones, $empresas);
    }

    protected function resetFacturasData(): void
    {
        $this->selectedInvoices = [];
        $this->invoiceAbonos = [];
        $this->providerAbonos = [];
        $this->facturasDisponibles = [];
        $this->openProviders = [];
        $this->providerDescriptions = [];
        $this->filters['monto_estimado'] = $this->montoEsperado;
    }

    public function loadFacturas(): void
    {
        if ($this->solicitud) {
            return;
        }

        $conexiones = $this->filters['conexiones'] ?? [];
        $empresas = $this->groupOptionsByConnection($this->filters['empresas'] ?? []);
        $sucursales = $this->groupOptionsByConnection($this->filters['sucursales'] ?? []);
        $desde = $this->filters['fecha_desde'] ?? null;
        $hasta = $this->filters['fecha_hasta'] ?? null;

        $this->resetFacturasData();

        if (empty($conexiones)) {
            return;
        }

        $this->facturasDisponibles = $this->buildFacturas($conexiones, $empresas, $sucursales, $desde, $hasta);
        $this->syncProviderMetadata($this->facturasDisponibles);
    }

    public function getTotalSeleccionadoProperty(): float
    {
        $selected = collect($this->getSelectedInvoices());

        return $selected->sum(fn(array $factura) => (float) ($factura['abono'] ?? 0));
    }

    public function getMontoEsperadoProperty(): float
    {
        $selected = collect($this->getSelectedInvoices());

        return $selected->sum(fn(array $factura) => (float) ($factura['saldo'] ?? $factura['total'] ?? 0));
    }

    public function getPresupuestoDisponibleProperty(): float
    {
        $aprobado = $this->montoAprobadoValue();
        return max(0, $aprobado - $this->abonoEnUso);
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver al listado')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(SolicitudPagoResource::getUrl()),
            ActionGroup::make([
                Action::make('exportPdf')
                    ->label('Reporte PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->form($this->getReportDescriptionForm())
                    ->action(fn(array $data) => $this->exportPdf($data['descripcion_reporte'] ?? '')),
                Action::make('exportDetailedPdf')
                    ->label('Reporte detallado PDF')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('danger')
                    ->form($this->getReportDescriptionForm())
                    ->action(fn(array $data) => $this->exportDetailedPdf($data['descripcion_reporte'] ?? '')),
                Action::make('exportExcel')
                    ->label('Reporte Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->action(fn() => $this->exportExcel()),
                Action::make('exportDetailedExcel')
                    ->label('Reporte detallado Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->action(fn() => $this->exportDetailedExcel()),
            ])
                ->label('Reporte PDF')
                ->button()                // 🔥 ESTO ES LA CLAVE
                ->iconPosition('before')
                ->color('danger')
                ->icon('heroicon-o-arrow-down-tray'),
            Action::make('guardarBorrador')
                ->label('Guardar borrador')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->visible(fn() => ! $this->isSolicitudAprobada())
                ->action(fn() => $this->guardarSolicitud('BORRADOR')),
            Action::make('aprobarSolicitud')
                ->label('Aprobar y enviar')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn() => ! $this->isSolicitudAprobada())
                ->action(fn() => $this->guardarSolicitud('APROBADA')),
        ];
    }

    protected function guardarSolicitud(string $estado = 'BORRADOR'): void
    {
        if ($this->isSolicitudAprobada()) {
            Notification::make()
                ->title('La solicitud ya fue aprobada y no puede modificarse.')
                ->warning()
                ->send();

            return;
        }

        $montoAprobado = $this->montoAprobadoValue();

        if ($estado === 'APROBADA' && $montoAprobado <= 0) {
            Notification::make()
                ->title('Ingrese un monto aprobado válido')
                ->warning()
                ->send();

            return;
        }

        if ($estado === 'APROBADA' && $this->totalSeleccionado <= 0) {
            Notification::make()
                ->title('Ingrese un abono para al menos una factura')
                ->warning()
                ->send();
            $this->redirect(SolicitudPagoResource::getUrl());
            return;
        }

        $facturasParaGuardar = $estado === 'APROBADA'
            ? collect($this->getSelectedInvoices())
            ->filter(fn(array $factura) => (float) ($factura['abono'] ?? 0) > 0)
            ->values()
            ->all()
            : $this->getFacturasConAbono();

        if ($estado === 'APROBADA' && empty($facturasParaGuardar)) {
            Notification::make()
                ->title('Seleccione al menos una factura')
                ->warning()
                ->send();

            return;
        }

        if ($estado !== 'APROBADA' && empty($facturasParaGuardar)) {
            Notification::make()
                ->title('No hay facturas para guardar en la solicitud')
                ->warning()
                ->send();

            return;
        }

        $conexion = $this->solicitud?->id_empresa ?? collect($this->filters['conexiones'] ?? [])->first();

        if (! $conexion) {
            Notification::make()
                ->title('Seleccione una conexión para guardar la solicitud')
                ->warning()
                ->send();

            return;
        }

        $montoEstimado = collect($facturasParaGuardar)
            ->sum(fn(array $factura) => (float) ($factura['saldo'] ?? $factura['total'] ?? 0));
        $montoUtilizado = collect($facturasParaGuardar)
            ->sum(fn(array $factura) => (float) ($factura['abono'] ?? 0));

        if ($estado === 'APROBADA' && $montoUtilizado > $montoAprobado) {
            Notification::make()
                ->title('El abono supera el monto aprobado')
                ->body('Ajuste los valores de abono o incremente el monto aprobado para continuar.')
                ->warning()
                ->send();

            return;
        }

        DB::transaction(function () use ($conexion, $facturasParaGuardar, $montoEstimado, $montoAprobado, $montoUtilizado, $estado) {
            $empresasSeleccionadas = $this->groupOptionsByConnection($this->filters['empresas'] ?? []);
            $sucursalesSeleccionadas = $this->groupOptionsByConnection($this->filters['sucursales'] ?? []);
            $estadoGuardar = $estado === 'APROBADA' ? 'BORRADOR' : $estado;

            $payload = [
                'id_empresa' => $conexion,
                'fecha' => $this->solicitud?->fecha ?? Carbon::now(),
                'tipo_solicitud' => 'Pago de Facturas',
                'monto_estimado' => $montoEstimado,
                'monto_aprobado' => $montoAprobado,
                'monto_utilizado' => $montoUtilizado,
                'motivo' => $this->filters['motivo'] ?? null,
                'estado' => $estadoGuardar,
            ];

            if ($this->solicitud) {
                if ($estado === 'APROBADA' && strtoupper((string) $this->solicitud->estado) !== 'BORRADOR') {
                    throw new \RuntimeException('Solo se puede aprobar una solicitud en estado BORRADOR.');
                }

                $this->solicitud->update($payload);
                $this->solicitud->contextos()->delete();
                $this->solicitud->detalles()->delete();
                $solicitud = $this->solicitud;
            } else {
                $payload['creado_por_id'] = Auth::id();
                $solicitud = SolicitudPago::create($payload);
                $this->solicitud = $solicitud;
            }

            $contextos = $this->mapContextosDesdeSeleccion($empresasSeleccionadas, $sucursalesSeleccionadas);
            if (! empty($contextos)) {
                $solicitud->contextos()->createMany($contextos);
            }

            $detalles = $this->mapDetallesDesdeSeleccion($facturasParaGuardar, $conexion);

            if ($estado === 'APROBADA') {
                $this->assertDetallesValidos($detalles);
                $solicitud->monto_estimado = collect($detalles)->sum('saldo_al_crear');
                $solicitud->monto_utilizado = collect($detalles)->sum('abono_aplicado');
                $solicitud->save();
            }

            if (! empty($detalles)) {
                $solicitud->detalles()->createMany($detalles);
            }

            if ($estado === 'APROBADA') {
                $solicitud->aprobado_por_id = Auth::id();
                $solicitud->aprobada_at = Carbon::now();
                $solicitud->estado = 'APROBADA';
                $solicitud->save();
            }
        });

        if ($this->solicitud) {
            $this->solicitud->refresh(['detalles']);
            $this->hydrateFromRecord();
        }

        $this->selectedInvoices = $this->solicitud ? $this->selectedInvoices : [];

        Notification::make()
            ->title($this->solicitud ? 'Solicitud de Pago guardada' : 'Solicitud de Pago creada')
            ->body($estado === 'APROBADA' ? 'La solicitud fue aprobada y enviada.' : 'La solicitud quedó guardada como borrador.')
            ->success()
            ->send();

        if ($estado === 'APROBADA') {
            $this->redirect(SolicitudPagoResource::getUrl());
        }
    }

    protected function mapDetallesDesdeSeleccion(array $facturas, int $conexion): array
    {
        return collect($facturas)
            ->map(function (array $factura) use ($conexion) {
                $abono = (float) ($factura['abono'] ?? $factura['saldo'] ?? 0);
                $saldo = (float) ($factura['saldo'] ?? 0);
                $total = (float) ($factura['total'] ?? $factura['monto'] ?? $saldo);
                $erpClave = $factura['key'] ?? $this->buildFacturaKey(
                    (string) ($factura['conexion_id'] ?? $conexion),
                    (string) ($factura['empresa_codigo'] ?? ''),
                    (string) ($factura['sucursal_codigo'] ?? ''),
                    (string) ($factura['proveedor_codigo'] ?? ''),
                    (string) ($factura['numero'] ?? ''),
                    (string) ($factura['proveedor_ruc'] ?? '')
                );

                return [
                    'erp_tabla' => ($factura['tipo'] ?? null) === 'compra' ? 'COMPRA' : 'SAEDMCP',
                    'erp_conexion' => (string) ($factura['conexion_id'] ?? $conexion),
                    'erp_empresa_id' => (string) ($factura['empresa_codigo'] ?? ''),
                    'erp_sucursal' => (string) ($factura['sucursal_codigo'] ?? ''),
                    'erp_clave' => $erpClave,
                    'proveedor_ruc' => (string) ($factura['proveedor_ruc'] ?? ''),
                    'proveedor_codigo' => $factura['proveedor_codigo'] ?? null,
                    'proveedor_nombre' => $factura['proveedor_nombre'] ?? '',
                    'numero_factura' => $factura['numero'] ?? '',
                    'fecha_emision' => $factura['fecha_emision'] ?? null,
                    'fecha_vencimiento' => $factura['fecha_vencimiento'] ?? null,
                    'monto_factura' => $total,
                    'saldo_al_crear' => $saldo,
                    'abono_aplicado' => $abono,
                    'estado_abono' => $this->resolveEstadoAbono($total, $abono),
                ];
            })
            ->values()
            ->all();
    }

    protected function resolveEstadoAbono(float $total, float $abono): string
    {
        $total = max(0, $total);
        $abono = max(0, $abono);

        if ($abono <= 0) {
            return 'SIN_ABONO';
        }

        if ($total > 0 && $abono >= $total) {
            return 'ABONADO';
        }

        return 'ABONADO_PARCIAL';
    }

    protected function isSolicitudAprobada(): bool
    {
        return $this->solicitud !== null
            && in_array(
                strtoupper((string) $this->solicitud->estado),
                ['APROBADA', strtoupper(SolicitudPago::ESTADO_APROBADA_ANULADA)],
                true
            );
    }

    protected function mapContextosDesdeSeleccion(array $empresasSeleccionadas, array $sucursalesSeleccionadas): array
    {
        $contextos = [];

        foreach ($this->filters['conexiones'] ?? [] as $conexionId) {
            $empresaId = (int) $conexionId;
            $conexion = (string) $conexionId;
            $sucursales = $sucursalesSeleccionadas[$conexionId] ?? [];

            foreach ($sucursales as $sucursalCodigo) {
                $contextos[] = [
                    'conexion' => $conexion,
                    'empresa_id' => $empresaId,
                    'sucursal_codigo' => (string) $sucursalCodigo,
                ];
            }
        }

        return collect($contextos)
            ->unique(fn(array $contexto) => $contexto['conexion'] . '|' . $contexto['empresa_id'] . '|' . $contexto['sucursal_codigo'])
            ->values()
            ->all();
    }

    protected function assertDetallesValidos(array $detalles): void
    {
        if (empty($detalles)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'detalles' => 'No hay facturas para aprobar.',
            ]);
        }

        foreach ($detalles as $detalle) {
            $saldo = (float) ($detalle['saldo_al_crear'] ?? 0);
            $abono = (float) ($detalle['abono_aplicado'] ?? 0);

            if ($abono < 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'abono' => 'El abono no puede ser negativo.',
                ]);
            }

            if ($abono > $saldo) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'abono' => 'El abono no puede superar el saldo al momento de crear la solicitud.',
                ]);
            }
        }
    }

    protected function getSelectedInvoices(): array
    {
        return $this->getFacturasConAbono($this->selectedInvoices);
    }

    protected function getFacturasConAbono(array $selectedKeys = []): array
    {
        $selectedKeys = collect($selectedKeys)->filter();
        $filterSelection = $selectedKeys->isNotEmpty();

        return collect($this->facturasDisponibles)
            ->flatMap(function (array $proveedor) {
                return collect($proveedor['empresas'] ?? [])->flatMap(function (array $empresa) use ($proveedor) {
                    return collect($empresa['sucursales'] ?? [])->flatMap(function (array $sucursal) use ($proveedor, $empresa) {
                        return collect($sucursal['facturas'] ?? [])->map(function (array $factura) use ($proveedor, $empresa, $sucursal) {
                            $abono = $this->resolveAbono($factura);
                            $providerKey = $proveedor['key'] ?? null;

                            return array_merge($factura, [
                                'proveedor_key' => $proveedor['key'] ?? null,
                                'proveedor_codigo' => $proveedor['proveedor_codigo'] ?? null,
                                'proveedor_nombre' => $proveedor['proveedor_nombre'] ?? null,
                                'proveedor_ruc' => $proveedor['proveedor_ruc'] ?? null,
                                'proveedor_actividad' => $proveedor['proveedor_actividad'] ?? null,
                                'empresa_codigo' => $empresa['empresa_codigo'] ?? null,
                                'empresa_nombre' => $empresa['empresa_nombre'] ?? null,
                                'sucursal_codigo' => $sucursal['sucursal_codigo'] ?? null,
                                'sucursal_nombre' => $sucursal['sucursal_nombre'] ?? null,
                                'tipo' => $factura['tipo'] ?? null,
                                'abono' => $abono,
                                'saldo_pendiente' => max(0, (float) ($factura['saldo'] ?? 0) - $abono),
                                'descripcion' => $this->providerDescriptions[$providerKey] ?? '',
                            ]);
                        });
                    });
                });
            })
            ->when($filterSelection, fn($collection) => $collection->filter(fn(array $factura) => $selectedKeys->contains($factura['key'] ?? null)))
            ->values()
            ->all();
    }

    public function updatedSelectedInvoices(): void
    {
        $this->recalcSelectionAndBudgets();
    }

    public function toggleEmpresaSelection(string $providerKey, string $empresaKey): void
    {
        $this->toggleFacturasSelection($this->getFacturaKeysByEmpresa($providerKey, $empresaKey));
    }

    public function toggleSucursalSelection(string $providerKey, string $empresaKey, string $sucursalKey): void
    {
        $this->toggleFacturasSelection($this->getFacturaKeysBySucursal($providerKey, $empresaKey, $sucursalKey));
    }

    public function empresaHasAllSelected(string $providerKey, string $empresaKey): bool
    {
        $facturaKeys = $this->getFacturaKeysByEmpresa($providerKey, $empresaKey);

        if (empty($facturaKeys)) {
            return false;
        }

        $selected = collect($this->selectedInvoices);

        return collect($facturaKeys)->every(fn($key) => $selected->contains($key));
    }

    public function empresaHasAnySelected(string $providerKey, string $empresaKey): bool
    {
        $facturaKeys = $this->getFacturaKeysByEmpresa($providerKey, $empresaKey);
        $selected = collect($this->selectedInvoices);

        return collect($facturaKeys)->contains(fn($key) => $selected->contains($key));
    }

    public function sucursalHasAllSelected(string $providerKey, string $empresaKey, string $sucursalKey): bool
    {
        $facturaKeys = $this->getFacturaKeysBySucursal($providerKey, $empresaKey, $sucursalKey);

        if (empty($facturaKeys)) {
            return false;
        }

        $selected = collect($this->selectedInvoices);

        return collect($facturaKeys)->every(fn($key) => $selected->contains($key));
    }

    public function sucursalHasAnySelected(string $providerKey, string $empresaKey, string $sucursalKey): bool
    {
        $facturaKeys = $this->getFacturaKeysBySucursal($providerKey, $empresaKey, $sucursalKey);
        $selected = collect($this->selectedInvoices);

        return collect($facturaKeys)->contains(fn($key) => $selected->contains($key));
    }

    protected function toggleFacturasSelection(array $facturaKeys): void
    {
        $facturaKeys = array_values(array_filter($facturaKeys));

        if (empty($facturaKeys)) {
            return;
        }

        $selected = collect($this->selectedInvoices);
        $allSelected = collect($facturaKeys)->every(fn($key) => $selected->contains($key));

        if ($allSelected) {
            $this->selectedInvoices = $selected
                ->reject(fn($key) => in_array($key, $facturaKeys, true))
                ->values()
                ->all();
        } else {
            if ($this->presupuestoDisponible <= 0) {
                return;
            }
            $this->selectedInvoices = $selected
                ->merge($facturaKeys)
                ->unique()
                ->values()
                ->all();
        }
$this->dispatch('refresh-resumen');
        $this->recalcSelectionAndBudgets();
    }

    public function updatedInvoiceAbonos($value, string $key): void
    {
        $factura = $this->findFacturaByKey($key);
        $saldoFactura = (float) ($factura['saldo'] ?? 0);

        // Normaliza: permite "1,23" y strings vacíos mientras escribe
        if ($value === '' || $value === null) {
            $this->invoiceAbonos[$key] = 0;

            $proveedorKey = $this->findProveedorKeyByFacturaKey($key);

            if ($proveedorKey) {
                $this->syncProviderAbonoFromInvoices($proveedorKey);
            }

            return;
        }

        $raw = is_string($value) ? str_replace(',', '.', $value) : $value;
        $numero = (float) $raw;

        $ajustado = $this->resolveAbonoPermitido($key, $numero, $saldoFactura);

        // Redondeo para evitar números raros por float
        $this->invoiceAbonos[$key] = round($ajustado, 2);

        $proveedorKey = $this->findProveedorKeyByFacturaKey($key);

        if ($proveedorKey) {
            $this->syncProviderAbonoFromInvoices($proveedorKey);
        }

        if ($ajustado > 0 && ! in_array($key, $this->selectedInvoices)) {
            $this->selectedInvoices[] = $key;
        }
    }

    public function updatedProviderAbonos($value, string $providerKey): void
    {
        $target = $value === '' || $value === null
            ? 0
            : (float) str_replace(',', '.', (string) $value);

        $maximo = $this->resolveMaximoProveedor($providerKey);
        $ajustado = min(max(0, $target), $maximo);
        $ajustado = round($ajustado, 2);

        $this->providerAbonos[$providerKey] = $ajustado;

        $this->distributeProviderAbono($providerKey, $ajustado);

        $this->filters['monto_estimado'] = $this->montoEsperado;
    }

    protected function resolveAbono(array $factura): float
    {
        $key = $factura['key'] ?? null;
        if (! $key) {
            return 0;
        }

        $saldoFactura = (float) ($factura['saldo'] ?? 0);

        $ingresado = (float) ($this->invoiceAbonos[$key] ?? 0);

        return $this->resolveAbonoPermitido($key, $ingresado, $saldoFactura);
    }

    protected function resolveAbonoPermitido(string $key, float $valorIngresado, float $saldoFactura): float
    {
        $ingresado = max(0, $valorIngresado);

        $totalSinEsta = collect($this->invoiceAbonos)
            ->except($key)
            ->sum(fn($v) => max(0, (float) $v));

        $montoAprobado = $this->montoAprobadoValue();   // ✅ aquí también
        $disponible = max(0, $montoAprobado - $totalSinEsta);

        $maxPermitido = min($saldoFactura, $disponible);

        $abonoFinal = min($ingresado, $maxPermitido);
        $abonoFinal = round($abonoFinal, 2);

        $this->invoiceAbonos[$key] = $abonoFinal;

        return $abonoFinal;
    }

    public function resolveMaximoProveedor(string $providerKey): float
    {
        $facturasProveedor = $this->getProveedorFacturas($providerKey);
        $facturaKeys = collect($facturasProveedor)->pluck('key');

        $totalProveedor = collect($facturasProveedor)->sum(fn(array $factura) => (float) ($factura['saldo'] ?? 0));

        $otrosAbonos = collect($this->invoiceAbonos)
            ->reject(fn($_, $facturaKey) => $facturaKeys->contains($facturaKey))
            ->sum(fn($v) => max(0, (float) $v));

        $disponible = max(0, $this->montoAprobadoValue() - $otrosAbonos);

        return min($totalProveedor, $disponible);
    }

    protected function ensureReportSelection(): ?array
    {
        $selected = $this->getSelectedInvoices();

        if (empty($selected)) {
            Notification::make()
                ->title('Seleccione al menos una factura')
                ->warning()
                ->send();

            return null;
        }

        return $selected;
    }

    protected function isCompraFactura(array $factura): bool
    {
        if (($factura['tipo'] ?? null) === 'compra') {
            return true;
        }

        $numero = (string) ($factura['numero'] ?? '');

        return str_starts_with($numero, 'COMPRA-');
    }

    protected function splitSelectedFacturas(): array
    {
        $facturas = [];
        $compras = [];

        foreach ($this->getSelectedInvoices() as $factura) {
            if ($this->isCompraFactura($factura)) {
                $compras[] = $factura;
            } else {
                $facturas[] = $factura;
            }
        }

        return [$facturas, $compras];
    }

    protected function buildComprasReportRows(array $compras): array
    {
        return collect($compras)
            ->groupBy(fn(array $factura) => ($factura['conexion_id'] ?? '') . '|' . ($factura['empresa_codigo'] ?? ''))
            ->map(function ($grupo) {
                $first = $grupo->first();
                $rows = $grupo->map(function (array $factura) {
                    $valor = (float) ($factura['total'] ?? $factura['monto'] ?? $factura['saldo'] ?? 0);
                    $abono = (float) ($factura['abono'] ?? 0);
                    $saldo = (float) ($factura['saldo_pendiente'] ?? max(0, ($factura['saldo'] ?? $valor) - $abono));

                    return [
                        'descripcion' => $factura['descripcion'] ?? '',
                        'numero' => $factura['numero'] ?? '',
                        'valor' => $valor,
                        'abono' => $abono,
                        'saldo' => $saldo,
                    ];
                })->values();

                $totales = [
                    'valor' => $rows->sum('valor'),
                    'abono' => $rows->sum('abono'),
                    'saldo' => $rows->sum('saldo'),
                ];

                return [
                    'conexion_nombre' => $first['conexion_nombre'] ?? '',
                    'empresa_nombre' => $first['empresa_nombre'] ?? ($first['empresa_codigo'] ?? 'N/D'),
                    'compras' => $rows->all(),
                    'totales' => $totales,
                ];
            })
            ->values()
            ->all();
    }

    protected function buildResumenPorEmpresaDesdeFacturas(array $facturas): array
    {
        return collect($facturas)
            ->groupBy(fn(array $factura) => $factura['empresa_nombre'] ?? $factura['empresa_codigo'] ?? 'N/D')
            ->map(function ($grupo, $empresa) {
                $valor = 0;
                $abono = 0;
                $saldo = 0;

                foreach ($grupo as $factura) {
                    $valorFactura = (float) ($factura['total'] ?? $factura['monto'] ?? $factura['saldo'] ?? 0);
                    $abonoFactura = (float) ($factura['abono'] ?? 0);
                    $saldoFactura = (float) ($factura['saldo_pendiente'] ?? max(0, ($factura['saldo'] ?? $valorFactura) - $abonoFactura));

                    $valor += $valorFactura;
                    $abono += $abonoFactura;
                    $saldo += $saldoFactura;
                }

                return [
                    'empresa' => $empresa,
                    'valor' => $valor,
                    'abono' => $abono,
                    'saldo' => $saldo,
                ];
            })
            ->values()
            ->all();
    }

    protected function buildTotalesDesdeFacturas(array $facturas): array
    {
        $valor = 0;
        $abono = 0;
        $saldo = 0;

        foreach ($facturas as $factura) {
            $valorFactura = (float) ($factura['total'] ?? $factura['monto'] ?? $factura['saldo'] ?? 0);
            $abonoFactura = (float) ($factura['abono'] ?? 0);
            $saldoFactura = (float) ($factura['saldo_pendiente'] ?? max(0, ($factura['saldo'] ?? $valorFactura) - $abonoFactura));

            $valor += $valorFactura;
            $abono += $abonoFactura;
            $saldo += $saldoFactura;
        }

        return [
            'valor' => $valor,
            'abono' => $abono,
            'saldo' => $saldo,
        ];
    }

    protected function buildReportRows(array $facturas): array
    {
        $proveedores = $this->getSelectedProvidersWithMetadata($facturas);

        return collect($proveedores)
            ->map(fn(array $proveedor) => [
                'proveedor' => $proveedor['proveedor_nombre'] ?? $proveedor['proveedor_codigo'] ?? '',
                'descripcion' => $proveedor['descripcion'] ?? '',
                'valor' => (float) ($proveedor['totales']['valor'] ?? 0),
                'abono' => (float) ($proveedor['totales']['abono'] ?? 0),
                'saldo' => (float) ($proveedor['totales']['saldo'] ?? 0),
            ])
            ->values()
            ->all();
    }

    protected function getSelectedProvidersWithMetadata(?array $facturas = null): array
    {
        $selected = $facturas ?? $this->getSelectedInvoices();
        $proveedores = [];

        foreach ($selected as $factura) {
            $providerKey = $factura['proveedor_key'] ?? null;

            if (! $providerKey) {
                continue;
            }

            $valor = (float) ($factura['total'] ?? $factura['monto'] ?? $factura['saldo'] ?? 0);
            $abono = (float) ($factura['abono'] ?? 0);
            $saldo = (float) ($factura['saldo_pendiente'] ?? max(0, ($factura['saldo'] ?? $valor) - $abono));
            if (! isset($proveedores[$providerKey])) {
                $proveedores[$providerKey] = [
                    'key' => $providerKey,
                    'proveedor_codigo' => $factura['proveedor_codigo'] ?? null,
                    'proveedor_nombre' => $factura['proveedor_nombre'] ?? null,
                    'proveedor_ruc' => $factura['proveedor_ruc'] ?? null,
                    'proveedor_actividad' => $factura['proveedor_actividad'] ?? null,
                    'descripcion' => $factura['descripcion'] ?? '',
                    'totales' => [
                        'valor' => 0,
                        'abono' => 0,
                        'saldo' => 0,
                    ],
                    'empresas' => [],
                ];
            }

            $empresaKey = ($factura['conexion_id'] ?? '') . '|' . ($factura['empresa_codigo'] ?? '');
            $sucursalKey = $empresaKey . '|' . ($factura['sucursal_codigo'] ?? '');

            if (! isset($proveedores[$providerKey]['empresas'][$empresaKey])) {
                $proveedores[$providerKey]['empresas'][$empresaKey] = [
                    'conexion_id' => $factura['conexion_id'] ?? null,
                    'conexion_nombre' => $factura['conexion_nombre'] ?? '',
                    'empresa_codigo' => $factura['empresa_codigo'] ?? null,
                    'empresa_nombre' => $factura['empresa_nombre'] ?? null,
                    'totales' => [
                        'valor' => 0,
                        'abono' => 0,
                        'saldo' => 0,
                    ],
                    'sucursales' => [],
                ];
            }

            if (! isset($proveedores[$providerKey]['empresas'][$empresaKey]['sucursales'][$sucursalKey])) {
                $proveedores[$providerKey]['empresas'][$empresaKey]['sucursales'][$sucursalKey] = [
                    'sucursal_codigo' => $factura['sucursal_codigo'] ?? null,
                    'sucursal_nombre' => $factura['sucursal_nombre'] ?? null,
                    'totales' => [
                        'valor' => 0,
                        'abono' => 0,
                        'saldo' => 0,
                    ],
                    'facturas' => [],
                ];
            }

            $proveedores[$providerKey]['empresas'][$empresaKey]['sucursales'][$sucursalKey]['facturas'][] = [
                'numero' => $factura['numero'] ?? '',
                'fecha_emision' => $factura['fecha_emision'] ?? '',
                'fecha_vencimiento' => $factura['fecha_vencimiento'] ?? '',
                'valor' => $valor,
                'abono' => $abono,
                'saldo' => $saldo,
                'sucursal_nombre' => $factura['sucursal_nombre'] ?? '',
            ];

            $proveedores[$providerKey]['totales']['valor'] += $valor;
            $proveedores[$providerKey]['totales']['abono'] += $abono;
            $proveedores[$providerKey]['totales']['saldo'] += $saldo;

            $proveedores[$providerKey]['empresas'][$empresaKey]['totales']['valor'] += $valor;
            $proveedores[$providerKey]['empresas'][$empresaKey]['totales']['abono'] += $abono;
            $proveedores[$providerKey]['empresas'][$empresaKey]['totales']['saldo'] += $saldo;

            $proveedores[$providerKey]['empresas'][$empresaKey]['sucursales'][$sucursalKey]['totales']['valor'] += $valor;
            $proveedores[$providerKey]['empresas'][$empresaKey]['sucursales'][$sucursalKey]['totales']['abono'] += $abono;
            $proveedores[$providerKey]['empresas'][$empresaKey]['sucursales'][$sucursalKey]['totales']['saldo'] += $saldo;
        }

        foreach ($proveedores as &$proveedor) {
            foreach ($proveedor['empresas'] as &$empresa) {
                foreach ($empresa['sucursales'] as &$sucursal) {
                    $sucursal['facturas'] = collect($sucursal['facturas'])->values()->all();
                }
                unset($sucursal);

                $empresa['sucursales'] = array_values($empresa['sucursales']);
            }
            unset($empresa);

            $proveedor['empresas'] = array_values($proveedor['empresas']);
        }
        unset($proveedor);

        return array_values($proveedores);
    }

    protected function buildEmpresasParaReportes(array $proveedores): array
    {
        $empresas = [];

        foreach ($proveedores as $proveedor) {
            foreach ($proveedor['empresas'] ?? [] as $empresa) {
                $empresaKey = ($empresa['conexion_id'] ?? '') . '|' . ($empresa['empresa_codigo'] ?? '');

                if (! isset($empresas[$empresaKey])) {
                    $empresas[$empresaKey] = [
                        'conexion_nombre' => $empresa['conexion_nombre'] ?? '',
                        'empresa_codigo' => $empresa['empresa_codigo'] ?? '',
                        'empresa_nombre' => $empresa['empresa_nombre'] ?? ($empresa['empresa_codigo'] ?? ''),
                        'proveedores' => [],
                        'totales' => [
                            'valor' => 0,
                            'abono' => 0,
                            'saldo' => 0,
                        ],
                    ];
                }

                $empresaData = [
                    'nombre' => $proveedor['proveedor_nombre'] ?? $proveedor['proveedor_codigo'],
                    'ruc' => $proveedor['proveedor_ruc'] ?? '',
                    'descripcion' => $proveedor['descripcion'] ?? '',
                    'totales' => $empresa['totales'] ?? ['valor' => 0, 'abono' => 0, 'saldo' => 0],
                    'sucursales' => $empresa['sucursales'] ?? [],
                ];

                $empresas[$empresaKey]['proveedores'][] = $empresaData;
                $empresas[$empresaKey]['totales']['valor'] += (float) ($empresa['totales']['valor'] ?? 0);
                $empresas[$empresaKey]['totales']['abono'] += (float) ($empresa['totales']['abono'] ?? 0);
                $empresas[$empresaKey]['totales']['saldo'] += (float) ($empresa['totales']['saldo'] ?? 0);
            }
        }

        return array_values($empresas);
    }

    protected function buildResumenPorEmpresa(array $empresas): array
    {
        return collect($empresas)
            ->map(fn(array $empresa) => [
                'empresa' => $empresa['empresa_nombre'] ?? $empresa['empresa_codigo'],
                'valor' => (float) ($empresa['totales']['valor'] ?? 0),
                'abono' => (float) ($empresa['totales']['abono'] ?? 0),
                'saldo' => (float) ($empresa['totales']['saldo'] ?? 0),
            ])
            ->all();
    }

    protected function buildTotalesGenerales(array $proveedores): array
    {
        return [
            'valor' => collect($proveedores)->sum(fn($p) => (float) ($p['totales']['valor'] ?? 0)),
            'abono' => collect($proveedores)->sum(fn($p) => (float) ($p['totales']['abono'] ?? 0)),
            'saldo' => collect($proveedores)->sum(fn($p) => (float) ($p['totales']['saldo'] ?? 0)),
        ];
    }

    protected function getReportDescriptionForm(): array
    {
        return [
            TextInput::make('descripcion_reporte')
                ->label('Descripción del reporte')
                ->placeholder('Presupuesto del 15 de enero al 18 de mayo')
                ->required()
                ->maxLength(255)
                ->default($this->filters['motivo'] ?? ''),
        ];
    }

    public function exportPdf(string $descripcionReporte = null)
    {
        $selected = $this->ensureReportSelection();

        if ($selected === null) {
            return null;
        }

        [$facturas, $compras] = $this->splitSelectedFacturas();

        $proveedores = $this->getSelectedProvidersWithMetadata($facturas);
        $empresas = $this->buildEmpresasParaReportes($proveedores);
        $resumen = $this->buildResumenPorEmpresaDesdeFacturas(array_merge($facturas, $compras));
        $totales = $this->buildTotalesDesdeFacturas(array_merge($facturas, $compras));
        $comprasReport = $this->buildComprasReportRows($compras);

        $descripcion = $descripcionReporte !== null && trim($descripcionReporte) !== ''
            ? $descripcionReporte
            : ($this->filters['motivo'] ?? 'Solicitud de pago de facturas');

        return response()->streamDownload(function () use ($empresas, $resumen, $totales, $descripcion, $comprasReport) {
            echo Pdf::loadView('pdfs.solicitud-pago-facturas-general', [
                'empresas' => $empresas,
                'resumenEmpresas' => $resumen,
                'usuario' => Auth::user()?->name,
                'totales' => $totales,
                'descripcionReporte' => $descripcion,
                'compras' => $comprasReport,
            ])->setPaper('a4', 'landscape')->stream();
        }, 'solicitud-pago-facturas.pdf');
    }

    public function exportExcel()
    {
        $selected = $this->ensureReportSelection();

        if ($selected === null) {
            return null;
        }

        $proveedores = $this->getSelectedProvidersWithMetadata();
        $empresas = $this->buildEmpresasParaReportes($proveedores);

        $rows = collect($empresas)
            ->flatMap(function (array $empresa) {
                return collect($empresa['proveedores'] ?? [])->map(function (array $proveedor) use ($empresa) {
                    return [
                        'Conexion' => $empresa['conexion_nombre'] ?? '',
                        'Empresa' => $empresa['empresa_nombre'] ?? $empresa['empresa_codigo'],
                        'Proveedor' => $proveedor['nombre'] ?? '',
                        'RUC' => $proveedor['ruc'] ?? '',
                        'Descripcion' => $proveedor['descripcion'] ?? '',
                        'Valor' => number_format((float) ($proveedor['totales']['valor'] ?? 0), 2, '.', ''),
                        'Abono' => number_format((float) ($proveedor['totales']['abono'] ?? 0), 2, '.', ''),
                        'Saldo pendiente' => number_format((float) ($proveedor['totales']['saldo'] ?? 0), 2, '.', ''),
                    ];
                });
            })
            ->values()
            ->all();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, array_keys($rows[0] ?? [
                'Conexion' => 'Conexion',
                'Empresa' => 'Empresa',
                'Proveedor' => 'Proveedor',
                'RUC' => 'RUC',
                'Descripcion' => 'Descripcion',
                'Valor' => 'Valor',
                'Abono' => 'Abono',
                'Saldo pendiente' => 'Saldo pendiente',
            ]));

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'solicitud-pago-facturas.csv');
    }

    public function exportDetailedPdf(string $descripcionReporte = null)
    {
        $selected = $this->ensureReportSelection();

        if ($selected === null) {
            return null;
        }

        [$facturas, $compras] = $this->splitSelectedFacturas();

        $proveedores = $this->getSelectedProvidersWithMetadata($facturas);
        $empresas = $this->buildEmpresasParaReportes($proveedores);
        $totales = $this->buildTotalesDesdeFacturas(array_merge($facturas, $compras));
        $comprasReport = $this->buildComprasReportRows($compras);

        $descripcion = $descripcionReporte !== null && trim($descripcionReporte) !== ''
            ? $descripcionReporte
            : ($this->filters['motivo'] ?? 'Solicitud de pago de facturas');

        return response()->streamDownload(function () use ($empresas, $totales, $descripcion, $comprasReport) {
            echo Pdf::loadView('pdfs.solicitud-pago-facturas-detallado', [
                'empresas' => $empresas,
                'totales' => $totales,
                'usuario' => Auth::user()?->name,
                'descripcionReporte' => $descripcion,
                'compras' => $comprasReport,
            ])->setPaper('a4', 'landscape')->stream();
        }, 'solicitud-pago-facturas-detallado.pdf');
    }

    public function exportDetailedExcel()
    {
        $selected = $this->ensureReportSelection();

        if ($selected === null) {
            return null;
        }

        $proveedores = $this->getSelectedProvidersWithMetadata();
        $empresas = $this->buildEmpresasParaReportes($proveedores);

        $rows = collect($empresas)
            ->flatMap(function (array $empresa) {
                return collect($empresa['proveedores'] ?? [])->flatMap(function (array $proveedor) use ($empresa) {
                    return collect($proveedor['sucursales'] ?? [])->flatMap(function (array $sucursal) use ($empresa, $proveedor) {
                        return collect($sucursal['facturas'] ?? [])->map(function (array $factura) use ($empresa, $proveedor, $sucursal) {
                            return [
                                'Conexion' => $empresa['conexion_nombre'] ?? '',
                                'Empresa' => $empresa['empresa_nombre'] ?? $empresa['empresa_codigo'],
                                'Sucursal' => $sucursal['sucursal_nombre'] ?? $sucursal['sucursal_codigo'],
                                'Proveedor' => $proveedor['nombre'] ?? '',
                                'RUC' => $proveedor['ruc'] ?? '',
                                'Descripcion' => $proveedor['descripcion'] ?? '',
                                'Factura' => $factura['numero'] ?? '',
                                'Fecha Emision' => $factura['fecha_emision'] ?? '',
                                'Fecha Vencimiento' => $factura['fecha_vencimiento'] ?? '',
                                'Valor' => number_format((float) ($factura['valor'] ?? 0), 2, '.', ''),
                                'Abono' => number_format((float) ($factura['abono'] ?? 0), 2, '.', ''),
                                'Saldo pendiente' => number_format((float) ($factura['saldo'] ?? 0), 2, '.', ''),
                            ];
                        });
                    });
                });
            })
            ->values()
            ->all();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, array_keys($rows[0] ?? [
                'Conexion' => 'Conexion',
                'Empresa' => 'Empresa',
                'Sucursal' => 'Sucursal',
                'Proveedor' => 'Proveedor',
                'RUC' => 'RUC',
                'Descripcion' => 'Descripcion',
                'Factura' => 'Factura',
                'Fecha Emision' => 'Fecha Emision',
                'Fecha Vencimiento' => 'Fecha Vencimiento',
                'Valor' => 'Valor',
                'Abono' => 'Abono',
                'Saldo pendiente' => 'Saldo pendiente',
            ]));

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'solicitud-pago-facturas-detallado.csv');
    }

    protected function recalcSelectionAndBudgets(): void
    {
        // 1) Normalizar selected
        $selectedKeys = collect($this->selectedInvoices)->filter()->unique()->values();

        // 2) Mantener invoiceAbonos SOLO de seleccionadas (pero sin borrar con unset en otras partes)
        $this->invoiceAbonos = array_intersect_key($this->invoiceAbonos, array_flip($selectedKeys->all()));

        // 3) Recalcular asignación respetando aprobado y saldo (orden determinista)
        $aprobado = $this->montoAprobadoValue();
        $totalAsignado = 0.0;

        // Orden determinista: por fecha emision asc (o por el orden original), NO por “como venga”
        $orderedKeys = $selectedKeys->sort()->values(); // mínimo: sort() estable por string
        // Ideal: ordenar por fecha emision consultando factura (más abajo te dejo mejora)

        foreach ($orderedKeys as $key) {
            $factura = $this->findFacturaByKey($key);
            $saldo = (float) ($factura['saldo'] ?? 0);

            $deseado = max(0, (float) ($this->invoiceAbonos[$key] ?? 0));
            if ($deseado <= 0) {
                $deseado = $saldo; // comportamiento actual: si está seleccionada y no tiene abono, intenta cubrir saldo
            }

            $disponible = max(0, $aprobado - $totalAsignado);
            $asignado = round(min($deseado, $saldo, $disponible), 2);

            $this->invoiceAbonos[$key] = $asignado;
            $totalAsignado += $asignado;
        }

        // 4) Meter resumen
        $this->syncAllProviderAbonosFromInvoices();
        $this->filters['monto_estimado'] = $this->montoEsperado;
    }


    protected function distributeProviderAbono(string $providerKey, float $monto): void
    {
        $facturas = $this->getProveedorFacturas($providerKey);

        if (empty($facturas)) {
            $this->providerAbonos[$providerKey] = 0;

            return;
        }

        $facturasKeys = collect($facturas)->pluck('key');

        // Reinicia los abonos actuales del proveedor para recalcular
        foreach ($facturasKeys as $facturaKey) {
            $this->invoiceAbonos[$facturaKey] = 0.0;
        }

        $otrosAbonos = collect($this->invoiceAbonos)
            ->sum(fn($v) => max(0, (float) $v));

        $presupuestoDisponible = max(0, $this->montoAprobadoValue() - $otrosAbonos);
        $montoDistribuible = min($monto, $presupuestoDisponible);
        $restante = $montoDistribuible;

        foreach ($facturas as $factura) {
            $facturaKey = $factura['key'] ?? null;
            if (! $facturaKey) {
                continue;
            }

            $saldo = (float) ($factura['saldo'] ?? 0);
            $asignado = min($saldo, $restante);
            $asignado = round($asignado, 2);

            $this->invoiceAbonos[$facturaKey] = $asignado;

            if ($asignado > 0 && ! in_array($facturaKey, $this->selectedInvoices)) {
                $this->selectedInvoices[] = $facturaKey;
            }

            $restante -= $asignado;

            if ($restante <= 0) {
                break;
            }
        }

        $this->providerAbonos[$providerKey] = $montoDistribuible - max(0, $restante);
        $this->selectedInvoices = collect($this->selectedInvoices)
            ->filter(fn($k) => (float)($this->invoiceAbonos[$k] ?? 0) > 0)
            ->values()
            ->all();


        $this->filters['monto_estimado'] = $this->montoEsperado;
    }


    protected function findFacturaByKey(string $key): array
    {
        return collect($this->facturasDisponibles)
            ->flatMap(fn(array $proveedor) => collect($proveedor['empresas'] ?? [])->flatMap(fn(array $empresa) => collect($empresa['sucursales'] ?? [])->flatMap(fn(array $sucursal) => collect($sucursal['facturas'] ?? []))))
            ->firstWhere('key', $key) ?? [];
    }

    protected function findProveedorByKey(string $providerKey): ?array
    {
        return collect($this->facturasDisponibles)->firstWhere('key', $providerKey);
    }

    protected function getProveedorFacturas(string $providerKey): array
    {
        $proveedor = $this->findProveedorByKey($providerKey);

        if (! $proveedor) {
            return [];
        }

        return collect($proveedor['empresas'] ?? [])
            ->flatMap(fn(array $empresa) => collect($empresa['sucursales'] ?? [])->flatMap(fn(array $sucursal) => collect($sucursal['facturas'] ?? [])))
            ->values()
            ->all();
    }

    protected function getFacturaKeysByEmpresa(string $providerKey, string $empresaKey): array
    {
        $proveedor = $this->findProveedorByKey($providerKey);

        if (! $proveedor) {
            return [];
        }

        $facturaKeys = [];

        foreach ($proveedor['empresas'] ?? [] as $empresa) {
            $currentEmpresaKey = ($empresa['conexion_id'] ?? '') . '|' . ($empresa['empresa_codigo'] ?? '');

            if ($currentEmpresaKey !== $empresaKey) {
                continue;
            }

            foreach ($empresa['sucursales'] ?? [] as $sucursal) {
                foreach ($sucursal['facturas'] ?? [] as $factura) {
                    if (! empty($factura['key'])) {
                        $facturaKeys[] = $factura['key'];
                    }
                }
            }
        }

        return $facturaKeys;
    }

    protected function getFacturaKeysBySucursal(string $providerKey, string $empresaKey, string $sucursalKey): array
    {
        $proveedor = $this->findProveedorByKey($providerKey);

        if (! $proveedor) {
            return [];
        }

        $facturaKeys = [];

        foreach ($proveedor['empresas'] ?? [] as $empresa) {
            $currentEmpresaKey = ($empresa['conexion_id'] ?? '') . '|' . ($empresa['empresa_codigo'] ?? '');

            if ($currentEmpresaKey !== $empresaKey) {
                continue;
            }

            foreach ($empresa['sucursales'] ?? [] as $sucursal) {
                $currentSucursalKey = $currentEmpresaKey . '|' . ($sucursal['sucursal_codigo'] ?? '');

                if ($currentSucursalKey !== $sucursalKey) {
                    continue;
                }

                foreach ($sucursal['facturas'] ?? [] as $factura) {
                    if (! empty($factura['key'])) {
                        $facturaKeys[] = $factura['key'];
                    }
                }
            }
        }

        return $facturaKeys;
    }

    protected function findProveedorKeyByFacturaKey(string $facturaKey): ?string
    {
        foreach ($this->facturasDisponibles as $proveedor) {
            foreach ($proveedor['empresas'] ?? [] as $empresa) {
                foreach ($empresa['sucursales'] ?? [] as $sucursal) {
                    foreach ($sucursal['facturas'] ?? [] as $factura) {
                        if (($factura['key'] ?? null) === $facturaKey) {
                            return $proveedor['key'] ?? null;
                        }
                    }
                }
            }
        }

        return null;
    }

    protected function syncProviderAbonoFromInvoices(string $providerKey): void
    {
        $facturaKeys = collect($this->getProveedorFacturas($providerKey))->pluck('key');

        $total = collect($this->invoiceAbonos)
            ->filter(fn($_, $key) => $facturaKeys->contains($key))
            ->sum(fn($v) => max(0, (float) $v));

        $this->providerAbonos[$providerKey] = round($total, 2);
    }

    protected function syncAllProviderAbonosFromInvoices(): void
    {
        foreach (collect($this->facturasDisponibles)->pluck('key') as $providerKey) {
            $this->syncProviderAbonoFromInvoices($providerKey);
        }
    }

    protected function buildFacturas(array $conexiones, array $empresasSeleccionadas, array $sucursalesSeleccionadas, ?string $fechaDesde, ?string $fechaHasta): array
    {
        $connectionNames = \App\Models\Empresa::query()->pluck('nombre_empresa', 'id');

        $registros = collect();

        foreach ($conexiones as $conexion) {
            $empresas = $empresasSeleccionadas[$conexion] ?? array_keys(SolicitudPagoResource::getEmpresasOptions($conexion));

            if (empty($empresas)) {
                continue;
            }

            $sucursales = $sucursalesSeleccionadas[$conexion] ?? [];
            $registros = $registros->merge($this->fetchInvoices($conexion, $empresas, $sucursales, $fechaDesde, $fechaHasta, $connectionNames[$conexion] ?? ''));
        }

        return $this->groupByProveedor($registros);
    }

    protected function fetchInvoices(int $conexion, array $empresas, array $sucursales, ?string $fechaDesde, ?string $fechaHasta, string $conexionNombre): array
    {
        $connectionName = SolicitudPagoResource::getExternalConnectionName($conexion);

        if (! $connectionName) {
            return [];
        }

        $empresasDisponibles = SolicitudPagoResource::getEmpresasOptions($conexion);
        $sucursalesDisponibles = SolicitudPagoResource::getSucursalesOptions($conexion, $empresas);
        $proveedoresBase = SolicitudPagoResource::getProveedoresBase($conexion, $empresas, $sucursales);

        $query = DB::connection($connectionName)
            ->table('saedmcp')
            ->join('saeclpv as prov', function ($join) {
                $join->on('prov.clpv_cod_empr', '=', 'saedmcp.dmcp_cod_empr')
                    ->on('prov.clpv_cod_sucu', '=', 'saedmcp.dmcp_cod_sucu')
                    ->on('prov.clpv_cod_clpv', '=', 'saedmcp.clpv_cod_clpv');
            })
            ->whereIn('saedmcp.dmcp_cod_empr', $empresas)
            ->when(! empty($sucursales), fn($q) => $q->whereIn('saedmcp.dmcp_cod_sucu', $sucursales))
            ->where('saedmcp.dmcp_est_dcmp', '<>', 'AN')
            ->selectRaw('
                saedmcp.dmcp_cod_empr   as empresa,
                saedmcp.dmcp_cod_sucu   as sucursal,
                saedmcp.clpv_cod_clpv   as proveedor_codigo,
                prov.clpv_nom_clpv      as proveedor_nombre,
                prov.clpv_ruc_clpv      as proveedor_ruc,
                prov.clpv_desc_actividades as proveedor_actividad,
                saedmcp.dmcp_num_fac    as numero_factura,
                MIN(saedmcp.dcmp_fec_emis) as fecha_emision,
                MAX(saedmcp.dmcp_fec_ven)  as fecha_vencimiento,
                ABS(SUM(
                        COALESCE(saedmcp.dcmp_deb_ml, 0)
                        - COALESCE(saedmcp.dcmp_cre_ml, 0)
                    )) as saldo
                 ')
            ->groupBy('saedmcp.dmcp_cod_empr', 'saedmcp.dmcp_cod_sucu', 'saedmcp.clpv_cod_clpv', 'prov.clpv_nom_clpv', 'prov.clpv_ruc_clpv', 'saedmcp.dmcp_num_fac')
            ->havingRaw('SUM(COALESCE(saedmcp.dcmp_deb_ml,0) - COALESCE(saedmcp.dcmp_cre_ml,0)) <> 0');

        if ($fechaDesde && $fechaHasta) {
            $query->whereBetween('saedmcp.dcmp_fec_emis', [$fechaDesde, $fechaHasta]);
        }

        return $query->get()
            ->map(function ($row) use ($conexion, $conexionNombre, $empresasDisponibles, $sucursalesDisponibles, $proveedoresBase) {
                $empresaCodigo = $row->empresa;
                $sucursalCodigo = $row->sucursal;

                return [
                    'conexion_id' => $conexion,
                    'conexion_nombre' => $conexionNombre,
                    'empresa_codigo' => $empresaCodigo,
                    'empresa_nombre' => $empresasDisponibles[$empresaCodigo] ?? $empresaCodigo,
                    'sucursal_codigo' => $sucursalCodigo,
                    'sucursal_nombre' => $sucursalesDisponibles[$sucursalCodigo] ?? $sucursalCodigo,
                    'proveedor_codigo' => $row->proveedor_codigo,
                    'proveedor_nombre' => $row->proveedor_nombre ?? ($proveedoresBase[$empresaCodigo . '|' . $sucursalCodigo . '|' . $row->proveedor_codigo]['nombre'] ?? $row->proveedor_codigo),
                    'proveedor_ruc' => $row->proveedor_ruc,
                    'proveedor_actividad' => $row->proveedor_actividad,
                    'numero' => $row->numero_factura,
                    'fecha_emision' => $row->fecha_emision,
                    'fecha_vencimiento' => $row->fecha_vencimiento,
                    'total' => abs((float) $row->saldo),
                    'saldo' => abs((float) $row->saldo),
                ];
            })
            ->all();
    }

    protected function groupByProveedor($registros): array
    {
        $agrupado = [];

        foreach ($registros as $row) {
            $proveedorKey = $this->buildProveedorKey($row['proveedor_codigo'] ?? '', $row['proveedor_ruc'] ?? '', $row['proveedor_nombre'] ?? '');
            $empresaKey = ($row['conexion_id'] ?? '') . '|' . ($row['empresa_codigo'] ?? '');
            $sucursalKey = $empresaKey . '|' . ($row['sucursal_codigo'] ?? '');
            $esCompra = ($row['tipo'] ?? null) === 'compra';

            if (! isset($agrupado[$proveedorKey])) {
                $agrupado[$proveedorKey] = [
                    'key' => $proveedorKey,
                    'proveedor_codigo' => $row['proveedor_codigo'] ?? null,
                    'proveedor_nombre' => $row['proveedor_nombre'] ?? null,
                    'proveedor_ruc' => $row['proveedor_ruc'] ?? null,
                    'proveedor_actividad' => $row['proveedor_actividad'] ?? null,
                    'total' => 0,
                    'facturas_count' => 0,
                    'es_compra' => $esCompra,
                    'empresas' => [],
                ];
            } elseif ($esCompra) {
                $agrupado[$proveedorKey]['es_compra'] = true;
            }

            if (! isset($agrupado[$proveedorKey]['empresas'][$empresaKey])) {
                $agrupado[$proveedorKey]['empresas'][$empresaKey] = [
                    'conexion_id' => $row['conexion_id'] ?? null,
                    'conexion_nombre' => $row['conexion_nombre'] ?? null,
                    'empresa_codigo' => $row['empresa_codigo'] ?? null,
                    'empresa_nombre' => $row['empresa_nombre'] ?? null,
                    'sucursales' => [],
                ];
            }

            if (! isset($agrupado[$proveedorKey]['empresas'][$empresaKey]['sucursales'][$sucursalKey])) {
                $agrupado[$proveedorKey]['empresas'][$empresaKey]['sucursales'][$sucursalKey] = [
                    'sucursal_codigo' => $row['sucursal_codigo'] ?? null,
                    'sucursal_nombre' => $row['sucursal_nombre'] ?? null,
                    'facturas' => [],
                ];
            }

            $facturaKey = $this->buildFacturaKey(
                $row['conexion_id'] ?? null,
                $row['empresa_codigo'] ?? null,
                $row['sucursal_codigo'] ?? null,
                $row['proveedor_codigo'] ?? null,
                $row['numero'] ?? null,
                $row['proveedor_ruc'] ?? null
            );

            $agrupado[$proveedorKey]['empresas'][$empresaKey]['sucursales'][$sucursalKey]['facturas'][] = [
                'key' => $facturaKey,
                'numero' => $row['numero'] ?? '',
                'fecha_emision' => $row['fecha_emision'] ?? null,
                'fecha_vencimiento' => $row['fecha_vencimiento'] ?? null,
                'saldo' => (float) ($row['saldo'] ?? 0),
                'total' => (float) ($row['total'] ?? $row['saldo'] ?? 0),
                'empresa_codigo' => $row['empresa_codigo'] ?? null,
                'empresa_nombre' => $row['empresa_nombre'] ?? null,
                'sucursal_codigo' => $row['sucursal_codigo'] ?? null,
                'sucursal_nombre' => $row['sucursal_nombre'] ?? null,
                'conexion_id' => $row['conexion_id'] ?? null,
                'conexion_nombre' => $row['conexion_nombre'] ?? null,
                'tipo' => $row['tipo'] ?? null,
            ];

            $agrupado[$proveedorKey]['total'] += (float) ($row['saldo'] ?? 0);
            $agrupado[$proveedorKey]['facturas_count']++;
        }

        foreach ($agrupado as &$proveedor) {
            foreach ($proveedor['empresas'] as &$empresa) {
                foreach ($empresa['sucursales'] as &$sucursal) {
                    $sucursal['facturas'] = collect($sucursal['facturas'])
                        ->sortBy('fecha_emision')
                        ->values()
                        ->all();
                }
                unset($sucursal);
                $empresa['sucursales'] = array_values($empresa['sucursales']);
            }
            unset($empresa);
            $proveedor['empresas'] = array_values($proveedor['empresas']);
        }
        unset($proveedor);

        $proveedores = collect($agrupado)
            ->sortBy('proveedor_nombre')
            ->values();

        return $proveedores
            ->values()
            ->all();
    }

    protected function syncProviderMetadata(array $proveedores): void
    {
        $existingDescriptions = $this->providerDescriptions;
        $this->providerDescriptions = [];

        foreach ($proveedores as $proveedor) {
            $key = $proveedor['key'];
            $this->providerDescriptions[$key] = $existingDescriptions[$key]
                ?? ($proveedor['proveedor_actividad'] ?? ($proveedor['proveedor_nombre'] ?? ''));
        }
    }

    protected function applySearch($proveedores)
    {
        $termino = trim((string) ($this->search !== '' ? $this->search : ($this->filters['search'] ?? '')));

        if ($termino === '') {
            return $proveedores;
        }

        $termino = mb_strtolower($termino);

        return collect($proveedores)->filter(function (array $proveedor) use ($termino) {
            $matchesProveedor = str_contains(mb_strtolower($proveedor['proveedor_nombre'] ?? ''), $termino)
                || str_contains(mb_strtolower($proveedor['proveedor_codigo'] ?? ''), $termino)
                || str_contains(mb_strtolower($proveedor['proveedor_ruc'] ?? ''), $termino);

            if ($matchesProveedor) {
                return true;
            }

            foreach ($proveedor['empresas'] ?? [] as $empresa) {
                foreach ($empresa['sucursales'] ?? [] as $sucursal) {
                    foreach ($sucursal['facturas'] ?? [] as $factura) {
                        if (str_contains(mb_strtolower((string) ($factura['numero'] ?? '')), $termino)) {
                            return true;
                        }
                    }
                }
            }

            return false;
        });
    }

    public function getProvidersPaginatedProperty(): LengthAwarePaginator
    {
        $filtrados = $this->applySearch($this->facturasDisponibles);
        $proveedores = $this->applySort(collect($filtrados))->values();
        $page = $this->getPage();
        $items = $proveedores->forPage($page, $this->perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $proveedores->count(),
            $this->perPage,
            $page
        );
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    protected function applySort($proveedores)
    {
        if (! $this->sortField) {
            return collect($proveedores);
        }

        return collect($proveedores)->sortBy(
            function (array $proveedor) {
                return match ($this->sortField) {
                    'total' => (float) ($proveedor['total'] ?? 0),
                    'selected' => $this->providerHasSelection($proveedor) ? 1 : 0,
                    default => mb_strtolower($proveedor['proveedor_nombre'] ?? $proveedor['proveedor_codigo'] ?? ''),
                };
            },
            descending: $this->sortDirection === 'desc'
        );
    }

    protected function providerHasSelection(array $proveedor): bool
    {
        $selected = collect($this->selectedInvoices);

        foreach ($proveedor['empresas'] ?? [] as $empresa) {
            foreach ($empresa['sucursales'] ?? [] as $sucursal) {
                foreach ($sucursal['facturas'] ?? [] as $factura) {
                    if ($selected->contains($factura['key'] ?? null)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function buildFacturasDesdeSolicitud(SolicitudPago $solicitud): array
    {
        $registros = collect();
        $conexionNombres = [];
        $empresaOptionsCache = [];
        $sucursalOptionsCache = [];

        foreach ($solicitud->detalles as $detalle) {
            $conexionId = (int) ($detalle->erp_conexion ?? $solicitud->id_empresa);
            $empresaCodigo = (string) ($detalle->erp_empresa_id ?? '');
            $sucursalCodigo = (string) ($detalle->erp_sucursal ?? '');
            $numeroFactura = (string) ($detalle->numero_factura ?? '');
            $esCompra = strtoupper((string) $detalle->erp_tabla) === 'COMPRA' || str_starts_with($numeroFactura, 'COMPRA-');

            if (! isset($conexionNombres[$conexionId])) {
                $conexionNombres[$conexionId] = \App\Models\Empresa::query()
                    ->where('id', $conexionId)
                    ->value('nombre_empresa') ?? (string) $conexionId;
            }

            if (! isset($empresaOptionsCache[$conexionId])) {
                $empresaOptionsCache[$conexionId] = SolicitudPagoResource::getEmpresasOptions($conexionId);
            }

            $empresaOptions = $empresaOptionsCache[$conexionId];

            if (! isset($sucursalOptionsCache[$conexionId][$empresaCodigo])) {
                $sucursalOptionsCache[$conexionId][$empresaCodigo] = SolicitudPagoResource::getSucursalesOptions($conexionId, array_filter([$empresaCodigo]));
            }

            $sucursalOptions = $sucursalOptionsCache[$conexionId][$empresaCodigo] ?? [];

            $registros->push([
                'key' => $detalle->erp_clave,
                'conexion_id' => $conexionId,
                'conexion_nombre' => $conexionNombres[$conexionId],

                'empresa_codigo' => $empresaCodigo,
                'empresa_nombre' => $empresaOptions[$empresaCodigo] ?? $empresaCodigo,

                'sucursal_codigo' => $sucursalCodigo,
                'sucursal_nombre' => $sucursalOptions[$sucursalCodigo] ?? $sucursalCodigo,

                'proveedor_codigo' => $detalle->proveedor_codigo ?? '',
                'proveedor_nombre' => $detalle->proveedor_nombre ?? ($detalle->proveedor_codigo ?? ''),
                'proveedor_ruc' => $detalle->proveedor_ruc,

                'numero' => $numeroFactura,
                'fecha_emision' => $detalle->fecha_emision,
                'fecha_vencimiento' => $detalle->fecha_vencimiento,
                'total' => (float) ($detalle->monto_factura ?? 0),
                'saldo' => (float) ($detalle->saldo_al_crear ?? 0),
                'abono' => (float) ($detalle->abono_aplicado ?? 0),
                'estado_abono' => $detalle->estado_abono ?? $this->resolveEstadoAbono((float) ($detalle->monto_factura ?? 0), (float) ($detalle->abono_aplicado ?? 0)),
                'tipo' => $esCompra ? 'compra' : null,
            ]);
        }

        return $this->groupByProveedor($registros);
    }

    protected function buildFacturaKey(?string $conexion, ?string $empresa, ?string $sucursal, ?string $proveedor, ?string $numero, ?string $ruc = null): string
    {
        $raw = trim(($conexion ?? '') . '|' . ($empresa ?? '') . '|' . ($sucursal ?? '') . '|' . ($proveedor ?? '') . '|' . ($numero ?? '') . '|' . ($ruc ?? ''));

        return hash('sha256', $raw);
    }

    protected function buildProveedorKey(?string $codigo, ?string $ruc, ?string $nombre): string
    {
        $ruc = preg_replace('/\\s+/', '', (string) $ruc);
        $ruc = preg_replace('/[^0-9A-Za-z]/', '', $ruc);

        if (! empty($ruc)) {
            return 'ruc:' . mb_strtolower($ruc);
        }

        $nombre = mb_strtolower(trim((string) $nombre));
        $nombre = preg_replace('/\\s+/', ' ', $nombre);

        if ($nombre !== '') {
            return 'nom:' . md5($nombre);
        }

        return 'cod:' . mb_strtolower(trim((string) $codigo));
    }

    protected function getEmpresasOptionsByConnections(array $conexiones): array
    {
        return collect($conexiones)
            ->flatMap(function ($conexion) {
                return collect(SolicitudPagoResource::getEmpresasOptions($conexion))
                    ->mapWithKeys(fn($nombre, $codigo) => [
                        $conexion . '|' . $codigo => $nombre,
                    ]);
            })
            ->all();
    }

    protected function getSucursalesOptionsByConnections(array $conexiones, array $empresasSeleccionadas): array
    {
        return collect($conexiones)
            ->flatMap(function ($conexion) use ($empresasSeleccionadas) {
                $empresas = $empresasSeleccionadas[$conexion] ?? [];

                return collect(SolicitudPagoResource::getSucursalesOptions($conexion, $empresas))
                    ->mapWithKeys(fn($nombre, $codigo) => [
                        $conexion . '|' . $codigo => $nombre,
                    ]);
            })
            ->all();
    }

    protected function groupOptionsByConnection(array $optionKeys): array
    {
        $agrupado = [];

        $walker = function ($item) use (&$agrupado, &$walker) {
            // Si ya viene agrupado: [conexion => [codigos...]]
            if (is_array($item)) {
                foreach ($item as $k => $v) {
                    // Caso: clave conexión y valor lista de códigos
                    if (is_numeric($k) && is_array($v)) {
                        foreach ($v as $codigo) {
                            if ($codigo !== null && $codigo !== '') {
                                $agrupado[(int) $k][] = (string) $codigo;
                            }
                        }
                        continue;
                    }

                    // Caso: array plano/anidado, seguir recorriendo
                    $walker($v);
                }
                return;
            }

            // Caso string esperado: "conexion|codigo"
            $value = trim((string) $item);
            if ($value === '') {
                return;
            }

            [$conexion, $codigo] = array_pad(explode('|', $value, 2), 2, null);

            if ($conexion !== null && $conexion !== '' && $codigo !== null && $codigo !== '') {
                $agrupado[(int) $conexion][] = (string) $codigo;
            }
        };

        foreach ($optionKeys as $value) {
            $walker($value);
        }

        // Opcional: quitar duplicados por conexión
        foreach ($agrupado as $c => $codigos) {
            $agrupado[$c] = array_values(array_unique($codigos));
        }

        return $agrupado;
    }


    protected function buildDefaultEmpresasSelection(array $conexiones): array
    {
        return collect($conexiones)
            ->flatMap(fn($conexion) => collect(SolicitudPagoResource::getEmpresasOptions($conexion))->keys()->map(fn($codigo) => $conexion . '|' . $codigo))
            ->values()
            ->all();
    }

    protected function buildDefaultSucursalesSelection(array $conexiones, array $empresasSeleccionadas): array
    {
        $empresas = $this->groupOptionsByConnection($empresasSeleccionadas);

        return collect($conexiones)
            ->flatMap(fn($conexion) => collect(SolicitudPagoResource::getSucursalesOptions($conexion, $empresas[$conexion] ?? []))
                ->keys()
                ->map(fn($codigo) => $conexion . '|' . $codigo))
            ->values()
            ->all();
    }
}
