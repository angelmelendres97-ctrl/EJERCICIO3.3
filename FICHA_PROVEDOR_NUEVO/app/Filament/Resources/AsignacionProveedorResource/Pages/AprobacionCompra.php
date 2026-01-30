<?php

namespace App\Filament\Resources\AsignacionProveedorResource\Pages;

use App\Filament\Resources\AsignacionProveedorResource;
use Filament\Resources\Pages\Page;

class AprobacionCompra extends Page
{
    protected static string $resource = AsignacionProveedorResource::class;

    protected static string $view = 'filament.resources.asignacion-proveedor-resource.pages.aprobacion-compra';

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('regresar')
                ->label('Regresar a Reporte')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(fn() => AsignacionProveedorResource::getUrl('index')),

            \Filament\Actions\Action::make('guardar')
                ->label('Guardar')
                ->color('primary')
                ->icon('heroicon-o-check-badge')
                ->visible(fn() => !$this->isReadOnly)
                ->action(fn() => $this->save()),

            \Filament\Actions\Action::make('finalizar')
                ->label('Finalizar Proforma')
                ->color('success')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->visible(fn() => !$this->isReadOnly)
                ->modalHeading('¿Finalizar Proforma?')
                ->modalDescription('¿Estás seguro de que deseas finalizar esta proforma? El estado cambiará a "Proforma Terminada".')
                ->action(function () {
                    $this->record->update(['estado' => 'Proforma Terminada']);

                    \Filament\Notifications\Notification::make()
                        ->title('Proforma finalizada correctamente')
                        ->success()
                        ->send();

                    // Optional: redirect or refresh
                    $this->redirect(AsignacionProveedorResource::getUrl('index'));
                }),
        ];
    }

    public $record;
    public $products = [];
    public $providers = [];
    public $matrix = [];

    // Almacena la selección: [detalle_proforma_id => proveedor_id]
    public $selectedProviders = [];

    // Almacena los valores editables: [detalle_proforma_id => [proveedor_id => ['cantidad' => val, 'precio' => val, 'observacion' => val]]]
    public $approvalValues = [];
    public $globalObservation = '';
    public $providerObservations = []; // [provider_id => text]
    public $bodegas = [];
    public $approvedQuantities = [];
    public $providerTotals = [];
    public $isReadOnly = false;

    public function mount($record)
    {
        $this->record = \App\Models\Proforma::findOrFail($record);

        if ($this->record->estado === 'Proforma Terminada') {
            $this->isReadOnly = true;
        }

        $this->loadData();
    }

    public function loadData()
    {
        // Obtener IDs de detalles
        $detalleIds = \App\Models\DetalleProforma::where('id_proforma', $this->record->id)->pluck('id');

        // Obtener proveedores únicos de los detalles
        $this->providers = \App\Models\DetalleProformaProveedor::whereIn('id_detalle_proforma', $detalleIds)
            ->with('proveedor')
            ->get()
            ->pluck('proveedor')
            ->unique('id')
            ->sortBy('nombre')
            ->values();

        // Inicializar totales
        foreach ($this->providers as $p) {
            $this->providerTotals[$p->id] = [
                'subtotal' => 0,
                'iva' => 0,
                'total' => 0
            ];
        }

        // Cargar observaciones
        $this->globalObservation = $this->record->observacion_comparativo;
        $obs = \App\Models\ProformaProveedorObservacion::where('id_proforma', $this->record->id)->get();
        foreach ($obs as $o) {
            $this->providerObservations[$o->id_proveedor] = $o->observacion;
        }

        $this->products = \App\Models\DetalleProforma::where('id_proforma', $this->record->id)
            ->with(['proveedores'])
            ->get();

        $this->fetchBodegaNames();

        foreach ($this->products as $product) {
            $this->approvalValues[$product->id] = [];

            foreach ($product->proveedores as $provider) {
                // Organizar datos para fácil acceso en la vista
                // Accedemos a los datos del pivote (tabla intermedia)
                $pivotData = $provider->pivot->toArray();
                $this->matrix[$product->id][$provider->id] = $pivotData;

                // Inicializar valores de aprobación con la oferta actual
                $this->approvalValues[$product->id][$provider->id] = [
                    'cantidad' => $provider->pivot->cantidad_aprobada ?? $provider->pivot->cantidad_oferta,
                    'precio' => $provider->pivot->precio_aprobado ?? $provider->pivot->valor_unitario_oferta,
                    'observacion' => $provider->pivot->observacion_aprobacion,
                ];

                // Si ya estaba aprobado, marcarlo seleccionado
                if ($provider->pivot->es_aprobado) {
                    $this->selectedProviders[$product->id][$provider->id] = true;
                    $this->approvedQuantities[$product->id] = ($this->approvedQuantities[$product->id] ?? 0) + $this->approvalValues[$product->id][$provider->id]['cantidad'];
                } else {
                    $this->selectedProviders[$product->id][$provider->id] = false;
                }
            }
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
            $this->bodegas = [];
        }
    }

    public function save()
    {
        // Validar que se haya seleccionado un proveedor por producto (opcional, o advertir)
        // Guardar cambios
        foreach ($this->selectedProviders as $detalle_id => $providers) {
            // First pass: Unapprove all for this detail to ensure clean state
            // (or handle selectively if more efficient, but this is safer)
            // \App\Models\DetalleProformaProveedor::where('id_detalle_proforma', $detalle_id)
            //    ->update(['es_aprobado' => false]);

            // Loop through each provider for the product
            foreach ($providers as $proveedor_id => $isSelected) {
                // Determine if approved
                $shouldApprove = (bool) $isSelected;

                $values = $this->approvalValues[$detalle_id][$proveedor_id] ?? null;

                if ($values) {
                    \App\Models\DetalleProformaProveedor::where('id_detalle_proforma', $detalle_id)
                        ->where('id_proveedor', $proveedor_id)
                        ->update([
                            'es_aprobado' => $shouldApprove,
                            'cantidad_aprobada' => $shouldApprove ? $values['cantidad'] : 0, // Reset if unselected? Or keep history?
                            'precio_aprobado' => $shouldApprove ? $values['precio'] : 0,
                            'observacion_aprobacion' => $shouldApprove ? ($values['observacion'] ?? null) : null,
                        ]);
                }
            }
        }

        // Actualizar estado de proforma si se desea (opcional)
        // $this->record->update(['estado' => 'Aprobacion Proveedores']);

        \Filament\Notifications\Notification::make()
            ->title('Aprobación guardada correctamente')
            ->success()
            ->send();
    }

    public function updated($name, $value)
    {
        if (str_starts_with($name, 'approvalValues') || str_starts_with($name, 'selectedProviders')) {
            $this->recalculateTotals();
        }
    }

    public function recalculateTotals()
    {
        // Reset totals
        foreach ($this->providers as $p) {
            $this->providerTotals[$p->id] = ['subtotal' => 0, 'iva' => 0, 'total' => 0];
        }

        foreach ($this->products as $product) {
            foreach ($this->providers as $provider) {
                // Check if this provider is selected for this product
                $isSelected = $this->selectedProviders[$product->id][$provider->id] ?? false;

                if ($isSelected) {
                    $qty = floatval($this->approvalValues[$product->id][$provider->id]['cantidad'] ?? 0);
                    $price = floatval($this->approvalValues[$product->id][$provider->id]['precio'] ?? 0);

                    // Get IVA % from original offer
                    $offerData = $this->matrix[$product->id][$provider->id] ?? [];
                    $ivaPercent = floatval($offerData['iva_porcentaje'] ?? 0);

                    $subtotal = $qty * $price;
                    $iva = $subtotal * ($ivaPercent / 100);
                    $total = $subtotal + $iva;

                    $this->providerTotals[$provider->id]['subtotal'] += $subtotal;
                    $this->providerTotals[$provider->id]['iva'] += $iva;
                    $this->providerTotals[$provider->id]['total'] += $total;
                }
            }
        }
    }
}
