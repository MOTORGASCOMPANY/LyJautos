<div>
    <div class="w-full pt-3">
        <div class=" w-full pt-2  mt-2 px-4 text-center mx-auto">
            <h1 class="text-2xl text-indigo-500 font-bold italic text-center py-2"><span class="text-none">⛽</span>
                INFORME DE CILINDROS</h1>
            <p class="text-sm leading-relaxed text-gray-500">
                Nota: Este informe <strong>no considera</strong> los cilindros pertenecientes a certificados que se
                encuentran en <strong>discrepancia</strong>.
            </p>
            <div class="w-full  items-center md:flex md:flex-row md:justify-center md:space-x-2 mt-4">
                <div class="flex items-center space-x-2">

                    <div class="flex bg-white items-center p-2 w-1/2 md:w-96 rounded-md mb-4 ">
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

                    <div class="flex bg-white items-center p-2 w-1/2 md:w-48 rounded-md mb-4 ">
                        <span>Desde: </span>
                        <x-date-picker wire:model="desde" placeholder="Fecha de inicio"
                            class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                    </div>
                    <div class="flex bg-white items-center p-2 w-1/2 md:w-48 rounded-md mb-4 ">
                        <span>Hasta: </span>
                        <x-date-picker wire:model="hasta" placeholder="Fecha de Fin"
                            class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                    </div>
                </div>

                <button wire:click="buscar"
                    class="bg-indigo-600 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                    <p class="truncate"> Generar informe </p>
                </button>
                <button wire:click="$emit('exportarInformeCilindro')"
                    class="bg-green-400 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                    <p class="truncate"><i class="fa-solid fa-file-excel fa-lg"></i> Desc. Excel </p>
                </button>
            </div>
            <div class="w-auto my-4">
                <x-jet-input-error for="desde" />
                <x-jet-input-error for="hasta" />
            </div>
            <div class="w-full text-center font-semibold text-gray-100 p-4 mb-4 border rounded-md bg-indigo-400 shadow-lg"
                wire:loading>
                CARGANDO <i class="fa-solid fa-spinner animate-spin"></i>
            </div>

            @if ($resultado && $resultado->count())
                <div class="flex flex-col my-4 py-4 rounded-md bg-white px-4 justify-center">
                    <div class="overflow-x-auto m-auto w-full">
                        <div class="inline-block min-w-full py-2 sm:px-6 ">
                            <div class="overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200 rounded-lg shadow-sm" id='data_cilindro'>
                                    <thead class="bg-indigo-100">
                                        <tr>
                                            <th colspan="10" class="px-3 py-2 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                                INFORMACIÓN DE CILINDROS
                                            </th>
                                        </tr>
                                        <tr>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                                #
                                            </th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                                Fecha Revisión
                                            </th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                                Inspector
                                            </th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                                Placa
                                            </th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                                N° Serie
                                            </th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                                Clase GNV
                                            </th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                                Marca
                                            </th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                                Capacidad
                                            </th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                                Fecha de Fabricación
                                            </th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-indigo-800 uppercase tracking-wider">
                                                Fecha fin de vida Utíl
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @php $i = 1; @endphp
                                        @foreach ($resultado as $cert)
                                            {{--if ($cert->Vehiculo && $cert->Inspector)--}}
                                            @if (is_object($cert) && $cert->Vehiculo && $cert->Inspector)
                                                @foreach ($cert->Vehiculo->Equipos->where('idTipoEquipo', 3) as $equipo)
                                                    <tr class="hover:bg-indigo-50 transition duration-150">
                                                        <td class="px-4 py-2 text-sm text-gray-700">
                                                            {{ $i++ }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-700">
                                                            {{ $cert->created_at->format('d/m/Y') }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-700">
                                                            {{ $cert->Inspector->name }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-700">
                                                            {{ $cert->Vehiculo->placa }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-700">
                                                            {{ $equipo->numSerie }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-700">
                                                            {{ $equipo->claseGnv }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-700">
                                                            {{ $equipo->marca }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-700">
                                                            {{ $equipo->capacidad }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-700">
                                                            {{ $equipo->fechaFab }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-700">
                                                            {{-- $equipo->fechaCaducidad ?? 'NE'--}}
                                                            @if (is_null($equipo->fechaCaducidad) && $equipo->fechaCaducidad_aplica == 0)
                                                                NE
                                                            @elseif (!is_null($equipo->fechaCaducidad))
                                                                {{ $equipo->fechaCaducidad }}
                                                            @else                                                       
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-center text-gray-500 mt-6">No se encontraron resultados para informe de cilindros.</p>
            @endif
        </div>
    </div>

    @push('js')
        <script>
            Livewire.on('exportarInformeCilindro', () => {
                // Obtener los datos de la tabla
                datae = document.getElementById('data_cilindro').innerHTML;
                console.log(datae);
                // Emitir el evento exportarExcel con los datos de la tabla
                Livewire.emit('exportarExcelCilindro', datae);
            });
        </script>
    @endpush
</div>
