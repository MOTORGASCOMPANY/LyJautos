<div class="bg-gray py-12 px-4 md:px-8 xl:px-10">
    <div class="container mx-auto bg-gray-200 rounded-xl shadow-lg p-8 border border-gray-200">
        <div class=" items-center md:block sm:block">
            <div class="px-2 w-64 mb-4 md:w-full">
                <h2 class="text-indigo-900 font-bold text-3xl">Registro de Memorandos</h2>
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

                @hasanyrole('administrador|supervisor|Administrador del sistema|auditoria')
                    <button wire:click="agregar"
                        class="bg-indigo-600 px-6 py-4 rounded-md text-white font-semibold tracking-wide cursor-pointer">
                        Agregar
                    </button>
                @endhasanyrole
            </div>
        </div>

        @if ($memorandos->count() > 0)
            <div class="overflow-x-auto rounded-xl">
                <table class="w-full whitespace-nowrap">
                    <thead class="bg-slate-600 font-bold text-white">
                        <tr>
                            <th scope="col" class="text-sm font-medium font-semibold text-white px-6 py-4 text-left">
                                #
                            </th>
                            <th scope="col" class="text-sm font-medium font-semibold text-white px-6 py-4 text-left">
                                Remitente
                            </th>
                            <th scope="col" class="text-sm font-medium font-semibold text-white px-6 py-4 text-left"
                                wire:click="order('destinatario->name')">
                                Inspector
                                @if ($this->sort == 'destinatario->name')
                                    @if ($this->direction == 'asc')
                                        <i class="fas fa-sort-alpha-up-alt float-right mt-0.5"></i>
                                    @else
                                        <i class="fas fa-sort-alpha-down-alt float-right mt-0.5"></i>
                                    @endif
                                @else
                                    <i class="fas fa-sort float-right mt-0.5"></i>
                                @endif
                            </th>
                            <th scope="col" class="text-sm font-medium font-semibold text-white px-6 py-4 text-left"
                                wire:click="order('fecha')">
                                Fecha de creación
                                @if ($sort == 'fecha')
                                    @if ($direction == 'asc')
                                        <i class="fas fa-sort-numeric-up-alt float-right mt-0.5"></i>
                                    @else
                                        <i class="fas fa-sort-numeric-down-alt float-right mt-0.5"></i>
                                    @endif
                                @else
                                    <i class="fas fa-sort float-right mt-0.5"></i>
                                @endif
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($memorandos as $memorando)
                            <tr tabindex="0" class="focus:outline-none h-16 border border-slate-300 rounded hover:bg-gray-300">
                                <td class="pl-5">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-indigo-800 px-3 py-1 bg-indigo-100 rounded-full">
                                            {{ $loop->iteration }}
                                        </p>
                                    </div>
                                </td>
                                <td class="pl-2">
                                    <div class="flex items-center">
                                        <p class="text-sm leading-none text-gray-700 font-medium ml-2">
                                            {{ $memorando->remitente }}
                                        </p>
                                    </div>
                                </td>
                                <td class="pl-2">
                                    <div class="flex items-center">
                                        <p class="text-sm leading-none text-gray-600 ml-2">
                                            {{ $memorando->destinatario->name }}
                                        </p>
                                    </div>
                                </td>
                                <td class="pl-2">
                                    <div class="flex items-center">
                                        <p class="text-sm leading-none text-gray-600 ml-2">
                                            {{ $memorando->fecha }}
                                        </p>
                                    </div>
                                </td>
                                <td>
                                    {{-- 
                                        <div x-data="{ dropdownMenu: false }" class="relative">
                                            <!-- Dropdown toggle button -->
                                            <button @click="dropdownMenu = ! dropdownMenu"
                                                class="flex items-center p-2 border border-indigo-500  bg-gray-200 rounded-md">
                                                <span class="mr-4">Seleccione <i class="fas fa-sort-down -mt-2"></i></span>
                                            </button>
                                            <!-- Dropdown list -->
                                            <div x-show="dropdownMenu"
                                                class="absolute py-2 mt-2  bg-slate-300 rounded-md shadow-xl w-70 z-20 ">
                                                <button wire:click="verMemorando({{ $memorando->id }})"
                                                    class="block px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-300 hover:text-white">
                                                    <i class="fas fa-eye"></i> Enlace
                                                </button>
                                                @hasanyrole('administrador|supervisor|Administrador del sistema')
                                                    <button wire:click="eliminarMemorando({{ $memorando->id }})"
                                                        class="block px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-300 hover:text-white">
                                                        <i class="fas fa-trash"></i> Eliminar
                                                    </button>
                                                @endhasanyrole
                                            </div>
                                        </div>
                                    --}}
                                    <div x-data="{ dropdownMenu: false }" class="relative inline-block text-left">
                                        <!-- Botón de Toggle con icono de 3 puntos (Kebab Menu) -->
                                        <button @click="dropdownMenu = ! dropdownMenu"
                                            x-on:keydown.escape.window="dropdownMenu = false"
                                            class="p-2 rounded-full text-gray-500 hover:text-indigo-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-150">
                                            {{-- Icono de 3 puntos (Ellipsis V) --}}
                                            <i class="fas fa-ellipsis-v text-lg"></i>
                                        </button>
                                        <!-- Dropdown List: Estilo moderno y z-index alto -->
                                        <div x-show="dropdownMenu"
                                            x-on:click.outside="dropdownMenu = false"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute right-0 w-48 mt-2 origin-top-right bg-white border border-gray-200 divide-y divide-gray-100 rounded-lg shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none z-30"
                                            role="menu" aria-orientation="vertical" aria-labelledby="menu-button"
                                            tabindex="-1">

                                            <div class="py-1" role="none">
                                                <!-- Opción 1: Ver -->
                                                <button wire:click="verMemorando({{ $memorando->id }})"
                                                    @click="dropdownMenu = false"
                                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition duration-150"
                                                    role="menuitem" tabindex="-1">
                                                    <i class="fas fa-eye w-5 h-5 mr-2"></i>
                                                    Memorando
                                                </button>

                                                <!-- Opción 2: Eliminar (Solo para roles específicos) -->
                                                @hasanyrole('administrador|supervisor|Administrador del sistema')
                                                    <button wire:click="eliminarMemorando({{ $memorando->id }})"
                                                        @click="dropdownMenu = false"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition duration-150"
                                                        role="menuitem" tabindex="-1">
                                                        <i class="fas fa-trash w-5 h-5 mr-2"></i>
                                                        Eliminar
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

            {{-- Paginación --}}
            @if ($memorandos->hasPages())
                <div class="mt-4 px-5 py-5 bg-white border rounded-xl">
                    {{ $memorandos->links() }}
                </div>
            @endif
        @else
            <div class="text-center mt-4 text-gray-500">
                <p>No tienes memorandos en este momento.</p>
            </div>
        @endif
    </div>
</div>
