<div class="container mx-auto">
    <div class="block justify-center mt-2 pt-8 max-h-max pb-8">
        <h1 class="text-center text-xl my-2 font-bold text-indigo-600"> REALIZAR MEMORANDO</h1>
        {{-- DIV PARA SELECCIONAR INSPECTOR --}}
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md my-4">
            <div class="bg-indigo-200 rounded-lg py-4 grid grid-cols-1 gap-8 justify-center">
                <div class="flex items-center justify-center p-4">
                    <div class="mr-4">
                        <x-jet-label value="Inspector:" for="Inspector" class="whitespace-nowrap" />
                    </div>
                    <div class="w-80">
                        <select wire:model="inspector" wire:change="seleccionarInspector()"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="0">Seleccione inspector...</option>
                            @foreach ($inspectores as $inspector)
                                <option value="{{ $inspector->id }}">{{ $inspector->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-jet-input-error for="inspector" />
                </div>
            </div>
        </div>

        @if ($mostrarCampos)
            <!-- FORMULARIO DE DATOS DEL MEMORANDO -->
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-xl my-4">
                <div class="bg-gray-400 py-4 px-6 rounded-t-lg">
                    <span class="text-lg font-semibold text-white">Datos del Memorando</span>
                </div>
                <div class="p-8">
                    <!-- Remitente -->
                    <div class="mb-6">
                        <x-jet-label value="Remitente:" />
                        <x-jet-input type="text" wire:model="remitente" list="items"
                            class="bg-gray-50 border-indigo-500 rounded-md block w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"/>
                        <datalist id="items">
                            <option value="LESLY PAMELA EGOAVIL LOMOTE"></option>
                            <option value="LOPEZ HENRIQUEZ SPASOJE BRATZO"></option>
                        </datalist>
                        <x-jet-input-error for="remitente" />
                    </div>
                    <!-- Cargo Remitente, Cargo Inspector -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <div>
                            <x-jet-label value="Cargo Remitente:" />
                            <x-jet-input type="text" wire:model="cargoremi" list="items3"
                                class="bg-gray-50 border-indigo-500 rounded-md block w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"/>
                            <datalist id="items3">
                                <option value="ADMINISTRADORA"></option>
                                <option value="INGENIERO SUPERVISOR"></option>
                            </datalist>
                            <x-jet-input-error for="cargoremi" />
                        </div>
                        <div>
                            <x-jet-label value="Cargo Inspector:" />
                            <x-jet-input type="text" wire:model="cargo" list="items2"
                                class="bg-gray-50 border-indigo-500 rounded-md block w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"/>
                            <datalist id="items2">
                                <option value="INSPECTOR DE GNV"></option>
                                <option value="INSPECTOR DE GLP"></option>
                            </datalist>
                            <x-jet-input-error for="cargo" />
                        </div>
                    </div>
                    <!-- Campo Motivo -->
                    <div class="mb-6">
                        <x-jet-label value="Motivo:" />
                        <x-textarea placeholder="Señor, por medio del presente me dirijo a usted para hacer de su conocimiento que ..."
                            class="w-full border-indigo-500 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            wire:model="motivo" />
                        <x-jet-input-error for="motivo" />
                    </div>
                </div>
            </div>
            
            <!-- VISTA PREVIA DEL MEMORANDO -->
            {{-- 
                <div class="mb-6 px-8 py-2">
                            @if ($memorandoPreview)
                                <div class="bg-gray-400 py-4 px-6 rounded-t-lg">
                                    <span class="text-lg font-semibold text-white dark:text-white">Vista Previa del
                                        Memorando</span>
                                </div>
                                @include('memorando', [
                                    'fecha' => $memorandoPreview['fecha'],
                                    'remitente' => $memorandoPreview['remitente'],
                                    'cargoremi' => $memorandoPreview['cargoremi'],
                                    'idUser' => $memorandoPreview['idUser'],
                                    'cargo' => $memorandoPreview['cargo'],
                                    'motivo' => $memorandoPreview['motivo'],
                                ])
                            @endif
                </div>
            --}}

            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md my-4 py-4">
                <div class="my-2 flex flex-col md:flex-row justify-evenly items-center">
                    <!-- campo fecha -->
                    <div>
                        <x-jet-input type="date"
                            class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full" wire:model="fecha"
                            required />
                        <x-jet-input-error for="fecha" />
                    </div>
                    <!-- boton certificar -->
                    <div>
                        <button wire:click="certificar" wire:loading.attr="disabled" wire.target="certificar"
                            class="hover:cursor-pointer border border-indigo-500 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 sm:mt-0 inline-flex items-center justify-center px-6 py-3 bg-indigo-400 hover:bg-indigo-500 focus:outline-none rounded">
                            <p class="text-sm font-medium leading-none text-white">
                                <span wire:loading wire:target="certificar">
                                    <i class="fas fa-spinner animate-spin"></i>
                                    &nbsp;
                                </span>
                                &nbsp;Certificar
                            </p>
                        </button>
                    </div>
                </div>
            </div>
        @endif
        
        @if ($memorando)
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md my-4 py-4">
                <div class="my-2 flex flex-row justify-evenly items-center" x-data="{ menu: false }">
                    <button type="button" x-on:click="menu = ! menu" id="menu-button" aria-expanded="true"
                        aria-haspopup="true" data-te-ripple-init data-te-ripple-color="light"
                        class="hover:cursor-pointer border border-indigo-500 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 sm:mt-0 inline-flex items-center justify-center px-6 py-2 bg-indigo-400 text-white hover:bg-indigo-500 focus:outline-none rounded">
                        Documentos &nbsp; <i class="fas fa-angle-down"></i>
                    </button>
                    <div x-show="menu" x-on:click.away="menu = false"
                        class="dropdown-menu transition-all duration-300 transform origin-top-right -translate-y-2 scale-95 absolute  dropdown-content bg-white shadow w-56 z-30 mt-6 border border-slate-800 rounded-md"
                        role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                        <div class="" role="none">
                            <a href="{{ $memorando->rutaVistaMemorando }}" target="__blank" rel="noopener noreferrer"
                                class="flex px-4 py-2 text-sm text-indigo-700 hover:bg-slate-600 hover:text-white justify-between items-center rounded-t-md hover:cursor-pointer">
                                <i class="fas fa-eye"></i>
                                <span>Ver Certificado.</span>
                            </a>
                            <a href="{{ $memorando->rutaDescargaMemorando }}" target="__blank" rel="noopener noreferrer"
                                class="flex px-4 py-2 text-sm text-indigo-700 hover:bg-slate-600 hover:text-white justify-between items-center hover:cursor-pointer">
                                <i class="fas fa-download"></i>
                                <span>desc. Certificado</span>
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('Memorando') }}"
                        class="hover:cursor-pointer focus:ring-2 focus:ring-offset-2 focus:ring-amber-600 sm:mt-0 inline-flex items-center justify-center px-6 py-3 bg-red-400 hover:bg-red-500 focus:outline-none rounded">
                        <p class="text-sm font-medium leading-none text-white">
                            <i class="fas fa-archive"></i>&nbsp;Finalizar
                        </p>
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
