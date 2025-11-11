<div>
    <x-jet-dialog-modal wire:model="openModal" maxWidth="6xl">

        <x-slot name="title">
            Detalles de estado y comprobante
        </x-slot>

        <x-slot name="content">

            @if (empty($detallesServicios))
                <p class="text-gray-500">No hay detalles registrados.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 rounded-xl overflow-hidden">
                        <thead class="bg-slate-600 text-white">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider">#</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider">Taller</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider">Inspector</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider">Vehículo</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider">Servicio</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider">Fecha</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider">Precio</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-gray-50">
                            @foreach ($detallesServicios as $i => $d)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-5 py-3 text-sm text-gray-700">{{ $i + 1 }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-800">{{ $d['taller'] }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-800">{{ $d['inspector'] }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-800">{{ $d['vehiculo'] }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-800">{{ $d['servicio'] }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-800">{{ $d['fecha'] }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-800">S/ {{ number_format($d['precio'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('openModal', false)">
                Cerrar
            </x-jet-secondary-button>
        </x-slot>

    </x-jet-dialog-modal>
</div>
