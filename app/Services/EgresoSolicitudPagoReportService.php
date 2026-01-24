<?php

namespace App\Services;

use App\Filament\Resources\SolicitudPagoResource;
use App\Models\SolicitudPago;
use Illuminate\Support\Facades\DB;

class EgresoSolicitudPagoReportService
{
    public function buildReporte(SolicitudPago $solicitud): array
    {
        $solicitud->loadMissing('detalles');

        $reportes = [];
        $grupos = $solicitud->detalles->groupBy(function ($detalle) use ($solicitud) {
            $conexion = (int) ($detalle->erp_conexion ?? $solicitud->id_empresa ?? 0);
            $empresa = (string) ($detalle->erp_empresa_id ?? '');
            $sucursal = (string) ($detalle->erp_sucursal ?? '');

            return implode('|', [$conexion, $empresa, $sucursal]);
        });

        foreach ($grupos as $grupo) {
            $primerDetalle = $grupo->first();
            $conexionId = (int) ($primerDetalle->erp_conexion ?? $solicitud->id_empresa ?? 0);
            $empresa = (string) ($primerDetalle->erp_empresa_id ?? '');
            $sucursal = (string) ($primerDetalle->erp_sucursal ?? '');
            $connectionName = SolicitudPagoResource::getExternalConnectionName($conexionId);

            $context = [
                'conexion' => $conexionId,
                'empresa' => $empresa,
                'sucursal' => $sucursal,
            ];

            if (! $connectionName || $empresa === '' || $sucursal === '') {
                $reportes[] = $this->buildEmptyReport($context);
                continue;
            }

            $facturas = $grupo->pluck('numero_factura')->filter()->unique()->values();
            $proveedores = $grupo->pluck('proveedor_codigo')->filter()->unique()->values();

            if ($facturas->isEmpty() && $proveedores->isEmpty()) {
                $reportes[] = $this->buildEmptyReport($context);
                continue;
            }

            $dirQuery = DB::connection($connectionName)
                ->table('saedir as dir')
                ->where('dir.dire_cod_empr', $empresa)
                ->where('dir.dire_cod_sucu', $sucursal);

            if ($facturas->isNotEmpty()) {
                $dirQuery->whereIn('dir.dir_num_fact', $facturas->all());
            }

            if ($proveedores->isNotEmpty()) {
                $dirQuery->whereIn('dir.dir_cod_cli', $proveedores->all());
            }

            if ($solicitud->fecha) {
                $dirQuery
                    ->join('saeasto as asto', function ($join) use ($empresa, $sucursal) {
                        $join->on('asto.asto_cod_asto', '=', 'dir.dire_cod_asto')
                            ->on('asto.asto_cod_empr', '=', 'dir.dire_cod_empr')
                            ->on('asto.asto_cod_sucu', '=', 'dir.dire_cod_sucu');
                    })
                    ->whereDate('asto.asto_fec_asto', $solicitud->fecha->toDateString())
                    ->select('dir.*');
            }

            $directorio = $dirQuery->get();
            $astoCodes = $directorio->pluck('dire_cod_asto')->unique()->values();

            if ($astoCodes->isEmpty()) {
                $reportes[] = $this->buildEmptyReport($context);
                continue;
            }

            $asientos = DB::connection($connectionName)
                ->table('saeasto')
                ->where('asto_cod_empr', $empresa)
                ->where('asto_cod_sucu', $sucursal)
                ->whereIn('asto_cod_asto', $astoCodes->all())
                ->orderBy('asto_fec_asto')
                ->get()
                ->keyBy('asto_cod_asto');

            $diario = DB::connection($connectionName)
                ->table('saedasi')
                ->where('asto_cod_empr', $empresa)
                ->where('asto_cod_sucu', $sucursal)
                ->whereIn('asto_cod_asto', $astoCodes->all())
                ->orderBy('asto_cod_asto')
                ->orderBy('dasi_cod_cuen')
                ->get()
                ->groupBy('asto_cod_asto');

            $directorioPorAsto = $directorio->groupBy('dire_cod_asto');

            foreach ($astoCodes as $astoCode) {
                $reportes[] = [
                    'context' => $context,
                    'asiento' => $asientos->get($astoCode),
                    'diario' => $diario->get($astoCode, collect()),
                    'directorio' => $directorioPorAsto->get($astoCode, collect()),
                ];
            }
        }

        return $reportes;
    }

    protected function buildEmptyReport(array $context): array
    {
        return [
            'context' => $context,
            'asiento' => null,
            'diario' => collect(),
            'directorio' => collect(),
        ];
    }
}
