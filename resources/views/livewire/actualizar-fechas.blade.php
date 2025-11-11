<div>
    <div class="sm:px-6 w-full pt-12 pb-4">
        {{-- Selección del modelo --}}
        @if (!$modelo)
            <h1 class="text-center text-xl my-4 font-bold text-indigo-500"> LISTA DE MODELOS</h1>
            <div class="max-w-5xl m-auto bg-white rounded-lg shadow-md my-4">
                <div class="bg-indigo-200 rounded-lg py-4 px-2 grid grid-cols-1 gap-8 sm:grid-cols-1">
                    <div class="flex items-center justify-center">
                        <x-jet-label value="MODELO:" class="mr-2" />
                        <select wire:model="modelo"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none ml-1 block w-1/3">
                            <option value="">Seleccione</option>
                            <option value="certificados">Certificados</option>
                            <option value="cer_pendientes">Cert. Pendientes</option>
                        </select>
                        <x-jet-input-error for="modelo" />
                    </div>
                </div>
            </div>
        @endif
        {{-- Mostrar modelo seleccionado --}}
        @if ($modelo)
            <div class="w-full pt-3">
                <div class="w-full pt-2 mt-2 px-4 text-center mx-auto">
                    <h1 class="text-2xl text-indigo-500 font-bold italic text-center py-8">
                        <span class="text-none">📍</span>CONSULTA DE {{ strtoupper(str_replace('_', ' ', $modelo)) }}
                    </h1>

                    {{-- Filtros de búsqueda --}}
                    <div class="w-full items-center md:flex md:justify-center md:space-x-2">
                        <div class="flex bg-white items-center p-2 rounded-md mb-4 md:space-x-2">
                            {{-- Select para cambiar tipo de búsqueda --}}
                            @if ($modelo === 'certificados')
                                <div class="flex items-center space-x-2 mr-4">
                                    <x-jet-label value="Buscar por:"/>
                                    <select wire:model="tipoBusqueda" id="tipoBusqueda"
                                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 truncate">
                                        <option value="placa">Placa</option>
                                        <option value="numSerie">Número de Serie</option>
                                        <option value="ubicacion">Ubicacion Chip</option>
                                    </select>
                                    <x-jet-input-error for="tipoBusqueda" />
                                </div>
                            @endif
                            <div>
                                <input class="bg-gray-50 outline-none block rounded-md border-indigo-500 w-full"
                                    wire:model="valorBusqueda" placeholder="{{ 
                                        $tipoBusqueda === 'numSerie' ? 'Número de Serie...' : 
                                        ($tipoBusqueda === 'ubicacion' ? 'Ubicación Chip...' : 'Placa...') 
                                    }}">
                                <x-jet-input-error for="valorBusqueda" />
                            </div>
                            <div>
                                <select wire:model="inspec"
                                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-80 truncate">
                                    <option value="">Seleccione Inspector</option>
                                    @foreach ($inspectores ?? [] as $inspector)
                                        <option value="{{ $inspector->id }}">{{ $inspector->name }}</option>
                                    @endforeach
                                </select>
                                <x-jet-input-error for="inspec" />
                            </div>
                            <div>
                                <select wire:model="talle"
                                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-80 truncate">
                                    <option value="">Seleccione Taller</option>
                                    @foreach ($talleres ?? [] as $taller)
                                        <option value="{{ $taller->id }}">{{ $taller->nombre }}</option>
                                    @endforeach
                                </select>
                                <x-jet-input-error for="talle" />
                            </div>
                        </div>
                        <div class="md:space-x-2">
                            <button wire:click="buscar"
                                class="bg-indigo-600 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                                <p class="truncate">Realizar Consulta</p>
                            </button>
                            @if ($consultaRealizada)
                                <button wire:click="abrirModal"
                                    class="bg-indigo-600 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                                    <p class="truncate">Update</p>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Mensaje de carga --}}
                    <div class="w-full text-center font-semibold text-gray-100 p-4 mb-4 border rounded-md bg-indigo-400 shadow-lg"
                        wire:loading wire:target="buscar">
                        CARGANDO <i class="fa-solid fa-spinner animate-spin"></i>
                    </div>

                    {{-- Tabla de resultados --}}
                    @if (count($resultados) > 0)
                        <div class="flex flex-col my-4 py-4 rounded-md bg-white px-4 justify-center">
                            <div class="overflow-x-auto m-auto w-full">
                                <div class="inline-block min-w-full py-2 sm:px-6">
                                    <div class="overflow-hidden">
                                        <table class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                                            <thead class="font-medium dark:border-neutral-500">
                                                <tr class="bg-indigo-200">
                                                    <th scope="col" class="border-r px-6 py-4">Inspector</th>
                                                    <th scope="col" class="border-r px-6 py-4">Taller</th>
                                                    <th scope="col" class="border-r px-6 py-4">Servicio</th>
                                                    <th scope="col" class="border-r px-6 py-4">Placa</th>
                                                    @if ($modelo === 'certificados')
                                                        <th scope="col" class="border-r px-6 py-4">NumSerie</th>
                                                        <th scope="col" class="border-r px-6 py-4">Ubicación</th>
                                                    @endif
                                                    <th scope="col" lass="border-r px-6 py-4">Fecha</th>
                                                    <th scope="col" class="border-r px-6 py-4">✔</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($resultados as $cert)
                                                    <tr class="bg-orange-200 hover:bg-orange-300">
                                                        <td class="whitespace-nowrap border-r px-6 py-4">
                                                            {{ $cert->Inspector->name ?? null }}
                                                        </td>
                                                        <td class="whitespace-nowrap border-r px-6 py-4">
                                                            {{ $cert->Taller->nombre ?? null }}
                                                        </td>
                                                        <td class="whitespace-nowrap border-r px-6 py-4">
                                                            {{ $cert->Servicio->tipoServicio->descripcion ?? null }}
                                                        </td>
                                                        <td class="whitespace-nowrap border-r px-6 py-4">
                                                            {{ $cert->Vehiculo->placa ?? 'N.A'}}
                                                        </td>
                                                        @if ($modelo === 'certificados')
                                                            <td class="whitespace-nowrap border-r px-6 py-4">
                                                                {{ $cert->Hoja->numSerie ?? '-' }}
                                                            </td>
                                                            <td class="whitespace-nowrap border-r px-6 py-4">
                                                                {{ $cert->UbicacionHoja ?? '-' }}
                                                            </td>
                                                        @endif
                                                        <td class="whitespace-nowrap border-r px-6 py-4">
                                                            {{ $cert->created_at ?? null }}
                                                        </td>
                                                        <td class="whitespace-nowrap border-r px-6 py-4">
                                                            <input type="checkbox" wire:model="seleccionados"
                                                                value="{{ $cert->id }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div
                            class="w-full text-center font-semibold text-gray-700 p-4 mt-4 border rounded-md bg-indigo-200 shadow-lg">
                            No se encontraron resultados para la búsqueda.
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>

    {{-- MODAL PARA ACTUALIZAR FECHA DEPENDIENDO DEL MODELO --}}
    <x-jet-dialog-modal wire:model="openEdit" wire:loading.attr="disabled">
        <x-slot name="title" class="font-bold">
            <h1 class="text-xl font-bold">Actualizar Fecha</h1>
        </x-slot>
        <x-slot name="content">
            <div class="flex items-center p-2 w-96 rounded-md mb-4 ">
                <span>Nueva Fecha: </span>
                <x-date-picker wire:model="created_at" placeholder="Seleccionar Fecha..."
                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('openEdit',false)" class="mx-2">
                Cancelar
            </x-jet-secondary-button>
            <x-jet-button wire:click="actualizar" wire:loading.attr="disabled" wire:target="update">
                Actualizar
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
