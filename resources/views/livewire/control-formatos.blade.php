<div>
    <div class="sm:px-6 w-full pt-12 pb-4 text-center mx-auto">
        <h1 class="text-center text-xl font-bold text-indigo-600"> CONTROL DE FORMATOS Y CHIPS</h1>
        <p class="text-sm leading-relaxed text-gray-500">
            Nota: Material chips no tienen año, para otros seleccionar hasta llegar con el grupo o guia.
        </p>

        <div class="max-w-5xl m-auto bg-white rounded-lg shadow-md my-4">
            <div class="bg-indigo-200 rounded-lg py-4 px-4 flex flex-wrap gap-4 justify-center">
                {{-- SELECT DE TIPO DE MATERIAL --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-center w-full sm:w-[300px]">
                    <x-jet-label value="Material:" class="mr-2" />
                    <select wire:model="tipoSeleccionado"
                        class="bg-gray-50 border-indigo-500 rounded-md outline-none w-full sm:flex-1 truncate">
                        <option value="">Seleccione...</option>
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->descripcion }}</option>
                        @endforeach
                    </select>
                    <x-jet-input-error for="tipoSeleccionado" />
                </div>
                {{-- SELECT DE AÑO --}}
                @if ($tipoSeleccionado != 2)
                    <div class="flex flex-col sm:flex-row items-start sm:items-center w-full sm:w-[300px]">
                        <x-jet-label value="Año:" class="mr-2" />
                        <select wire:model="anioSeleccionado"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none w-full sm:flex-1 truncate">
                            <option value="">Seleccione...</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                        </select>
                        <x-jet-input-error for="anioSeleccionado" />
                    </div>
                @endif
                {{-- SELECT DE GRUPOS --}}
                @if ($tipoSeleccionado)
                    <div class="flex flex-col sm:flex-row items-start sm:items-center w-full sm:w-[300px]">
                        <x-jet-label value="Grupo:" class="mr-2" />
                        <select wire:model="grupoSeleccionado"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none w-full sm:flex-1 truncate">
                            <option value="">Seleccione...</option>
                            @foreach ($grupos as $grupo)
                                {{--<option value="{{ $grupo }}">{{ $grupo . (minSerie - maxSerie) }}</option>--}}
                                <option value="{{ $grupo['grupo'] }}">
                                    {{ $grupo['grupo'] }} @if ($tipoSeleccionado != 2) ({{ $grupo['minSerie'] }} - {{ $grupo['maxSerie'] }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <x-jet-input-error for="grupo" />
                    </div>
                @endif
            </div>
        </div>

        @if ($materiales && count($materiales) > 0)
            <div class="overflow-x-auto m-auto w-full">
                <div class="inline-block min-w-full py-2 sm:px-6 ">
                    {{-- BOTÓN EXPORTAR EXCELL --}}
                    <div class="bg-gray-200 rounded-md p-4 mb-4 flex justify-center items-center">
                        <button wire:click="$emit('exportarCtrlFormatos')"
                            class="bg-green-400 px-6 py-4 w-1/3 text-sm rounded-md text-sm text-white font-semibold tracking-wide cursor-pointer ">
                            <p class="truncate"><i class="fa-solid fa-file-excel fa-lg"></i> Exportar Excel </p>
                        </button>
                    </div>
                    {{-- TABLA DE DATOS --}}
                    <div class="overflow-hidden rounded-lg shadow-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 bg-white" id="data_formatos">
                            <thead class="bg-indigo-100 rounded-t-lg">
                                <tr>
                                    <th colspan="8"
                                        class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                        {{ $this->nombreTipo . ' - ' . $grupoSeleccionado . ($tipoSeleccionado != 2 ? ' ' . $this->rangoSeries : '') }}
                                    </th>
                                </tr>
                                <tr>
                                    @if ($tipoSeleccionado != 2)
                                        <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                            N° DE FORMATO
                                        </th>
                                    @endif
                                    <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                        INSPECTOR
                                    </th>
                                    @if ($tipoSeleccionado != 2)
                                        <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                            TALLER
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                            TALLER / EXTERNO
                                        </th>
                                    @endif
                                    <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                        FECHA DE ENTREGA
                                    </th>
                                    @if ($tipoSeleccionado != 2)
                                        <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                            FECHA DE PAGO
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                            ESTADO
                                        </th>
                                    @endif
                                    <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                        PLACA
                                    </th>
                                    @if ($tipoSeleccionado == 2)
                                        <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                            FECHA DE USO
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                            NOTA
                                        </th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($materiales as $index => $material)
                                    <tr class="@if ($material->estado == 5) bg-red-100 text-red-900 font-semibold @else hover:bg-indigo-50 @endif transition">
                                        @if ($tipoSeleccionado != 2)
                                            <td class="px-4 py-2 text-center">
                                                {{ $material->numSerie ?? null }}
                                            </td>
                                        @endif
                                        <td class="px-4 py-2 text-center">
                                            @if ($material->estado == 1)
                                                STOCK ALMACEN MOTORGAS
                                            @elseif($material->estado == 2)
                                                {{ $material->ubicacion }}
                                            @elseif($material->estado == 3 || $material->estado == 4 || $material->estado == 5)
                                                {{ $material->Inspector->name ?? null }}
                                            @else
                                            @endif
                                        </td>
                                        @if ($tipoSeleccionado != 2)
                                            <td class="px-4 py-2 text-center">
                                                {{ $material->taller ?? null }}
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                {{ $material->taller_o_externo ?? null }}
                                            </td>
                                        @endif
                                        <td class="px-4 py-2 text-center">
                                            {{ $material->fecha_entrega ? \Carbon\Carbon::parse($material->fecha_entrega)->format('d/m/Y') : null }}
                                        </td>
                                        @if ($tipoSeleccionado != 2)
                                            <td class="px-4 py-2 text-center">
                                                {{-- $material->fecha_ultima_foto_boleta ? \Carbon\Carbon::parse($material->fecha_ultima_foto_boleta)->format('d/m/Y') : null --}}
                                                {{ $material->fecha_pago ? \Carbon\Carbon::parse($material->fecha_pago)->format('d/m/Y') : null }}
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                {{ $material->estado == 1 ? 'MOTORGAS' : 
                                                    ($material->estado == 2 ? 'ENVIO' : 
                                                    ($material->estado == 3 ? 'POSESIÓN' : 
                                                    ($material->estado == 4 ? 'CONSUMIDO' : 
                                                    ($material->estado == 5 ? 'ANULADO' : '')))) }}
                                            </td>
                                        @endif
                                        <td class="px-4 py-2 text-center">
                                            {{ $material->placa ?? null }}
                                        </td>
                                        @if ($tipoSeleccionado == 2)
                                            <td class="px-4 py-2 text-center">
                                                {{ $material->fecha_uso ? \Carbon\Carbon::parse($material->fecha_uso)->format('d/m/Y') : null }}
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                {{ $material->tipo_servicio }}
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('js')
        <script>
            Livewire.on('exportarCtrlFormatos', () => {
                // Obtener los datos de la tabla
                datae = document.getElementById('data_formatos').innerHTML;
                console.log(datae);
                // Emitir el evento exportarExcel con los datos de la tabla
                Livewire.emit('exportarExcelFormatos', datae);
            });
        </script>
    @endpush
</div>
