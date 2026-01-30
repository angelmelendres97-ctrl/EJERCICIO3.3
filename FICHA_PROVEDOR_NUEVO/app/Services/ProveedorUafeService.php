<?php

namespace App\Services;

use App\Filament\Resources\ProveedorResource;
use App\Mail\UafeDocumentosMail;
use App\Models\AdjuntoProveedorUafe;
use App\Models\ArchivoUafe;
use App\Models\Proveedores;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProveedorUafeService
{
    public const ESTADO_PENDIENTE = 'PE';
    public const ESTADO_INCOMPLETO = 'IN';
    public const ESTADO_ACTIVO = 'AC';
    public const ESTADO_VENCIDO = 'VE';

    public static function usaValidacionUafe(?int $empresaId, ?int $admgEmpresa): bool
    {
        if (! $empresaId || ! $admgEmpresa) {
            return false;
        }

        $connectionName = ProveedorResource::getExternalConnectionName($empresaId);
        if (! $connectionName) {
            return false;
        }

        $valor = DB::connection($connectionName)
            ->table('saeempr')
            ->where('empr_cod_empr', $admgEmpresa)
            ->value('emmpr_uafe_cprov');

        return self::valorLogicoActivado($valor);
    }

    public static function obtenerDocumentosUafe(int $admgEmpresa): Collection
    {
        return ArchivoUafe::query()
            ->where('empr_cod_empr', $admgEmpresa)
            ->where('estado', 'AC')
            ->orderBy('id')
            ->get();
    }

    public static function calcularEstadoDocumentoUafe(?string $estadoBase, ?string $fechaVencimiento): string
    {
        $estado = $estadoBase ?: self::ESTADO_PENDIENTE;

        if ($estado !== self::ESTADO_ACTIVO) {
            return self::ESTADO_PENDIENTE;
        }

        if (! $fechaVencimiento) {
            return self::ESTADO_PENDIENTE;
        }

        $hoy = Carbon::now()->toDateString();
        $fecha = substr((string) $fechaVencimiento, 0, 10);

        return ($hoy > $fecha) ? self::ESTADO_VENCIDO : self::ESTADO_ACTIVO;
    }

    public static function construirEstadoDocumentos(Proveedores $record): array
    {
        $documentos = self::obtenerDocumentosUafe((int) $record->admg_id_empresa);

        $adjuntos = AdjuntoProveedorUafe::query()
            ->where('id_clpv', $record->id)
            ->where('id_empresa', $record->admg_id_empresa)
            ->where('id_sucursal', $record->admg_id_sucursal)
            ->get()
            ->keyBy('id_archivo_uafe');

        return $documentos->map(function (ArchivoUafe $doc) use ($adjuntos) {
            $adjunto = $adjuntos->get($doc->id);
            $fechaVencimiento = $adjunto?->fecha_vencimiento_uafe?->format('Y-m-d');
            $estadoCalculado = self::calcularEstadoDocumentoUafe($adjunto?->estado, $fechaVencimiento);

            return [
                'id_archivo_uafe' => $doc->id,
                'titulo' => $doc->titulo,
                'archivo' => $adjunto?->ruta,
                'cumple' => $estadoCalculado === self::ESTADO_ACTIVO,
                'fecha_entrega' => $adjunto?->fecha_entrega?->format('Y-m-d'),
                'fecha_vencimiento' => $fechaVencimiento,
                'estado' => $estadoCalculado,
            ];
        })->values()->all();
    }

    public static function guardarDocumentos(Proveedores $record, array $documentos, ?int $userId = null): void
    {
        $documentos = collect($documentos);

        $fechaVencimiento = self::obtenerFechaVencimientoUafe($record);
        $periodoUafe = $fechaVencimiento ? (int) substr($fechaVencimiento, 0, 4) : null;

        DB::transaction(function () use ($record, $documentos, $fechaVencimiento, $periodoUafe, $userId) {
            $documentos->each(function (array $doc) use ($record, $fechaVencimiento, $periodoUafe, $userId) {
                $idArchivo = (int) ($doc['id_archivo_uafe'] ?? 0);
                if (! $idArchivo) {
                    return;
                }

                $titulo = $doc['titulo'] ?? ArchivoUafe::query()->whereKey($idArchivo)->value('titulo');
                $cumple = (bool) ($doc['cumple'] ?? false);
                $estado = $cumple ? self::ESTADO_ACTIVO : self::ESTADO_PENDIENTE;

                $fechaEntrega = $cumple ? Carbon::now()->toDateString() : null;
                $fechaVenc = $cumple ? $fechaVencimiento : null;
                $periodo = $cumple ? $periodoUafe : null;

                $adjunto = AdjuntoProveedorUafe::query()
                    ->where('id_clpv', $record->id)
                    ->where('id_empresa', $record->admg_id_empresa)
                    ->where('id_sucursal', $record->admg_id_sucursal)
                    ->where('id_archivo_uafe', $idArchivo)
                    ->first();

                $payload = [
                    'titulo' => $titulo,
                    'estado' => $estado,
                    'ruta' => $doc['archivo'] ?? $adjunto?->ruta,
                    'user_web' => $userId,
                    'fecha_server' => Carbon::now(),
                    'fecha_entrega' => $fechaEntrega,
                    'fecha_vencimiento_uafe' => $fechaVenc,
                    'periodo_uafe' => $periodo,
                ];

                if ($adjunto) {
                    $adjunto->update($payload);
                    return;
                }

                AdjuntoProveedorUafe::query()->create(array_merge($payload, [
                    'id_empresa' => $record->admg_id_empresa,
                    'id_sucursal' => $record->admg_id_sucursal,
                    'id_clpv' => $record->id,
                    'id_archivo_uafe' => $idArchivo,
                ]));
            });
        });

        self::actualizarEstadoUafeProveedor($record);
    }

    public static function actualizarEstadoUafeProveedor(Proveedores $record): void
    {
        $documentos = self::construirEstadoDocumentos($record);

        if (empty($documentos)) {
            $record->update(['uafe_estado' => self::ESTADO_ACTIVO]);
            return;
        }

        $activos = collect($documentos)->filter(fn(array $doc) => ($doc['estado'] ?? '') === self::ESTADO_ACTIVO)->count();
        $total = count($documentos);

        if ($activos === $total) {
            $estado = self::ESTADO_ACTIVO;
        } elseif ($activos === 0) {
            $estado = self::ESTADO_PENDIENTE;
        } else {
            $estado = self::ESTADO_INCOMPLETO;
        }

        $record->update(['uafe_estado' => $estado]);
    }

    public static function sincronizarEstadoExterno(Proveedores $record, array $empresasSeleccionadas = []): void
    {
        $destino = self::mapEstadoExterno($record->uafe_estado);
        $targets = self::normalizarEmpresasSeleccionadas($record, $empresasSeleccionadas);

        foreach ($targets as $empresaId => $admgEmpresa) {
            $connectionName = ProveedorResource::getExternalConnectionName((int) $empresaId);
            if (! $connectionName) {
                continue;
            }

            DB::connection($connectionName)
                ->table('saeclpv')
                ->where('clpv_cod_empr', $admgEmpresa)
                ->where('clpv_ruc_clpv', $record->ruc)
                ->whereIn('clpv_est_clpv', ['A', 'P'])
                ->update(['clpv_est_clpv' => $destino]);
        }
    }

    public static function notificarDocumentos(Proveedores $record): void
    {
        $documentos = self::obtenerDocumentosUafe((int) $record->admg_id_empresa);

        if (! $record->correo || $documentos->isEmpty()) {
            return;
        }

        Mail::to($record->correo)->send(new UafeDocumentosMail($record, $documentos));
    }

    public static function mapEstadoExterno(?string $uafeEstado): string
    {
        if (! $uafeEstado) {
            return 'A';
        }

        return $uafeEstado === self::ESTADO_PENDIENTE ? 'P' : 'A';
    }

    private static function obtenerFechaVencimientoUafe(Proveedores $record): ?string
    {
        $connectionName = ProveedorResource::getExternalConnectionName((int) $record->id_empresa);
        if (! $connectionName) {
            return null;
        }

        $fecha = DB::connection($connectionName)
            ->table('saetprov')
            ->where('tprov_cod_empr', $record->admg_id_empresa)
            ->where('tprov_des_tprov', $record->tipo_proveedor)
            ->value('tprov_venc_uafe');

        return $fecha ? substr((string) $fecha, 0, 10) : null;
    }

    private static function normalizarEmpresasSeleccionadas(Proveedores $record, array $empresasSeleccionadas): array
    {
        if (empty($empresasSeleccionadas)) {
            return [$record->id_empresa => $record->admg_id_empresa];
        }

        $targets = [];

        foreach ($empresasSeleccionadas as $empresaValue) {
            [$empresaId, $admgEmpresa] = explode('-', $empresaValue, 2);
            $targets[(int) $empresaId] = trim($admgEmpresa);
        }

        return $targets;
    }

    private static function valorLogicoActivado($valor): bool
    {
        $normalizado = strtolower(trim((string) $valor));

        return in_array($normalizado, ['t', 'true', '1', 's', 'si', 'y'], true);
    }
}
