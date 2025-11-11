<div class="bg-gray py-6 px-4 md:px-8 xl:px-10">
    <div class="mt-7 max-w-screen-2xl mx-auto">
        <div class="mb-6">
            <h2 class="text-indigo-900 font-bold text-3xl">Registro de Empleados</h2>
        </div>
        <!-- Filtros -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <!-- Mostrar -->
            <div class="flex bg-gray-50 items-center p-2 rounded-md">
                <span>Mostrar</span>
                <select wire:model="cant" class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>Entradas</span>
            </div>
            <!-- Planilla/Externo  -->
            @hasanyrole('administrador|Administrador del sistema|auditoria')
                <div class="flex bg-gray-50 items-center p-2 rounded-md">
                    <span>Estado: </span>
                    <select wire:model="es" class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block">
                        <option value="">Seleccione</option>
                        <option value="planilla">Planilla</option>
                        <option value="externo">Externo</option>
                    </select>                
                </div>
            @endhasanyrole
            <!-- Buscar -->
            <div class="flex bg-gray-50 items-center w-full md:w-1/2 p-2 rounded-md">
            <!--div class="flex bg-gray-50 items-center w-full md:w-1/3 lg:w-1/4 p-2 rounded-md"-->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                        clip-rule="evenodd" />
                </svg>
                <input class="bg-gray-50 outline-none block rounded-md border-indigo-500 w-full ml-2" type="text"
                    wire:model="search" placeholder="Buscar...">
            </div>
            <!-- Agregar -->
            @hasanyrole('administrador|Administrador del sistema|auditoria')
                <div class="flex items-center space-x-3">
                    {{-- 
                        <button wire:click="agregar"
                            class="bg-indigo-600 px-6 py-3 rounded-md text-white font-semibold tracking-wide cursor-pointer hover:bg-indigo-700">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            Contrato
                        </button>
                    --}}
                    <button wire:click="agregar"
                        class="relative flex items-center justify-start bg-indigo-600 text-white rounded-md w-12 h-12 overflow-hidden transition-all duration-300 hover:w-36 hover:bg-indigo-700 group p-3">
                        <div class="flex items-center justify-center w-full h-full">
                            <i class="fa fa-plus text-lg transition-transform duration-300 group-hover:translate-x-0"></i>
                            <span
                                class="absolute left-1/2 ml-3 opacity-0 group-hover:opacity-100 group-hover:relative group-hover:left-0 text-sm font-semibold transition-all duration-300 whitespace-nowrap translate-x-3 group-hover:translate-x-0">
                                Contrato
                            </span>
                        </div>
                    </button>
                    <a href="{{ route('pdf.resumen.vacaciones') }}" target="_blank"
                        class="relative flex items-center justify-start bg-indigo-600 text-white rounded-md w-12 h-12 overflow-hidden transition-all duration-300 hover:w-36 hover:bg-indigo-700 group p-3">
                        <div class="flex items-center justify-center w-full h-full">
                            <i class="fa fa-umbrella-beach text-lg transition-transform duration-300 group-hover:translate-x-0"></i>
                            <span
                                class="absolute left-1/2 ml-3 opacity-0 group-hover:opacity-100 group-hover:relative group-hover:left-0 text-sm font-semibold transition-all duration-300 whitespace-nowrap translate-x-3 group-hover:translate-x-0">
                                Rsmn.Vacac
                            </span>
                        </div>
                    </a>
                </div>
            @endhasanyrole
        </div>

        <!-- Tabla -->
        @if ($empleados->count() > 0)
            <table class="w-full whitespace-nowrap">
                <thead class="bg-slate-600 border-b font-bold text-white">
                    <tr>
                        <th scope="col" class=" bg-gray-100" colspan="8"></th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white" colspan="3">
                            <div class="flex justify-center items-center">
                                <span>Vacaciones</span>
                            </div>
                        </th>
                        <th scope="col" class="bg-gray-100">
                            <div class="flex justify-center items-center">
                                <span></span>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-6 py-3 text-left"
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
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-6 py-3 text-left">
                            Empleado
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-4 py-3 text-left">
                            Dni
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-4 py-3 text-left">
                            F. Base
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-4 py-3 text-left">
                            F. Inicio
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-4 py-3 text-left">
                            F. Fin
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-4 py-3 text-left">
                            Area
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white px-4 py-3 text-left">
                            Documentos
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white  py-3">
                            <div class="flex justify-center items-center flex-col">
                                <span>Ganados</span>
                            </div>
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white py-3">
                            <div class="flex justify-center items-center flex-col">
                                <span>Tomados</span>
                            </div>
                        </th>
                        <th scope="col" class="text-sm font-medium font-semibold text-white py-3">
                            <div class="flex justify-center items-center flex-col">
                                <span>Restantes</span>
                            </div>
                        </th>
                        @hasanyrole('administrador|Administrador del sistema')
                            <th scope="col" class="text-sm font-medium font-semibold text-white px-12 py-3 text-left">
                                <div class="flex justify-center items-center">
                                    <span>Acciones</span>
                                </div>
                            </th>
                        @endhasanyrole
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($empleados as $emple)
                        <tr tabindex="0" class="focus:outline-none h-16 border border-slate-300 rounded hover:bg-gray-300">
                            <!-- ID -->
                            <td class="pl-5">
                                <div class="flex items-center">
                                    <p class="text-indigo-900 p-1 bg-indigo-200 rounded-md">
                                        {{ $emple->id }}
                                    </p>
                                </div>
                            </td>
                            <!-- EMPLEADO -->
                            <td class="pl-2">
                                <div class="flex items-center">
                                    <p class="text-sm leading-none text-gray-600 ml-2">
                                        {{ $emple->empleado->name ?? 'N.A' }}
                                    </p>
                                </div>
                            </td>
                            <!-- DNI -->
                            <td class="pl-2">
                                <div class="flex items-center">
                                    <p class="text-sm leading-none text-gray-600 ml-2 p-2 bg-green-200 rounded-full">
                                        {{ $emple->dniEmpleado }}
                                    </p>
                                </div>
                            </td>
                            <!-- FECHA BASE -->
                            <td>
                                <div class="flex items-center">
                                    <p class="text-sm leading-none text-gray-600 ml-2">
                                        {{ \Carbon\Carbon::parse($emple->fechaInicio)->format('d-m-Y') }}
                                    </p>
                                </div>
                            </td>
                            <!-- FECHA INICIO -->
                            <td>
                                <div class="flex items-center">
                                    <p class="text-sm leading-none text-gray-600 ml-2">
                                        {{ \Carbon\Carbon::parse($emple->fechaIniciodos)->format('d-m-Y') }}
                                    </p>
                                </div>
                            </td>
                            <!-- FECHA EXPIRACION -->
                            <td>
                                <div class="flex items-center">
                                    <p class="text-sm leading-none text-gray-600 ml-2">
                                        {{ \Carbon\Carbon::parse($emple->fechaExpiracion)->format('d-m-Y') }}
                                    </p>
                                </div>
                            </td>
                            <!-- AREA -->
                            <td>
                                <div class="flex items-center">
                                    <p class="text-sm leading-none text-gray-600 ml-2">
                                        {{ $emple->cargo }}
                                    </p>
                                </div>
                            </td>
                            <!-- DOCUMENTOS -->
                            <td>
                                <div class="relative px-5 text-center" x-data="{ menu: false }">
                                    <button type="button" x-on:click="menu = ! menu" id="menu-button"
                                        aria-expanded="true" aria-haspopup="true" data-te-ripple-init
                                        data-te-ripple-color="light"
                                        class="hover:animate-pulse inline-block rounded-full bg-amber-400 p-2 uppercase leading-normal text-white shadow-md transition duration-150 ease-in-out hover:bg-amber-600 hover:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.3),0_4px_18px_0_rgba(59,113,202,0.2)] focus:bg-amber-600 focus:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.3),0_4px_18px_0_rgba(59,113,202,0.2)] focus:outline-none focus:ring-0 active:bg-amber-700 active:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.3),0_4px_18px_0_rgba(59,113,202,0.2)]">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="h-4 w-4">
                                            <path fill-rule="evenodd"
                                                d="M19.5 21a3 3 0 003-3V9a3 3 0 00-3-3h-5.379a.75.75 0 01-.53-.22L11.47 3.66A2.25 2.25 0 009.879 3H4.5a3 3 0 00-3 3v12a3 3 0 003 3h15zm-6.75-10.5a.75.75 0 00-1.5 0v4.19l-1.72-1.72a.75.75 0 00-1.06 1.06l3 3a.75.75 0 001.06 0l3-3a.75.75 0 10-1.06-1.06l-1.72 1.72V10.5z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div x-show="menu" x-on:click.away="menu = false"
                                        class="origin-top-right absolute right-12 mt-2 w-56 rounded-md shadow-lg bg-gray-300 ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-40"
                                        role="menu" aria-orientation="vertical" aria-labelledby="menu-button"
                                        tabindex="-1">
                                        <div class="" role="none">
                                            @php
                                                $esExterno = $emple->cont_externo == 1; // Verifica si el empleado es externo
                                                $ruta = $esExterno
                                                    ? route('contratoTrabajo', ['id' => $emple->id])
                                                    : route('contratoPlanilla', ['id' => $emple->id]);
                                            @endphp
                                            <a href="{{ $ruta }}" target="_blank" rel="noopener noreferrer"
                                                class="flex px-4 py-2 text-sm text-indigo-700 hover:bg-slate-600 hover:text-white justify-between items-center rounded-t-md hover:cursor-pointer">
                                                <i class="fa-solid fa-file-pdf"></i>
                                                <span>Pdf Contrato</span>
                                            </a>
                                            <a wire:click="redirectVacacion({{ $emple->id }})" target="__blank"
                                                rel="noopener noreferrer"
                                                class="flex px-4 py-2 text-sm text-indigo-700 hover:bg-slate-600 hover:text-white justify-between items-center rounded-t-md hover:cursor-pointer">
                                                <i class="fa fa-plane" aria-hidden="true"></i>
                                                <span>Vacaciones</span>
                                            </a>
                                            <a wire:click="redirectContrato({{ $emple->id }})" target="__blank"
                                                rel="noopener noreferrer"
                                                class="flex px-4 py-2 text-sm text-indigo-700 hover:bg-slate-600 hover:text-white justify-between items-center rounded-t-md hover:cursor-pointer">
                                                <i class="fas fa-folder-plus"></i>
                                                <span>Documentos</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <!-- DIAS GANADOS -->
                            <td>
                                <div class="flex justify-center items-center">
                                    <p class="text-sm leading-none text-gray-600 ml-2">
                                        {{ $emple->vacaciones->dias_ganados ?? 'N.A' }}
                                    </p>
                                </div>
                            </td>
                            <!-- DIAS TOMADOS -->
                            <td>
                                <div class="flex justify-center items-center">
                                    <p class="text-sm leading-none text-gray-600 ml-2">
                                        {{ $emple->vacaciones->dias_tomados ?? 'N.A' }}
                                    </p>
                                </div>
                            </td>
                            <!-- DIAS RESTANTES -->
                            <td>
                                <div class="flex justify-center items-center">
                                    <p class="text-sm leading-none text-gray-600 ml-2">
                                        {{ $emple->vacaciones->dias_restantes ?? 'N.A' }}
                                    </p>
                                </div>
                            </td>
                            <!-- ACCIONES -->
                            @hasanyrole('administrador|Administrador del sistema')
                                <td class="text-center">
                                    <div class="flex justify-center items-center space-x-2">
                                        <button wire:click="abrirModal({{ $emple->id }})"
                                            class="group flex py-2 px-2 text-center items-center rounded-md bg-blue-300 font-bold text-white cursor-pointer hover:bg-blue-400 hover:animate-pulse">
                                            <i class="fa fa-pencil"></i>
                                            <span
                                                class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-50">
                                                Editar
                                            </span>
                                        </button>
                                        <button wire:click="$emit('deleteContrato',{{ $emple->id }})"
                                            class="group flex py-2 px-2 text-center items-center rounded-md bg-red-500 font-bold text-white cursor-pointer hover:bg-red-700 hover:animate-pulse">
                                            <i class="fas fa-times-circle"></i>
                                            <span
                                                class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute  translate-y-full opacity-0 m-4 mx-auto z-50">
                                                Eliminar
                                            </span>
                                        </button>
                                    </div>
                                </td>
                            @endhasanyrole
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">
                {{ $empleados->links() }}
            </div>
        @else
            <div class="text-center mt-4 text-gray-500">
                <p>No tienes empleados en este momento.</p>
            </div>
        @endif
    </div>

    {{-- EDITAR CONTRATO --}}
    <x-jet-dialog-modal wire:model="openEdit">
        <x-slot name="title">
            <h1 class="text-xl font-bold">Editando contrato</h1>
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-2 gap-4 py-2">
                <div>
                    <x-jet-label value="Dni:" />
                    <x-jet-input type="text"
                        class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full "
                        wire:model="emp.dniEmpleado" />
                    <x-jet-input-error for="emp.dniEmpleado" />
                </div>
                <div>
                    <x-jet-label value="Domicilio:" />
                    <x-jet-input type="text"
                        class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full "
                        wire:model="emp.domicilioEmpleado" />
                    <x-jet-input-error for="emp.domicilioEmpleado" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 py-2">
                <div>
                    <x-jet-label value="Celular:" />
                    <x-jet-input type="number"
                        class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full "
                        wire:model="emp.celularEmpleado" />
                    <x-jet-input-error for="emp.celularEmpleado" />
                </div>
                <div>
                    <x-jet-label value="Correo:" />
                    <x-jet-input type="email"
                        class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full "
                        wire:model="emp.correoEmpleado" />
                    <x-jet-input-error for="emp.correoEmpleado" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 py-2">
                <div>
                    <x-jet-label value="Fecha Base:" />
                    <x-jet-input type="date"
                        class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full "
                        wire:model="emp.fechaInicio"/>
                    <x-jet-input-error for="emp.fechaInicio" />
                </div>
                <div>
                    <x-jet-label value="Fecha de Nacimiento:" />
                    <x-jet-input type="date"
                        class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full "
                        wire:model="emp.cumpleaosEmpleado" />
                    <x-jet-input-error for="emp.cumpleaosEmpleado" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 py-2">
                <div>
                    <x-jet-label value="Fecha Inicio:" />
                    <x-jet-input type="date"
                        class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full "
                        wire:model="emp.fechaIniciodos" />
                    <x-jet-input-error for="emp.fechaIniciodos" />
                </div>
                <div>
                    <x-jet-label value="Fecha Expiración:" />
                    <x-jet-input type="date"
                        class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full "
                        wire:model="emp.fechaExpiracion" />
                    <x-jet-input-error for="emp.fechaExpiracion " />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 py-2">
                <div>
                    <x-jet-label value="Cargo:" />
                    <x-jet-input type="text"
                        class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full "
                        wire:model="emp.cargo" />
                    <x-jet-input-error for="emp.cargo" />
                </div>
                <div>
                    <x-jet-label value="Monto:" />
                    <x-jet-input type="number"
                        class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full "
                        wire:model="emp.pago" />
                    <x-jet-input-error for="emp.pago" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 py-2">
                <div>
                    <x-jet-label value="Sueldo Neto:" />
                    <x-jet-input type="number" class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full "
                        wire:model="emp.sueldo_neto" />
                    <x-jet-input-error for="emp.sueldo_neto" />
                </div>
                {{-- Eliminamos porque empleados o inspectores eventuales no tienen contrato, pasamos a la tabla users
                <div>
                    <x-jet-label value="N° Cuenta:" />
                    <x-jet-input type="text" class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full "
                        wire:model="emp.numero_cuenta" />
                    <x-jet-input-error for="emp.numero_cuenta" />
                </div>
                --}}
            </div>
            <!-- Checkbox para mostrar contrato externo -->
            <div class="flex items-center my-4">
                <input type="checkbox" id="emp.cont_externo" wire:model="emp.cont_externo"
                    class="mr-2 text-indigo-500 focus:ring-indigo-400 focus:ring-opacity-25 border-gray-300 rounded">
                <label for="emp.cont_externo" class="text-gray-700">¿Contrato Externo?</label>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('openEdit',false)" class="mx-2">
                Cancelar
            </x-jet-secondary-button>
            <x-jet-button loading:attribute="disabled" wire:click="editarContrato" wire:target="editarContrato">
                Actualizar
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>


    {{-- JS --}}
    @push('js')
        <script>
            Livewire.on('deleteContrato', contratoId => {
                Swal.fire({
                    title: '¿Estas seguro de eliminar este contrato?',
                    text: "una vez eliminado este contrato, no podras recuperarlo.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Si, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {

                        Livewire.emitTo('empleados', 'eliminarContrato', contratoId);

                        Swal.fire(
                            'Listo!',
                            'Contrato eliminado correctamente.',
                            'success'
                        )
                    }
                })
            });
        </script>
    @endpush
</div>


{{-- 
@php
    $esExterno = $emple->cont_externo == 1; // Verifica si el empleado es externo
    $ruta = $esExterno
        ? route('contratoTrabajo', ['id' => $emple->id])
        : route('contratoPlanilla', ['id' => $emple->id]);
@endphp
<a href="{{ $ruta }}" target="_blank"
    class="group flex py-2 px-2 text-center items-center rounded-md bg-blue-300 font-bold text-white cursor-pointer hover:bg-blue-400 hover:animate-pulse">
    <i class="fa-solid fa-file-pdf"></i>
    <span class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-100">
        Contrato
    </span>
</a>
--}}

{{-- 
<a wire:click="redirectContrato({{ $emple->id }})"
    class="group flex py-2 px-2 text-center items-center rounded-md bg-green-300 font-bold text-white cursor-pointer hover:bg-green-400 hover:animate-pulse">
    <i class="fas fa-folder-plus"></i>
    <span
        class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-100">
        Datos/Doc
    </span>
</a>
<button wire:click="redirectVacacion({{ $emple->id }})"
    class="group flex py-2 px-2 text-center items-center rounded-md bg-yellow-500 font-bold text-white cursor-pointer hover:bg-ywllow-700 hover:animate-pulse">
    <i class="fa fa-plane" aria-hidden="true"></i>
    <span
        class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute  translate-y-full opacity-0 m-4 mx-auto z-50">
        Vacaciones
    </span>
</button>
--}}
