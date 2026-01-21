<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-center w-10">
                    </th>
                    <th class="px-3 py-2 border-l">Bodega</th>
                    <th class="px-3 py-2 border-l">Producto</th>
                    <th class="px-3 py-2 text-right border-l">Aprobado</th>
                    <th class="px-3 py-2 w-32 border-l">Precio/Costo</th>
                    <th class="px-3 py-2 text-center w-10 border-l">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($getState() as $uuid => $item)
                    <tr class="hover:bg-gray-50 transition" wire:key="{{ $field->getStatePath() }}.{{ $uuid }}">
                        <td class="px-3 py-2 text-center">
                            <div class="flex items-center justify-center">
                                {{ $getChildComponentContainer($uuid)->getComponent('seleccionado') }}
                            </div>
                        </td>
                        <td class="px-3 py-2 border-l text-xs">
                            {{ $item['bodega'] ?? '-' }}
                        </td>
                        <td class="px-3 py-2 border-l">
                            <div class="font-medium text-gray-900 text-xs">{{ $item['producto'] ?? '-' }}</div>
                        </td>
                        <td class="px-3 py-2 border-l text-right text-xs">
                            {{ $item['cantidad'] ?? '0.00' }}
                        </td>
                        <td class="px-3 py-2 border-l">
                            <input type="number" step="0.01" wire:model="{{ $field->getStatePath() }}.{{ $uuid }}.precio"
                                class="w-full text-xs border border-gray-300 rounded px-2 py-1 focus:ring-primary-500 focus:border-primary-500" />
                        </td>
                        <td class="px-2 py-2 border-l text-center">
                            {{ $getAction('delete') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-dynamic-component>