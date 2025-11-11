<div class="block justify-center mt-12 pt-8 max-h-max pb-8">
    <h1 class="text-center text-xl my-4 font-bold text-indigo-900"> REALIZAR NUEVO SERVICIO</h1>
    {{-- DIV PARA SELECCIONAR TALLER Y TIPO DE SERVICIO --}}
    <div class="max-w-5xl m-auto bg-white rounded-lg shadow-md my-4">
        <div class=" bg-indigo-200 rounded-lg py-4 px-2 grid grid-cols-1 gap-8 sm:grid-cols-2">
            <!-- Seleccionar Taller -->
            <div>
                <x-jet-label value="Taller:" />
                <select wire:model="taller"
                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full ">
                    <option value="">Seleccione</option>
                    @foreach ($talleres as $taller)
                        <option value="{{ $taller->id }}">{{ $taller->nombre }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for="taller" />
            </div>
            <!-- Seleccionar Tipo de Servicio -->
            <div>
                <x-jet-label value="Servicio:" />
                <select wire:model="servicio" class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full "
                    wire:loading.attr="disabled" wire:target="taller">
                    @if (isset($servicios))
                        <option value="">Seleccione </option>
                        @foreach ($servicios as $item)
                            <option value="{{ $item->id }}">{{ $item->tipoServicio->descripcion }}</option>
                        @endforeach
                    @else
                        <option value="">Seleccione un taller</option>
                    @endif
                </select>
                <x-jet-input-error for="serv" />
            </div>
        </div>
    </div>

    @if ($servicio)
        @switch($tipoServicio->id)
            @case(3)
                @if ($estado)
                    @switch($estado)
                        @case('esperando')
                            <div class="max-w-5xl m-auto bg-white rounded-lg shadow-md my-4 py-4">
                                <div class="my-2 flex flex-col md:flex-row justify-evenly items-center">
                                    <div class="rounded-lg py-4 px-4 grid grid-cols-2 gap-8  w-full">
                                        <div>
                                            <x-jet-label value="Placa:" />
                                            <x-jet-input type="text" wire:model="placa" class="w-full" requerid />
                                            <x-jet-input-error for="placa" />
                                        </div>
                                        <div>
                                            <x-jet-label value="NumSerie:" />
                                            <x-jet-input type="text" wire:model="numSerie" class="w-full" requerid />
                                            <x-jet-input-error for="numSerie" />
                                        </div>
                                    </div>
                                </div>
                                <div class="my-2 flex flex-col md:flex-row justify-evenly items-center">
                                    <div class="my-2 flex flex-row justify-evenly items-center">
                                        <button wire:click="certificarGlp" wire:loading.attr="disabled" wire.target="certificarGlp"
                                            class="hover:cursor-pointer border border-indigo-500 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 sm:mt-0 inline-flex items-center justify-center px-6 py-3 bg-indigo-400 hover:bg-indigo-500 focus:outline-none rounded">
                                            <p class="text-sm font-medium leading-none text-white">
                                                <span wire:loading wire:target="certificarGlp">
                                                    <i class="fas fa-spinner animate-spin"></i>
                                                    &nbsp;
                                                </span>
                                                &nbsp;Certificar
                                            </p>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @break

                        @case('certificado')
                            @if ($certificacion)
                                <div class="max-w-5xl m-auto bg-white rounded-lg shadow-md my-4 py-4">
                                    <div class="my-2 flex flex-row justify-evenly items-center" x-data="{ menu: false }">
                                        <a href="{{ route('servicioTemporal') }}"
                                            class="hover:cursor-pointer focus:ring-2 focus:ring-offset-2 focus:ring-amber-600 sm:mt-0 inline-flex items-center justify-center px-6 py-3 bg-red-400 hover:bg-red-500 focus:outline-none rounded">
                                            <p class="text-sm font-medium leading-none text-white">
                                                <i class="fas fa-archive"></i>&nbsp;Finalizar
                                            </p>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @break

                        @default
                    @endswitch
                @endif
            @break

            @case(4)
                @if ($estado)
                    @switch($estado)
                        @case('esperando')
                            <div class="max-w-5xl m-auto bg-white rounded-lg shadow-md my-4 py-4">
                                <div class="my-2 flex flex-col md:flex-row justify-evenly items-center">
                                    <div class="rounded-lg py-4 px-4 grid grid-cols-2 gap-8  w-full">
                                        <div>
                                            <x-jet-label value="Placa:" />
                                            <x-jet-input type="text" wire:model="placa" class="w-full" requerid />
                                            <x-jet-input-error for="placa" />
                                        </div>
                                        <div>
                                            <x-jet-label value="NumSerie:" />
                                            <x-jet-input type="text" wire:model="numSerie" class="w-full" requerid />
                                            <x-jet-input-error for="numSerie" />
                                        </div>
                                    </div>
                                </div>
                                <div class="my-2 flex flex-col md:flex-row justify-evenly items-center">
                                    <div class="my-2 flex flex-row justify-evenly items-center">
                                        <button wire:click="certificarGlp" wire:loading.attr="disabled" wire.target="certificarGlp"
                                            class="hover:cursor-pointer border border-indigo-500 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 sm:mt-0 inline-flex items-center justify-center px-6 py-3 bg-indigo-400 hover:bg-indigo-500 focus:outline-none rounded">
                                            <p class="text-sm font-medium leading-none text-white">
                                                <span wire:loading wire:target="certificarGlp">
                                                    <i class="fas fa-spinner animate-spin"></i>
                                                    &nbsp;
                                                </span>
                                                &nbsp;Certificar
                                            </p>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @break

                        @case('certificado')
                            @if ($certificacion)
                                <div class="max-w-5xl m-auto bg-white rounded-lg shadow-md my-4 py-4">
                                    <div class="my-2 flex flex-row justify-evenly items-center" x-data="{ menu: false }">
                                        <a href="{{ route('servicioTemporal') }}"
                                            class="hover:cursor-pointer focus:ring-2 focus:ring-offset-2 focus:ring-amber-600 sm:mt-0 inline-flex items-center justify-center px-6 py-3 bg-red-400 hover:bg-red-500 focus:outline-none rounded">
                                            <p class="text-sm font-medium leading-none text-white">
                                                <i class="fas fa-archive"></i>&nbsp;Finalizar
                                            </p>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @break

                        @default
                    @endswitch
                @endif
            @break

            @default
            @break

        @endswitch
    @endif
</div>
