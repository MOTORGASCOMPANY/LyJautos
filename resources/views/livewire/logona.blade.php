<div>
    <div class="sm:px-6 w-full pt-12 pb-4">
        <!-- Filtros y Boton -->
        <div class="bg-gray-200 px-8 py-4 rounded-xl w-full">
            <div class="p-2 w-64 my-4 md:w-full">
                <h2 class="text-indigo-600 font-bold text-3xl">
                    <i class="fa-solid fa-square-poll-vertical fa-xl"></i>
                    &nbsp;REPORTE GENERAL TALLER RESUMEN
                </h2>
            </div>
            <div class="flex flex-wrap items-center space-x-2">
                <!-- Filtro de taller -->
                <div x-data="tallerFilter" class="flex bg-white items-center p-2 rounded-md mb-4">
                    <span class="mr-1">Taller: </span>
                    <div class="relative">
                        <div x-on:click="isOpen = !isOpen" class="cursor-pointer">
                            <input wire:model="taller" type="text" placeholder="Seleccione" readonly
                                class="bg-gray-50 border-indigo-500 rounded-md outline-none px-4 py-2 w-full md:w-80">
                        </div>
                        <div x-show="isOpen" x-on:click.away="isOpen = false"
                            class="absolute z-10 mt-2 bg-white border rounded-md shadow-md max-h-96 overflow-y-auto w-full md:w-80">
                            <input x-model="search" type="text" placeholder="Buscar Taller..."
                                class="w-full px-4 py-2 bg-gray-50 border-indigo-500 rounded-md outline-none">
                            <template x-for="taller in filteredTalleres" :key="taller.id">
                                <label :for="'taller_' + taller.id" class="block px-4 py-2 cursor-pointer">
                                    <input :id="'taller_' + taller.id" type="checkbox" :value="taller.id"
                                        @change="toggleTaller(taller.id)" class="mr-2">
                                    <span x-text="taller.nombre"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>
                <!-- Desde -->
                {{-- 
                <div class="flex bg-white items-center p-2 w-48 rounded-md mb-4 ">
                    <span>Desde: </span>
                    <x-date-picker wire:model="fechaInicio" placeholder="Fecha de inicio"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                </div>
                --}}
                <div class="flex bg-white items-center p-2 w-64 rounded-md mb-4">
                    <span>Desde:</span>
                    <input type="date" wire:model="fechaInicio"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate"
                        placeholder="Fecha de inicio"
                    />
                </div>
                <!-- Hasta -->
                {{--
                <div class="flex bg-white items-center p-2 w-48 rounded-md mb-4 ">
                    <span>Hasta: </span>
                    <x-date-picker wire:model="fechaFin" placeholder="Fecha de Fin"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                </div>
                --}}
                <div class="flex bg-white items-center p-2 w-64 rounded-md mb-4">
                    <span>Hasta:</span>
                    <input type="date" wire:model="fechaFin"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate"
                        placeholder="Fecha de Fin"
                    />
                </div>
                <!-- Botón procesar -->
                <button wire:click="procesar"
                    class="bg-green-400 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                    <p class="truncate"> Generar reporte </p>
                </button>
            </div>
            <div class="w-auto my-4">
                <x-jet-input-error for="taller" />
                <x-jet-input-error for="fechaInicio" />
                <x-jet-input-error for="fechaFin" />
            </div>
            <div class="w-full text-center font-semibold text-gray-100 p-4 mb-4 border rounded-md bg-indigo-400 shadow-lg"
                wire:loading>
                CARGANDO <i class="fa-solid fa-spinner animate-spin"></i>
            </div>
        </div>

        <!-- Tablas resumen -->
        @if ($aux)
            <div wire:model="">
                <div id='data_1'>
                    <h4 class="text-center text-indigo-600 text-xl font-bold mb-4 mt-4">
                        {{ 'Resumen de Talleres ' . $fechaInicio . ' al ' . $fechaFin }}
                    </h4>
                    <!-- Tabla de talleres semanales -->
                    <div class="bg-gray-200 shadow-md rounded-xl w-full mt-4 p-6">
                        <table class="min-w-full border text-center text-sm font-medium rounded-xl overflow-hidden">
                            <thead>
                                <tr>
                                    <th colspan="9"
                                        class="text-center text-indigo-600 text-xl font-bold py-4 bg-indigo-100">
                                        Pagos Semanales
                                    </th>
                                </tr>
                                <tr class="bg-indigo-200 text-gray-800">
                                    <th class="px-4 py-3 border-r">#</th>
                                    <th class="px-4 py-3 border-r">TALLERES</th>
                                    <th class="px-4 py-3 border-r">ENCARGADOS</th>
                                    <th class="px-4 py-3 border-r"></th>
                                    <th class="px-4 py-3 border-r">FAC O BOLT</th>
                                    <th class="px-4 py-3 border-r">OBSERVACIONES</th>
                                    <th class="px-4 py-3 border-r">TOTAL</th>
                                    <!--th class="px-4 py-3 border-r">PAGO</th-->
                                    <th class="px-4 py-3 border-r">BOLETAS</th>
                                    <!--th class="px-4 py-3 border-r">AUDITORIA</th-->
                                    <th class="px-4 py-3">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($semanales as $data)
                                    <tr
                                        class="@if ($data['porcentaje_pagado'] == 100) bg-green-100 hover:bg-green-200
                                                @elseif ($data['porcentaje_pagado'] == 0) bg-red-100 hover:bg-red-200
                                                @else bg-yellow-100 hover:bg-yellow-200 @endif">
                                        <td class="px-4 py-3 border-r">{{ $loop->iteration }}</td>
                                        <td class="px-4 py-3 border-r font-semibold text-gray-700">
                                            {{ $data['taller'] }}</td>
                                        <td class="px-4 py-3 border-r">{{ $data['encargado'] ?? 'NA' }}</td>
                                        <td class="px-4 py-3 border-r">TALLER</td>
                                        <td class="px-4 py-3 border-r"></td>
                                        <td class="px-4 py-3 border-r"></td>
                                        <td class="px-4 py-3 border-r font-medium text-gray-700">
                                            {{ number_format($data['total'], 2) }}
                                        </td>
                                        {{-- 
                                        <td class="px-4 py-3 font-bold @if ($data['porcentaje_pagado'] == 100) text-green-600
                                            @elseif ($data['porcentaje_pagado'] == 0) text-red-600
                                            @else text-yellow-600 @endif"
                                            title="@if ($data['porcentaje_pagado'] == 100) Pagado
                                            @elseif ($data['porcentaje_pagado'] == 0) No Pagado
                                            @else Parcialmente Pagado @endif">
                                            {{ $data['porcentaje_pagado'] }}%
                                        </td>
                                        --}}
                                        {{-- Renderizar boletas si existen --}}
                                        @if (!empty($data['boletas_ids']))
                                            @foreach ($data['boletas_ids'] as $boleta)
                                                <td class="px-4 py-3 border-r">
                                                    <a href="{{ route('generaPdfBoleta', ['id' => $boleta['id']]) }}"
                                                        target="_blank"
                                                        class="group inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-300 text-white font-bold cursor-pointer hover:bg-indigo-400 hover:animate-pulse">
                                                        <i class="fa-solid fa-file-pdf"></i>
                                                    </a>
                                                </td>
                                                {{-- 
                                                @switch($boleta['auditoria'])
                                                    @case(0)
                                                        <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                                            <span
                                                                class="relative inline-block px-3 py-1 font-semibold text-red-900 leading-tight">
                                                                <span aria-hidden
                                                                    class="absolute inset-0 bg-red-200 opacity-50 rounded-full"></span>
                                                                <span class="relative">Por revisar</span>
                                                            </span>
                                                        </td>
                                                    @break

                                                    @case(1)
                                                        <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                                            <span
                                                                class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                                                                <span aria-hidden
                                                                    class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                                                                <span class="relative">Aprobado</span>
                                                            </span>
                                                        </td>
                                                    @break

                                                    @default
                                                        <td class="px-4 py-3 border-r"></td>
                                                @endswitch
                                                --}}
                                            @endforeach
                                        @else
                                            {{-- Si no hay boletas, renderizar celdas vacías --}}
                                            <td class="px-4 py-3 border-r"></td>
                                            <!--td class="px-4 py-3 border-r"></td-->
                                        @endif
                                        <td class="px-4 py-3 border-r">
                                            @if (!empty($data['boletas_ids']))
                                                @foreach ($data['boletas_ids'] as $boleta)
                                                    <div class="flex items-center justify-center">
                                                        <input type="checkbox"
                                                            class="w-5 h-5 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500 focus:ring-2"
                                                            wire:click="toggleAuditoria({{ $boleta['id'] }})"
                                                            {{ $boleta['auditoria'] ? 'checked' : '' }}>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-indigo-100">
                                    <td colspan="8" class="px-4 py-3 text-right font-bold text-gray-700">
                                        Total:
                                    </td>
                                    <td class="px-4 py-3 font-bold text-indigo-700">
                                        S/{{ number_format(collect($semanales)->sum('total'), 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tabla de talleres diarios -->
                    <div class="bg-gray-200 shadow-md rounded-xl w-full mt-4 p-6">
                        <table class="min-w-full border text-center text-sm font-medium rounded-xl overflow-hidden">
                            <thead>
                                <tr>
                                    <th colspan="9"
                                        class="text-center text-indigo-600 text-xl font-bold py-4 bg-indigo-100">
                                        Pagos Diarios
                                    </th>
                                </tr>
                                <tr class="bg-indigo-200 text-gray-800">
                                    <th class="px-4 py-3 border-r">#</th>
                                    <th class="px-4 py-3 border-r">TALLERES</th>
                                    <th class="px-4 py-3 border-r">ENCARGADOS</th>
                                    <th class="px-4 py-3 border-r"></th>
                                    <th class="px-4 py-3 border-r">FAC O BOLT</th>
                                    <th class="px-4 py-3 border-r">OBSERVACIONES</th>
                                    <th class="px-4 py-3 border-r">TOTAL</th>
                                    <!--th class="px-4 py-3 border-r">PAGO</th-->
                                    <th class="px-4 py-3 border-r">BOLETAS</th>
                                    <!--th class="px-4 py-3 border-r">AUDITORIA</th-->
                                    <th class="px-4 py-3">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($diarios as $data)
                                    <tr class="@if ($data['porcentaje_pagado'] == 100) bg-green-100 hover:bg-green-200 
                                                @elseif ($data['porcentaje_pagado'] == 0) bg-red-100 hover:bg-red-200 
                                                @else bg-yellow-100 hover:bg-yellow-200 @endif">

                                        <td class="px-4 py-3 border-r">{{ $loop->iteration }}</td>
                                        <td class="px-4 py-3 border-r font-semibold text-gray-700">{{ $data['taller'] }}</td>
                                        <td class="px-4 py-3 border-r">{{ $data['encargado'] ?? 'NA' }}</td>
                                        <td class="px-4 py-3 border-r">TALLER</td>
                                        <td class="px-4 py-3 border-r"></td>
                                        <td class="px-4 py-3 border-r"></td>
                                        <td class="px-4 py-3 border-r font-medium text-gray-700">{{ number_format($data['total'], 2) }}</td>
                                        {{-- 
                                        <td class="px-4 py-3 font-bold @if ($data['porcentaje_pagado'] == 100) text-green-600
                                                  @elseif ($data['porcentaje_pagado'] == 0) text-red-600
                                                  @else text-yellow-600 @endif"
                                            title="@if ($data['porcentaje_pagado'] == 100) Pagado 
                                                  @elseif ($data['porcentaje_pagado'] == 0) No Pagado 
                                                  @else Parcialmente Pagado @endif">
                                            {{ $data['porcentaje_pagado'] }}%
                                        </td>
                                        --}}
                                        <td class="px-4 py-3 border-r">
                                            @if (!empty($data['boletas_ids']))
                                                @foreach ($data['boletas_ids'] as $boleta)
                                                    <div class="relative group inline-block">
                                                        <a href="{{ route('generaPdfBoleta', ['id' => $boleta['id']]) }}" target="_blank"
                                                            class="group inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-300 text-white font-bold cursor-pointer hover:bg-indigo-600 hover:animate-pulse">
                                                            <i class="fa-solid fa-file-pdf"></i>
                                                        </a>
                                                        <!-- Tooltip -->
                                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block
                                                                    bg-gray-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap shadow-lg">
                                                            Fecha: {{ \Carbon\Carbon::parse($boleta['fechaInicio'])->format('d/m/Y') }}
                                                        </div>
                                                    </div>
                                                    {{-- 
                                                    @switch($boleta['auditoria'])
                                                        @case(0)
                                                            <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                                                <span
                                                                    class="relative inline-block px-3 py-1 font-semibold text-red-900 leading-tight">
                                                                    <span aria-hidden
                                                                        class="absolute inset-0 bg-red-200 opacity-50 rounded-full"></span>
                                                                    <span class="relative">Por revisar</span>
                                                                </span>
                                                            </td>
                                                        @break

                                                        @case(1)
                                                            <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                                                <span
                                                                    class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                                                                    <span aria-hidden
                                                                        class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                                                                    <span class="relative">Aprobado</span>
                                                                </span>
                                                            </td>
                                                        @break

                                                        @default
                                                            <td class="px-4 py-3 border-r"></td>
                                                    @endswitch
                                                    --}}
                                                @endforeach
                                            @else
                                                <td class="px-4 py-3 border-r"></td>
                                                <!--td class="px-4 py-3 border-r"></td-->
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 border-r">
                                            <div class="flex items-center justify-center space-x-2">                                                 
                                                @if (!empty($data['boletas_ids']))
                                                    @foreach ($data['boletas_ids'] as $boleta)                                                         
                                                        <div class="relative group inline-block">
                                                            <input type="checkbox"
                                                                class="w-5 h-5 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500 focus:ring-2"
                                                                wire:click="toggleAuditoria({{ $boleta['id'] }})"
                                                                {{ $boleta['auditoria'] ? 'checked' : '' }}>
                                                            <!-- Tooltip -->
                                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block
                                                                        bg-gray-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap shadow-lg">
                                                                Fecha: {{ \Carbon\Carbon::parse($boleta['fechaInicio'])->format('d/m/Y') }}
                                                            </div>
                                                        </div>
                                                        {{--
                                                        <div class="relative group inline-block">
                                                            <a wire:click="cycleAuditoria({{ $boleta['id'] }})"
                                                            class="group inline-flex items-center justify-center w-8 h-8 bg-white border-gray-300 rounded-full font-bold cursor-pointer hover:bg-gray-200 hover:animate-pulse
                                                                    @if($boleta['auditoria'] == 0)
                                                                    @elseif($boleta['auditoria'] == 1)
                                                                    @elseif($boleta['auditoria'] == 2)
                                                                    @endif">
                                                                @if($boleta['auditoria'] == 0)
                                                                    ⭕
                                                                @elseif($boleta['auditoria'] == 1)
                                                                    ✅
                                                                @elseif($boleta['auditoria'] == 2)
                                                                    ❌
                                                                @else
                                                                    ⚠️
                                                                @endif
                                                            </a>
                                                            <!-- Tooltip -->
                                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block
                                                                        bg-gray-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap shadow-lg">
                                                                Fecha: {{ \Carbon\Carbon::parse($boleta['fechaInicio'])->format('d/m/Y') }}
                                                            </div>
                                                        </div>
                                                        --}}
                                                    @endforeach
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-indigo-100">
                                    <td colspan="8" class="px-4 py-3 text-right font-bold text-gray-700">
                                        Total:
                                    </td>
                                    <td class="px-4 py-3 font-bold text-indigo-700">
                                        S/{{ number_format(collect($diarios)->sum('total'), 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Cierre de talleres -->
                    <div class="bg-gray-200 shadow-md rounded-xl w-full mt-4 p-6 border-b-4 border-indigo-600">
                        <table class="min-w-full border text-center text-sm font-medium rounded-xl overflow-hidden">
                            <tbody>
                                <tr class="bg-indigo-200">
                                    <td colspan="8" class="px-4 py-3 flex justify-between items-center">
                                        <span class="text-left font-bold text-gray-700">
                                            <i class="fas fa-clipboard-list text-indigo-600"></i> Cierre de Talleres
                                        </span>
                                        <span class="font-bold text-indigo-700">
                                            S/{{ number_format(collect($diarios)->sum('total') + collect($semanales)->sum('total'), 2) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('js')
        <script>
            Livewire.on('exportaData', () => {
                // Obtener los datos de la tabla
                data = document.getElementById('data_1').innerHTML;
                console.log(data);
                // Emitir el evento exportarExcel con los datos de la tabla
                Livewire.emit('exportarExcel', data);
            });
        </script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('tallerFilter', () => ({
                    isOpen: false,
                    search: '',
                    talleres: @json($talleres),
                    selectedTalleres: @entangle('taller').defer,
                    get filteredTalleres() {
                        if (this.search === '') {
                            return this.talleres;
                        }
                        return this.talleres.filter(taller =>
                            taller.nombre.toLowerCase().includes(this.search.toLowerCase())
                        );
                    },
                    toggleTaller(id) {
                        if (this.selectedTalleres.includes(id)) {
                            this.selectedTalleres = this.selectedTalleres.filter(tallerId =>
                                tallerId !== id);
                        } else {
                            this.selectedTalleres.push(id);
                        }
                        this.$wire.set('taller', this.selectedTalleres);
                    }
                }));
            });
        </script>
    @endpush
</div>
