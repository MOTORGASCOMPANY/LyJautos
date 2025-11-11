<div class="bg-gray py-4 md:py-7 px-4 md:px-8 xl:px-10">
    <div class="mt-7 overflow-x-auto">
        <div class=" items-center md:block sm:block">
            <div class="px-2 w-64 mb-4 md:w-full">
                <h2 class="text-indigo-500 font-bold text-3xl">Registro de Estado de cuenta y Comprobantes</h2>
            </div>

            <div class="w-full items-center md:flex  md:justify-between">
                <!-- Cantidas de filas -->
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
                <!-- Search -->
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
            </div>

            <div class="w-full items-center md:flex  md:justify-between">
                <!-- Taller -->
                <div class="flex bg-white items-center p-2 rounded-md mb-4 ">
                    <span>Taller: </span>
                    <select wire:model="ta"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate">
                        <option value="">SELECCIONE</option>
                        @isset($talleres)
                            @foreach ($talleres as $taller)
                                <option class="" value="{{ $taller->id }}">{{ $taller->nombre }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                </div>
                <!-- Inspector -->
                <div class="flex bg-white items-center p-2 rounded-md mb-4 ">
                    <span>Inspector: </span>
                    <select wire:model="ins"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate">
                        <option value="">SELECCIONE</option>
                        @isset($inspectores)
                            @foreach ($inspectores as $inspector)
                                <option value="{{ $inspector->id }}">{{ $inspector->name }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>
                <!-- Fecha desde -->
                <div class="flex bg-white items-center p-2 w-48 rounded-md mb-4 ">
                    <span>Desde: </span>
                    <x-date-picker wire:model="fechaInicio" placeholder="Fecha de inicio"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                </div>
                <!-- Fecha hasta -->
                <div class="flex bg-white items-center p-2 w-48 rounded-md mb-4 ">
                    <span>Hasta: </span>
                    <x-date-picker wire:model="fechaFin" placeholder="Fecha de Fin"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                </div>
                {{-- 
                @hasanyrole('administrador|Administrador del sistema')
                    <button wire:click="agregar"
                        class="bg-indigo-600 px-6 py-2 rounded-md text-white font-semibold tracking-wide cursor-pointer">
                        Importar
                    </button>
                @endhasanyrole
                --}}
            </div>
        </div>

        @if ($boletas->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border border-gray-300 rounded-xl overflow-hidden">
                    <thead class="bg-slate-600 border-b font-bold text-white">
                        <tr>
                            <th scope="col" class="w-10 text-sm font-medium font-semibold text-white px-2 py-3 text-center cursor-pointer"
                                wire:click="order('id')">
                                ID
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
                            <th scope="col" class="w-16 text-sm font-medium font-semibold text-white px-2 py-3 text-left">
                                #
                            </th>
                            <th scope="col" class="text-sm font-medium font-semibold text-white px-3 py-3 text-left w-64">
                                Taller / Inspector
                            </th>
                            <th scope="col" class="w-20 text-sm font-medium font-semibold text-white px-3 py-3 text-left">
                                F.Inicio
                            </th>
                            <th scope="col" class="w-20 text-sm font-medium font-semibold text-white px-3 py-3 text-left">
                                F. Fin
                            </th>
                            <th scope="col" class="w-16 text-sm font-medium font-semibold text-white px-2 py-3 text-center">
                                Anual
                            </th>
                            <th scope="col" class="w-16 text-sm font-medium font-semibold text-white px-2 py-3 text-center">
                                Inicial
                            </th>
                            <th scope="col" class="w-16 text-sm font-medium font-semibold text-white px-2 py-3 text-center">
                                Desmnte
                            </th>
                            <th scope="col" class="w-16 text-sm font-medium font-semibold text-white px-2 py-3 text-center">
                                Duplido
                            </th>
                            <th scope="col" class="w-20 text-sm font-medium font-semibold text-white px-3 py-3 text-left">
                                Monto
                            </th>
                            @hasanyrole('administrador|Administrador del sistema')
                                <th scope="col" class="w-16 text-sm font-medium font-semibold text-white px-2 py-3 text-center">
                                    Estado
                                </th>
                            @endhasanyrole
                            <th scope="col" class="w-20 text-sm font-medium font-semibold text-white px-3 py-3 text-center">
                                Auditoria
                            </th>
                            <th scope="col" class="w-10 text-sm font-medium font-semibold text-white px-1 py-3 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($boletas as $bol)
                            <tr tabindex="0" class="focus:outline-none h-14 hover:bg-gray-200 transition duration-150 ease-in-out">
                                {{-- 
                                    <td class="pl-5">
                                        <div class="flex items-center">
                                            <p class="text-indigo-900 p-1 bg-indigo-200 rounded-md">
                                                {{ $bol->id }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="pl-2">
                                        <div class="flex items-center">
                                            <p class="text-sm leading-none text-gray-600 ml-2">
                                                {{ $bol->identificador ?? null }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="pl-2">
                                        <div class="flex items-center">
                                            <p class="text-sm leading-none text-gray-600 ml-2">
                                                @if ($bol->Certificador == null || ($bol->Taller && $bol->Certificador))
                                                    {{ $bol->Taller->nombre ?? 'NE' }}
                                                @else
                                                    {{ $bol->Certificador->name ?? 'NE' }}
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center">
                                            <p class="text-sm leading-none text-gray-600 ml-2">
                                                {{ \Carbon\Carbon::parse($bol->fechaInicio)->format('d-m-Y') }}
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center">
                                            <p class="text-sm leading-none text-gray-600 ml-2">
                                                {{ \Carbon\Carbon::parse($bol->fechaFin)->format('d-m-Y') }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="pl-10">
                                        <div class="flex items-center ">
                                            <p class="text-sm leading-none text-gray-600 ml-2">
                                                {{ $bol->anual ?? '0' }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="pl-10">
                                        <div class="flex items-center ">
                                            <p class="text-sm leading-none text-gray-600 ml-2">
                                                {{ $bol->inicial ?? '0' }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="pl-10">
                                        <div class="flex items-center ">
                                            <p class="text-sm leading-none text-gray-600 ml-2">
                                                {{ $bol->desmonte ?? '0' }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="pl-10">
                                        <div class="flex items-center ">
                                            <p class="text-sm leading-none text-gray-600 ml-2">
                                                {{ $bol->duplicado ?? '0' }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="pl-5">
                                        <div class="flex items-center">
                                            <p class="text-sm leading-none text-gray-600 ml-2">
                                                {{ $bol->monto }}
                                            </p>
                                        </div>
                                    </td>
                                    @hasanyrole('administrador|Administrador del sistema')
                                        @switch($bol->estado)
                                            @case(0)
                                                <td class="px-5 py-5 border-b border-gray-200 text-sm text-center">
                                                    <span
                                                        class="inline-flex items-center justify-center w-10 h-10 font-semibold text-red-900 bg-red-200 opacity-50 rounded-full">
                                                        <i class="fas fa-times-circle"></i>
                                                    </span>
                                                </td>
                                            @break

                                            @case(1)
                                                <td class="px-5 py-5 border-b border-gray-200 text-sm text-center">
                                                    <span
                                                        class="inline-flex items-center justify-center w-10 h-10 font-semibold text-green-700 bg-green-100 rounded-full">
                                                        <i class="fas fa-check-circle"></i>
                                                    </span>
                                                </td>
                                            @break

                                            @default
                                                <td class="px-5 py-5 border-b border-gray-200 text-sm text-center">
                                                    <span
                                                        class="inline-flex items-center justify-center w-10 h-10 font-semibold text-gray-700 bg-gray-100 rounded-full">
                                                        <i class="fas fa-question-circle"></i>
                                                    </span>
                                                </td>
                                        @endswitch
                                    @endhasanyrole
                                    @switch($bol->auditoria)
                                        @case(0)
                                            <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                                <span class="relative inline-block px-3 py-1 font-semibold text-red-900 leading-tight">
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
                                    @endswitch                                    
                                --}}
                                <td class="px-2 py-2 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2 py-2 text-xs font-semibold rounded-md bg-indigo-200 text-indigo-900">
                                        {{ $bol->id }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ $bol->identificador ?? null }}
                                </td>
                                <td class="px-3 py-2 whitespace-normal break-words text-sm text-gray-700 w-64 max-w-xs">
                                    @if ($bol->Certificador == null || ($bol->Taller && $bol->Certificador))
                                        {{ $bol->Taller->nombre ?? 'NE' }}
                                    @else
                                        {{ $bol->Certificador->name ?? 'NE' }}
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($bol->fechaInicio)->format('d-m-Y') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($bol->fechaFin)->format('d-m-Y') }}
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ $bol->anual ?? '0' }}
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ $bol->inicial ?? '0' }}
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ $bol->desmonte ?? '0' }}
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ $bol->duplicado ?? '0' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                    S/. {{ number_format($bol->monto, 2) }}
                                </td>

                                @hasanyrole('administrador|Administrador del sistema')
                                    @switch($bol->estado)
                                        @case(0)
                                            <td class="px-2 py-2 whitespace-nowrap text-center text-sm">
                                                <span class="inline-flex items-center justify-center w-8 h-8 font-semibold text-red-700 bg-red-100 rounded-full" title="Anulado">
                                                    <i class="fas fa-times-circle"></i>
                                                </span>
                                            </td>
                                            @break
                                        @case(1)
                                            <td class="px-2 py-2 whitespace-nowrap text-center text-sm">
                                                <span class="inline-flex items-center justify-center w-8 h-8 font-semibold text-green-700 bg-green-100 rounded-full" title="Activo">
                                                    <i class="fas fa-check-circle"></i>
                                                </span>
                                            </td>
                                            @break
                                        @default
                                            <td class="px-2 py-2 whitespace-nowrap text-center text-sm">
                                                <span class="inline-flex items-center justify-center w-8 h-8 font-semibold text-gray-700 bg-gray-100 rounded-full" title="Desconocido">
                                                    <i class="fas fa-question-circle"></i>
                                                </span>
                                            </td>
                                    @endswitch
                                @endhasanyrole

                                @switch($bol->auditoria)
                                    @case(0)
                                        <td class="px-3 py-2 whitespace-nowrap text-sm">
                                            <span class="px-3 py-1 text-xs font-semibold leading-tight text-red-900 bg-red-200 rounded-full">Por revisar</span>
                                        </td>
                                        @break
                                    @case(1)
                                        <td class="px-3 py-2 whitespace-nowrap text-sm">
                                            <span class="px-3 py-1 text-xs font-semibold leading-tight text-green-900 bg-green-200 rounded-full">Aprobado</span>
                                        </td>
                                        @break
                                    @default
                                        <td class="px-3 py-2 whitespace-nowrap text-sm">
                                            <span class="px-3 py-1 text-xs font-semibold leading-tight text-gray-900 bg-gray-200 rounded-full">N/A</span>
                                        </td>
                                @endswitch
                                
                                <td class="px-1 py-2 text-center whitespace-nowrap">
                                    <div x-data="{ open: false }" @click.away="open = false">
                                        <button @click="open = ! open" class="inline-flex items-center p-2 text-gray-500 hover:bg-gray-200 rounded-full focus:outline-none">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>

                                        <div x-show="open"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute right-0 mt-2 w-48 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-20 origin-top-right">

                                            <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                                                <!-- generar pdf -->
                                                <a href="{{ route('generaPdfBoleta', ['id' => $bol->id]) }}" target="_blank"
                                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                                                    <i class="fa-solid fa-file-pdf mr-2 w-4"></i> Generar PDF
                                                </a>
                                                <!-- actualizar auditoria -->
                                                @hasanyrole('auditoria|Administrador del sistema')
                                                    <div class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 cursor-pointer">
                                                        
                                                        <label for="auditoria-{{ $bol->id }}" class="flex items-center w-full cursor-pointer">
                                                            <i class="fa-solid fa-check-to-slot mr-2 w-4"></i> <span>Auditoría</span>
                                                        </label>

                                                        <x-jet-input id="auditoria-{{ $bol->id }}" type="checkbox" wire:model="auditoria.{{ $bol->id }}" 
                                                            class="ml-auto w-5 h-5 border-indigo-500 text-indigo-600 focus:ring-indigo-500" />
                                                    </div>
                                                @endhasanyrole

                                                <button 
                                                    wire:click="$emit('mostrarDetalleBoleta', {{ $bol->id }})"
                                                    @click="open = false"
                                                    class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"
                                                >
                                                    <i class="fas fa-info-circle mr-2 w-4"></i> Detalles
                                                </button>

                                                @hasanyrole('administrador|Administrador del sistema')
                                                    <a href="Boletas/{{ $bol->id }}" target="_blank"
                                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                                                        <i class="fas fa-folder-plus mr-2 w-4"></i> Subir Voucher
                                                    </a>
                                                    <button wire:click="abrirModal({{ $bol->id }})"
                                                            @click="open = false"
                                                            class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                                                        <i class="fas fa-pen mr-2 w-4"></i> Editar Datos
                                                    </button>
                                                    <div class="border-t border-gray-100 my-1"></div>
                                                    <button wire:click="$emit('deleteBoleta',{{ $bol->id }})"
                                                            @click="open = false"
                                                            class="flex items-center w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700">
                                                        <i class="fas fa-times-circle mr-2 w-4"></i> Eliminar
                                                    </button>
                                                @endhasanyrole
                                            </div>
                                        </div>
                                    </div>                                    
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $boletas->links() }}
            </div>
        @else
            <div class="text-center mt-4 text-gray-500">
                <p>No tienes boletas en este momento.</p>
            </div>
        @endif
    </div>

    @if ($boleta)
        <x-jet-dialog-modal wire:model="openEdit">
            <x-slot name="title">
                <h1 class="text-xl font-bold">Editando documento</h1>
            </x-slot>
            <x-slot name="content">
                @hasanyrole('administrador|Administrador del sistema')
                    @if ($boleta->taller == null)
                        <div>
                            <x-jet-label value="Certificador:" />
                            <select wire:model="boleta.certificador"
                                class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full">
                                <option value="">Seleccione Inspector</option>
                                @foreach ($inspectores as $ins)
                                    <option value="{{ $ins->id }}">{{ $ins->name }}</option>
                                @endforeach
                            </select>
                            <x-jet-input-error for="boleta.certificador" />
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-4 py-2">
                            <div>
                                <x-jet-label value="Taller:" />
                                <select wire:model="boleta.taller"
                                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full">
                                    <option value="">Seleccione Taller</option>
                                    @foreach ($talleres as $taller2)
                                        <option value="{{ $taller2->id }}">{{ $taller2->nombre }}</option>
                                    @endforeach
                                </select>
                                <x-jet-input-error for="boleta.taller" />
                            </div>
                            <div>
                                <x-jet-label value="Certificador:" />
                                <select wire:model="boleta.certificador"
                                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full">
                                    <option value="">Seleccione Inspector</option>
                                    @foreach ($inspectores as $ins)
                                        <option value="{{ $ins->id }}">{{ $ins->name }}</option>
                                    @endforeach
                                </select>
                                <x-jet-input-error for="boleta.certificador" />
                            </div>
                        </div>
                    @endif
                @endhasanyrole

                <div class="grid grid-cols-3 gap-4 py-2">
                    <div>
                        <x-jet-label value="Fecha Inicio:" />
                        <x-jet-input type="date"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full"
                            wire:model="boleta.fechaInicio" />
                        <x-jet-input-error for="boleta.fechaInicio" />
                    </div>
                    <div>
                        <x-jet-label value="Fecha Fin:" />
                        <x-jet-input type="date"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full"
                            wire:model="boleta.fechaFin" />
                        <x-jet-input-error for="boleta.fechaFin" />
                    </div>
                    <div>
                        <x-jet-label value="Monto:" />
                        <x-jet-input type="number"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full"
                            wire:model="boleta.monto" />
                        <x-jet-input-error for="boleta.monto" />
                    </div>
                </div>
                <div class="grid grid-cols-4 gap-4 py-2">
                    <div>
                        <x-jet-label value="Anual:" />
                        <x-jet-input type="number" wire:model="boleta.anual"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full" />
                        <x-jet-input-error for="boleta.anual" />
                    </div>
                    <div>
                        <x-jet-label value="Duplicado:" />
                        <x-jet-input type="number" wire:model="boleta.duplicado"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full" />
                        <x-jet-input-error for="boleta.duplicado" />
                    </div>
                    <div>
                        <x-jet-label value="Inicial:" />
                        <x-jet-input type="number" wire:model="boleta.inicial"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full" />
                        <x-jet-input-error for="boleta.inicial" />
                    </div>
                    <div>
                        <x-jet-label value="Desmonte:" />
                        <x-jet-input type="number" wire:model="boleta.desmonte"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full" />
                        <x-jet-input-error for="boleta.desmonte" />
                    </div>
                </div>
                <div>
                    <x-jet-label value="Observación:" />
                    <x-textarea
                        class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        wire:model="boleta.observacion" style="height: 350px;" />
                    <x-jet-input-error for="boleta.observacion" />
                </div>
            </x-slot>
            <x-slot name="footer">
                <x-jet-secondary-button wire:click="$set('openEdit',false)" class="mx-2">
                    Cancelar
                </x-jet-secondary-button>
                <x-jet-button wire:click="editarBoleta" wire:target="editarBoleta" wire:loading.attr="disabled">
                    Actualizar
                </x-jet-button>
            </x-slot>
        </x-jet-dialog-modal>
    @endif

    {{-- JS --}}
    @push('js')
        <script>
            Livewire.on('deleteBoleta', boletaId => {
                Swal.fire({
                    title: '¿Estas seguro de eliminar esta boleta?',
                    text: "una vez eliminado esta boleta, no podras recuperarlo.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Si, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {

                        Livewire.emitTo('lista-boletas', 'eliminarBoleta', boletaId);

                        Swal.fire(
                            'Listo!',
                            'Boleta eliminada correctamente.',
                            'success'
                        )
                    }
                })
            });
        </script>
    @endpush

    <livewire:boleta-servicio-detalle />

</div>
