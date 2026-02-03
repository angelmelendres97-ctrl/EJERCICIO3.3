<?php

namespace App\Filament\Resources\FlujoCajaResource\Pages;

use App\Filament\Resources\FlujoCajaResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\Empresa;
use Carbon\Carbon;

class Reporte extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = FlujoCajaResource::class;

    protected static string $view = 'filament.resources.flujo-caja-resource.pages.reporte';

    protected static ?string $title = 'Flujo de Caja';

    public ?array $data = [];

    // Report Data
    public $reportHeader = [];
    public $reportData = [];
    public $bankData = [];
    public $cxcData = [];
    public $cxpData = [];
    public $showBankModal = false;

    // CXC Modal
    public $showCxcModal = false;
    public $cxcModalTitle = 'DETALLE CUENTAS POR COBRAR';

    // CXP Modal
    public $showCxpModal = false;
    public $cxpModalTitle = 'DETALLE CUENTAS POR PAGAR';

    public $reportConditions = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Filtros del Reporte')
                    ->columns(4)
                    ->schema([
                        Forms\Components\Select::make('connection_id')
                            ->label('Conexión')
                            ->options(Empresa::where('status_conexion', true)->pluck('nombre_empresa', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('empresa_id', null)), // Reset company

                        Forms\Components\Select::make('empresa_id')
                            ->label('Empresa')
                            ->options(function (Forms\Get $get) {
                                return $this->getCompanies($get('connection_id'));
                            })
                            ->required()
                            ->live()
                            ->preload()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('sucursal_id', null)), // Reset branch

                        Forms\Components\Select::make('sucursal_id')
                            ->label('Sucursal')
                            ->options(function (Forms\Get $get) {
                                return $this->getSucursales($get('connection_id'), $get('empresa_id'));
                            })
                            ->required()
                            ->preload(),

                        Forms\Components\Select::make('periodo')
                            ->label('Periodo')
                            ->options([
                                1 => 'Diario',
                                7 => '7 Días',
                                15 => 'Quincenal',
                                30 => 'Mensual'
                            ])
                            ->default(7)
                            ->required(),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('consultar')
                                ->label('Consultar')
                                ->color('primary')
                                ->action(fn() => $this->consultar()),
                            Forms\Components\Actions\Action::make('exportarPdf')
                                ->label('Exportar PDF')
                                ->color('danger')
                                ->icon('heroicon-o-document-arrow-down')
                                ->action(fn() => $this->exportarPdf())
                        ])->alignCenter()->columnSpanFull(),
                    ])
            ]);
    }

    public function getCompanies($connectionId)
    {
        if (!$connectionId)
            return [];

        $connection = FlujoCajaResource::getExternalConnectionName($connectionId);
        if (!$connection)
            return [];

        try {
            return DB::connection($connection)
                ->table('saeempr') // Assuming this table exists for Companies
                ->pluck('empr_nom_empr', 'empr_cod_empr')
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getSucursales($connectionId, $empresaCod)
    {
        if (!$connectionId || !$empresaCod)
            return [];

        $connection = FlujoCajaResource::getExternalConnectionName($connectionId);
        if (!$connection)
            return [];

        try {
            return DB::connection($connection)
                ->table('saesucu')
                ->where('sucu_cod_empr', $empresaCod) // Filter by selected company code
                ->pluck('sucu_nom_sucu', 'sucu_cod_sucu')
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function consultar()
    {
        $data = $this->form->getState();
        $empresaId = $data['empresa_id'];
        $sucursalId = $data['sucursal_id'];
        $periodo = (int) $data['periodo'];
        $connectionId = $data['connection_id']; // Use connection_id to find connection name

        $conn = FlujoCajaResource::getExternalConnectionName($connectionId);
        if (!$conn) {
            Notification::make()->title('Error de conexión')->danger()->send();
            return;
        }

        try {
            // 1. Fetch Bank Data (Legacy Logic)
            $this->bankData = $this->fetchBankData($conn, $empresaId, $sucursalId);

            // 1b. Fetch CxC and CxP Details
            $this->cxcData = $this->fetchCxcData($conn, $empresaId, $sucursalId);
            $this->cxpData = $this->fetchCxpData($conn, $empresaId, $sucursalId);

            // Calculate Initial Bank Total
            $initialBankTotal = collect($this->bankData)->sum('saldo');

            // 2. Build Report Columns & Data
            $this->generateReportData($conn, $empresaId, $sucursalId, $periodo, $initialBankTotal);

            Notification::make()->title('Consulta generada')->success()->send();

        } catch (\Exception $e) {
            Notification::make()->title('Error al consultar: ' . $e->getMessage())->danger()->send();
        }
    }

    protected function fetchBankData($conn, $empresaCod, $sucursalCod)
    {
        try {
            // Step 1: Get Accounts
            $query = DB::connection($conn)
                ->table('saectab')
                ->join('saebanc', function ($join) {
                    $join->on('banc_cod_empr', '=', 'ctab_cod_empr')
                        ->on('banc_cod_banc', '=', 'ctab_cod_banc');
                })
                ->join('saesucu', function ($join) {
                    $join->on('sucu_cod_empr', '=', 'ctab_cod_empr')
                        ->on('sucu_cod_sucu', '=', 'ctab_cod_sucu');
                })
                ->where('ctab_cod_empr', $empresaCod);

            if ($sucursalCod) {
                $query->where('ctab_cod_sucu', $sucursalCod);
            }

            $accounts = $query->select([
                'ctab_cod_cuen',
                'ctab_num_ctab',
                'banc_nom_banc',
                'sucu_nom_sucu',
                'sucu_cod_sucu'
            ])->get();

            $results = [];
            $fecha = Carbon::now()->format('Y-m-d'); // Cut-off date for balance

            foreach ($accounts as $acc) {
                // Step 2: Calculate Balance (Debits - Credits before Today)
                $balanceQuery = DB::connection($conn)
                    ->table('saedasi as d')
                    ->join('saeasto as a', function ($join) {
                        $join->on('d.asto_cod_asto', '=', 'a.asto_cod_asto')
                            ->on('d.asto_cod_empr', '=', 'a.asto_cod_empr')
                            ->on('d.asto_cod_sucu', '=', 'a.asto_cod_sucu')
                            ->on('d.asto_cod_ejer', '=', 'a.asto_cod_ejer');
                    })
                    ->where('d.dasi_cod_cuen', $acc->ctab_cod_cuen)
                    ->where('a.asto_fec_asto', '<', $fecha)
                    ->where('a.asto_est_asto', '<>', 'AN')
                    ->where('a.asto_cod_empr', $empresaCod)
                    ->where('a.asto_cod_sucu', $acc->sucu_cod_sucu)
                    ->selectRaw('SUM(d.dasi_dml_dasi) as debits, SUM(d.dasi_cml_dasi) as credits')
                    ->first();

                // Logic: abs(credits - debits)
                // Note: Credits usually Influx for some, Debits for others? 
                // Legacy: $valor = abs($dasi_cme_dasi - $dasi_dml_dasi);
                $debits = $balanceQuery->debits ?? 0;
                $credits = $balanceQuery->credits ?? 0;
                $saldo = ($credits - $debits) * -1;

                $results[] = [
                    'cuenta_contable' => $acc->ctab_cod_cuen,
                    'sucursal' => $acc->sucu_nom_sucu,
                    'banco' => $acc->banc_nom_banc,
                    'cuenta' => $acc->ctab_num_ctab,
                    'valor' => number_format($saldo, 2),
                    'saldo' => $saldo
                ];
            }

            return $results;

        } catch (\Exception $e) {
            Notification::make()->title('Error bancos: ' . $e->getMessage())->danger()->send();
            return [];
        }
    }

    protected function fetchCxcData($conn, $empresaCod, $sucursalCod)
    {
        try {
            $query = DB::connection($conn)
                ->table('saedmcc')
                // Join with saeclpv (Clients) - using saedmcc.clpv_cod_clpv
                ->join('saeclpv', 'saedmcc.clpv_cod_clpv', '=', 'saeclpv.clpv_cod_clpv')
                ->where('dmcc_cod_empr', $empresaCod)
                ->where('dmcc_est_dmcc', '<>', 'AN')
                ->where('clpv_clopv_clpv', 'CL'); // Filter for Clients

            if ($sucursalCod) {
                $query->where('dmcc_cod_sucu', $sucursalCod);
            }

            // Get Balance grouping by Client
            $results = $query->select([
                'saedmcc.clpv_cod_clpv as codigo',
                'clpv_nom_clpv as nombre',
                DB::raw('SUM(dmcc_deb_ml - dmcc_cre_ml) as saldo')
            ])
                ->groupBy('saedmcc.clpv_cod_clpv', 'clpv_nom_clpv')
                ->havingRaw('SUM(dmcc_deb_ml - dmcc_cre_ml) > 0')
                ->orderBy('saldo', 'desc')
                ->get()
                ->toArray();

            return array_map(function ($item) {
                return (array) $item;
            }, $results);

        } catch (\Exception $e) {
            return [];
        }
    }

    protected function fetchCxpData($conn, $empresaCod, $sucursalCod)
    {
        try {
            $query = DB::connection($conn)
                ->table('saedmcp')
                // Join with saeclpv (Providers)
                ->join('saeclpv', 'saedmcp.clpv_cod_clpv', '=', 'saeclpv.clpv_cod_clpv')
                ->where('dmcp_cod_empr', $empresaCod)
                ->where('dmcp_est_dcmp', '<>', 'AN')
                ->where('clpv_clopv_clpv', 'PV'); // Filter for Providers

            if ($sucursalCod) {
                $query->where('dmcp_cod_sucu', $sucursalCod);
            }

            // Get Balance grouping by Provider
            $results = $query->select([
                'saedmcp.clpv_cod_clpv as codigo',
                'clpv_nom_clpv as nombre',
                DB::raw('SUM(dcmp_cre_ml - dcmp_deb_ml) as saldo')
            ])
                ->groupBy('saedmcp.clpv_cod_clpv', 'clpv_nom_clpv')
                ->havingRaw('SUM(dcmp_cre_ml - dcmp_deb_ml) > 0')
                ->orderBy('saldo', 'desc')
                ->get()
                ->toArray();

            return array_map(function ($item) {
                return (array) $item;
            }, $results);

        } catch (\Exception $e) {
            return [];
        }
    }

    protected function generateReportData($conn, $empresaCod, $sucursalCod, $periodo, $initialBankTotal)
    {
        $headers = [];
        $rows = [
            'banco' => ['label' => 'BANCO', 'values' => []],
            'cxc' => ['label' => 'CUENTAS COBRAR', 'values' => []],
            'total_ingreso' => ['label' => 'TOTAL INGRESO', 'values' => []],
            'cxp' => ['label' => 'CUENTAS PAGAR', 'values' => []],
            'nomina' => ['label' => 'NOMINA', 'values' => []],
            'total_pagar' => ['label' => 'TOTAL A PAGAR', 'values' => []],
            'flujo' => ['label' => 'FLUJO DE CAJA', 'values' => []],
        ];

        $today = Carbon::now();
        $dateIterator = $today->copy();

        $cumulativeBalance = $initialBankTotal;

        $iterDate = Carbon::now();
        $previousFlow = 0; // Will be set in first loop

        for ($s = 0; $s <= 12; $s++) {
            $headerLabel = "";
            $condicionDateOperator = "";
            $condicionDateVal = null;

            // 1. Determine Range
            if ($periodo == 1) {
                if ($s == 0) {
                    $dateVal = $iterDate->copy()->subDay()->format('Y-m-d');
                    $headerLabel = "FECHA " . $dateVal;
                    $condicionDateOperator = "<=";
                    $condicionDateVal = $dateVal;
                } else {
                    if ($s == 1) {
                        $iterDate = Carbon::now();
                    } else {
                        $iterDate->addDay();
                    }
                    $dateVal = $iterDate->format('Y-m-d');
                    $headerLabel = $dateVal;
                    $condicionDateOperator = "=";
                    $condicionDateVal = $dateVal;
                }
            } else {
                if ($s == 0) {
                    $dateVal = $iterDate->copy()->subDay()->format('Y-m-d');
                    $headerLabel = "FECHA " . $dateVal;
                    $condicionDateOperator = "<=";
                    $condicionDateVal = $dateVal;
                    $iterDate = Carbon::now();
                } else {
                    $startDate = $iterDate->format('Y-m-d');
                    $iterDate->addDays($periodo);
                    $endDate = $iterDate->copy()->subDay()->format('Y-m-d');
                    $headerLabel = $startDate . " AL " . $endDate;
                    $condicionDateOperator = "BETWEEN";
                    $condicionDateVal = [$startDate, $endDate];
                }
            }
            $headers[] = $headerLabel;

            // Store condition for interactive modals
            $this->reportConditions[] = [
                'operator' => $condicionDateOperator,
                'value' => $condicionDateVal,
                'label' => $headerLabel
            ];

            // 2. Query CXC
            $cxcQuery = DB::connection($conn)->table('saedmcc')
                ->where('dmcc_est_dmcc', '<>', 'AN')
                ->where('dmcc_cod_empr', $empresaCod);
            if ($sucursalCod)
                $cxcQuery->where('dmcc_cod_sucu', $sucursalCod);

            if ($condicionDateOperator === 'BETWEEN') {
                $cxcQuery->whereBetween('dmcc_fec_ven', $condicionDateVal);
            } else {
                $cxcQuery->whereDate('dmcc_fec_ven', $condicionDateOperator, $condicionDateVal);
            }
            // Legacy: Deb - Cre
            $cxcVal = $cxcQuery->selectRaw('COALESCE(SUM(dmcc_deb_ml - dmcc_cre_ml), 0) as val')->value('val');

            // 3. Query CXP
            $cxpQuery = DB::connection($conn)->table('saedmcp')
                ->where('dmcp_est_dcmp', '<>', 'AN')
                ->where('dmcp_cod_empr', $empresaCod);
            if ($sucursalCod)
                $cxpQuery->where('dmcp_cod_sucu', $sucursalCod);

            if ($condicionDateOperator === 'BETWEEN') {
                $cxpQuery->whereBetween('dmcp_fec_ven', $condicionDateVal);
            } else {
                $cxpQuery->whereDate('dmcp_fec_ven', $condicionDateOperator, $condicionDateVal);
            }
            // Legacy: Cre - Deb
            $cxpVal = $cxpQuery->selectRaw('COALESCE(SUM(dcmp_cre_ml - dcmp_deb_ml), 0) as val')->value('val');

            // 4. Query Nomina
            // Legacy checks if Last Day of Range == Last Day of Month (approx)
            // Simplified: Query all payroll payments that "real date" (YYYYMM) matches the YYYYMM of the period end?
            // Legacy SQL: pago_per_pago = '$fec_pago' (YYYYMM from $ultimo_dia)
            // We'll check if the range touches the end of the month.

            $checkDate = is_array($condicionDateVal) ? $condicionDateVal[1] : $condicionDateVal;
            $checkCarbon = Carbon::parse($checkDate);
            $yearMonth = $checkCarbon->format('Ym');

            $nominaVal = 0;
            // Only trigger if checkDate is last day of its month (or passed logic if range crosses it?)
            // Legacy specifically calculates $ultimo_dia = (Month + 1) - 1 day.
            // If $ultimo_dia == $fecha_i (Current End Date of period).
            // So ONLY if the period ENDS exactly on the last day of the month.

            if ($checkCarbon->copy()->endOfMonth()->isSameDay($checkCarbon)) {
                $nominaVal = DB::connection($conn)->table('saepago')
                    ->where('pago_cod_empr', $empresaCod)
                    ->where('pago_per_pago', $yearMonth)
                    ->sum('pago_val_pago') ?? 0;
            }

            // 5. Calculate Totals
            $startBalance = ($s == 0) ? $initialBankTotal : $previousFlow;

            $totalIngreso = abs($startBalance + $cxcVal);
            $totalPagar = abs($cxpVal + $nominaVal);

            // Legacy Flow Logic: abs(Ingreso - Egresos)
            $flujo = abs($totalIngreso - $totalPagar);

            $previousFlow = $flujo;

            // Format for View
            $rows['banco']['values'][] = number_format($startBalance, 2);
            $rows['cxc']['values'][] = number_format($cxcVal, 2);
            $rows['total_ingreso']['values'][] = number_format($totalIngreso, 2);
            $rows['cxp']['values'][] = number_format($cxpVal, 2);
            $rows['nomina']['values'][] = number_format($nominaVal, 2);
            $rows['total_pagar']['values'][] = number_format($totalPagar, 2);
            $rows['flujo']['values'][] = number_format($flujo, 2);
        }

        $this->reportHeader = $headers;
        $this->reportData = $rows;
    }

    public function exportarPdf()
    {
        // Ensure data is fresh
        $this->consultar();

        if (empty($this->reportData)) {
            Notification::make()
                ->title('No hay datos para exportar')
                ->warning()
                ->send();
            return;
        }

        // Add Header Info
        $formState = $this->form->getState();

        $empresaName = DB::connection(FlujoCajaResource::getExternalConnectionName($formState['connection_id']))
            ->table('saeempr')->where('empr_cod_empr', $formState['empresa_id'])->value('empr_nom_empr');

        $sucursalName = DB::connection(FlujoCajaResource::getExternalConnectionName($formState['connection_id']))
            ->table('saesucu')
            ->where('sucu_cod_empr', $formState['empresa_id'])
            ->where('sucu_cod_sucu', $formState['sucursal_id'])
            ->value('sucu_nom_sucu');

        $reportHeaderInfo = [
            'empresa' => $empresaName,
            'sucursal' => $sucursalName,
            'periodo' => $formState['periodo'] == 1 ? 'Diario' : $formState['periodo'] . ' Días',
            'fecha' => now()->format('d-m-Y H:i'),
            'title' => 'Flujo de Caja'
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.flujo_caja', [
            'reportHeader' => $reportHeaderInfo,
            'dates' => $this->reportHeader, // Pass for column headers
            'reportData' => $this->reportData, // Pass directly
            'bankData' => $this->bankData,
            'cxcData' => $this->cxcData,
            'cxpData' => $this->cxpData,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'FlujoCaja_' . now()->format('Ymd_His') . '.pdf');
    }

    public function openCxpTotal()
    {
        $this->cxpModalTitle = 'DETALLE CUENTAS POR PAGAR - TOTAL';
        $data = $this->form->getState();
        // Fetch all (existing logic)
        $this->cxpData = $this->fetchCxpData(
            FlujoCajaResource::getExternalConnectionName($data['connection_id']),
            $data['empresa_id'],
            $data['sucursal_id']
        );
        $this->showCxpModal = true;
    }

    public function openCxpDetails($index)
    {
        if (!isset($this->reportConditions[$index])) {
            return;
        }

        $condition = $this->reportConditions[$index];
        $this->cxpModalTitle = 'DETALLE C. PAGAR: ' . $condition['label'];

        $data = $this->form->getState();
        $conn = FlujoCajaResource::getExternalConnectionName($data['connection_id']);

        $this->cxpData = $this->fetchCxpDataWithCondition(
            $conn,
            $data['empresa_id'],
            $data['sucursal_id'],
            $condition['operator'],
            $condition['value']
        );

        $this->showCxpModal = true;
    }

    protected function fetchCxpDataWithCondition($conn, $empresaCod, $sucursalCod, $operator, $value)
    {
        try {
            $query = DB::connection($conn)
                ->table('saedmcp')
                //->join('saeclpv', 'saedmcp.clpv_cod_clpv', '=', 'saeclpv.clpv_cod_clpv')
                ->leftJoin('saeclpv as saeclpv', function ($join) {
                    $join->on('saedmcp.clpv_cod_clpv', '=', 'saeclpv.clpv_cod_clpv')
                        ->where(function ($q) {
                            $q->where('saeclpv.clpv_clopv_clpv', 'PV')
                                ->orWhereNull('saeclpv.clpv_clopv_clpv');
                        });
                })
                ->where('dmcp_cod_empr', $empresaCod)
                ->where('dmcp_est_dcmp', '<>', 'AN');
            //->where('clpv_clopv_clpv', 'PV');

            if ($sucursalCod) {
                $query->where('dmcp_cod_sucu', $sucursalCod);
            }

            if ($operator === 'BETWEEN') {
                $query->whereBetween('dmcp_fec_ven', $value);
            } else {
                $query->whereDate('dmcp_fec_ven', $operator, $value);
            }

            // DEBUG: Capture SQL
            $sql = $query->select([
                'saedmcp.clpv_cod_clpv as codigo',
                'clpv_nom_clpv as nombre',
                DB::raw('SUM(dcmp_cre_ml - dcmp_deb_ml) as saldo')
            ])
                ->groupBy('saedmcp.clpv_cod_clpv', 'clpv_nom_clpv')
                ->havingRaw('SUM(dcmp_cre_ml - dcmp_deb_ml) != 0')
                ->orderBy('saldo', 'desc');

            $results = $sql->get()->toArray();

            return array_map(function ($item) {
                return (array) $item;
            }, $results);

        } catch (\Exception $e) {
            return [];
        }
    }

    public function openCxcTotal()
    {
        $this->cxcModalTitle = 'DETALLE CUENTAS POR COBRAR - TOTAL';
        $data = $this->form->getState();
        $conn = FlujoCajaResource::getExternalConnectionName($data['connection_id']);

        $this->cxcData = $this->fetchCxcData(
            $conn,
            $data['empresa_id'],
            $data['sucursal_id']
        );
        $this->showCxcModal = true;
    }

    public function openCxcDetails($index)
    {
        if (!isset($this->reportConditions[$index])) {
            return;
        }

        $condition = $this->reportConditions[$index];
        $this->cxcModalTitle = 'DETALLE C. COBRAR: ' . $condition['label'];

        $data = $this->form->getState();
        $conn = FlujoCajaResource::getExternalConnectionName($data['connection_id']);

        $this->cxcData = $this->fetchCxcDataWithCondition(
            $conn,
            $data['empresa_id'],
            $data['sucursal_id'],
            $condition['operator'],
            $condition['value']
        );

        $this->showCxcModal = true;
    }

    protected function fetchCxcDataWithCondition($conn, $empresaCod, $sucursalCod, $operator, $value)
    {
        try {
            $query = DB::connection($conn)
                ->table('saedmcc')
                // Join with saeclpv (Clients) - Using saedmcc.clpv_cod_clpv
                ->leftJoin('saeclpv as saeclpv', function ($join) {
                    $join->on('saedmcc.clpv_cod_clpv', '=', 'saeclpv.clpv_cod_clpv')
                        ->where(function ($q) {
                            $q->where('saeclpv.clpv_clopv_clpv', 'CL')
                                ->orWhereNull('saeclpv.clpv_clopv_clpv');
                        });
                })
                ->where('dmcc_cod_empr', $empresaCod)
                ->where('dmcc_est_dmcc', '<>', 'AN');
            //->where('clpv_clopv_clpv', 'CL');

            if ($sucursalCod) {
                $query->where('dmcc_cod_sucu', $sucursalCod);
            }

            if ($operator === 'BETWEEN') {
                $query->whereBetween('dmcc_fec_ven', $value);
            } else {
                $query->whereDate('dmcc_fec_ven', $operator, $value);
            }

            // DEBUG: Capture SQL
            $sql = $query->select([
                'saedmcc.clpv_cod_clpv as codigo',
                'clpv_nom_clpv as nombre',
                DB::raw('SUM(dmcc_deb_ml - dmcc_cre_ml) as saldo')
            ])
                ->groupBy('saedmcc.clpv_cod_clpv', 'clpv_nom_clpv')
                ->havingRaw('SUM(dmcc_deb_ml - dmcc_cre_ml) != 0')
                ->orderBy('saldo', 'desc');

            $results = $sql->get()->toArray();

            return array_map(function ($item) {
                return (array) $item;
            }, $results);

        } catch (\Exception $e) {
            return [];
        }
    }
}
