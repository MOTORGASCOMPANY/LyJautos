<div class="mt-16 flex justify-center">
    <div class="bg-white p-6 rounded shadow max-w-xl w-full">
        <h2 class="text-lg font-bold text-indigo-600 mb-4">Mis comprobantes</h2>

        @if($sinPlanilla)
            <div class="text-center py-8 text-gray-500">
                <i class="fa-regular fa-folder-open fa-2x mb-3"></i>
                <p class="text-sm">Aún no tienes comprobantes registrados.</p>
            </div>
        @else
            <div class="mb-4">
                <label class="text-sm font-medium">Periodo</label>
                <select wire:model="periodoSeleccionado" class="border rounded px-2 py-1 ml-2">
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo }}">{{ $periodo }}</option>
                    @endforeach
                </select>
            </div>

            @forelse($detalles as $detalle)
                @if($detalle->archivos->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <i class="fa-regular fa-folder-open fa-2x mb-3"></i>
                        <p class="text-sm text-gray-500">No tienes archivos en este periodo.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($detalle->archivos->groupBy('tipo') as $tipo => $archivosTipo)
                            <div>
                                <h4 class="text-xs font-medium uppercase text-gray-500 mb-2">{{ ucfirst($tipo) }}</h4>
                                <div class="space-y-2">
                                    @foreach($archivosTipo as $archivo)
                                        <div class="flex items-center justify-between border rounded p-2">
                                            <div class="flex items-center space-x-3">
                                                @if(in_array(strtolower($archivo->extension), ['jpg','jpeg','png']))
                                                    <img src="{{ Storage::url($archivo->ruta) }}" class="w-12 h-12 object-cover rounded" />
                                                @else
                                                    <div class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded text-xs">PDF</div>
                                                @endif
                                                <div class="text-sm">
                                                    <div class="font-medium">{{ $archivo->nombre }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        Subido: {{ $archivo->created_at->format('d/m/Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ Storage::url($archivo->ruta) }}" target="_blank" class="px-2 py-1 border rounded text-xs">Ver</a>
                                                <a href="{{ Storage::url($archivo->ruta) }}" download class="px-2 py-1 border rounded text-xs">Descargar</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @empty
                <p class="text-sm text-gray-500">No tienes archivos en este periodo.</p>
            @endforelse
        @endif
    </div>
</div>
