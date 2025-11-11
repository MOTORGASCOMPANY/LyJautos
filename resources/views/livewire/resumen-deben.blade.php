<div class="min-h-screen bg-gray-100 py-10">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Encabezado -->
        <div class="flex flex-col items-center text-center space-y-2 mb-6 mt-6">
            <h3 class="text-4xl font-extrabold text-indigo-600 flex items-center space-x-2">
                <i class="fa-solid fa-chart-line"></i>
                <span>Reporte de Deudas</span>
            </h3>
            <p class="text-gray-500 text-lg mb-2">Consulta y analiza los datos detallados de las deudas de talleres e
                inspectores.</p>
        </div>

        <!-- Botónes de acción -->
        <div class="flex justify-center mt-2 mb-8 space-x-4">
            <button wire:click="procesar"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-8 rounded-lg shadow-lg transition transform hover:scale-105">
                Generar Reporte
            </button>
            <button wire:click="$emit('exportaData')"
                class="bg-red-600 px-6 py-4 w-1/6 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-lg shadow-lg transition transform hover:scale-105">
                <p class="truncate"><i class="fa-solid fa-file-pdf"></i></i> Desc. Pdf </p>
            </button>
        </div>

        <!-- Indicador de carga -->
        <div wire:loading
            class="w-full text-center bg-indigo-100 text-indigo-700 font-medium py-3 rounded-lg mb-6 shadow-sm border border-indigo-300">
            <i class="fa-solid fa-spinner animate-spin"></i> Cargando datos...
        </div>

        <div class="space-y-4" id='data_1'>
            <!-- Tabla Talleres-->
            @if (!empty($tablaFinalTalleres) && $tablaFinalTalleres->isNotEmpty())
                <div class="text-center mb-2">
                    <h4 class="font-extrabold text-gray-800 uppercase tracking-wide">
                        Deudas de Talleres
                    </h4>
                    <div class="w-16 h-1 bg-indigo-600 mx-auto mt-2 rounded"></div>
                </div>
                <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                    <table class="table-auto w-full text-sm text-left">
                        <thead class="bg-indigo-500 text-white">
                            <tr>
                                <th class="px-4 py-2 font-medium">Talleres</th>
                                @foreach (array_keys(collect($tablaFinalTalleres->first()['totales'])->toArray()) as $semanatalle)
                                    <th class="px-4 py-2 font-medium">{{ $semanatalle }}</th>
                                @endforeach
                                <th class="px-4 py-2 font-medium">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tablaFinalTalleres as $filatalle)
                                <tr class="{{ $loop->even ? 'bg-gray-100' : 'bg-white' }}">
                                    <td class="border px-4 py-2 text-gray-700">{{ $filatalle['taller'] }}</td>
                                    @foreach ($filatalle['totales'] as $total)
                                        <td class="border px-4 py-2 text-gray-700">{{ $total }}</td>
                                    @endforeach
                                    <td class="border px-4 py-2 font-semibold text-indigo-700">
                                        {{ $filatalle['total_taller'] }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-indigo-100">
                                <td colspan="{{ 1 + collect($tablaFinalTalleres->first()['totales'])->count() }}"
                                    class="px-4 py-2 text-right font-bold text-indigo-800">
                                    Total:
                                </td>
                                <td class="px-4 py-2 font-bold text-indigo-800">
                                    {{ number_format($tablaFinalTalleres->sum('total_taller'), 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Tabla Inspectores-->
            @if (!empty($tablaFinalInspectores) && $tablaFinalInspectores->isNotEmpty())
                <div class="text-center mb-2">
                    <h4 class="font-extrabold text-gray-800 uppercase tracking-wide">
                        Deudas de Inspectores
                    </h4>
                    <div class="w-16 h-1 bg-indigo-600 mx-auto mt-2 rounded"></div>
                </div>
                <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                    <table class="table-auto w-full text-sm text-left">
                        <thead class="bg-indigo-500 text-white">
                            <tr>
                                <th class="px-4 py-2 font-medium">Inspector</th>
                                @foreach (array_keys(collect($tablaFinalInspectores->first()['totales'])->toArray()) as $semana)
                                    <th class="px-4 py-2 font-medium">{{ $semana }}</th>
                                @endforeach
                                <th class="px-4 py-2 font-medium">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tablaFinalInspectores as $fila)
                                <tr class="{{ $loop->even ? 'bg-gray-100' : 'bg-white' }}">
                                    <td class="border px-4 py-2 text-gray-700">{{ $fila['inspector'] }}</td>
                                    @foreach ($fila['totales'] as $total)
                                        <td class="border px-4 py-2 text-gray-700">{{ $total }}</td>
                                    @endforeach
                                    <td class="border px-4 py-2 font-semibold text-indigo-700">
                                        {{ $fila['total_inspector'] }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-indigo-100">
                                <td colspan="{{ 1 + collect($tablaFinalInspectores->first()['totales'])->count() }}"
                                    class="px-4 py-2 text-right font-bold text-indigo-800">
                                    Total:
                                </td>
                                <td class="px-4 py-2 font-bold text-indigo-800">
                                    {{ number_format($tablaFinalInspectores->sum('total_inspector'), 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Tabla Contados-->
            @if (!empty($tablaFinalContados) && $tablaFinalContados->isNotEmpty())
                <div class="text-center mb-2">
                    <h4 class="font-extrabold text-gray-800 uppercase tracking-wide">
                        Deudas de Formatos Contado
                    </h4>
                    <div class="w-16 h-1 bg-indigo-600 mx-auto mt-2 rounded"></div>
                </div>
                <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                    <table class="table-auto w-full text-sm text-left">
                        <thead class="bg-indigo-500 text-white">
                            <tr>
                                <th class="px-4 py-2 font-medium">Inspector</th>
                                <th class="px-4 py-2 font-medium">Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tablaFinalContados as $fila)
                                <tr class="{{ $loop->even ? 'bg-gray-100' : 'bg-white' }}">
                                    <td class="border px-4 py-2 text-gray-700">{{ $fila['inspector'] }}</td>
                                    <td class="border px-4 py-2 font-semibold text-indigo-700">
                                        {{ $fila['total_precio'] }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-indigo-100">
                                <td class="px-4 py-2 text-right font-bold text-indigo-800">Total:</td>
                                <td class="px-4 py-2 font-bold text-indigo-800">
                                    {{ number_format($tablaFinalContados->sum('total_precio'), 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-gray-500"></p>
            @endif

            <!-- Cierre -->
            @if ($cierre)
                <div class="text-center mb-2">
                    <h4 class="font-extrabold text-gray-800 uppercase tracking-wide">
                        Cierre de Deudas
                    </h4>
                    <div class="w-16 h-1 bg-indigo-600 mx-auto mt-2 rounded"></div>
                </div>
                <div class="overflow-x-auto bg-white shadow-lg rounded-md mt-6">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-indigo-200 text-indigo-800">
                                <th class="px-4 py-2 text-left">
                                    <div class="flex items-center space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 55.667 55.667"
                                            class="h-6 w-6 fill-current text-indigo-800">
                                            <g>
                                                <path
                                                    d="M52.667,12.333H3c-1.657,0-3,1.007-3,2.25c0,0,0,0.504,0,1.125s1.343,1.125,3,1.125h49.667c1.657,0,3-0.503,3-1.124 c0-0.331,0-0.716,0-1.126C55.667,13.34,54.324,12.333,52.667,12.333z">
                                                </path>
                                                <path
                                                    d="M0,26.5v13.833c0,1.657,1.343,3,3,3h49.667c1.657,0,3-1.343,3-3V26.5c0-1.657-1.343-3-3-3H3C1.343,23.5,0,24.843,0,26.5z M16.708,32h-13c-0.829,0-1.5-0.671-1.5-1.5s0.671-1.5,1.5-1.5h13c0.829,0,1.5,0.671,1.5,1.5C18.208,31.329,17.537,32,16.708,32z M34.375,32h-13c-0.829,0-1.5-0.671-1.5-1.5s0.671-1.5,1.5-1.5h13c0.829,0,1.5,0.671,1.5,1.5C35.875,31.329,35.204,32,34.375,32z M48.875,40.167h-6.167c-1.657,0-3-1.157-3-2.583s1.343-2.583,3-2.583h6.167c1.657,0,3,1.157,3,2.583S50.532,40.167,48.875,40.167z M52.041,32h-13c-0.829,0-1.5-0.671-1.5-1.5s0.671-1.5,1.5-1.5h13c0.829,0,1.5,0.671,1.5,1.5C53.541,31.329,52.87,32,52.041,32z">
                                                </path>
                                            </g>
                                        </svg>
                                        <span class="font-bold">CIERRE</span>
                                    </div>
                                </th>
                                <th class="px-4 py-2 text-right font-bold text-indigo-800">S/{{ number_format($cierre, 2) }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @else
                <p class="text-center text-gray-500"></p>
            @endif
        </div>
    </div>

    @push('js')
        <script>
            Livewire.on('exportaData', () => {
                // Obtener los datos de la tabla
                let data = document.getElementById('data_1').outerHTML;

                // Emitir el evento exportarPdf con los datos de las tablas
                Livewire.emit('exportarPdf', data);
            });
        </script>
    @endpush
</div>


<!-- Tabla Contados-->
{{-- 
        @if ($tablaFinalContados)
            <div class="overflow-x-auto bg-white shadow-lg rounded-md mt-6">
                <table class="table-auto w-full border border-gray-200">
                    <thead class="bg-indigo-400 text-white">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium">Formatos al Contado</th>
                            @foreach (array_keys($tablaFinalContados->first()['totales']->toArray()) as $semana)
                                <th class="px-4 py-2 text-left font-medium">{{ $semana }}</th>
                            @endforeach
                            <th class="px-4 py-2 text-left font-medium">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tablaFinalContados as $fila)
                            <tr class="{{ $loop->even ? 'bg-gray-100' : 'bg-white' }}">
                                <td class="border px-4 py-2 text-gray-700">{{ $fila['inspector'] }}</td>
                                @foreach ($fila['totales'] as $total)
                                    <td class="border px-4 py-2 text-gray-700">{{ $total }}</td>
                                @endforeach
                                <td class="border px-4 py-2 text-gray-700">{{ $fila['total_inspector'] }}</td>
                            </tr>
                        @endforeach
                        <tr class="dark:border-neutral-500 bg-indigo-200">
                            <td colspan="{{ 1 + $tablaFinalContados->first()['totales']->count() }}"
                                class="px-4 py-2 dark:border-neutral-500 font-bold text-right">
                                Total: 
                            </td>
                            <td class="px-4 py-2 dark:border-neutral-500 font-bold">
                                {{ number_format($tablaFinalContados->sum('total_inspector'), 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
--}}
