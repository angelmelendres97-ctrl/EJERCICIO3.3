<?php

namespace App\Services;

use App\Filament\Resources\SolicitudPagoResource;
use App\Models\Empresa;
use App\Models\SolicitudPago;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SolicitudPagoReportService
{
    public function exportPdf(SolicitudPago $solicitud): StreamedResponse
    {
        $solicitud->loadMissing('detalles');

        $facturas = $this->buildFacturaRowsFromSolicitud($solicitud);
        [$facturasNormales, $compras] = $this->splitFacturas($facturas);

        $proveedores = $this->getProvidersWithMetadata($facturasNormales);
        $empresas = $this->buildEmpresasParaReportes($proveedores);
        $resumen = $this->buildResumenPorEmpresaDesdeFacturas(array_merge($facturasNormales, $compras));
        $totales = $this->buildTotalesDesdeFacturas(array_merge($facturasNormales, $compras));
        $comprasReport = $this->buildComprasReportRows($compras);
        $descripcion = $this->buildDescripcionReporte($solicitud);

        return response()->streamDownload(function () use ($empresas, $resumen, $totales, $descripcion, $comprasReport) {
            echo Pdf::loadView('pdfs.solicitud-pago-facturas-general', [
                'empresas' => $empresas,
                'resumenEmpresas' => $resumen,
                'usuario' => Auth::user()?->name,
                'totales' => $totales,
                'descripcionReporte' => $descripcion,
                'compras' => $comprasReport,
            ])->setPaper('a4', 'landscape')->stream();
        }, $this->buildPdfFilename($solicitud));
    }

    public function streamPdf(SolicitudPago $solicitud): Response
    {
        $solicitud->loadMissing('detalles');

        $facturas = $this->buildFacturaRowsFromSolicitud($solicitud);
        [$facturasNormales, $compras] = $this->splitFacturas($facturas);

        $proveedores = $this->getProvidersWithMetadata($facturasNormales);
        $empresas = $this->buildEmpresasParaReportes($proveedores);
        $resumen = $this->buildResumenPorEmpresaDesdeFacturas(array_merge($facturasNormales, $compras));
        $totales = $this->buildTotalesDesdeFacturas(array_merge($facturasNormales, $compras));
        $comprasReport = $this->buildComprasReportRows($compras);
        $descripcion = $this->buildDescripcionReporte($solicitud);

        return Pdf::loadView('pdfs.solicitud-pago-facturas-general', [
            'empresas' => $empresas,
            'resumenEmpresas' => $resumen,
            'usuario' => Auth::user()?->name,
            'totales' => $totales,
            'descripcionReporte' => $descripcion,
            'compras' => $comprasReport,
        ])
            ->setPaper('a4', 'landscape')
            ->stream($this->buildPdfFilename($solicitud), ['Attachment' => false]);
    }

    public function exportExcel(SolicitudPago $solicitud): StreamedResponse
    {
        $solicitud->loadMissing('detalles');

        $facturas = $this->buildFacturaRowsFromSolicitud($solicitud);
        $proveedores = $this->getProvidersWithMetadata($facturas);
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
                        'Area' => $proveedor['area'] ?? '',
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
                'Area' => 'Area',
                'Valor' => 'Valor',
                'Abono' => 'Abono',
                'Saldo pendiente' => 'Saldo pendiente',
            ]));

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $this->buildExcelFilename($solicitud));
    }

    public function exportDetailedPdf(SolicitudPago $solicitud): StreamedResponse
    {
        $solicitud->loadMissing('detalles');

        $facturas = $this->buildFacturaRowsFromSolicitud($solicitud);
        [$facturasNormales, $compras] = $this->splitFacturas($facturas);

        $proveedores = $this->getProvidersWithMetadata($facturasNormales);
        $empresas = $this->buildEmpresasParaReportes($proveedores);
        $totales = $this->buildTotalesDesdeFacturas(array_merge($facturasNormales, $compras));
        $comprasReport = $this->buildComprasReportRows($compras);
        $descripcion = $this->buildDescripcionReporte($solicitud);

        return response()->streamDownload(function () use ($empresas, $totales, $descripcion, $comprasReport) {
            echo Pdf::loadView('pdfs.solicitud-pago-facturas-detallado', [
                'empresas' => $empresas,
                'totales' => $totales,
                'usuario' => Auth::user()?->name,
                'descripcionReporte' => $descripcion,
                'compras' => $comprasReport,
            ])->setPaper('a4', 'landscape')->stream();
        }, $this->buildDetailedPdfFilename($solicitud));
    }

    public function exportDetailedExcel(SolicitudPago $solicitud): StreamedResponse
    {
        $solicitud->loadMissing('detalles');

        $facturas = $this->buildFacturaRowsFromSolicitud($solicitud);
        $proveedores = $this->getProvidersWithMetadata($facturas);
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
                                'Area' => $proveedor['area'] ?? '',
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
                'Area' => 'Area',
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
        }, $this->buildDetailedExcelFilename($solicitud));
    }

    public function streamDetailedPdf(SolicitudPago $solicitud): Response
    {
        $solicitud->loadMissing('detalles');

        $facturas = $this->buildFacturaRowsFromSolicitud($solicitud);
        [$facturasNormales, $compras] = $this->splitFacturas($facturas);

        $proveedores = $this->getProvidersWithMetadata($facturasNormales);
        $empresas = $this->buildEmpresasParaReportes($proveedores);
        $totales = $this->buildTotalesDesdeFacturas(array_merge($facturasNormales, $compras));
        $comprasReport = $this->buildComprasReportRows($compras);
        $descripcion = $this->buildDescripcionReporte($solicitud);

        return Pdf::loadView('pdfs.solicitud-pago-facturas-detallado', [
            'empresas' => $empresas,
            'totales' => $totales,
            'usuario' => Auth::user()?->name,
            'descripcionReporte' => $descripcion,
            'compras' => $comprasReport,
        ])
            ->setPaper('a4', 'landscape')
            ->stream($this->buildDetailedPdfFilename($solicitud), ['Attachment' => false]); // ✅ inline
    }


    protected function buildFacturaRowsFromSolicitud(SolicitudPago $solicitud): array
    {
        $registros = [];
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
                $conexionNombres[$conexionId] = Empresa::query()
                    ->where('id', $conexionId)
                    ->value('nombre_empresa') ?? (string) $conexionId;
            }

            if (! isset($empresaOptionsCache[$conexionId])) {
                $empresaOptionsCache[$conexionId] = SolicitudPagoResource::getEmpresasOptions($conexionId);
            }

            if (! isset($sucursalOptionsCache[$conexionId][$empresaCodigo])) {
                $sucursalOptionsCache[$conexionId][$empresaCodigo] = SolicitudPagoResource::getSucursalesOptions($conexionId, array_filter([$empresaCodigo]));
            }

            $empresaOptions = $empresaOptionsCache[$conexionId];
            $sucursalOptions = $sucursalOptionsCache[$conexionId][$empresaCodigo] ?? [];

            $total = (float) ($detalle->monto_factura ?? 0);
            $saldo = (float) ($detalle->saldo_al_crear ?? 0);
            $abono = (float) ($detalle->abono_aplicado ?? 0);

            $proveedorNombre = $detalle->proveedor_nombre ?? ($detalle->proveedor_codigo ?? '');
            $proveedorKey = $this->buildProveedorKey($detalle->proveedor_codigo, $detalle->proveedor_ruc, $proveedorNombre);

            $registros[] = [
                'key' => $detalle->erp_clave,
                'conexion_id' => $conexionId,
                'conexion_nombre' => $conexionNombres[$conexionId],
                'empresa_codigo' => $empresaCodigo,
                'empresa_nombre' => $empresaOptions[$empresaCodigo] ?? $empresaCodigo,
                'sucursal_codigo' => $sucursalCodigo,
                'sucursal_nombre' => $sucursalOptions[$sucursalCodigo] ?? $sucursalCodigo,
                'proveedor_key' => $proveedorKey,
                'proveedor_codigo' => $detalle->proveedor_codigo ?? '',
                'proveedor_nombre' => $proveedorNombre,
                'proveedor_ruc' => $detalle->proveedor_ruc,
                'proveedor_actividad' => $detalle->proveedor_actividad,
                'area' => $detalle->area ?? '',
                'numero' => $numeroFactura,
                'fecha_emision' => $detalle->fecha_emision,
                'fecha_vencimiento' => $detalle->fecha_vencimiento,
                'total' => $total,
                'saldo' => $saldo,
                'abono' => $abono,
                'saldo_pendiente' => max(0, $saldo - $abono),
                'descripcion' => $detalle->descripcion ?? '',
                'tipo' => $esCompra ? 'compra' : null,
            ];
        }

        return $registros;
    }

    protected function splitFacturas(array $facturas): array
    {
        $normales = [];
        $compras = [];

        foreach ($facturas as $factura) {
            if ($this->isCompraFactura($factura)) {
                $compras[] = $factura;
            } else {
                $normales[] = $factura;
            }
        }

        return [$normales, $compras];
    }

    protected function isCompraFactura(array $factura): bool
    {
        if (($factura['tipo'] ?? null) === 'compra') {
            return true;
        }

        $numero = (string) ($factura['numero'] ?? '');

        return str_starts_with($numero, 'COMPRA-');
    }

    protected function getProvidersWithMetadata(array $facturas): array
    {
        $proveedores = [];

        foreach ($facturas as $factura) {
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
                    'area' => $factura['area'] ?? '',
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
                    'area' => $proveedor['area'] ?? '',
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

    protected function buildProveedorKey(?string $codigo, ?string $ruc, ?string $nombre): string
    {
        $ruc = preg_replace('/\s+/', '', (string) $ruc);
        $ruc = preg_replace('/[^0-9A-Za-z]/', '', $ruc);

        if (! empty($ruc)) {
            return 'ruc:' . mb_strtolower($ruc);
        }

        $nombre = mb_strtolower(trim((string) $nombre));
        $nombre = preg_replace('/\s+/', ' ', $nombre);

        if ($nombre !== '') {
            return 'nom:' . md5($nombre);
        }

        return 'cod:' . mb_strtolower(trim((string) $codigo));
    }

    protected function buildDescripcionReporte(SolicitudPago $solicitud): string
    {
        $motivo = trim((string) $solicitud->motivo);

        if ($motivo !== '') {
            return $motivo;
        }

        return 'Solicitud de pago #' . $solicitud->getKey();
    }

    protected function buildPdfFilename(SolicitudPago $solicitud): string
    {
        return 'solicitud-pago-' . $solicitud->getKey() . '.pdf';
    }

    protected function buildExcelFilename(SolicitudPago $solicitud): string
    {
        return 'solicitud-pago-' . $solicitud->getKey() . '.csv';
    }

    protected function buildDetailedPdfFilename(SolicitudPago $solicitud): string
    {
        return 'solicitud-pago-detallado-' . $solicitud->getKey() . '.pdf';
    }

    protected function buildDetailedExcelFilename(SolicitudPago $solicitud): string
    {
        return 'solicitud-pago-detallado-' . $solicitud->getKey() . '.csv';
    }
}
