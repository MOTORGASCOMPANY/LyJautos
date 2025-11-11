<div class="flex box-border">
    <div class="container mx-auto py-12">
        <div class="max-w-screen-2xl mx-auto">
            <x-table-planillas :periodos="$periodos">

                <x-slot name="titulo">
                    <h2 class="text-indigo-600 font-bold text-2xl flex items-center space-x-2">
                        <i class="fa-solid fa-table fa-xl"></i>
                        <span>Tabla de Planillas</span>
                    </h2>
                </x-slot>

                <x-slot name="btnAgregar">
                    <button wire:click="$emit('abrirCrearPlanilla')"
                        class="bg-indigo-600 px-6 py-4 rounded-md text-white font-semibold tracking-wide cursor-pointer">
                        Planilla &nbsp;<i class="fas fa-plus"></i>
                    </button>
                </x-slot>

                <x-slot name="contenido">
                    @if ($periodoSeleccionado)
                        <div class="overflow-x-auto bg-white shadow rounded-lg">
                            <table class="w-full text-sm table-auto">
                                <thead class="bg-slate-600 text-white">
                                    <tr>
                                        <th class="px-2 py-2">#</th>
                                        <th class="px-2 py-2">Inspector</th>
                                        <th class="px-2 py-2">Taller</th>
                                        <th class="px-2 py-2">Celular</th>
                                        <th class="px-2 py-2">Fecha Ingreso</th>
                                        <th class="px-2 py-2">Sueldo Neto</th>
                                        <th class="px-2 py-2">Sueldo Base</th>                                        
                                        <th class="px-2 py-2">Planilla</th>
                                        <th class="px-2 py-2">Observación</th>
                                        <th class="px-2 py-2">N° Tarjeta</th>
                                        <th class="px-2 py-2">Total Pago</th>
                                        <th class="px-2 py-2">Pagado</th>
                                        <th class="px-2 py-2">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($detalles as $i => $detalle)
                                        <tr class="border-b hover:bg-gray-100">
                                            <td class="px-2 py-2 text-center">{{ $i + 1 }}</td>
                                            <td class="px-2 py-2">
                                                {{ optional(optional($detalle->contrato)->empleado)->name ?? optional($detalle->usuario)->name }}
                                            </td>
                                            <td class="px-2 py-2 text-center text-xs text-gray-500">
                                                {{ $detalle->taller }}
                                            </td>
                                            <td class="px-2 py-2 text-center text-xs">
                                                {{ optional(optional($detalle->contrato)->empleado)->celular ?? (optional($detalle->usuario)->celular ?? '-') }}
                                            </td>
                                            <td class="px-2 py-2 text-center text-xs text-gray-500">
                                                {{ optional($detalle->contrato)->fechaInicio ?? '-' }}
                                            </td>
                                            <td class="px-2 py-2 text-center text-xs text-gray-500">
                                                {{ number_format(optional($detalle->contrato)->sueldo_neto ?? 0, 2) }}
                                            </td>
                                            <td class="px-2 py-2 text-center text-xs text-gray-500">
                                                {{ $detalle->sueldo_base ? number_format($detalle->sueldo_base, 2) : '-' }}
                                            </td>                                            
                                            <td class="px-2 py-2 text-center text-xs text-gray-500">{{ $detalle->planilla }}</td>
                                            {{--<td class="px-3 py-3">{{ $detalle->observacion }}</td>--}}
                                            <td class="px-4 py-2 text-center text-xs text-gray-500">
                                                @php
                                                    $observaciones = [];

                                                    if ($detalle->horas_extras > 0) {
                                                        $observaciones[] = "Hrs extras: {$detalle->horas_extras}";
                                                    }
                                                    if ($detalle->pasajes > 0) {
                                                        $observaciones[] = "Pasajes: {$detalle->pasajes}";
                                                    }
                                                    if ($detalle->otros > 0) {
                                                        $observaciones[] = "Otros: {$detalle->otros}";
                                                    }
                                                    if ($detalle->descuentos > 0) {
                                                        $observaciones[] = "Descuentos: {$detalle->descuentos}";
                                                    }
                                                @endphp

                                                {{ $detalle->observacion }}
                                                @if(count($observaciones))
                                                    @if($detalle->observacion) , @endif
                                                    {{ implode(' , ', $observaciones) }}
                                                @endif
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                                {{ optional(optional($detalle->contrato)->empleado)->numero_cuenta ?? (optional($detalle->usuario)->numero_cuenta ?? '-') }}
                                            </td>
                                            <td class="px-2 py-2 text-center font-bold text-green-600">
                                                {{ number_format($detalle->total_pago, 2) }}
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                                <div class="flex justify-center items-center space-x-2">
                                                    <input type="checkbox" wire:click="togglePago({{ $detalle->id }})"
                                                        @checked($detalle->pagado)
                                                        class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="flex justify-center items-center space-x-2">
                                                    <button type="button"
                                                        wire:click="$emit('abrirArchivos', {{ $detalle->id }})"
                                                        class="group flex py-2 px-2 text-center items-center rounded-md bg-yellow-300 font-bold text-white cursor-pointer hover:bg-yellow-400 hover:animate-pulse">
                                                        <i class="fa-solid fa-folder"></i>
                                                        <span
                                                            class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-50">
                                                            Archivos
                                                        </span>
                                                    </button>
                                                    <button wire:click="edit({{ $detalle->id }})"
                                                        class="group flex py-2 px-2 text-center items-center rounded-md bg-blue-300 font-bold text-white cursor-pointer hover:bg-blue-400 hover:animate-pulse">
                                                        <i class="fa fa-pencil"></i>
                                                        <span
                                                            class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-50">
                                                            Editar
                                                        </span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="border-b hover:bg-gray-100">
                                        <td colspan="10" class="font-bold text-right pr-2">Total Planilla:</td>
                                        <td class="px-3 py-3 text-center font-bold text-green-600">
                                            {{ number_format($totalPlanilla, 2) }}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                            {{-- 
                            <div class="p-3">
                                {{ $detalles->links() }}
                            </div>
                            --}}
                        </div>
                    @else
                        <div class="px-6 py-4 text-center font-bold bg-indigo-200 rounded-md">
                            Seleccione un periodo para ver los detalles de la planilla.
                        </div>
                    @endif

                    <!-- Componente crear planilla -->
                    @livewire('crear-planilla')

                    <!-- Componente archivos -->
                    @livewire('planilla-archivos')

                </x-slot>
            </x-table-planillas>
        </div>
    </div>


    <!-- Dialog modal para editar datos -->
    <x-jet-dialog-modal wire:model="openEdit">
        <x-slot name="title">
            Editar Planilla Detalle
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-jet-label for="sueldo_base" value="Sueldo base" />
                    <x-jet-input id="sueldo_base" type="number" step="0.01" wire:model="form.sueldo_base"
                        class="w-full" />
                    <x-jet-input-error for="form.sueldo_base" class="mt-1" />
                </div>

                <div>
                    <x-jet-label for="horas_extras" value="Horas extras" />
                    <x-jet-input id="horas_extras" type="number" step="0.01" wire:model="form.horas_extras"
                        class="w-full" />
                    <x-jet-input-error for="form.horas_extras" class="mt-1" />
                </div>

                <div>
                    <x-jet-label for="otros" value="Otros" />
                    <x-jet-input id="otros" type="number" step="0.01" wire:model="form.otros"
                        class="w-full" />
                    <x-jet-input-error for="form.otros" class="mt-1" />
                </div>

                <div>
                    <x-jet-label for="pasajes" value="Pasajes" />
                    <x-jet-input id="pasajes" type="number" step="0.01" wire:model="form.pasajes"
                        class="w-full" />
                    <x-jet-input-error for="form.pasajes" class="mt-1" />
                </div>

                <div>
                    <x-jet-label for="descuentos" value="Descuentos" />
                    <x-jet-input id="descuentos" type="number" step="0.01" wire:model="form.descuentos"
                        class="w-full" />
                    <x-jet-input-error for="form.descuentos" class="mt-1" />
                </div>

                <div>
                    <x-jet-label for="total_pago" value="Total Pago" />
                    <x-jet-input id="total_pago" type="number" step="0.01" wire:model="form.total_pago"
                        class="w-full" readonly />
                    <x-jet-input-error for="form.total_pago" class="mt-1" />
                </div>

                <div class="col-span-2">
                    <x-jet-label for="taller" value="Taller" />
                    <x-jet-input id="taller" type="text" wire:model.defer="form.taller" class="w-full" />
                    <x-jet-input-error for="form.taller" class="mt-1" />
                </div>

                <div class="col-span-2">
                    <x-jet-label for="planilla" value="Planilla" />
                    <x-jet-input id="planilla" type="text" wire:model.defer="form.planilla" class="w-full" />
                    <x-jet-input-error for="form.planilla" class="mt-1" />
                </div>

                <div class="col-span-2">
                    <x-jet-label for="observacion" value="Observación" />
                    <textarea id="observacion" wire:model.defer="form.observacion"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                    <x-jet-input-error for="form.observacion" class="mt-1" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('openEdit',false)" class="mx-2">
                Cancelar
            </x-jet-secondary-button>

            <x-jet-button wire:click="update" wire:loading.attr="disabled" wire:target="update">
                Guardar
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
