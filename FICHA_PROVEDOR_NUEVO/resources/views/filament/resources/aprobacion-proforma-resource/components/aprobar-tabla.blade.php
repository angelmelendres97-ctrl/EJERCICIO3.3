<div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-500 border-collapse border border-gray-300">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th scope="col" class="px-4 py-3 border border-gray-300">Bodega</th>
                <th scope="col" class="px-4 py-3 border border-gray-300">Producto</th>
                <th scope="col" class="px-4 py-3 border border-gray-300 text-right">Solicitada</th>
                <th scope="col" class="px-4 py-3 border border-gray-300 text-right">Aprobada</th>
                <th scope="col" class="px-4 py-3 border border-gray-300 text-right">Costo</th>
                <th scope="col" class="px-4 py-3 border border-gray-300 text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                use App\Filament\Resources\AprobacionProformaResource;
                use Illuminate\Support\Facades\DB;

                $empresaId = $getRecord()->id_empresa;
                $connectionName = AprobacionProformaResource::getExternalConnectionName($empresaId);
            @endphp

            @foreach ($getRecord()->detalles as $detalle)
                @php
                    $bodegaName = $detalle->id_bodega;
                    $productoName = $detalle->codigo_producto;

                    if ($connectionName) {
                        try {
                            $bodegaName = DB::connection($connectionName)
                                ->table('saebode')
                                ->where('bode_cod_bode', $detalle->id_bodega)
                                ->value('bode_nom_bode') ?? $detalle->id_bodega;

                            $productoName = DB::connection($connectionName)
                                ->table('saeprod')
                                ->where('prod_cod_prod', $detalle->codigo_producto)
                                ->value('prod_nom_prod') ?? $detalle->codigo_producto;
                        } catch (\Exception $e) {
                            // Keep default
                        }
                    }
                @endphp
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-4 py-2 border border-gray-300">{{ $bodegaName }}</td>
                    <td class="px-4 py-2 border border-gray-300">
                        <div class="font-medium text-gray-900">{{ $productoName }}</div>
                        <div class="text-xs text-gray-500">{{ $detalle->codigo_producto }}</div>
                    </td>
                    <td class="px-4 py-2 border border-gray-300 text-right">{{ number_format($detalle->cantidad, 2) }}</td>
                    <td class="px-4 py-2 border border-gray-300 text-right font-bold text-success-600">
                        {{ number_format($detalle->cantidad_aprobada, 2) }}</td>
                    <td class="px-4 py-2 border border-gray-300 text-right">${{ number_format($detalle->costo, 2) }}</td>
                    <td class="px-4 py-2 border border-gray-300 text-right font-medium text-gray-900">
                        ${{ number_format($detalle->cantidad_aprobada * $detalle->costo, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-50">
            <tr>
                <td colspan="5" class="px-4 py-3 border border-gray-300 text-right font-bold text-gray-900">TOTAL
                    GENERAL:</td>
                <td class="px-4 py-3 border border-gray-300 text-right font-bold text-lg text-primary-600">
                    @php
                        $total = $getRecord()->detalles->sum(function ($d) {
                            return $d->cantidad_aprobada * $d->costo;
                        });
                    @endphp
                    ${{ number_format($total, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>