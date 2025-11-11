<div>
    <div class="sm:px-6 w-full pt-12 pb-4">
        <div class="bg-gray-200 px-8 py-4 rounded-xl w-full">
            <div class="p-2 my-4">
                <h2 class="text-indigo-600 font-bold text-3xl">
                    <i class="fa-solid fa-square-poll-vertical fa-xl"></i>
                    &nbsp;REPORTE DEBEN ACTUALIZAR
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
                <div class="flex bg-gray-50 items-center p-2 rounded-md mb-4 ">
                    <span class="mr-1">Servicio: </span>
                    <select wire:model="servicio"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate">
                        <option value="">SELECCIONE</option>
                        @isset($tipos)
                            @foreach ($tipos as $tipo)
                                <option class="" value="{{ $tipo->id }}">{{ $tipo->descripcion }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>
                <div class="flex bg-white items-center p-2 w-48 rounded-md mb-4 ">
                    <span>Desde: </span>
                    <x-date-picker wire:model="fechaInicio" placeholder="Fecha de inicio"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                </div>
                <div class="flex bg-white items-center p-2 w-48 rounded-md mb-4 ">
                    <span>Hasta: </span>
                    <x-date-picker wire:model="fechaFin" placeholder="Fecha de Fin"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                </div>
                <button wire:click="procesar()"
                    class="bg-green-400 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                    <p class="truncate"> Generar reporte </p>
                </button>
                <button wire:click="actualizarPagado()"
                    class="bg-blue-400 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                    <p class="truncate"> Actualizar </p>
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

        {{-- TABLA  ACTUALIZAR DEBEN  --}}
        @if (isset($certificaciones))
            <div wire.model="">
                <div class="bg-gray-200  px-8 py-4 rounded-xl w-full mt-4">
                    <div class="overflow-x-auto m-auto w-full">
                        <div class="inline-block min-w-full py-2 sm:px-6">
                            <div class="overflow-hidden">
                                <table
                                    class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                                    <thead class="font-medium dark:border-neutral-500">
                                        <tr class="bg-indigo-200">
                                            <th scope="col" class="border-r px-6 py-4">#</th>
                                            <th scope="col" class="border-r px-6 py-4">ID</th>
                                            <th scope="col" class="border-r px-6 py-4">Taller</th>
                                            <th scope="col" class="border-r px-6 py-4">Inspector</th>
                                            <th scope="col" class="border-r px-6 py-4">Hoja</th>
                                            <th scope="col" class="border-r px-6 py-4">Vehículo</th>
                                            <th scope="col" class="border-r px-6 py-4">Servicio</th>
                                            <th scope="col" class="border-r px-6 py-4">Fecha</th>
                                            <th scope="col" class="border-r px-6 py-4">Updated_at</th>
                                            <th scope="col" class="border-r px-6 py-4">Precio</th>
                                            <th class="px-6 py-4">
                                                <input type="checkbox" wire:model="selectAll">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($certificaciones as $key => $data)
                                            <tr class="bg-orange-200 hover:bg-orange-300">
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $key + 1 }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $data['id'] }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $data['taller'] ?? 'N.A' }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $data['inspector'] ?? 'N.A' }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $data['num_hoja'] ?? 'N.A' }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    @if ($data['servicio'] == 'Chip por deterioro')
                                                        @php
                                                            $ubicacionParts = explode('/', $data['ubi_hoja']);
                                                            $secondPart = isset($ubicacionParts[1])
                                                                ? trim($ubicacionParts[1])
                                                                : 'N.A';
                                                            echo $secondPart;
                                                        @endphp
                                                    @else
                                                        {{ $data['placa'] ?? 'En tramite' }}
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $data['servicio'] ?? 'N.E' }}</td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $data['fecha'] ?? 'S.F' }}</td>
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $data['updated_at'] ?? 'S.F' }}</td>
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-3">
                                                    {{ $data['precio'] ?? 'S.P' }}
                                                </td>
                                                <td>
                                                    <input type="checkbox" wire:model="selectedRows"
                                                        value="{{ $data['id'] }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-green-200">
                                            <td colspan="10" class="border-r px-6 py-3 font-bold text-right">
                                                Total:
                                            </td>
                                            <td class="border-r px-6 py-3 font-bold">
                                                {{ number_format($certificaciones->sum('precio'), 2) }}
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
    </div>

    {{-- JS --}}
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
