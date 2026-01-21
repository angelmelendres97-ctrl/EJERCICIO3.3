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

        $items = array_slice($this->resultados, ($page - 1) * $perPage, $perPage);

        return new LengthAwarePaginator(
            $items,
            count($this->resultados),
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

        // 1. Ordenar por Proveedor (ASC) y Fecha Emisión (ASC)
        usort($this->resultados, function ($a, $b) {
            $proveedorCmp = strcmp($a['proveedor'], $b['proveedor']);
            if ($proveedorCmp === 0) {
                return strcmp($a['emision'], $b['emision']);
            }
            return $proveedorCmp;
        });

        // 2. Agrupar y Calcular Sub-totales
        $finalResults = [];
        $currentProveedor = null;
        $grupoRows = [];

        $processGroup = function ($rows) use (&$finalResults) {
            if (empty($rows))
                return;

            // Agregar filas del grupo
            foreach ($rows as $row) {
                $row['type'] = 'data'; // Marcar como fila de datos
                $finalResults[] = $row;
            }

            // Calcular totales
            $totalFactura = 0;
            $totalAbono = 0;
            $totalSaldo = 0;
            $proveedorName = $rows[0]['proveedor'];

            foreach ($rows as $row) {
                $totalFactura += $row['total_factura'];
                $totalAbono += $row['abono'];
                $totalSaldo += $row['saldo'];
            }

            // Agregar fila de resumen
            $finalResults[] = [
                'type' => 'summary',
                'empresa_origen' => '', // No aplica
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
            if ($currentProveedor !== $row['proveedor']) {
                // Procesar grupo anterior
                if (!empty($grupoRows)) {
                    $processGroup($grupoRows);
                }
                // Iniciar nuevo grupo
                $currentProveedor = $row['proveedor'];
                $grupoRows = [];
            }
            $grupoRows[] = $row;
        }

        // Procesar último grupo
        if (!empty($grupoRows)) {
            $processGroup($grupoRows);
        }

        $this->resultados = $finalResults;

        $this->dispatch('updateTable');
    }

    public function exportarPdf()
    {
        // Ejecutar la consulta para tener los datos frescos
        $this->consultar();

        if (empty($this->resultados)) {
            \Filament\Notifications\Notification::make()
                ->title('No hay datos para exportar')
                ->warning()
                ->send();
            return;
        }

        // Filtrar solo filas de datos (excluir subtotales generados para la vista web)
        $rawData = collect($this->resultados)->where('type', 'data')->all();

        // Obtener nombres de empresas únicos
        $nombresEmpresas = collect($rawData)->pluck('empresa_origen')->unique()->implode(' - ');

        // Agrupar por proveedor para el PDF
        $groupedResults = collect($rawData)->groupBy('proveedor');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.saldos_vencidos', compact('groupedResults', 'nombresEmpresas'))
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'SaldosVencidos_' . now()->format('Ymd_His') . '.pdf');
    }
}