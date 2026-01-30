<?php

namespace App\Services;

use App\Filament\Resources\ProveedorResource;
use App\Models\Proveedores;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UafeService
{
    public static function isUafeEnabled(string $connectionName, int $empresaCodigo): bool
    {
        $valor = DB::connection($connectionName)
            ->table('saeempr')
            ->where('empr_cod_empr', $empresaCodigo)
            ->value('emmpr_uafe_cprov');

        return self::normalizeBoolean($valor);
    }

    public static function resolveProveedorCodigo(string $connectionName, int $empresaCodigo, string $ruc): ?int
    {
        $codigo = DB::connection($connectionName)
            ->table('saeclpv')
            ->where('clpv_cod_empr', $empresaCodigo)
            ->where('clpv_ruc_clpv', $ruc)
            ->where('clpv_clopv_clpv', 'PV')
            ->value('clpv_cod_clpv');

        return $codigo ? (int) $codigo : null;
    }

    public static function getUafeFormState(Proveedores $record): array
    {
        $connectionName = ProveedorResource::getExternalConnectionName($record->id_empresa);
        $empresaCodigo = (int) $record->admg_id_empresa;

        if (! $connectionName || ! $empresaCodigo) {
            return [
                'usa_uafe' => false,
                'estado_label' => 'No aplica',
                'documentos' => [],
            ];
        }

        if (! self::isUafeEnabled($connectionName, $empresaCodigo)) {
            return [
                'usa_uafe' => false,
                'estado_label' => 'No aplica',
                'documentos' => [],
            ];
        }

        $proveedorCodigo = self::resolveProveedorCodigo($connectionName, $empresaCodigo, (string) $record->ruc);

        if (! $proveedorCodigo) {
            return [
                'usa_uafe' => true,
                'estado_label' => 'Proveedor sin código externo',
                'documentos' => [],
            ];
        }

        $documentos = self::getUafeDocumentos($connectionName, $empresaCodigo, $proveedorCodigo);
        $cumple = self::documentosCumplen($documentos);

        return [
            'usa_uafe' => true,
            'estado_label' => $cumple ? 'Cumple' : 'Pendiente/Incompleto',
            'documentos' => $documentos,
        ];
    }

    public static function syncDocumentos(Proveedores $record, array $documentos, ?int $usuarioId = null): void
    {
        $connectionName = ProveedorResource::getExternalConnectionName($record->id_empresa);
        $empresaCodigo = (int) $record->admg_id_empresa;
        $sucursalCodigo = (int) $record->admg_id_sucursal;

        if (! $connectionName || ! $empresaCodigo) {
            return;
        }

        if (! self::isUafeEnabled($connectionName, $empresaCodigo)) {
            return;
        }

        $proveedorCodigo = self::resolveProveedorCodigo($connectionName, $empresaCodigo, (string) $record->ruc);

        if (! $proveedorCodigo) {
            return;
        }

        $fechaVencimiento = self::obtenerFechaVencimientoUafe(
            $connectionName,
            $empresaCodigo,
            $proveedorCodigo
        );

        $fechaVencimientoSql = $fechaVencimiento ? Carbon::parse($fechaVencimiento)->format('Y-m-d') : null;
        $periodo = $fechaVencimiento ? Carbon::parse($fechaVencimiento)->year : null;

        $catalogo = self::getUafeCatalogo($connectionName, $empresaCodigo)->keyBy('id');

        DB::connection($connectionName)->transaction(function () use (
            $connectionName,
            $empresaCodigo,
            $sucursalCodigo,
            $proveedorCodigo,
            $documentos,
            $fechaVencimientoSql,
            $periodo,
            $catalogo,
            $usuarioId
        ) {
            foreach ($documentos as $documento) {
                $idUafe = (int) ($documento['id_uafe'] ?? 0);
                if (! $idUafe) {
                    continue;
                }

                $estado = ! empty($documento['cumple']) ? 'AC' : 'PE';
                $ruta = $documento['archivo'] ?? null;

                $registro = DB::connection($connectionName)
                    ->table('comercial.adjuntos_clpv')
                    ->where('id_empresa', $empresaCodigo)
                    ->where('id_sucursal', $sucursalCodigo)
                    ->where('id_clpv', $proveedorCodigo)
                    ->where('id_archivo_uafe', $idUafe)
                    ->first();

                $titulo = $catalogo[$idUafe]->titulo ?? 'Documento UAFE';

                $payload = [
                    'estado' => $estado,
                    'fecha_entrega' => $estado === 'AC' ? now() : null,
                    'fecha_vencimiento_uafe' => $estado === 'AC' ? $fechaVencimientoSql : null,
                    'periodo_uafe' => $estado === 'AC' ? $periodo : null,
                ];

                if ($ruta) {
                    $payload['ruta'] = $ruta;
                    $payload['user_web'] = $usuarioId;
                    $payload['fecha_server'] = now();
                }

                if ($registro) {
                    DB::connection($connectionName)
                        ->table('comercial.adjuntos_clpv')
                        ->where('id', $registro->id)
                        ->update($payload);
                } else {
                    DB::connection($connectionName)
                        ->table('comercial.adjuntos_clpv')
                        ->insert(array_merge([
                            'id_empresa' => $empresaCodigo,
                            'id_sucursal' => $sucursalCodigo,
                            'id_clpv' => $proveedorCodigo,
                            'id_archivo_uafe' => $idUafe,
                            'titulo' => $titulo,
                        ], $payload));
                }
            }

            $cumple = self::documentosCumplen(
                self::getUafeDocumentos($connectionName, $empresaCodigo, $proveedorCodigo)
            );

            $estadoProveedor = $cumple ? 'A' : 'P';

            DB::connection($connectionName)
                ->table('saeclpv')
                ->where('clpv_cod_empr', $empresaCodigo)
                ->where('clpv_cod_clpv', $proveedorCodigo)
                ->whereIn('clpv_est_clpv', ['A', 'P'])
                ->update(['clpv_est_clpv' => $estadoProveedor]);
        });
    }

    public static function enviarNotificacion(Proveedores $record): bool
    {
        $connectionName = ProveedorResource::getExternalConnectionName($record->id_empresa);
        $empresaCodigo = (int) $record->admg_id_empresa;

        if (! $connectionName || ! $empresaCodigo) {
            return false;
        }

        if (! self::isUafeEnabled($connectionName, $empresaCodigo)) {
            return false;
        }

        $correoDestino = trim((string) $record->correo);
        if ($correoDestino === '') {
            return false;
        }

        $config = self::getUafeMailConfig($connectionName, $empresaCodigo);
        if (! $config) {
            return false;
        }

        $adjuntos = self::getUafeCatalogo($connectionName, $empresaCodigo)
            ->map(function ($documento) {
                $ruta = self::resolveUafeDocumentPath((string) $documento->ruta);

                if (! $ruta || ! file_exists($ruta)) {
                    return null;
                }

                $mimeType = mime_content_type($ruta) ?: 'application/octet-stream';
                $content = base64_encode(file_get_contents($ruta));

                return [
                    'name' => basename($ruta) ?: $documento->titulo,
                    'content' => $content,
                    'mime_type' => $mimeType,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $payload = [
            'smtp_server' => $config['server'] . ':' . $config['port'],
            'secure_type' => $config['secure_type'],
            'username' => $config['username'],
            'password' => $config['password'],
            'from_address' => $config['from'],
            'to_address' => [$correoDestino],
            'to_cc' => array_filter([$config['cc'] ?? null]),
            'title' => 'Documentos UAFE',
            'content' => self::buildUafeMailBody($connectionName, $empresaCodigo),
            'attachments' => $adjuntos,
        ];

        $mailUrl = config('services.uafe.mail_url');
        $tokenApi = $config['token_api'] ?? null;

        if (! $mailUrl || ! $tokenApi) {
            return false;
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Token-Api' => $tokenApi,
        ])->post($mailUrl, $payload);

        if (! $response->ok()) {
            Log::warning('UAFE mail response error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return $response->ok() && (bool) ($response->json('result') ?? false);
    }

    public static function getEstadoInicialProveedor(string $connectionName, int $empresaCodigo): string
    {
        return self::isUafeEnabled($connectionName, $empresaCodigo) ? 'P' : 'A';
    }

    private static function getUafeDocumentos(string $connectionName, int $empresaCodigo, int $proveedorCodigo): array
    {
        $catalogo = self::getUafeCatalogo($connectionName, $empresaCodigo);

        $adjuntos = DB::connection($connectionName)
            ->table('comercial.adjuntos_clpv')
            ->where('id_empresa', $empresaCodigo)
            ->where('id_clpv', $proveedorCodigo)
            ->whereNotNull('id_archivo_uafe')
            ->where('estado', '<>', 'AN')
            ->get()
            ->keyBy('id_archivo_uafe');

        return $catalogo->map(function ($documento) use ($adjuntos) {
            $adjunto = $adjuntos->get($documento->id);
            $estado = $adjunto->estado ?? 'PE';
            $fechaVencimiento = $adjunto->fecha_vencimiento_uafe ?? null;
            $estadoCalculado = self::calcularEstadoDocumento($estado, $fechaVencimiento);

            return [
                'id_uafe' => (int) $documento->id,
                'titulo' => $documento->titulo,
                'archivo' => $adjunto->ruta ?? null,
                'fecha_entrega' => $adjunto?->fecha_entrega ? substr((string) $adjunto->fecha_entrega, 0, 10) : null,
                'fecha_vencimiento' => $fechaVencimiento ? substr((string) $fechaVencimiento, 0, 10) : null,
                'estado' => $estadoCalculado,
                'cumple' => $estadoCalculado === 'AC',
            ];
        })->values()->all();
    }

    private static function getUafeCatalogo(string $connectionName, int $empresaCodigo): Collection
    {
        return DB::connection($connectionName)
            ->table('comercial.archivos_uafe')
            ->where('empr_cod_empr', $empresaCodigo)
            ->where('estado', 'AC')
            ->orderBy('id')
            ->get();
    }

    private static function documentosCumplen(array $documentos): bool
    {
        if (empty($documentos)) {
            return true;
        }

        foreach ($documentos as $documento) {
            if (($documento['estado'] ?? 'PE') !== 'AC') {
                return false;
            }
        }

        return true;
    }

    private static function obtenerFechaVencimientoUafe(string $connectionName, int $empresaCodigo, int $proveedorCodigo): ?string
    {
        $fecha = DB::connection($connectionName)
            ->table('saetprov')
            ->where('tprov_cod_empr', $empresaCodigo)
            ->where('tprov_cod_tprov', function ($query) use ($empresaCodigo, $proveedorCodigo) {
                $query->select('clpv_cod_tprov')
                    ->from('saeclpv')
                    ->where('clpv_cod_empr', $empresaCodigo)
                    ->where('clpv_cod_clpv', $proveedorCodigo)
                    ->limit(1);
            })
            ->value('tprov_venc_uafe');

        return $fecha ? substr((string) $fecha, 0, 10) : null;
    }

    private static function calcularEstadoDocumento(?string $estadoBd, ?string $fechaVencimiento): string
    {
        $estadoBase = $estadoBd ?: 'PE';

        if ($estadoBase !== 'AC') {
            return 'PE';
        }

        if (! $fechaVencimiento) {
            return 'PE';
        }

        $hoy = Carbon::now()->format('Y-m-d');
        $fecha = substr($fechaVencimiento, 0, 10);

        return $hoy > $fecha ? 'VE' : 'AC';
    }

    private static function normalizeBoolean($valor): bool
    {
        $normalizado = strtolower(trim((string) $valor));

        return in_array($normalizado, ['t', 'true', '1', 's', 'si', 'y'], true);
    }

    private static function resolveUafeDocumentPath(string $ruta): ?string
    {
        $ruta = trim($ruta);
        if ($ruta === '') {
            return null;
        }

        if (Str::startsWith($ruta, ['http://', 'https://'])) {
            return null;
        }

        if (Str::startsWith($ruta, ['/'])) {
            return $ruta;
        }

        $basePath = config('services.uafe.documents_base_path', storage_path('app/public'));

        return rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($ruta, DIRECTORY_SEPARATOR);
    }

    private static function getUafeMailConfig(string $connectionName, int $empresaCodigo): ?array
    {
        $config = DB::connection($connectionName)
            ->table('comercial.config_email')
            ->where('id_empresa', $empresaCodigo)
            ->where('id_tipo', 1)
            ->first();

        if (! $config) {
            return null;
        }

        $tokenApi = DB::connection($connectionName)
            ->table('saeempr')
            ->where('empr_cod_empr', $empresaCodigo)
            ->value('empr_token_api');

        $secureType = ($config->ssltls === 'S' || $config->ssltls === 'ssl') ? 'ssl' : 'tls';

        return [
            'server' => $config->server,
            'port' => $config->port,
            'username' => $config->user,
            'password' => $config->pass,
            'from' => $config->mail,
            'secure_type' => $secureType,
            'token_api' => $tokenApi,
        ];
    }

    private static function buildUafeMailBody(string $connectionName, int $empresaCodigo): string
    {
        $empresa = DB::connection($connectionName)
            ->table('saeempr')
            ->where('empr_cod_empr', $empresaCodigo)
            ->first();

        $compania = $empresa->empr_nom_empr ?? 'Empresa';
        $direccion = $empresa->empr_dir_empr ?? '';
        $telefono = $empresa->empr_tel_resp ?? '';

        return "<div style='width: 900px;'>
                <table style='width:850px;'> 
                    <tr>
                        <td>Estimado cliente, se han enviado los formularios UAFE para su revisión.</td>
                    </tr>
                </table>
                <br/>
                <table style='width:850px;'>
                    <tr> 
                        <td>Atentamente,</td>
                    </tr> 
                    <tr>&nbsp;</tr>
                    <tr>&nbsp;</tr>
                    <tr>
                        <td style='font-weight: bold; font-size: 13px;'>{$compania}</td>
                    </tr>
                    <tr>&nbsp;</tr>
                    <tr>
                        <td style='font-weight: bold;'>Dire.: {$direccion}</td>
                    </tr>
                    <tr>
                        <td style='font-weight: bold;'>Telf.: {$telefono}</td>
                    </tr>
                     <tr>&nbsp;</tr>
                </table>
            </div>";
    }
}
