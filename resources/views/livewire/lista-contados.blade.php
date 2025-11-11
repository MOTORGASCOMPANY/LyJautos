<div class="bg-gray py-4 md:py-7 px-4 md:px-8 xl:px-10">
    <div class="mt-7 overflow-x-auto">
        <div class=" items-center md:block sm:block">
            <div class="px-2 w-64 mb-4 md:w-full">
                <h2 class="text-indigo-600 font-bold text-3xl uppercase">
                    <i class="fa-solid fa-newspaper"></i>
                    &nbsp;Formatos al contado
                </h2>
            </div>
            <div class="w-full items-center md:flex  md:justify-between">
                <div class="flex bg-gray-50 items-center p-2 rounded-md mb-4">
                    <span>Mostrar</span>
                    <select wire:model="cant"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block ">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>Entradas</span>
                </div>
                <!-- Nuevo campo para buscar -->
                <div class="flex bg-gray-50 items-center lg:w-3/6 p-2 rounded-md mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                            clip-rule="evenodd" />
                    </svg>
                    <input class="bg-gray-50 outline-none block rounded-md border-indigo-500 w-full" type="text"
                        wire:model="search" placeholder="buscar...">
                </div>
                <!--NUevos campo desde , hasta para filtrar por created_at -->
                <div class="flex bg-white items-center p-2 w-48 rounded-md mb-4">
                    <span>Desde: </span>
                    <x-date-picker wire:model="fechaInicio" placeholder="Fecha de inicio"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                </div>
                <div class="flex bg-white items-center p-2 w-48 rounded-md mb-4">
                    <span>Hasta: </span>
                    <x-date-picker wire:model="fechaFin" placeholder="Fecha de Fin"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                </div>
                <div class="flex items-center mb-4">
                    <a class="bg-indigo-500 px-6  py-4 rounded-md text-white font-semibold tracking-wide cursor-pointer"
                        href="">
                        Nuevo &nbsp;<i class="fas fa-plus"></i>
                    </a>
                </div>
            </div>
        </div>

        @if (count($contados))
            <table class="w-full whitespace-nowrap">
                <thead class="bg-slate-600 border-b font-bold text-white">
                    <tr>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-5 py-3 text-left"
                            wire:click="order('id')">
                            Id
                            @if ($sort == 'id')
                                @if ($direction == 'asc')
                                    <i class="fas fa-sort-numeric-up-alt float-right mt-0.5"></i>
                                @else
                                    <i class="fas fa-sort-numeric-down-alt float-right mt-0.5"></i>
                                @endif
                            @else
                                <i class="fas fa-sort float-right mt-0.5"></i>
                            @endif
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-5 py-3 text-left">
                            Inspector
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-5 py-3 text-left">
                            Estado
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-5 py-3 text-left">
                            Cantidad
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-5 py-3 text-left">
                            Monto
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-5 py-3 text-left">
                            Pagado
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-5 py-3 text-left"
                            wire:click="order('created_at')">
                            Fecha de creación
                            @if ($sort == 'created_at')
                                @if ($direction == 'asc')
                                    <i class="fas fa-sort-numeric-up-alt float-right mt-0.5"></i>
                                @else
                                    <i class="fas fa-sort-numeric-down-alt float-right mt-0.5"></i>
                                @endif
                            @else
                                <i class="fas fa-sort float-right mt-0.5"></i>
                            @endif
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-5 py-3 text-left">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($contados as $item)
                        <tr tabindex="0"
                            class="focus:outline-none h-16 border border-slate-300 rounded hover:bg-gray-300">
                            <td class="pl-5">
                                <div class="flex items-center">
                                    <p class="text-indigo-900 p-1 bg-indigo-200 rounded-md">
                                        {{ $item->id }}
                                    </p>
                                </div>
                            </td>
                            <td class="pl-2">
                                <div class="flex items-center">
                                    <p class="text-sm leading-none text-gray-600 ml-2">
                                        {{ $item->salida->usuarioAsignado->name ?? 'NE'}}
                                    </p>
                                </div>
                            </td>
                            @switch($item->estado)
                                @case(0)
                                    <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                        <span
                                            class="inline-flex items-center justify-center w-10 h-10 font-semibold text-red-900 bg-red-200 opacity-50 rounded-full">
                                            <i class="fas fa-times-circle"></i>
                                        </span>
                                    </td>
                                @break

                                @case(1)
                                    <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                        <span
                                            class="inline-flex items-center justify-center w-10 h-10 font-semibold text-green-700 bg-green-100 rounded-full">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                    </td>
                                @break

                                @default
                                    <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                        <span
                                            class="inline-flex items-center justify-center w-10 h-10 font-semibold text-gray-700 bg-gray-100 rounded-full">
                                            <i class="fas fa-question-circle"></i>
                                        </span>
                                    </td>
                            @endswitch
                            <td class="pl-2">
                                <div class="flex items-center">
                                    <p class="text-sm leading-none text-gray-600 ml-2">
                                        {{ $item->cantidad }}
                                    </p>
                                </div>
                            </td>
                            <td class="pl-2">
                                <div class="flex items-center">
                                    <p class="text-sm leading-none text-gray-600 ml-2">
                                        {{ $item->precio }}
                                    </p>
                                </div>
                            </td>
                            <td class="pl-2">
                                <div class="flex items-center">
                                    @switch($item->pagado)
                                        @case(0)
                                            <p class="text-xs rounded-full leading-none p-1 font-bold text-red-700 bg-red-200">
                                                Pendiente
                                            </p>
                                        @break

                                        @case(2)
                                            <p
                                                class="text-xs rounded-full leading-none p-1 font-bold text-green-700 bg-green-200">
                                                Completo
                                            </p>
                                        @break

                                        @default
                                            <p class="text-xs rounded-full leading-none text-gray-600 ml-2">
                                                Sin datos
                                            </p>
                                    @endswitch
                                </div>
                            </td>
                            <td class="pl-2">
                                <div class="flex items-center">
                                    <p class="text-sm leading-none text-gray-600 ml-2">
                                        {{ $item->created_at->format('d-m-Y h:m:i a') }}
                                    </p>
                                </div>
                            </td>
                            <td class="">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('generaPdfContado', ['id' => $item->id]) }}" target="_blank"
                                        class="group flex py-2 px-2 text-center items-center rounded-md bg-orange-300 font-bold text-white cursor-pointer hover:bg-orange-400 hover:animate-pulse">
                                        <i class="fa-solid fa-file-pdf"></i>
                                        <span
                                            class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-100">
                                            Pdf
                                        </span>
                                    </a>
                                    <a href="Contados/{{ $item->id }}" target="_blank"
                                        class="group flex py-2 px-2 text-center items-center rounded-md bg-indigo-300 font-bold text-white cursor-pointer hover:bg-indigo-400 hover:animate-pulse">
                                        <i class="fas fa-folder-plus"></i>
                                        <span
                                            class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-100">
                                            Vaucher
                                        </span>
                                    </a>
                                    <button wire:click="abrirModal({{ $item->id }})"
                                        class="group flex py-2 px-2 text-center items-center rounded-md bg-amber-300 font-bold text-white cursor-pointer hover:bg-amber-400 hover:animate-pulse">
                                        <i class="fas fa-pen"></i>
                                        <span
                                            class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-50">
                                            Editar
                                        </span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($contados->hasPages())
                <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-2 overflow-x-auto">
                    <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
                        <div class="px-5 py-5 bg-white border-t">
                            {{ $contados->links() }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="px-6 py-4 text-center font-bold bg-indigo-200 rounded-md">
                No se encontro ningun registro.
            </div>
        @endif
    </div>

    @if ($contado)
        <x-jet-dialog-modal wire:model="openEdit">
            <x-slot name="title">
                <h1 class="text-xl font-bold">Contado</h1>
            </x-slot>
            <x-slot name="content">

                <div class="grid grid-cols-1 gap-4 py-2">
                    <div>
                        <x-jet-label value="Inspector:" />
                        <x-jet-input type="text"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full"
                            wire:model="nombreInspector" disabled />
                        <x-jet-input-error for="nombreInspector" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 py-2">
                    <div>
                        <x-jet-label value="Precio:" />
                        <x-jet-input type="number"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full"
                            wire:model="contado.precio" />
                        <x-jet-input-error for="contado.precio" />
                    </div>
                    <div>
                        <x-jet-label value="Pagado:" />
                        <x-jet-input type="text"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full"
                            value="{{ $contado->pagado == 0 ? 'Pendiente' : ($contado->pagado == 2 ? 'Completo' : 'Desconocido') }}"
                            disabled />
                        <x-jet-input-error for="contado.pagado" />
                    </div>
                </div>
                <div>
                    <x-jet-label value="Observación:" />
                    <x-textarea
                        class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        wire:model="contado.observacion" style="height: 200px;" />
                    <x-jet-input-error for="contado.observacion" />
                </div>
            </x-slot>
            <x-slot name="footer">
                <x-jet-secondary-button wire:click="$set('openEdit',false)" class="mx-2">
                    Cancelar
                </x-jet-secondary-button>
                <x-jet-button wire:click="editarContado" wire:target="editarContado" wire:loading.attr="disabled">
                    Actualizar
                </x-jet-button>
            </x-slot>
        </x-jet-dialog-modal>
    @endif
</div>
