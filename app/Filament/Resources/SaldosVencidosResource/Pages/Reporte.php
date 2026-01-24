<?php

namespace App\Filament\Resources\SaldosVencidosResource\Pages;

use App\Filament\Resources\SaldosVencidosResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\Facades\DB;
use App\Models\Empresa;

class Reporte extends Page implements HasForms
{
    use InteractsWithForms;
    use WithPagination;

    protected static string $resource = SaldosVencidosResource::class;

    protected static string $view = 'filament.resources.saldos-vencidos-resource.pages.reporte';

    protected static ?string $title = 'Reporte Saldos Vencidos';

    public ?array $data = [];
    public bool $consultado = false;
    public array $resultados = [];
    public ?string $search = '';

    public function mount(): void
    {
        $this->form->fill([
            'fecha_desde' => now()->startOfMonth(),
            'fecha_hasta' => now(),
        ]);
    }

    public function getPaginatedResultsProperty()
    {
        $page = $this->getPage();
        $perPage = 50;

        // Filter results based on search
        $filteredResults = $this->resultados;

        if (!empty($this->search)) {
            $searchLower = strtolower($this->search);
            $filteredResults = array_filter($this->resultados, function ($row) use ($searchLower) {
                // Keep summaries if they match logic, but safer to match content.
                // We will rely on re-processing or just naive text match.

                // If it is a summary/header row, we might want to keep it if a child is visible, 
                // but that's complex. For now, simple text match.
                // Note: Providing 'proveedor' in headers helps match.

                return str_contains(strtolower($row['proveedor'] ?? ''), $searchLower)
                    || str_contains(strtolower($row['ruc'] ?? ''), $searchLower)
                    || str_contains(strtolower($row['numero_factura'] ?? ''), $searchLower)
                    || str_contains(strtolower($row['detalle'] ?? ''), $searchLower);
            });
        }

        $items = array_slice($filteredResults, ($page - 1) * $perPage, $perPage);

        return new LengthAwarePaginator(
            $items,
            count($filteredResults),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Filtros del Reporte')
                    ->schema([
                        Forms\Components\Select::make('conexiones')
                            ->label('Conexiones')
                            ->options(Empresa::query()->pluck('nombre_empresa', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('proveedor_ruc', null))
                            ->required(),
                        Forms\Components\Select::make('proveedor_ruc')
                            ->label('Proveedor')
                            ->searchable()
                            ->preload()
                            ->options(function (Forms\Get $get) {
                                $conexiones = $get('conexiones');
                                if (empty($conexiones))
                                    return [];

                                $proveedores = [];

                                foreach ($conexiones as $conexionId) {
                                    $connectionName = SaldosVencidosResource::getExternalConnectionName($conexionId);
                                    if (!$connectionName)
                                        continue;

                                    try {
                                        $rows = DB::connection($connectionName)
                                            ->table('saeclpv')
                                            ->select('clpv_ruc_clpv', 'clpv_nom_clpv')
                                            ->where('clpv_clopv_clpv', 'PV')
                                            ->get();

                                        foreach ($rows as $row) {
                                            $ruc = trim($row->clpv_ruc_clpv);
                                            // Usar RUC como clave para evitar duplicados
                                            if (!empty($ruc)) {
                                                $proveedores[$ruc] = trim($row->clpv_nom_clpv) . " ($ruc)";
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        continue;
                                    }
                                }

                                asort($proveedores);
                                return $proveedores;
                            }),
                        Forms\Components\DatePicker::make('fecha_hasta')
                            ->label('Fecha Corte / Hasta')
                            ->required()
                            ->default(now()),
                        Actions::make([
                            Actions\Action::make('consultar')
                                ->label('Consultar')
                                ->action('consultar'),
                            Actions\Action::make('exportarPdf')
                                ->label('Exportar PDF')
                                ->color('danger')
                                ->icon('heroicon-o-document-arrow-down')
                                ->action('exportarPdf'),
                        ])
                            ->alignCenter()
                            ->columnSpanFull(),
                    ])->columns(3),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Radio::make('tipo_reporte')
                            ->label('Tipo de Reporte')
                            ->options([
                                'detallado' => 'Detallado (Por Factura)',
                                'global' => 'Global (Acumulado por Proveedor)',
                            ])
                            ->default('detallado')
                            ->inline()
                            ->required(),
                    ])->compact(),
            ]);
    }

    public function consultar()
    {
        $this->consultado = true;
        $this->resultados = [];

        $formData = $this->form->getState();
        $conexiones = $formData['conexiones'] ?? [];
        $fechaHasta = $formData['fecha_hasta'];
        $proveedorRuc = $formData['proveedor_ruc'] ?? null;
        $tipoReporte = $formData['tipo_reporte'] ?? 'detallado';

        foreach ($conexiones as $conexionId) {
            $connectionName = SaldosVencidosResource::getExternalConnectionName($conexionId);
            if (!$connectionName)
                continue;

            try {
                $empresasExternas = DB::connection($connectionName)->table('saeempr')->get();

                foreach ($empresasExternas as $empresaExt) {
                    $codEmpr = $empresaExt->empr_cod_empr;
                    $nomEmpr = $empresaExt->empr_nom_empr;

                    $sql = "
                        SELECT 
                            C.clpv_cod_clpv as codigo_proveedor,
                            C.clpv_cod_ciud as codigo_ciudad,
                            C.clpv_ruc_clpv as ruc,
                            C.clpv_nom_clpv as nombre_proveedor,
                            d.dmcp_num_fac as numero_factura,
                            MIN ( d.dmcp_det_dcmp ) as detalle,
                            MIN ( d.dmcp_cod_sucu ) AS codigo_sucursal,
                            MIN ( d.dmcp_cod_fact ) AS codigo_factura,
                            MIN ( d.dcmp_fec_emis ) FILTER ( WHERE d.dcmp_cre_ml > 0 ) AS fecha_emision,
                            MAX ( d.dmcp_fec_ven ) AS fecha_vencimiento,
                            SUM ( d.dcmp_deb_ml ) AS abono,
                            SUM ( d.dcmp_cre_ml ) AS total_factura,
                            ABS(SUM ( d.dcmp_deb_ml - d.dcmp_cre_ml )) AS saldo
                        FROM
                            saedmcp d
                            JOIN saeclpv C ON C.clpv_cod_clpv = d.clpv_cod_clpv 
                            AND C.clpv_cod_empr = ? 
                            AND C.clpv_clopv_clpv = 'PV' 
                        WHERE
                            d.dmcp_cod_empr = ? 
                            AND d.dmcp_est_dcmp <> 'AN' 
                            AND d.dcmp_fec_emis <= ?
                    ";

                    $params = [
                        $codEmpr,
                        $codEmpr,
                        $fechaHasta
                    ];

                    if (!empty($proveedorRuc)) {
                        $sql .= " AND C.clpv_ruc_clpv = ? ";
                        $params[] = $proveedorRuc;
                    }

                    $sql .= "
                        GROUP BY
                            C.clpv_cod_clpv,
                            C.clpv_cod_ciud,
                            C.clpv_ruc_clpv,
                            C.clpv_nom_clpv,
                            d.dmcp_num_fac 
                        HAVING
                            SUM(d.dcmp_deb_ml - d.dcmp_cre_ml) < 0
                        ORDER BY
                            d.dmcp_num_fac;
                    ";

                    $rows = DB::connection($connectionName)->select($sql, $params);

                    foreach ($rows as $row) {
                        $this->resultados[] = [
                            'empresa_origen' => $nomEmpr,
                            'codigo_proveedor' => $row->codigo_proveedor,
                            'ruc' => $row->ruc,
                            'proveedor' => $row->nombre_proveedor,
                            'numero_factura' => $row->numero_factura,
                            'detalle' => $row->detalle,
                            'emision' => $row->fecha_emision,
                            'vencimiento' => $row->fecha_vencimiento,
                            'abono' => $row->abono,
                            'total_factura' => $row->total_factura,
                            'saldo' => $row->saldo,
                        ];
                    }
                }

            } catch (\Exception $e) {
                // Log error
            }
        }

        // -----------------------------------------------------------------
        // PROCESAMIENTO POST-CONSULTA: ORDENAMIENTO Y SUB-TOTALES
        // -----------------------------------------------------------------

        // 2. Agrupar y Calcular Sub-totales
        $finalResults = [];
        $currentEmpresa = null;
        $currentProveedor = null;
        $grupoRows = [];

        // Track Company Summaries
        $companyTotals = ['factura' => 0, 'abono' => 0, 'saldo' => 0];

        // Helper to inject company summary
        $injectCompanySummary = function ($companyName) use (&$finalResults, &$companyTotals) {
            $finalResults[] = [
                'type' => 'company_summary',
                'empresa_origen' => $companyName, // To avoid breaking, although not strictly needed
                'proveedor' => 'TOTAL POR PAGAR ' . $companyName,
                'total_factura' => $companyTotals['factura'],
                'abono' => $companyTotals['abono'],
                'saldo' => $companyTotals['saldo'],
                // Empty fields
                'ruc' => '',
                'numero_factura' => '',
                'detalle' => '',
                'emision' => '',
                'vencimiento' => '',
                'codigo_proveedor' => ''
            ];
            // Reset totals
            $companyTotals['factura'] = 0;
            $companyTotals['abono'] = 0;
            $companyTotals['saldo'] = 0;
        };

        if ($tipoReporte === 'global') {
            // ---------------- GLOBAL REPORT LOGIC ----------------
            $aggregated = [];

            foreach ($this->resultados as $row) {
                $key = $row['empresa_origen'] . '|' . $row['proveedor'];

                if (!isset($aggregated[$key])) {
                    $aggregated[$key] = [
                        'empresa_origen' => $row['empresa_origen'],
                        'proveedor' => $row['proveedor'],
                        'ruc' => $row['ruc'],
                        'codigo_proveedor' => $row['codigo_proveedor'],
                        'total_factura' => 0,
                        'abono' => 0,
                        'saldo' => 0,
                        'numero_factura' => '-',
                        'detalle' => '-',
                        'emision' => '-',
                        'vencimiento' => '-',
                        'type' => 'data_global'
                    ];
                }

                $aggregated[$key]['total_factura'] += $row['total_factura'];
                $aggregated[$key]['abono'] += $row['abono'];
                $aggregated[$key]['saldo'] += $row['saldo'];
            }

            $this->resultados = array_values($aggregated);

            usort($this->resultados, function ($a, $b) {
                $empresaCmp = strcmp($a['empresa_origen'], $b['empresa_origen']);
                if ($empresaCmp !== 0)
                    return $empresaCmp;
                return strcmp($a['proveedor'], $b['proveedor']);
            });

            foreach ($this->resultados as $row) {
                if ($currentEmpresa !== $row['empresa_origen']) {
                    if ($currentEmpresa !== null) {
                        $injectCompanySummary($currentEmpresa);
                    }

                    $finalResults[] = [
                        'type' => 'company_header',
                        'empresa_origen' => $row['empresa_origen'],
                        'proveedor' => 'EMPRESA: ' . $row['empresa_origen'],
                        'total_factura' => 0,
                        'abono' => 0,
                        'saldo' => 0,
                        'ruc' => '',
                        'numero_factura' => '',
                        'detalle' => '',
                        'emision' => '',
                        'vencimiento' => '',
                        'codigo_proveedor' => ''
                    ];
                    $currentEmpresa = $row['empresa_origen'];
                }

                // Add to company totals
                $companyTotals['factura'] += $row['total_factura'];
                $companyTotals['abono'] += $row['abono'];
                $companyTotals['saldo'] += $row['saldo'];

                $finalResults[] = $row;
            }

            // Inject last company summary
            if ($currentEmpresa !== null) {
                $injectCompanySummary($currentEmpresa);
            }

        } else {
            // ---------------- DETAILED REPORT LOGIC ----------------
            usort($this->resultados, function ($a, $b) {
                $empresaCmp = strcmp($a['empresa_origen'], $b['empresa_origen']);
                if ($empresaCmp !== 0)
                    return $empresaCmp;
                $proveedorCmp = strcmp($a['proveedor'], $b['proveedor']);
                if ($proveedorCmp === 0)
                    return strcmp($a['emision'], $b['emision']);
                return $proveedorCmp;
            });

            $processGroup = function ($rows) use (&$finalResults, &$companyTotals) {
                if (empty($rows))
                    return;

                foreach ($rows as $row) {
                    $row['type'] = 'data';
                    $finalResults[] = $row;

                    // Add to company totals
                    $companyTotals['factura'] += $row['total_factura'];
                    $companyTotals['abono'] += $row['abono'];
                    $companyTotals['saldo'] += $row['saldo'];
                }

                $totalFactura = 0;
                $totalAbono = 0;
                $totalSaldo = 0;
                $proveedorName = $rows[0]['proveedor'];

                foreach ($rows as $row) {
                    $totalFactura += $row['total_factura'];
                    $totalAbono += $row['abono'];
                    $totalSaldo += $row['saldo'];
                }

                $finalResults[] = [
                    'type' => 'summary',
                    'empresa_origen' => '',
                    'codigo_proveedor' => '',
                    'ruc' => '',
                    'proveedor' => 'TOTAL ' . $proveedorName,
                    'numero_factura' => '',
                    'detalle' => '',
                    'emision' => '',
                    'vencimiento' => '',
                    'abono' => $totalAbono,
                    'total_factura' => $totalFactura,
                    'saldo' => $totalSaldo,
                ];
            };

            foreach ($this->resultados as $row) {
                if ($currentEmpresa !== $row['empresa_origen']) {
                    if (!empty($grupoRows)) {
                        $processGroup($grupoRows);
                        $grupoRows = [];
                    }
                    if ($currentEmpresa !== null) {
                        $injectCompanySummary($currentEmpresa);
                    }

                    $finalResults[] = [
                        'type' => 'company_header',
                        'empresa_origen' => $row['empresa_origen'],
                        'proveedor' => 'EMPRESA: ' . $row['empresa_origen'],
                        'total_factura' => 0,
                        'abono' => 0,
                        'saldo' => 0,
                        'ruc' => '',
                        'numero_factura' => '',
                        'detalle' => '',
                        'emision' => '',
                        'vencimiento' => '',
                        'codigo_proveedor' => ''
                    ];

                    $currentEmpresa = $row['empresa_origen'];
                    $currentProveedor = $row['proveedor'];
                } else if ($currentProveedor !== $row['proveedor']) {
                    if (!empty($grupoRows)) {
                        $processGroup($grupoRows);
                    }
                    $currentProveedor = $row['proveedor'];
                    $grupoRows = [];
                }
                $grupoRows[] = $row;
            }

            if (!empty($grupoRows)) {
                $processGroup($grupoRows);
            }
            if ($currentEmpresa !== null) {
                $injectCompanySummary($currentEmpresa);
            }
        }

        $this->resultados = $finalResults;
        $this->dispatch('updateTable');
    }

    public function exportarPdf()
    {
        $this->consultar();

        if (empty($this->resultados)) {
            \Filament\Notifications\Notification::make()
                ->title('No hay datos para exportar')
                ->warning()
                ->send();
            return;
        }

        // Pass full results list and report type to PDF
        $resultados = $this->resultados;
        $tipoReporte = $this->data['tipo_reporte'] ?? 'detallado';

        // Extract company names for title (naive)
        $nombresEmpresas = collect($resultados)
            ->whereIn('type', ['company_header', 'data', 'data_global'])
            ->pluck('empresa_origen')
            ->unique()
            ->filter()
            ->implode(' - ');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.saldos_vencidos', compact('resultados', 'nombresEmpresas', 'tipoReporte'))
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'SaldosVencidos_' . now()->format('Ymd_His') . '.pdf');
    }
}