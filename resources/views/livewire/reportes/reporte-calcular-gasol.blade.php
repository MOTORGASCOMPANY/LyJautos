<div>
    <div class="sm:px-6 w-full pt-12 pb-4">
        <div class="bg-gray-200  px-8 py-4 rounded-xl w-full ">
            <div class=" items-center md:block sm:block">
                <div class="p-2 w-64 my-4 md:w-full">
                    <h2 class="text-indigo-600 font-bold text-3xl">
                        <i class="fa-solid fa-square-poll-vertical fa-xl"></i>
                        &nbsp;REPORTE MTG COMPLETO
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

                    <button wire:click="reportes()"
                        class="bg-green-400 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                        <p class="truncate"> Generar reporte </p>
                    </button>
                </div>
                <div class="w-auto my-4">
                    <x-jet-input-error for="taller" />
                    <x-jet-input-error for="ins" />
                    <x-jet-input-error for="fechaInicio" />
                    <x-jet-input-error for="fechaFin" />
                </div>
                <div class="w-full text-center font-semibold text-gray-100 p-4 mb-4 border rounded-md bg-indigo-400 shadow-lg"
                    wire:loading>
                    CARGANDO <i class="fa-solid fa-spinner animate-spin"></i>
                </div>
            </div>
        </div>

        {{-- Tabla reporte 1 --}}
        @if ($aux)
            <div wire.model="">
                <div class="m-auto flex justify-center items-center bg-gray-200 rounded-md w-full p-4 mt-4">
                    <button wire:click="$emit('exportaDataExterno')"
                        class="bg-green-400 px-6 py-4 w-1/3 text-sm rounded-md text-sm text-white font-semibold tracking-wide cursor-pointer ">
                        <p class="truncate"><i class="fa-solid fa-file-excel fa-lg"></i> Desc. Excel </p>
                    </button>
                </div>
                <div class="bg-gray-200  px-8 py-4 rounded-xl w-full mt-4">
                    <div class="overflow-x-auto m-auto w-full">
                        <div class="inline-block min-w-full py-2 sm:px-6">
                            <div class="overflow-hidden">
                                <table
                                    class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden"
                                    id='data_1'>
                                    <thead class="font-medium dark:border-neutral-500">
                                        <tr>
                                            <th scope="col"
                                                class="text-center text-indigo-600 text-xl font-bold mb-4"
                                                colspan="7">
                                                {{ 'Reporte Externos ' . $fechaInicio . ' al ' . $fechaFin }}
                                            </th>
                                        </tr>
                                        <tr>
                                            <td colspan="7" style="height: 20px;"></td>
                                        </tr>
                                        <tr class="bg-indigo-200">
                                            <th scope="col" class="border-r px-6 py-4">
                                                #
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Inspector
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Anual Gnv
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Inicial Gnv
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Desmonte
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Duplicado
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Monto
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($aux as $nombre => $servicio)
                                            <tr class="bg-orange-200 hover:bg-orange-300">
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $nombre ?? 'N.A' }}
                                                </td>
                                                @php
                                                    $cants = $this->cuentaServicios($servicio);
                                                    // Sumamos los precios de los servicios del inspector actual
                                                    // Convertimos $servicio en una colección antes de usar sum()
                                                    $totalMontoInspector = collect($servicio)->sum(
                                                        fn($item) => (float) $item['precio'],
                                                    );
                                                @endphp
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $cants['Revisión anual GNV'] ?? 0 }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $cants['Conversión a GNV'] ?? 0 }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $cants['Desmonte de Cilindro'] ?? 0 }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $cants['Duplicado GNV'] ?? 0 }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ number_format($totalMontoInspector, 2) }}
                                                </td>

                                            </tr>
                                        @endforeach
                                        <tr class="bg-green-200">
                                            <td colspan="6" class="border-r px-6 py-3 font-bold text-right">
                                                CIERRE DE EXTERNOS:
                                            </td>
                                            <td class="border-r px-6 py-3 font-bold">
                                                {{ number_format($aux->flatten(1)->sum('precio'), 2) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Tabla reporte 2 --}}
        @if ($asistir)
            <div wire:model="">
                <div class="m-auto flex justify-center items-center bg-gray-200 rounded-md w-full p-4 mt-4">
                    <button wire:click="$emit('exportaDataRsmn')"
                        class="bg-green-400 px-6 py-4 w-1/3 text-sm rounded-md text-sm text-white font-semibold tracking-wide cursor-pointer ">
                        <p class="truncate"><i class="fa-solid fa-file-excel fa-lg"></i> Desc. Excel </p>
                    </button>
                </div>
                <div id='data_2'>
                    <!-- Tabla Talleres -->
                    <div class="bg-gray-200 px-8 py-4 rounded-xl w-full mt-4">                        
                        <!-- Tabla semanales -->
                        <table class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                            <thead class="font-medium dark:border-neutral-500">
                                <tr>
                                    <th scope="col" class="text-center text-indigo-600 text-xl font-bold mb-4"
                                        colspan="7">
                                        {{ 'Resumen Talleres ' . $fechaInicio . ' al ' . $fechaFin }}
                                    </th>
                                </tr>
                                <tr>
                                    <th scope="col" class="text-center text-indigo-600 text-xl font-bold mb-4"
                                        colspan="7">
                                        {{ 'Pagos Semanales ' }}
                                    </th>
                                </tr>
                                <tr class="bg-indigo-200">
                                    <th scope="col" class="border-r px-6 py-4">#</th>
                                    <th scope="col" class="border-r px-6 py-4">TALLERES
                                    </th>
                                    <th scope="col" class="border-r px-6 py-4">ENCARGADOS
                                    </th>
                                    <th scope="col" class="border-r px-6 py-4"></th>
                                    <th scope="col" class="border-r px-6 py-4">FAC O BOLT
                                    </th>
                                    <th scope="col" class="border-r px-6 py-4">
                                        OBSERVACIONES
                                    </th>
                                    <th scope="col" class="border-r px-6 py-4">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($semanales as $data)
                                    <tr class="bg-orange-200 hover:bg-orange-300">
                                        <td class="whitespace-nowrap border-r px-6 py-3">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="whitespace-nowrap border-r px-6 py-3">
                                            {{ $data['taller'] }}
                                        </td>
                                        <td class="whitespace-nowrap border-r px-6 py-3">
                                            {{ $data['encargado'] ?? 'NA' }}
                                        </td>
                                        <td class="whitespace-nowrap border-r px-6 py-3">
                                            TALLER
                                        </td>
                                        <td class="whitespace-nowrap border-r px-6 py-3"></td>
                                        <td class="whitespace-nowrap border-r px-6 py-3"></td>
                                        <td class="whitespace-nowrap border-r px-6 py-3">
                                            {{ number_format($data['total'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-green-200">
                                    <td colspan="6" class="border-r px-6 py-3 font-bold text-right">
                                        Total:
                                    </td>
                                    <td class="border-r px-6 py-3 font-bold">
                                        {{ number_format(collect($semanales)->sum('total'), 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Tabla diarios -->
                        <table class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                            <thead class="font-medium dark:border-neutral-500">
                                <tr>
                                    <th scope="col" class="text-center text-indigo-600 text-xl font-bold mb-4"
                                        colspan="7">
                                        {{ 'Pagos Diarios ' }}
                                    </th>
                                </tr>
                                <tr class="bg-indigo-200">
                                    <th scope="col" class="border-r px-6 py-4">#</th>
                                    <th scope="col" class="border-r px-6 py-4">TALLERES
                                    </th>
                                    <th scope="col" class="border-r px-6 py-4">ENCARGADOS
                                    </th>
                                    <th scope="col" class="border-r px-6 py-4"></th>
                                    <th scope="col" class="border-r px-6 py-4">FAC O BOLT
                                    </th>
                                    <th scope="col" class="border-r px-6 py-4">
                                        OBSERVACIONES</th>
                                    <th scope="col" class="border-r px-6 py-4">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($diarios as $data)
                                    <tr class="bg-orange-200 hover:bg-orange-300">
                                        <td class="whitespace-nowrap border-r px-6 py-3">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="whitespace-nowrap border-r px-6 py-3">
                                            {{ $data['taller'] }}
                                        </td>
                                        <td class="whitespace-nowrap border-r px-6 py-3">
                                            {{ $data['encargado'] ?? 'NA' }}
                                        </td>
                                        <td class="whitespace-nowrap border-r px-6 py-3">
                                            TALLER
                                        </td>
                                        <td class="whitespace-nowrap border-r px-6 py-3"></td>
                                        <td class="whitespace-nowrap border-r px-6 py-3"></td>
                                        <td class="whitespace-nowrap border-r px-6 py-3">
                                            {{ number_format($data['total'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-green-200">
                                    <td colspan="6" class="border-r px-6 py-3 font-bold text-right">
                                        Total:
                                    </td>
                                    <td class="border-r px-6 py-3 font-bold">
                                        {{ number_format(collect($diarios)->sum('total'), 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tabla Contados -->
                    @if (count($tablaFinalContados) > 0)
                        <div class="bg-gray-200 px-8 py-4 rounded-xl w-full mt-4">
                            <table class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                                <thead class="font-medium dark:border-neutral-500">
                                    <tr>
                                        <th scope="col" class="text-center text-indigo-600 text-xl font-bold mb-4"
                                            colspan="7">
                                            {{ 'Resumen Contados ' . $fechaInicio . ' al ' . $fechaFin }}
                                        </th>
                                    </tr>
                                    <tr class="bg-indigo-200">
                                        <th scope="col" class="border-r px-6 py-4">#</th>
                                        <th scope="col" class="border-r px-6 py-4">INSPECTOR</th>
                                        <th scope="col" class="border-r px-6 py-4">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tablaFinalContados as $item)
                                        <tr class="bg-orange-200 hover:bg-orange-300">
                                            <td class="whitespace-nowrap border-r px-6 py-3">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="whitespace-nowrap border-r px-6 py-3">
                                                {{ $item['inspector'] }}
                                            </td>
                                            <td class="whitespace-nowrap border-r px-6 py-3">
                                                {{ $item['total_precio'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-green-200">
                                        <td colspan="2" class="border-r px-6 py-3 font-bold text-right">
                                            Total:
                                        </td>
                                        <td class="border-r px-6 py-3 font-bold">
                                            S/
                                            {{ number_format(collect($tablaFinalContados)->sum('total_precio'), 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Cierre semanal -->
                    <div class="bg-gray-200 shadow-md rounded-xl p-4 w-full text-center mt-2">
                        <p class="text-sm font-bold text-gray-700">Cierre Semanal</p>
                        <p class="text-sm text-gray-500">EXTERNOS =
                            {{ number_format($aux->flatten(1)->sum('precio'), 2) }} +
                            TALLERES =
                            {{ number_format(collect($semanales)->sum('total') + collect($diarios)->sum('total'), 2) }}
                            +
                            CONTADOS = {{ number_format(collect($tablaFinalContados)->sum('total_precio'), 2) }}</p>

                        <p class="text-sm font-bold text-gray-700">
                            Total: S/
                            {{ number_format($aux->flatten(1)->sum('precio') + collect($semanales)->sum('total') + collect($diarios)->sum('total') + collect($tablaFinalContados)->sum('total_precio'), 2) }}
                        </p>
                    </div>
                </div>
            </div>
        @endif




    </div>
    @push('js')
        <script>
            Livewire.on('exportaDataExterno', () => {
                // Obtener los datos de la tabla
                datae = document.getElementById('data_1').innerHTML;
                console.log(datae);
                // Emitir el evento exportarExcel con los datos de la tabla
                Livewire.emit('exportarExcelExterno', datae);
            });
        </script>
        <!--script>
                                        Livewire.on('exportaData', () => {
                                            // Obtener los datos de ambas tablas
                                            const data1 = document.getElementById('data_1').innerHTML;
                                            const data2 = document.getElementById('data_2').innerHTML;

                                            // Crear un objeto para enviar ambos conjuntos de datos
                                            const exportData = {
                                                data1: data1,
                                                data2: data2
                                            };

                                            console.log(exportData);
                                            // Emitir el evento exportarPdf con los datos de ambas tablas
                                            Livewire.emit('exportarExcel', exportData);
                                        });
                                </script-->
        <script>
            Livewire.on('exportaDataRsmn', () => {
                // Obtener los datos de la tabla
                data = document.getElementById('data_2').innerHTML;
                console.log(data);
                // Emitir el evento exportarExcel con los datos de la tabla
                Livewire.emit('exportarExcelRsmn', data);
            });
        </script>
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
