<div>
    <div class="sm:px-6 w-full pt-12 pb-4">
        <div class="bg-gray-200  px-8 py-4 rounded-xl w-full ">
            <div class=" items-center md:block sm:block">
                <div class="p-2 w-64 my-4 md:w-full">
                    <h2 class="text-indigo-600 font-bold text-3xl">
                        <i class="fa-solid fa-square-poll-vertical fa-xl"></i>
                        &nbsp;REPORTE EXTERNO
                    </h2>
                </div>
                <div class="flex flex-wrap items-center space-x-2">
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

                    <div x-data="inspectorFilter" class="flex bg-white items-center p-2 rounded-md mb-4">
                        <span class="mr-1">Inspector: </span>
                        <div class="relative">
                            <div x-on:click="isOpen = !isOpen" class="cursor-pointer">
                                <input wire:model="ins" type="text" placeholder="Seleccione" readonly
                                    class="bg-gray-50 border-indigo-500 rounded-md outline-none px-4 py-2 w-full md:w-80">
                            </div>
                            <div x-show="isOpen" x-on:click.away="isOpen = false"
                                class="absolute z-10 mt-2 bg-white border rounded-md shadow-md max-h-96 overflow-y-auto w-full md:w-80">
                                <input x-model="search" type="text" placeholder="Buscar Inspector..."
                                    class="w-full px-4 py-2 bg-gray-50 border-indigo-500 rounded-md outline-none">
                                <template x-for="inspector in filteredInspectores" :key="inspector.id">
                                    <label :for="'inspector_' + inspector.id" class="block px-4 py-2 cursor-pointer">
                                        <input :id="'inspector_' + inspector.id" type="checkbox" :value="inspector.id"
                                            @change="toggleInspector(inspector.id)" class="mr-2">
                                        <span x-text="inspector.name"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        {{-- 
                        <div class="flex bg-white items-center p-2 w-1/2 md:w-48 rounded-md mb-4 ">
                            <span>Desde: </span>
                            <x-date-picker wire:model="fechaInicio" placeholder="Fecha de inicio"
                                class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                        </div>
                        <div class="flex bg-white items-center p-2 w-1/2 md:w-48 rounded-md mb-4 ">
                            <span>Hasta: </span>
                            <x-date-picker wire:model="fechaFin" placeholder="Fecha de Fin"
                                class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                        </div>
                        --}}
                        <div class="flex bg-white items-center p-2 w-1/2 md:w-64 rounded-md mb-4">
                            <span>Desde:</span>
                            <input type="date" 
                                wire:model="fechaInicio"
                                class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate"
                                placeholder="Fecha de inicio"
                            />
                        </div>
                        <div class="flex bg-white items-center p-2 w-1/2 md:w-64 rounded-md mb-4">
                            <span>Hasta:</span>
                            <input type="date" 
                                wire:model="fechaFin"
                                class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate"
                                placeholder="Fecha de Fin"
                            />
                        </div>
                    </div>
                    <button wire:click="procesar()"
                        class="bg-green-400 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                        <p class="truncate"> Generar Reporte </p>
                    </button>
                </div>
                <div class="w-auto my-4">
                    <x-jet-input-error for="fechaInicio" />
                    <x-jet-input-error for="fechaFin" />
                </div>
                <div class="w-full text-center font-semibold text-gray-100 p-4 mb-4 border rounded-md bg-indigo-400 shadow-lg"
                    wire:loading>
                    CARGANDO <i class="fa-solid fa-spinner animate-spin"></i>
                </div>
            </div>
        </div>

        @if (!empty($aux))
            <div wire.model="">
                <h4 class="text-center text-indigo-600 text-xl font-bold mb-4 mt-4">
                    {{ 'Resumen de Externos ' . $fechaInicio . ' al ' . $fechaFin }}
                </h4>
                <div class="bg-gray-200 shadow-md rounded-xl w-full mt-4 p-6">
                    <table class="min-w-full border text-center text-sm font-medium rounded-xl overflow-hidden">
                        <thead>
                            <tr class="bg-indigo-200 text-gray-800">
                                <th class="px-4 py-3 border-r">#</th>
                                <th class="px-4 py-3 border-r">Inspector</th>
                                <th class="px-4 py-3 border-r">Anual Gnv</th>
                                <th class="px-4 py-3 border-r">Inicial Gnv</th>
                                <th class="px-4 py-3 border-r">Desmonte</th>
                                <th class="px-4 py-3 border-r">Duplicado</th>
                                <th class="px-4 py-3 border-r">Monto</th>
                                <!--th class="px-4 py-3 border-r">Pago</th-->
                                <th class="px-4 py-3 border-r">Boletas</th>
                                <!--th class="px-4 py-3 border-r">Auditoria</th-->
                                <th class="px-4 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($aux as $inspector => $items)
                                <tr
                                    class="@if ($porcentajePagados[$inspector]['porcentaje'] == 100) bg-green-100 hover:bg-green-200
                                          @elseif ($porcentajePagados[$inspector]['porcentaje'] == 0) bg-red-100 hover:bg-red-200
                                          @else bg-yellow-100 hover:bg-yellow-200 @endif">
                                    <td class="px-4 py-3 border-r">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 border-r font-semibold text-gray-700">
                                        {{ $inspector ?? 'N.A' }}</td>
                                    <td class="px-4 py-3 border-r">
                                        {{ $cantidades['Revisión anual GNV'][$inspector] ?? 0 }}</td>
                                    <td class="px-4 py-3 border-r">
                                        {{ $cantidades['Conversión a GNV'][$inspector] ?? 0 }}</td>
                                    <td class="px-4 py-3 border-r">
                                        {{ $cantidades['Desmonte de Cilindro'][$inspector] ?? 0 }}</td>
                                    <td class="px-4 py-3 border-r">{{ $cantidades['Duplicado GNV'][$inspector] ?? 0 }}
                                    </td>
                                    <td class="px-4 py-3 border-r font-medium text-gray-700">
                                        {{ number_format($precios[$inspector] ?? 0, 2) }}
                                    </td>
                                    {{-- 
                                    <td class="px-4 py-3 font-bold @if ($porcentajePagados[$inspector]['porcentaje'] == 100) text-green-600
                                                                  @elseif ($porcentajePagados[$inspector]['porcentaje'] == 0) text-red-600
                                                                  @else text-yellow-600 @endif"
                                        title="@if ($porcentajePagados[$inspector]['porcentaje'] == 100) Pagado
                                               @elseif ($porcentajePagados[$inspector]['porcentaje'] == 0) No Pagado
                                               @else Parcialmente Pagado @endif">
                                        {{ number_format($porcentajePagados[$inspector]['porcentaje'] ?? 0) }}%
                                    </td>
                                    --}}
                                    {{-- Columna Boletas --}}
                                    @if (!empty($items['boletas']))
                                        <td class="px-4 py-3 border-r">
                                            @foreach ($items['boletas'] as $boleta)
                                                <a href="{{ route('generaPdfBoleta', ['id' => $boleta['id']]) }}"
                                                    target="_blank"
                                                    class="group inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-300 text-white font-bold cursor-pointer hover:bg-indigo-400 hover:animate-pulse">
                                                    <i class="fa-solid fa-file-pdf"></i>
                                                </a>
                                            @endforeach
                                        </td>
                                    @else
                                        <td class="px-4 py-3 border-r"></td>
                                    @endif
                                    {{-- Columna Auditoria 
                                    @if (!empty($items['boletas']))
                                        @foreach ($items['boletas'] as $boleta)
                                            <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                                @switch($boleta['auditoria'])
                                                    @case(0)
                                                        <span
                                                            class="relative inline-block px-3 py-1 font-semibold text-red-900 leading-tight">
                                                            <span aria-hidden
                                                                class="absolute inset-0 bg-red-200 opacity-50 rounded-full"></span>
                                                            <span class="relative">Por revisar</span>
                                                        </span>
                                                    @break

                                                    @case(1)
                                                        <span
                                                            class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                                                            <span aria-hidden
                                                                class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                                                            <span class="relative">Aprobado</span>
                                                        </span>
                                                    @break

                                                    @default
                                                        <span
                                                            class="relative inline-block px-3 py-1 font-semibold text-gray-900 leading-tight">
                                                            <span aria-hidden
                                                                class="absolute inset-0 bg-gray-200 opacity-50 rounded-full"></span>
                                                            <span class="relative">Sin información</span>
                                                        </span>
                                                @endswitch
                                            </td>
                                        @endforeach
                                    @else
                                        <td class="px-4 py-3 border-r"></td>
                                    @endif
                                    --}}
                                    <td class="px-4 py-3 border-r">
                                        @if (!empty($items['boletas']))
                                            @foreach ($items['boletas'] ?? [] as $boleta)
                                                <div class="flex items-center justify-center">
                                                    <input type="checkbox"
                                                        class="w-5 h-5 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500 focus:ring-2"
                                                        wire:click="toggleAuditoria({{ $boleta['id'] }})"
                                                        @if($boleta['auditoria']) checked @endif>
                                                </div>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-indigo-100">
                                <td colspan="8" class="px-4 py-3 text-right font-bold text-gray-700">
                                    CIERRE DE EXTERNOS:
                                </td>
                                <td class="px-4 py-3 font-bold text-indigo-700">
                                    S/{{ number_format(array_sum($precios), 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    @push('js')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('inspectorFilter', () => ({
                    isOpen: false,
                    search: '',
                    inspectores: @json($inspectores),
                    selectedInspectores: @entangle('ins').defer,
                    get filteredInspectores() {
                        if (this.search === '') {
                            return this.inspectores;
                        }
                        return this.inspectores.filter(inspector =>
                            inspector.name.toLowerCase().includes(this.search.toLowerCase())
                        );
                    },
                    toggleInspector(id) {
                        if (this.selectedInspectores.includes(id)) {
                            this.selectedInspectores = this.selectedInspectores.filter(inspectorId =>
                                inspectorId !== id);
                        } else {
                            this.selectedInspectores.push(id);
                        }
                        this.$wire.set('ins', this.selectedInspectores);
                    }
                }));

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
