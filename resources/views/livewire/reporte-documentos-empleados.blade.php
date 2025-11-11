<div>
    <div class="w-full pt-3">
        <div class="w-full pt-2  mt-2 px-4 text-center mx-auto">
            <h1 class="text-2xl text-indigo-500 font-bold italic text-center py-8"><span
                    class="text-none">📕</span>REPORTE DE DOCUMENTOS EMPLEADOS
            </h1>
            <div class="flex flex-wrap justify-center items-center gap-4">
                <!-- FILTRO PARA TIPO DE DOCUMENTO -->
                <div class="flex bg-white items-center p-2 rounded-md mb-4 w-full md:w-auto">
                    <span>Documento: </span>
                    <select wire:model="tipoDocumento"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full md:w-auto truncate">
                        <option value="">SELECCIONE</option>
                        @foreach ($tiposDocumentos as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombreTipo }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- FILTRO PARA EMPLEADO -->
                <div class="flex bg-white items-center p-2 rounded-md mb-4 w-full md:w-auto">
                    <span>Empleados: </span>
                    <select wire:model="empleado"
                        class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block w-full md:w-auto truncate">
                        <option value="">SELECCIONE</option>
                        @foreach ($empleados as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- BOTÓN PARA GENERAR REPORTE -->
                <button wire:click="documentos"
                    class="bg-indigo-400 px-6 py-4 w-full md:w-auto rounded-md text-white font-semibold tracking-wide cursor-pointer mb-4">
                    <p class="truncate"> Generar Reporte</p>
                </button>
            </div>

            <div wire:loading wire:target="documentos">
                <div class="flex justify-center">
                    <img src="{{ asset('images/mtg.png') }}" alt="Logo Motorgas Company" width="100" height="100">
                </div>
                <div class="text-center">
                    <i class="fa-solid fa-circle-notch fa-xl animate-spin text-indigo-800 "></i>
                    <p class="text-center text-black font-bold italic">CARGANDO...</p>
                </div>
            </div>

            {{-- DOCUMENTOS QUE FALTAN --}}
            @if (!empty($documentos))
                <div class="flex flex-col my-4 py-4 rounded-md bg-white px-4 justify-center">
                    <div class="inline-block min-w-full py-2 sm:px-6">
                        <div class="overflow-hidden">
                            <table class="min-w-full border text-center text-sm font-light rounded-xl overflow-hidden">
                                <thead class="font-medium bg-indigo-200">
                                    <tr>
                                        <th class="border-r px-6 py-4">ID</th>
                                        <th class="border-r px-6 py-4">Empleado</th>
                                        <th class="border-r px-6 py-4">ID Tipo</th>
                                        <th class="border-r px-6 py-4">Documento Faltante</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($documentos as $doc)
                                        <tr class="bg-orange-200">
                                            <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                {{ $doc->id_empleado }}
                                            </td>
                                            <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                {{ $doc->nombre_empleado }}
                                            </td>
                                            <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                {{ $doc->id_tipo_documento }}
                                            </td>
                                            <td class="whitespace-nowrap border-r px-6 py-4 font-medium">
                                                {{ $doc->documento_faltante }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
