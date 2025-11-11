<div>
    <div class="sm:px-6 w-full pt-12 pb-4">
        <div class="w-full pt-3">
            <div class="w-full pt-2 mt-2 px-4 text-center mx-auto">
                <h1 class="text-2xl text-indigo-500 font-bold italic text-center py-8">
                    <span class="text-none">📍</span>ACTUALIZAR CERTIFICADO A EXTERNO
                </h1>
                {{-- Filtros de búsqueda --}}
                <div class="w-full items-center md:flex md:justify-center md:space-x-2">
                    <div class="flex bg-white items-center p-2 rounded-md mb-4 md:space-x-2">
                        {{-- Select para cambiar tipo de búsqueda --}}
                        <div class="flex items-center space-x-2 mr-4">
                            <x-jet-label value="Buscar por:" />
                            <select wire:model="tipoBusqueda" id="tipoBusqueda"
                                class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 truncate">
                                <option value="placa">Placa</option>
                                <option value="numSerie">Número de Serie</option>
                                <option value="ubicacion">Ubicacion Chip</option>
                            </select>
                            <x-jet-input-error for="tipoBusqueda" />
                        </div>
                        <!-- Input para busqueda -->
                        <div>
                            <input class="bg-gray-50 outline-none block rounded-md border-indigo-500 w-full"
                                wire:model="valorBusqueda"
                                placeholder="{{ $tipoBusqueda === 'numSerie'
                                    ? 'Número de Serie...'
                                    : ($tipoBusqueda === 'ubicacion'
                                        ? 'Ubicación Chip...'
                                        : 'Placa...') }}">
                            <x-jet-input-error for="valorBusqueda" />
                        </div>
                        <!-- Select de Inspector -->
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
                        <!-- Select de Taller -->
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
                                                <th scope="col" class="border-r px-6 py-4">NumSerie</th>
                                                <th scope="col" class="border-r px-6 py-4">Ubicación</th>
                                                <th scope="col" class="border-r px-6 py-4">Externo</th>
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
                                                        {{ $cert->Vehiculo->placa ?? 'N.A' }}
                                                    </td>
                                                    <td class="whitespace-nowrap border-r px-6 py-4">
                                                        {{ $cert->Hoja->numSerie ?? '-' }}
                                                    </td>                                                    
                                                    <td class="whitespace-nowrap border-r px-6 py-4">
                                                        {{ $cert->UbicacionHoja ?? '-' }}
                                                    </td>
                                                    <td >
                                                        <div class="flex items-center justify-center">
                                                            @if ($cert->externo == 1)
                                                                <i class="far fa-check-circle fa-lg" style="color: forestgreen;"></i>
                                                            @else
                                                                <i class="far fa-times-circle fa-lg" style="color: red;"></i>
                                                            @endif
                                                        </div>
                                                    </td>
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
    </div>

    {{-- MODAL PARA ACTUALIZAR EXTERNO --}}
    <x-jet-dialog-modal wire:model="openEdit" wire:loading.attr="disabled">
        <x-slot name="title" class="font-bold">
            <h1 class="text-xl font-bold">Actualizar Externos</h1>
        </x-slot>
        <x-slot name="content">
            <div class="w-full">
                <x-jet-label value="¿Es un certificado externo?" class="mb-2"/>
                <select wire:model="externo" class="w-full rounded-md border-gray-300">
                    <option value="">Seleccione una opción</option>
                    <option value="1">Marcar como Externo ✅</option>
                    <option value="0">Marcar como Interno ❌</option>
                </select>                
                <x-jet-input-error for="externo" />
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
