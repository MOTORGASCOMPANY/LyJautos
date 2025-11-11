<div class="sm:px-6 w-full pt-12 pb-2">
    <!-- Contenedor principal con fondo claro y bordes redondeados -->
    <div class="bg-white shadow-lg px-8 py-8 rounded-xl w-full">
        <!-- Título del módulo -->
        <div class="mb-8 text-center">
            <h2 class="text-indigo-600 font-extrabold text-3xl uppercase flex items-center justify-center gap-3">
                <i class="fa-solid fa-newspaper text-4xl"></i>
                Formatos al contado
            </h2>
        </div>

        <!-- Contenedor para el selector y botón -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Selección de fechas -->
            <div
                class="flex items-center justify-center bg-gray-100 rounded-md p-3 gap-4 shadow-sm border border-gray-200">
                <span>Desde: </span>
                <x-date-picker wire:model="fechaInicio" wire:change="actualizarDatos" placeholder="Fecha de inicio"
                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                <span>Hasta: </span>
                <x-date-picker wire:model="fechaFin" wire:change="actualizarDatos" placeholder="Fecha de Fin"
                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
            </div>
            <!-- Selección de tipo de material -->
            <div
                class="flex items-center justify-center bg-gray-100 rounded-md p-3 gap-4 shadow-sm border border-gray-200">
                <span>Seleccione: </span>
                <select wire:model="tipoSeleccionado" wire:change="actualizarDatos" id="tipoSeleccionado"
                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate">
                    <option value="">Seleccione un tipo</option>
                    @foreach ($tipos as $tipo)
                        <option value="{{ $tipo }}">{{ $tipo }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for="tipoSeleccionado" class="ml-2 text-red-500" />
            </div>
            <!-- Boton Descargar Excell -->
            <div class="flex items-center justify-center">
                <button wire:click="$emit('exportaDataContado')"
                    class="bg-green-400 px-6 py-4 w-96 text-sm rounded-md text-sm text-white font-semibold tracking-wide cursor-pointer ">
                    <p class="truncate"><i class="fa-solid fa-file-excel fa-lg"></i> Desc. Excel </p>
                </button>
            </div>
        </div>

        <!-- Tabla de resultados -->
        @if ($data)
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse bg-white rounded-lg shadow-md" id='data_1'>
                    <thead
                        class="cursor-pointer hover:font-bold hover:text-indigo-500  px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase">#</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase">FECHA DE ENTREGA</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase">FORMATO</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase">TIPO</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase">INSPECTOR</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase">CANTIDAD</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase">STOCK</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase">MONTO</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase">PAGADO</th>
                            {{-- 
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase">METODO DE PAGO</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase">FECHA Y HORA</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase">BANCO</th>
                            --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $index => $item)
                            <tr class="{{ $loop->even ? 'bg-gray-100' : 'bg-white' }} hover:bg-gray-200">
                                <td class="px-5 py-4 text-sm font-medium text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $item['fecha']->format('d-m-Y H:i:s') }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $item['numSerie'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">
                                    @if ($tipoSeleccionado === 'MODIFICACION')
                                        MODI
                                    @elseif ($tipoSeleccionado === 'FORMATO GLP')
                                        GLP
                                    @elseif ($tipoSeleccionado === 'CHIP')
                                        CHIP
                                    @else
                                        NE
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $item['inspector'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $item['cantidad'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $item['stock'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $item['monto'] }}</td>
                                <td class="pl-2">
                                    <div class="flex items-center">
                                        @if ($item['pagado'] === 2)
                                            <p
                                                class="text-xs rounded-full leading-none p-1 font-bold text-green-700 bg-green-200">
                                                Completo
                                            </p>
                                        @else
                                            <p
                                                class="text-xs rounded-full leading-none p-1 font-bold text-red-700 bg-red-200">
                                                Pendiente
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                {{-- 
                                <td class="px-5 py-4 text-sm text-gray-700">YAPE</td>
                                <td class="px-5 py-4 text-sm text-gray-700"></td>
                                <td class="px-5 py-4 text-sm text-gray-700">BCP</td>
                                --}}
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-4 text-center text-gray-500">
                                    No se encontraron materiales asociados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <!-- Mensaje cuando no hay registros -->
            <div class="px-6 py-4 text-center text-indigo-700 bg-indigo-100 rounded-md font-bold">
                No se encontró ningún registro.
            </div>
        @endif
    </div>

    @push('js')
        <script>
            Livewire.on('exportaDataContado', () => {
                // Obtener los datos de la tabla
                datae = document.getElementById('data_1').innerHTML;
                console.log(datae);
                // Emitir el evento exportarExcel con los datos de la tabla
                Livewire.emit('exportarExcelContado', datae);
            });
        </script>
    @endpush
</div>
