<?php

namespace App\Filament\Resources\AsignacionProveedorResource\Pages;

use App\Filament\Resources\AsignacionProveedorResource;
use App\Models\Proforma;
use App\Models\DetalleProformaProveedor;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class ComparativoProveedores extends Page
{
    protected static string $resource = AsignacionProveedorResource::class;

    protected static string $view = 'filament.resources.asignacion-proveedor-resource.pages.comparativo-proveedores';

    public Proforma $record;

    public $matrix = [];
    public $providers = [];
    public $products = [];
    public $grandTotals = [];
    public $bodegas = [];
    public $globalObservation = '';
    public $providerObservations = []; // [provider_id => text]

    public function mount(Proforma $record)
    {
        $this->record = $record;
        $this->loadData();
    }

    public function loadData()
    {
        // Get all pivot records for this proforma's items
        $pivotRecords = DetalleProformaProveedor::whereIn(
            'id_detalle_proforma',
            $this->record->detalles->pluck('id')
        )
            ->with(['proveedor', 'detalleProforma'])
            ->get();

        // Extract unique providers and attach contact info from the first pivot found
        $this->providers = $pivotRecords->map(function ($pivot) {
            $prov = $pivot->proveedor;
            // Create a plain object/array to ensure Livewire preserves these properties
            return (object) [
                'id' => $prov->id,
                'nombre' => $prov->nombre,
                'correo_display' => $pivot->correo ?? $prov->correo,
                'contacto_display' => $pivot->contacto ?? $prov->telefono,
            ];
        })
            ->unique('id')
            ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        // Extract unique products (detalles)
        $this->products = $this->record->detalles;

        // Fetch Bodega Names using External Connection
        $this->fetchBodegaNames();

        // Build Matrix: [product_id][provider_id] => Record
        foreach ($pivotRecords as $pivot) {
            $data = $pivot->toArray();

            // Default logic: If offer values are 0, use assigned values
            if ((float) $data['valor_unitario_oferta'] == 0) {
                $data['valor_unitario_oferta'] = $data['costo'];
            }

            if ((float) $data['cantidad_oferta'] == 0 && $pivot->detalleProforma) {
                // Use approved quantity if available and > 0, otherwise requested quantity
                $aprobada = (float) ($pivot->detalleProforma->cantidad_aprobada ?? 0);
                $data['cantidad_oferta'] = $aprobada > 0 ? $aprobada : $pivot->detalleProforma->cantidad;
            }

            // Recalculate line totals if they are 0 and we have defaults
            if ((float) $data['subtotal_oferta'] == 0 && (float) $data['cantidad_oferta'] > 0) {
                $qty = (float) $data['cantidad_oferta'];
                $unitPrice = (float) $data['valor_unitario_oferta'];
                $discountPercent = (float) ($data['descuento_porcentaje'] ?? 0);
                $ivaPercent = (float) ($data['iva_porcentaje'] ?? 0);
                $charges = (float) ($data['otros_cargos'] ?? 0);

                $subtotal = $qty * $unitPrice;
                $discountVal = $subtotal * ($discountPercent / 100);
                $subtotalAfterDisc = $subtotal - $discountVal;
                $ivaVal = $subtotalAfterDisc * ($ivaPercent / 100);
                $total = $subtotalAfterDisc + $ivaVal + $charges;

                $data['subtotal_oferta'] = $subtotal;
                $data['total_oferta'] = $total;
            }

            $this->matrix[$pivot->id_detalle_proforma][$pivot->id_proveedor] = $data;
        }

        $this->calculateGrandTotals();

        // Load Global Observation
        $this->globalObservation = $this->record->observacion_comparativo;

        // Load Provider Observations
        $provObs = \App\Models\ProformaProveedorObservacion::where('id_proforma', $this->record->id)->get();
        foreach ($provObs as $obs) {
            $this->providerObservations[$obs->id_proveedor] = $obs->observacion;
        }
    }

    public function fetchBodegaNames()
    {
        $uniqueBodegaIds = $this->products->pluck('id_bodega')->unique()->filter()->values()->all();

        if (empty($uniqueBodegaIds)) {
            return;
        }

        $connectionName = AsignacionProveedorResource::getExternalConnectionName($this->record->id_empresa);
        if (!$connectionName) {
            return;
        }

        try {
            $this->bodegas = \Illuminate\Support\Facades\DB::connection($connectionName)
                ->table('saebode')
                ->where('bode_cod_empr', $this->record->amdg_id_empresa)
                ->whereIn('bode_cod_bode', $uniqueBodegaIds)
                ->pluck('bode_nom_bode', 'bode_cod_bode')
                ->all();
        } catch (\Exception $e) {
            // Fallback or log error if needed
            $this->bodegas = [];
        }
    }

    public function calculateGrandTotals()
    {
        $this->grandTotals = [];

        foreach ($this->providers as $provider) {
            $provId = $provider->id;
            $this->grandTotals[$provId] = [
                'subtotal' => 0,
                'descuento' => 0,
                'otros_cargos' => 0,
                'iva_breakdown' => [], // 'percent' => amount
                'iva_total' => 0,
                'total' => 0,
            ];

            foreach ($this->products as $product) {
                if (isset($this->matrix[$product->id][$provId])) {
                    $pivot = $this->matrix[$product->id][$provId];
                    // Handle both Model and Array (after livewire update)
                    $attributes = is_array($pivot) ? $pivot : $pivot->toArray();

                    $qty = (float) ($attributes['cantidad_oferta'] ?? 0);
                    $unitPrice = (float) ($attributes['valor_unitario_oferta'] ?? 0);
                    $discountPercent = (float) ($attributes['descuento_porcentaje'] ?? 0);
                    $ivaPercent = (float) ($attributes['iva_porcentaje'] ?? 0);
                    $charges = (float) ($attributes['otros_cargos'] ?? 0);

                    $subtotal = $qty * $unitPrice;
                    $discountVal = $subtotal * ($discountPercent / 100);
                    $subtotalAfterDisc = $subtotal - $discountVal;

                    $ivaKey = (string) $ivaPercent;
                    $ivaVal = $subtotalAfterDisc * ($ivaPercent / 100);

                    $total = $subtotalAfterDisc + $ivaVal + $charges;

                    // Accumulate
                    $this->grandTotals[$provId]['subtotal'] += $subtotal;
                    $this->grandTotals[$provId]['descuento'] += $discountVal;
                    $this->grandTotals[$provId]['otros_cargos'] += $charges;
                    $this->grandTotals[$provId]['total'] += $total;

                    if (!isset($this->grandTotals[$provId]['iva_breakdown'][$ivaKey])) {
                        $this->grandTotals[$provId]['iva_breakdown'][$ivaKey] = 0;
                    }
                    $this->grandTotals[$provId]['iva_breakdown'][$ivaKey] += $ivaVal;
                    $this->grandTotals[$provId]['iva_total'] += $ivaVal;
                }
            }
        }
    }

    public function updated($name, $value)
    {
        // $name comes in format: matrix.123.456.field_name

        if (str_starts_with($name, 'matrix.')) {
            $parts = explode('.', $name);
            if (count($parts) >= 4) {
                $prodId = $parts[1];
                $provId = $parts[2];
                // $field = $parts[3];

                $this->recalculateLine($prodId, $provId);
                $this->calculateGrandTotals();
            }
        }
    }

    public function recalculateLine($prodId, $provId)
    {
        if (isset($this->matrix[$prodId][$provId])) {
            $data = $this->matrix[$prodId][$provId];

            $qty = (float) ($data['cantidad_oferta'] ?? 0);
            $unitPrice = (float) ($data['valor_unitario_oferta'] ?? 0);
            $discountPercent = (float) ($data['descuento_porcentaje'] ?? 0);
            $ivaPercent = (float) ($data['iva_porcentaje'] ?? 0);
            $charges = (float) ($data['otros_cargos'] ?? 0);

            $subtotal = $qty * $unitPrice;
            $discountVal = $subtotal * ($discountPercent / 100);
            $subtotalAfterDisc = $subtotal - $discountVal;
            $ivaVal = $subtotalAfterDisc * ($ivaPercent / 100);
            $total = $subtotalAfterDisc + $ivaVal + $charges;

            // Update in place (it's already an array now)
            $this->matrix[$prodId][$provId]['subtotal_oferta'] = $subtotal;
            $this->matrix[$prodId][$provId]['total_oferta'] = $total;

            // Auto-save to Database
            if (isset($data['id'])) {
                DetalleProformaProveedor::where('id', $data['id'])->update([
                    'cantidad_oferta' => $qty,
                    'valor_unitario_oferta' => $unitPrice,
                    'subtotal_oferta' => $subtotal,
                    'descuento_porcentaje' => $discountPercent,
                    'iva_porcentaje' => $ivaPercent,
                    'otros_cargos' => $charges,
                    'total_oferta' => $total,
                    'observacion_oferta' => $data['observacion_oferta'] ?? '',
                ]);
            }
        }
    }

    public function save()
    {
        // Loop through matrix and save updateable fields
        foreach ($this->matrix as $prodId => $provs) {
            foreach ($provs as $provId => $data) {
                // Determine if $data is an array (from Livewire binding) or Model
                $id = is_array($data) ? ($data['id'] ?? null) : $data->id;

                $pivot = DetalleProformaProveedor::find($id);
                if ($pivot) {
                    $attributes = is_array($data) ? $data : $data->toArray();

                    $qty = (float) ($attributes['cantidad_oferta'] ?? 0);
                    $unitPrice = (float) ($attributes['valor_unitario_oferta'] ?? 0);
                    $subtotal = $qty * $unitPrice;
                    $discountPercent = (float) ($attributes['descuento_porcentaje'] ?? 0);
                    $discountVal = $subtotal * ($discountPercent / 100);
                    $subtotalAfterDisc = $subtotal - $discountVal;
                    $ivaPercent = (float) ($attributes['iva_porcentaje'] ?? 0);
                    $ivaVal = $subtotalAfterDisc * ($ivaPercent / 100);
                    $charges = (float) ($attributes['otros_cargos'] ?? 0);
                    $total = $subtotalAfterDisc + $ivaVal + $charges;

                    $pivot->update([
                        'cantidad_oferta' => $qty,
                        'valor_unitario_oferta' => $unitPrice,
                        'subtotal_oferta' => $subtotal,
                        'descuento_porcentaje' => $discountPercent,
                        'iva_porcentaje' => $ivaPercent,
                        'otros_cargos' => $charges,
                        'total_oferta' => $total,
                        'observacion_oferta' => $attributes['observacion_oferta'] ?? '',
                    ]);
                }
            }
        }

        // Save Global Observation
        $this->record->update(['observacion_comparativo' => $this->globalObservation]);

        // Save Provider Observations
        foreach ($this->providerObservations as $provId => $obs) {
            \App\Models\ProformaProveedorObservacion::updateOrCreate(
                [
                    'id_proforma' => $this->record->id,
                    'id_proveedor' => $provId,
                ],
                [
                    'observacion' => $obs,
                ]
            );
        }

        // Reload data to refresh totals
        $this->loadData();

        Notification::make()->title('Valores guardados y recalculados')->success()->send();
    }

    // Livewire method to update a specific cell
    public function updateCell($pivotId, $field, $value)
    {
        $pivot = DetalleProformaProveedor::find($pivotId);
        if ($pivot) {
            $pivot->$field = $value;
            $pivot->save();
            // Trigger load to refresh view/calculations?
            // Better to do it purely frontend with alpine or simplistic livewire binding
        }
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('regresar')
                ->label('Regresar')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(AsignacionProveedorResource::getUrl('index')),

            Action::make('pdf')
                ->label('Generar PDF')
                ->color('danger')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    $data = [
                        'record' => $this->record,
                        'providers' => $this->providers,
                        'products' => $this->products,
                        'matrix' => $this->matrix,
                        'bodegas' => $this->bodegas,
                        'grandTotals' => $this->grandTotals,
                        'providerObservations' => $this->providerObservations,
                    ];

                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('filament.resources.asignacion-proveedor-resource.pdf.cuadro-comparativo', $data)
                        ->setPaper('a4', 'landscape');

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'Cuadro_Comparativo_' . $this->record->id . '.pdf');
                }),

            Action::make('guardar')
                ->label('Guardar')
                ->color('primary')
                ->icon('heroicon-o-document-check')
                ->action('save')
                ->visible(fn() => !in_array($this->record->estado, ['Comparativo Precios', 'Proforma Terminada'])),

            Action::make('finalizar')
                ->label('Finalizar Comparativo Precios')
                ->color('info')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Finalizar Comparativo de Precios')
                ->modalDescription('¿Está seguro de terminar el proceso de comparación de precios?')
                ->action(function () {
                    $this->record->update(['estado' => 'Comparativo Precios']);
                    Notification::make()->title('Proceso finalizado correctamente')->success()->send();
                    $this->redirect(AsignacionProveedorResource::getUrl('index'));
                })
                ->visible(fn() => !in_array($this->record->estado, ['Comparativo Precios', 'Proforma Terminada'])),
        ];
    }

    public function getTitle(): string
    {
        return 'Comparativo de Precios - Proforma #' . $this->record->id;
    }
}
