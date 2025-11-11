<x-jet-dialog-modal wire:model="open" maxWidth="6xl">
    <x-slot name="title">
        Crear Nueva Planilla
    </x-slot>

    <x-slot name="content">
        <div class="flex items-end space-x-6 mb-6">
            <!-- Periodo -->
            <div>
                <x-jet-label for="periodo" value="Periodo:" class="font-semibold text-gray-700" />
                <x-jet-input id="periodo" type="date"
                    class="w-48 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    wire:model="periodo" />
                @error('periodo')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>
            <!-- Buscar y resultados de busqueda -->
            <div class="flex flex-1 items-center space-x-3">
                <div class="w-80">
                    <x-jet-label for="searchInspector" value="Buscar inspector:" class="font-semibold text-gray-700" />
                    <x-jet-input id="searchInspector" type="text"
                        class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Buscar..." wire:model.debounce.500ms="searchInspector" />
                </div>

                @if (!empty($resultados))
                    <div class="bg-white shadow border rounded-md max-h-48 overflow-y-auto flex-1 mt-2">
                        @foreach ($resultados as $inspector)
                            <div class="px-3 py-2 hover:bg-gray-100 flex justify-between items-center">
                                <span>{{ $inspector->name }}</span>
                                <x-jet-button class="ml-2" wire:click="agregarInspector({{ $inspector->id }})">
                                    Agregar
                                </x-jet-button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        {{-- Error debajo del input, centrado también --}}
        @error('periodo')
            <div class="flex justify-center">
                <span class="text-red-500 text-xs">{{ $message }}</span>
            </div>
        @enderror

        @if ($editable && count($editable))
            <div class="overflow-x-auto mt-4">
                <table class="w-full border rounded-lg text-sm">
                    <thead class="bg-slate-600 text-white">
                        <tr>
                            <th class="px-3 py-2">Inspector</th>
                            <th class="px-3 py-2">Sueldo Base</th>
                            <th class="px-3 py-2">+ Horas</th>
                            <th class="px-3 py-2">Pasajes</th>
                            <th class="px-3 py-2">Otros</th>
                            <th class="px-3 py-2">Descuentos</th>
                            <th class="px-3 py-2">Observación</th>
                            <th class="px-3 py-2">Total Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($editable as $index => $row)
                            <tr class="border-b">
                                <td class="px-3 py-2 flex items-center justify-between">
                                    <span>{{ $row['nombre'] }}</span>

                                    {{-- Si es fila manual (sin contrato) mostramos botón quitar 
                                    @if (empty($row['contrato_id']))
                                    @endif--}}
                                    <button type="button" wire:click="quitarInspector({{ $index }})"
                                        class="text-xs text-red-600 hover:text-red-800 ml-2">
                                        Quitar
                                    </button>
                                    
                                </td>

                                {{-- Sueldo base: si no tiene contrato, input editable; si tiene contrato mostramos el valor --}}
                                <td class="px-3 py-2 text-center">
                                    @if (empty($row['contrato_id']))
                                        <x-jet-input type="number" step="0.01" class="w-28 text-right"
                                            wire:model.lazy="editable.{{ $index }}.sueldo_base"
                                            wire:change="recalcular({{ $index }})" />
                                    @else
                                        <div>{{ number_format($row['sueldo_base'], 2) }}</div>
                                    @endif
                                </td>

                                <td class="px-3 py-2">
                                    <x-jet-input type="number" step="0.01" class="w-20 text-right"
                                        wire:model.lazy="editable.{{ $index }}.horas_extras"
                                        wire:change="recalcular({{ $index }})" />
                                </td>

                                <td class="px-3 py-2">
                                    <x-jet-input type="number" step="0.01" class="w-20 text-right"
                                        wire:model.lazy="editable.{{ $index }}.pasajes"
                                        wire:change="recalcular({{ $index }})" />
                                </td>

                                <td class="px-3 py-2">
                                    <x-jet-input type="number" step="0.01" class="w-20 text-right"
                                        wire:model.lazy="editable.{{ $index }}.otros"
                                        wire:change="recalcular({{ $index }})" />
                                </td>

                                <td class="px-3 py-2">
                                    <x-jet-input type="number" step="0.01" class="w-20 text-right"
                                        wire:model.lazy="editable.{{ $index }}.descuentos"
                                        wire:change="recalcular({{ $index }})" />
                                </td>

                                <td class="px-3 py-2">
                                    <x-jet-input type="text" class="w-48"
                                        wire:model.lazy="editable.{{ $index }}.observacion" />
                                </td>

                                <td class="px-3 py-2 text-center font-bold text-green-600">
                                    {{ number_format($row['total_pago'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-slot>

    <x-slot name="footer">
        <x-jet-secondary-button wire:click="$set('open',false)" class="mx-2">
            Cancelar
        </x-jet-secondary-button>
        <x-jet-button wire:click="save" wire:loading.attr="disabled" wire:target="save">
            Guardar Planilla
        </x-jet-button>
    </x-slot>
</x-jet-dialog-modal>
