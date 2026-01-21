<?php

namespace App\Http\Controllers;

use App\Filament\Resources\ResumenPedidosResource;
use App\Models\ResumenPedidos;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ResumenPedidosController extends Controller
{
    /**
     * Generate and download a PDF for the given purchase order summary.
     *
     * @param  \App\Models\ResumenPedidos  $resumenPedidos
     * @return \Illuminate\Http\Response
     */
    public function descargarPdf(ResumenPedidos $resumenPedidos)
    {
        // Load necessary relationships to avoid N+1 problems.
        $resumenPedidos->load('empresa', 'usuario');
        $detalles = $resumenPedidos->detalles()
            ->whereHas('ordenCompra', fn($query) => $query->where('anulada', false))
            ->with('ordenCompra')
            ->get();

        $groupedDetalles = $this->buildGroupedDetalles($detalles);
        $totalGeneral = $detalles->sum(fn($detalle) => (float) ($detalle->ordenCompra->total ?? 0));

        $nombreEmpresaTitulo = $this->resolveTituloEmpresa($resumenPedidos, $detalles);

        // The view 'pdfs.resumen_pedidos' will be created.
        $pdf = Pdf::loadView('pdfs.resumen_pedidos', [
            'resumen' => $resumenPedidos,
            'detalles' => $detalles,
            'groupedDetalles' => $groupedDetalles,
            'totalGeneral' => $totalGeneral,
            'nombreEmpresaTitulo' => $nombreEmpresaTitulo,
        ])->setPaper('a4', 'landscape');

        // Returns the PDF to be viewed in the browser.
        return $pdf->stream('resumen-pedidos-' . $resumenPedidos->id . '.pdf');
    }

    protected function resolveTituloEmpresa(ResumenPedidos $resumenPedidos, Collection $detalles): string
    {
        $nombreEmpresaTitulo = $resumenPedidos->empresa->nombre_empresa ?? 'Nombre de Empresa no disponible';

        $empresasUnicas = $detalles
            ->map(fn($detalle) => $detalle->ordenCompra->id_empresa . '|' . $detalle->ordenCompra->amdg_id_empresa)
            ->unique()
            ->count();

        $sucursalesUnicas = $detalles
            ->map(fn($detalle) => $detalle->ordenCompra->id_empresa . '|' . $detalle->ordenCompra->amdg_id_empresa . '|' . $detalle->ordenCompra->amdg_id_sucursal)
            ->unique()
            ->count();

        if ($empresasUnicas > 1 || $sucursalesUnicas > 1) {
            return 'Resumen de pedidos';
        }

        if ($resumenPedidos->tipo === 'PB') {
            return $resumenPedidos->empresa->nombre_pb ?: $nombreEmpresaTitulo;
        }

        if ($resumenPedidos->tipo !== 'AZ') {
            return $nombreEmpresaTitulo;
        }

        $connectionName = ResumenPedidosResource::getExternalConnectionName((int) $resumenPedidos->id_empresa);
        if (! $connectionName) {
            return $nombreEmpresaTitulo;
        }

        try {
            $empresaNombre = DB::connection($connectionName)
                ->table('saeempr')
                ->where('empr_cod_empr', $resumenPedidos->amdg_id_empresa)
                ->value('empr_nom_empr');
        } catch (\Exception $e) {
            $empresaNombre = null;
        }

        return $empresaNombre ?: $nombreEmpresaTitulo;
    }

    protected function buildGroupedDetalles(Collection $detalles): array
    {
        $nombresExternos = $this->buildExternalNames($detalles);

        return $detalles
            ->groupBy(function ($detalle) {
                $orden = $detalle->ordenCompra;
                return $orden->id_empresa . '|' . $orden->amdg_id_empresa . '|' . $orden->amdg_id_sucursal;
            })
            ->map(function ($items, $key) use ($nombresExternos) {
                [$conexionId, $empresaId, $sucursalId] = array_pad(explode('|', (string) $key, 3), 3, null);
                $orden = $items->first()->ordenCompra;
                $conexionNombre = $orden->empresa->nombre_empresa ?? '';
                $empresaNombre = $nombresExternos['empresas'][$conexionId][$empresaId] ?? $empresaId;
                $sucursalNombre = $nombresExternos['sucursales'][$conexionId][$empresaId][$sucursalId] ?? $sucursalId;

                return [
                    'conexion_id' => $conexionId,
                    'empresa_id' => $empresaId,
                    'sucursal_id' => $sucursalId,
                    'conexion_nombre' => $conexionNombre,
                    'empresa_nombre' => $empresaNombre,
                    'sucursal_nombre' => $sucursalNombre,
                    'detalles' => $items,
                    'total' => $items->sum(fn($detalle) => (float) ($detalle->ordenCompra->total ?? 0)),
                ];
            })
            ->values()
            ->all();
    }

    protected function buildExternalNames(Collection $detalles): array
    {
        $empresaNombrePorConexion = [];
        $sucursalNombrePorConexion = [];

        $detalles->groupBy(fn($detalle) => $detalle->ordenCompra->id_empresa)
            ->each(function (Collection $items, $conexionId) use (&$empresaNombrePorConexion, &$sucursalNombrePorConexion) {
                $connectionName = ResumenPedidosResource::getExternalConnectionName((int) $conexionId);

                if (! $connectionName) {
                    return;
                }

                $empresaCodes = $items->pluck('ordenCompra.amdg_id_empresa')->filter()->unique()->values()->all();
                $sucursalCodes = $items->pluck('ordenCompra.amdg_id_sucursal')->filter()->unique()->values()->all();

                if (! empty($empresaCodes)) {
                    try {
                        $empresaNombrePorConexion[$conexionId] = DB::connection($connectionName)
                            ->table('saeempr')
                            ->whereIn('empr_cod_empr', $empresaCodes)
                            ->pluck('empr_nom_empr', 'empr_cod_empr')
                            ->all();
                    } catch (\Exception $e) {
                        $empresaNombrePorConexion[$conexionId] = [];
                    }
                }

                if (! empty($empresaCodes) && ! empty($sucursalCodes)) {
                    try {
                        $sucursales = DB::connection($connectionName)
                            ->table('saesucu')
                            ->whereIn('sucu_cod_empr', $empresaCodes)
                            ->whereIn('sucu_cod_sucu', $sucursalCodes)
                            ->get(['sucu_cod_empr', 'sucu_cod_sucu', 'sucu_nom_sucu']);

                        foreach ($sucursales as $sucursal) {
                            $sucursalNombrePorConexion[$conexionId][$sucursal->sucu_cod_empr][$sucursal->sucu_cod_sucu] = $sucursal->sucu_nom_sucu;
                        }
                    } catch (\Exception $e) {
                        $sucursalNombrePorConexion[$conexionId] = [];
                    }
                }
            });

        return [
            'empresas' => $empresaNombrePorConexion,
            'sucursales' => $sucursalNombrePorConexion,
        ];
    }
}
