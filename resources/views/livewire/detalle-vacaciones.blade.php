<div class="bg-gray py-6 px-4 md:px-8 xl:px-10">
    <div class="mt-7 max-w-screen-2xl mx-auto">
        <div class="mb-6">
            <h2 class="text-indigo-900 font-bold text-3xl">Detalle de Vacaciones</h2>
        </div>

        <!-- Filtros -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <!-- Mostrar -->
            <div class="flex bg-gray-50 items-center p-2 rounded-md">
                <span>Mostrar</span>
                <select wire:model="perPage" class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>Entradas</span>
            </div>

            <!-- Buscar -->
            <div class="flex bg-gray-50 items-center w-full md:w-1/2 p-2 rounded-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                </svg>
                <input type="text" wire:model.debounce.500ms="search"
                    class="bg-gray-50 outline-none block rounded-md border-indigo-500 w-full ml-2"
                    placeholder="Buscar empleado...">
            </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="w-full border-collapse">
                <thead class="bg-slate-600 text-white">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Empleado</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Fecha Inicio</th>
                        <th class="px-4 py-2 text-center text-sm font-semibold">Días Ganados</th>
                        <th class="px-4 py-2 text-center text-sm font-semibold">Días Tomados</th>
                        <th class="px-4 py-2 text-center text-sm font-semibold">Días Restantes</th>
                        <th class="px-4 py-2 text-center text-sm font-semibold">Próx. Vacaciones</th>
                        <th class="px-4 py-2 text-center text-sm font-semibold">Solic. Vacaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contratos as $contrato)
                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 transition">
                            <td class="px-4 py-2 text-sm">{{ $contrato->empleado->name ?? 'Sin nombre' }}</td>
                            <td class="px-4 py-2 text-sm">
                                {{ $contrato->fechaInicio ? \Carbon\Carbon::parse($contrato->fechaInicio)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-2 text-center text-sm">{{ $contrato->vacaciones->dias_ganados ?? 0 }}</td>
                            <td class="px-4 py-2 text-center text-sm">{{ $contrato->vacaciones->dias_tomados ?? 0 }}</td>
                            <td class="px-4 py-2 text-center text-sm">{{ $contrato->vacaciones->dias_restantes ?? 0 }}</td>
                            <td class="px-4 py-2 text-center text-sm">
                                {{ $contrato->fechaExpiracion ? \Carbon\Carbon::parse($contrato->fechaExpiracion)->addDay()->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-2 text-center text-sm">
                                {{ $contrato->solicVacaciones ? \Carbon\Carbon::parse($contrato->solicVacaciones)->addDay()->format('d/m/Y') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">
                                No se encontraron registros de vacaciones.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $contratos->links() }}
        </div>
    </div>
</div>
