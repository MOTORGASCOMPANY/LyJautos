<div class="min-h-screen bg-gray-100 py-12 px-6">
    <div class="container mx-auto bg-gray-200 rounded-xl shadow-lg p-8 border border-gray-200">
        {{-- Título --}}
        <div class="mb-4 border-b pb-3">
            <h1 class="text-2xl font-bold text-indigo-600 tracking-tight">
                Reporte de Fotos por Inspector
            </h1>
            <span class="text-xs">Control de cumplimiento fotografias reglamentarias</span>
        </div>

        {{-- Filtros --}}
        <div class="flex flex-wrap gap-4 md:gap-2 mb-4">
            <!-- Mostrar -->
            <div class="flex items-center bg-gray-50 items-center p-2 rounded-md mb-4">
                {{-- <span>Mostrar</span> --}}
                <select wire:model="perPage"
                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 w-full items-center md:flex  md:justify-center">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <!-- Estado fotos -->
            <div class="flex items-center bg-gray-50 items-center p-2 rounded-md mb-4">
                {{-- <span>Estd: </span> --}}
                <select wire:model="estado"
                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate">
                    <option value="">Todos</option>
                    <option value="completos">Comp</option>
                    <option value="incompletos">Incom</option>
                </select>
            </div>
            <!--  Inspector -->
            <div class="flex items-center bg-gray-50 items-center p-2 rounded-md mb-4 w-80">
                {{-- <span>Inspector: </span> --}}
                <select wire:model="ins"
                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate">
                    <option value="">Seleccione Inspector</option>
                    @isset($inspectores)
                        @foreach ($inspectores as $inspector)
                            <option class="" value="{{ $inspector->id }}">{{ $inspector->name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>
            <!-- Fecha, desde -->
            <div class="flex items-center bg-gray-50 items-center p-2 w-40 rounded-md mb-4 ">
                {{-- <span>Desde: </span> --}}
                <x-date-picker wire:model="fecIni" placeholder="Fecha de inicio"
                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
            </div>
            <!-- Fecha, hasta -->
            <div class="flex items-center bg-gray-50 items-center p-2 w-40 rounded-md mb-4 ">
                {{-- <span>Hasta: </span> --}}
                <x-date-picker wire:model="fecFin" placeholder="Fecha de Fin"
                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
            </div>
            <!-- Exportar -->
            <div class="flex justify-end mb-4">
                <button wire:click="exportarExcel"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-md transition">
                    <i class="fa-solid fa-file-excel"></i>
                    Exportar a Excel
                </button>
            </div>
        </div>


        {{-- Tabla --}}
        @if (!$mostrarTabla)
            <div class="py-6 px-6 bg-gradient-to-r from-indigo-50 to-white border rounded-xl shadow shadow-indigo-100 text-center">
                <p class="text-indigo-700 text-sm">
                    Seleccione un <span>rango de fechas</span> para generar el reporte.
                </p>
            </div>
        @else
            @if ($resumen->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 rounded-xl overflow-hidden">
                        <thead class="bg-gray-300 text-gray-800">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider">#</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider">Inspector</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider">GNV Cant.</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider">GNV Incomp.</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider">GNV %</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider">GLP Cant.</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider">GLP Incomp.</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider">GLP %</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider">Detalles</th>

                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($resumen as $i => $row)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-5 py-3 text-sm text-gray-700">{{ $i + 1 }}</td>
                                    <td class="px-5 py-3 text-sm font-semibold text-gray-800">{{ $row['inspector'] }}</td>
                                    <td class="px-5 py-3 text-center text-sm text-gray-700">{{ $row['gnv_tot'] }}</td>
                                    <td class="px-5 py-3 text-center text-sm text-gray-700">{{ $row['gnv_incomp'] }}</td>
                                    <td class="px-5 py-3 text-center text-sm">
                                        <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-800 font-semibold">
                                            {{ $row['gnv_pct'] }}%
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-center text-sm text-gray-700">{{ $row['glp_tot'] }}</td>
                                    <td class="px-5 py-3 text-center text-sm text-gray-700">{{ $row['glp_incomp'] }}</td>
                                    <td class="px-5 py-3 text-center text-sm">
                                        <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-800 font-semibold">
                                            {{ $row['glp_pct'] }}%
                                        </span>
                                    </td>

                                    <td class="px-5 py-3 text-sm text-gray-600">
                                        @if ($row['gnv_incomp'] > 0 || $row['glp_incomp'] > 0)
                                            <button wire:click="verDetalles('{{ $row['inspector'] }}')"
                                                class="text-indigo-600 font-semibold hover:underline">
                                                Ver detalles
                                            </button>
                                        @else
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                                <i class="fa-solid fa-check"></i>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-10 text-center bg-white border rounded-xl shadow-sm">
                    <p class="text-gray-500 font-medium">No se encontraron registros.</p>
                </div>
            @endif
        @endif

        {{-- Modal detalles --}}
        @if ($openModal)
            <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[80vh] overflow-y-auto">
                    <div class="flex justify-between items-center border-b px-6 py-4">
                        <h2 class="text-lg font-semibold text-indigo-700">
                            📸 Detalles faltantes GNV & GLP
                        </h2>
                        <button wire:click="$set('openModal', false)" class="text-gray-400 hover:text-gray-700">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- GNV --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-600 mb-2">GNV incompletos</h3>
                            @if (!empty($detalles['gnv']))
                                @foreach ($detalles['gnv'] as $d)
                                    <div class="px-3 py-2 bg-red-50 text-gray-700 rounded-lg border border-red-200 mb-2">
                                        <span class="font-bold">{{ $d['placa'] }}</span>
                                        <span class="text-gray-700"> — {{ $d['certificado'] }}</span>
                                    </div>
                                @endforeach
                            @else
                                <span class="text-gray-500 text-sm">No hay faltantes GNV.</span>
                            @endif
                        </div>

                        {{-- GLP --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-600 mb-2">GLP incompletos</h3>
                            @if (!empty($detalles['glp']))
                                @foreach ($detalles['glp'] as $d)
                                    <div class="px-3 py-2 bg-yellow-50 text-gray-700 rounded-lg border border-yellow-200 mb-2">
                                        <span class="font-bold">{{ $d['placa'] }}</span>
                                        <span class="text-gray-700"> — {{ $d['certificado'] }}</span>
                                    </div>
                                @endforeach
                            @else
                                <span class="text-gray-500 text-sm">No hay faltantes GLP.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>


{{-- 
    <div class="w-full  items-center md:flex md:flex-row md:justify-center md:space-x-2">
                <div class="flex items-center space-x-2">
                    <div class="flex bg-white items-center p-2 w-1/2 md:w-48 rounded-md mb-4 ">
                        <span>Desde: </span>
                        <x-date-picker wire:model="fechaInicio" placeholder="Fecha de inicio"
                            class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                    </div>
                    <div class="flex bg-white items-center p-2 w-1/2 md:w-48 rounded-md mb-4 ">
                        <span>Hasta: </span>
                        <x-date-picker wire:model="fechaFin" placeholder="Fecha de Fin"
                            class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full truncate" />
                    </div>
                </div>

                <button wire:click="generarReporte2"
                    class="bg-indigo-600 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                    <p class="truncate"> Generar reporte </p>
                </button>
    </div>
    <div class="w-auto my-4">
                <x-jet-input-error for="fechaInicio" />
                <x-jet-input-error for="fechaFin" />
    </div>
    <div class="w-full text-center font-semibold text-gray-100 p-4 mb-4 border rounded-md bg-indigo-400 shadow-lg"
                wire:loading>
                CARGANDO <i class="fa-solid fa-spinner animate-spin"></i>
    </div>
    @if (isset($inspectoresConFotos))
                @if ($inspectoresConFotos->count())
                    <div class="flex flex-col my-4 py-4 rounded-md bg-white px-4 justify-center">
                        <div class="m-auto flex justify-center items-center bg-gray-300 rounded-md w-full p-4">
                            <button wire:click="exportarExcel"
                                class="bg-green-400 px-6 py-4 w-1/3 text-sm rounded-md text-sm text-white font-semibold tracking-wide cursor-pointer ">
                                <p class="truncate"><i class="fa-solid fa-file-excel fa-lg"></i> Desc. Excel </p>
                            </button>
                        </div>
                        <div class="overflow-x-auto m-auto w-full" wire:ignore>
                            <div class="inline-block min-w-full py-2 sm:px-6">
                                <div class="overflow-hidden">
                                    <table class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                                        <thead class="font-medium dark:border-neutral-500">
                                            <tr class="bg-indigo-200">
                                                <th scope="col" class="border-r px-6 py-4">
                                                    #
                                                </th>
                                                <th scope="col" class="border-r px-6 py-4">
                                                    Inspector
                                                </th>  
                                                <th scope="col" class="border-r px-6 py-4">
                                                    Exp Realizados GNV
                                                </th>
                                                <th scope="col" class="border-r px-6 py-4">
                                                    Exp Subidos GNV
                                                </th>
                                                <th scope="col" class="border-r px-6 py-4">
                                                    Porcentaje GNV
                                                </th>

                                                <th scope="col" class="border-r px-6 py-4">
                                                    Exp Realizados GLP
                                                </th>                                                
                                                <th scope="col" class="border-r px-6 py-4">
                                                    Exp Subidos GLP
                                                </th>                                                 
                                                <th scope="col" class="border-r px-6 py-4">
                                                    Porcentaje GLP
                                                </th>

                                                <th scope="col" class="border-r px-6 py-4">
                                                    Exp Realizados MODI
                                                </th>                                                
                                                <th scope="col" class="border-r px-6 py-4">
                                                    Exp Subidos MODI
                                                </th>                                                 
                                                <th scope="col" class="border-r px-6 py-4">
                                                    Porcentaje MODI
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($inspectoresConFotos as $key => $item)
                                                        <tr class="bg-orange-200 hover:bg-orange-300">
                                                            <td class="whitespace-nowrap border-r px-6 py-4">
                                                                {{ $key + 1 }}
                                                            </td>
                                                            <td class="whitespace-nowrap border-r px-6 py-4">
                                                                {{ $item['nombreInspector'] ?? 'N.A' }}
                                                            </td>
                                                            <td class="whitespace-nowrap border-r px-6 py-4">
                                                                {{ $item['expRealizadosGNV'] ?? 'N.A' }}
                                                            </td>
                                                            <td class="whitespace-nowrap border-r px-6 py-4">
                                                                {{ $item['expSubidosGNV'] ?? 'N.A' }}
                                                            </td>
                                                            <td class="whitespace-nowrap border-r px-6 py-4">
                                                                {{ $item['porcentajeGNV'] ?? 'N.A' }}
                                                            </td>

                                                            <td class="whitespace-nowrap border-r px-6 py-4">
                                                                {{ $item['expRealizadosGLP'] ?? 'N.A' }}
                                                            </td>                                                            
                                                            <td class="whitespace-nowrap border-r px-6 py-4">
                                                                {{ $item['expSubidosGLP'] ?? 'N.A' }}
                                                            </td>                                                            
                                                            <td class="whitespace-nowrap border-r px-6 py-4">
                                                                {{ $item['porcentajeGLP'] ?? 'N.A' }}
                                                            </td>

                                                            <td class="whitespace-nowrap border-r px-6 py-4">
                                                                {{ $item['expRealizadosMODI'] ?? 'N.A' }}
                                                            </td>                                                            
                                                            <td class="whitespace-nowrap border-r px-6 py-4">
                                                                {{ $item['expSubidosMODI'] ?? 'N.A' }}
                                                            </td>                                                            
                                                            <td class="whitespace-nowrap border-r px-6 py-4">
                                                                {{ $item['porcentajeMODI'] ?? 'N.A' }}
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
                    <div class="w-full text-center font-semibold text-gray-100 p-4 mb-4 border rounded-md bg-indigo-400 shadow-lg"
                        wire:loading.class="hidden">
                        No se encontraron inspectores que hayan subido fotos en el rango de fechas seleccionado.
                    </div>
                @endif
    @endif
--}}
