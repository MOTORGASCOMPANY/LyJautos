<div>
    <div class="w-full pt-3">
        <div class="w-full pt-2  mt-2 px-4 text-center mx-auto">
            <h1 class="text-2xl text-indigo-500 font-bold italic text-center py-8"><span
                    class="text-none">📕</span>REPORTE DE DOCUMENTOS AUTORIZADOS DE TALLERES (GNV, GLP)</h1>
            <div class="flex flex-wrap items-center space-x-2">
                <div class="flex bg-white items-center p-2 rounded-md mb-4">
                    <span>Documento: </span>
                    <select wire:model="tipoDocumentoId"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate">
                        <option value="">SELECCIONE</option>
                        @isset($tipodocumentos)
                            @foreach ($tipodocumentos as $tipo)
                                <option class="" value="{{ $tipo->id }}">{{ $tipo->nombreTipo }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>
                <div class="flex bg-white items-center p-2 rounded-md mb-4 ">
                    <span>Taller: </span>
                    <select wire:model="tallerId"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate">
                        <option value="">SELECCIONE</option>
                        @isset($talleres)
                            @foreach ($talleres as $taller)
                                <option class="" value="{{ $taller->id }}">{{ $taller->nombre }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>
                <button wire:click="venceHoy"
                    class="bg-indigo-400 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                    <p class="truncate"> Doc. Vencidos </p>
                </button>
                <button wire:click="documentosRestantes"
                    class="bg-indigo-400 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                    <p class="truncate"> Doc. Restantes </p>
                </button>
                <button wire:click="documentosSubidos"
                    class="bg-indigo-400 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                    <p class="truncate"> Doc. Existentes </p>
                </button>
            </div>
            <div wire:loading wire:target="venceHoy,documentosRestantes,documentosSubidos">
                <div class="flex justify-center">
                    <img src="{{ asset('images/mtg.png') }}" alt="Logo Motorgas Company" width="100" height="100">
                </div>
                <div class="text-center">
                    <i class="fa-solid fa-circle-notch fa-xl animate-spin text-indigo-800 "></i>
                    <p class="text-center text-black font-bold italic">CARGANDO...</p>
                </div>
            </div>

            {{-- DOCUMENTOS QUE VENCIERON HASTA LA FECHA ACTUAL --}}
            @if (isset($documentos))
                @if ($documentos->count())
                    <div class="flex flex-col my-4 py-4 rounded-md bg-white px-4 justify-center">
                        <div class="inline-block min-w-full py-2 sm:px-6">
                            <div class="overflow-hidden">
                                <table
                                    class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                                    <thead class="font-medium dark:border-neutral-500">
                                        <tr class="bg-indigo-200">
                                            <th scope="col" class="border-r px-6 py-4">
                                                #
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Taller
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Documento
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Combustible
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Vence
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($documentos as $key => $item)
                                            <tr class="bg-orange-200">
                                                <td class="whitespace-nowrap border-r px-6 py-4">
                                                    {{ $key + 1 }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                    @foreach ($item->talleres as $taller)
                                                        {{ $taller->nombre }}
                                                    @endforeach
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                    {{ $item->TipoDocumento->nombreTipo }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                    @foreach ($item->documentostaller as $comb)
                                                        {{ $comb->combustible }}
                                                    @endforeach
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                    {{ $item->fechaExpiracion->format('d-m-Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                @endif
            @endif

            {{-- DOCUMENTOS QUE LE FALTAN A CADA TALLER --}}
            @if (isset($docrestante))
                @if ($docrestante->count())
                    <div class="flex flex-col my-4 py-4 rounded-md bg-white px-4 justify-center">
                        <div class="inline-block min-w-full py-2 sm:px-6">
                            <div class="overflow-hidden">
                                <table
                                    class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                                    <thead class="font-medium dark:border-neutral-500">
                                        <tr class="bg-indigo-200">
                                            <th scope="col" class="border-r px-6 py-4">
                                                #
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Taller
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Documento
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Combustible
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($docrestante as $key => $item)
                                            <tr class="bg-orange-200">
                                                <td class="whitespace-nowrap border-r px-6 py-4">
                                                    {{ $key + 1 }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                    {{ $item['taller'] }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                    {{ $item['tipoDocumento'] }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                    {{ $item['combustible'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                @else
                @endif
            @endif

            {{-- DOCUMENTOS QUE YA FUERON SUBIDOS --}}
            @if (isset($docSubidos))
                @if ($docSubidos->count())
                    <div class="flex flex-col my-4 py-4 rounded-md bg-white px-4 justify-center">
                        <div class="inline-block min-w-full py-2 sm:px-6">
                            <div class="overflow-hidden">
                                <table
                                    class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                                    <thead class="font-medium dark:border-neutral-500">
                                        <tr class="bg-indigo-200">
                                            <th scope="col" class="border-r px-6 py-4">
                                                #
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Taller
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Documento
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Combustible
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Emision
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Expira
                                            </th>
                                            <th scope="col" class="border-r px-6 py-4">
                                                Estado
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($docSubidos as $key => $item)
                                            <tr class="bg-orange-200">
                                                <td class="whitespace-nowrap border-r px-6 py-4">
                                                    {{ $key + 1 }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                    {{ $item['taller'] }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                    {{ $item['tipoDocumento'] }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                    {{ $item['combustible'] }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                    {{ $item['fechaInicio'] }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                    {{ $item['fechaExpiracion'] }}
                                                </td>
                                                <td class="whitespace-nowrap border-r px-6 py-4 font-medium 
                                                    {{ $item['estado'] == 'Caduco' ? 'text-red-500 font-bold' : 'text-green-500 font-bold' }}">
                                                    {{ $item['estado'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                @endif
            @endif
        </div>
    </div>
</div>
