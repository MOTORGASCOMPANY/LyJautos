<div class="container mx-auto py-12 mt-4">
    <div class="flex flex-row  justify-between items-center">
        <h2 class="mt-8 font-bold text-lg text-indigo-600"> Vacaciones del Empleado {{ $empleado->empleado->name ?? '' }}
        </h2>
        <div class="flex space-x-2">
            <!-- Para registro de vacaciones asignadas -->
            @hasanyrole('administrador|Administrador del sistema')
                @livewire('vacaciones-asignadas', ['contratoId' => $empleado->id ?? ''])
            @endhasanyrole

            <!-- Agregue esto para solicitud de vacaciones -->
                @livewire('vacaciones-solicitud', ['contratoId' => $empleado->id ?? ''])

            <button data-tooltip-target="tooltip-dark" type="button" wire:click="regresar" wire:target="regresar"
                class="group flex py-4 px-4 text-center rounded-md bg-indigo-400 font-bold text-white cursor-pointer hover:bg-indigo-500 hover:animate-pulse">
                <i class="fa-solid fa-rotate-left"></i>
                <span
                    class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-100">
                    Regresar
                </span>
            </button>
        </div>
    </div>

    <hr class="my-4">
    @if (isset($empleado->vacaciones))
        <div>
            @livewire('registro-vacacion', ['contratoId' => $empleado->id], key($empleado->idUser . '-' . $empleado->id))
        </div>
    @endif

    @if ($empleado->vacaciones->solicitudes->count())
        <div class="mt-6">
            <h3 class="font-semibold text-indigo-600 mb-2">Solicitudes Vacaciones Registradas</h3>
            <div class="overflow-x-auto rounded-2xl shadow-sm border border-slate-200">
                <table class="min-w-full border-collapse overflow-hidden rounded-2xl">
                    <thead class="bg-slate-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold rounded-tl-2xl">Inicio Deseado</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">Término Deseado</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">Comentario</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold rounded-tr-2xl">Registrado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($empleado->vacaciones->solicitudes as $sol)
                            <tr class="hover:bg-gray-100 transition-colors duration-150 border-b last:border-0">
                                <td class="px-6 py-3 text-sm text-gray-700 rounded-l-2xl">
                                    {{ $sol->f_inicio_deseado }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700">
                                    {{ $sol->f_termino_deseado }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700">
                                    {{ $sol->comentario }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700 rounded-r-2xl">
                                    {{ $sol->created_at->format('d/m/Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
