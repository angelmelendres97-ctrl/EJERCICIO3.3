<?php

namespace App\Services;

use App\Filament\Resources\SolicitudPagoResource;
use App\Models\SolicitudPago;
use Illuminate\Support\Facades\DB;

class EgresoSolicitudPagoReportService
{
    public function buildReport(SolicitudPago $solicitud): array
    {
        $solicitud->loadMissing('asientos');

        $reportes = [];

        foreach ($solicitud->asientos as $asiento) {
            $connectionName = SolicitudPagoResource::getExternalConnectionName((int) $asiento->conexion);

            if (! $connectionName) {
                continue;
            }

            $asientoData = DB::connection($connectionName)
                ->table('saeasto')
                ->where('asto_cod_empr', $asiento->empresa_id)
                ->where('asto_cod_sucu', $asiento->sucursal_codigo)
                ->where('asto_cod_asto', $asiento->asto_cod_asto)
                ->where('asto_cod_ejer', $asiento->ejercicio)
                ->where('asto_num_prdo', $asiento->periodo)
                ->first();

            $diario = DB::connection($connectionName)
                ->table('saedasi')
                ->where('asto_cod_empr', $asiento->empresa_id)
                ->where('asto_cod_sucu', $asiento->sucursal_codigo)
                ->where('asto_cod_asto', $asiento->asto_cod_asto)
                ->where('asto_cod_ejer', $asiento->ejercicio)
                ->where('dasi_num_prdo', $asiento->periodo)
                ->orderBy('dasi_cod_cuen')
                ->get();

            $directorio = DB::connection($connectionName)
                ->table('saedir')
                ->where('dire_cod_empr', $asiento->empresa_id)
                ->where('dire_cod_sucu', $asiento->sucursal_codigo)
                ->where('dire_cod_asto', $asiento->asto_cod_asto)
                ->where('asto_cod_ejer', $asiento->ejercicio)
                ->where('asto_num_prdo', $asiento->periodo)
                ->orderBy('dir_cod_dir')
                ->get();

            $reportes[] = [
                'context' => [
                    'empresa' => $asiento->empresa_id,
                    'sucursal' => $asiento->sucursal_codigo,
                ],
                'asiento' => $asientoData,
                'diario' => $diario,
                'directorio' => $directorio,
            ];
        }

        return $reportes;
    }
}
