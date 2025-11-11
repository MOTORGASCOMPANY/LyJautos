<div>
    <x-jet-dialog-modal wire:model="open" wire:key="archivos-{{ $detalleId }}">
        <x-slot name="title">
            Gestionar comprobantes –
            {{ $detalle?->contrato?->empleado?->name ?? ($detalle?->usuario?->name ?? 'Empleado no definido') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-6">

                {{-- Info empleado --}}
                <div class="bg-gray-50 p-4 rounded-md border">
                    <h3 class="font-semibold text-sm text-gray-700">Empleado</h3>
                    <p class="text-sm mt-2">
                        {{ $detalle?->contrato?->empleado?->name ?? ($detalle?->usuario?->name ?? '-') }}
                    </p>
                    <p class="text-xs text-gray-500">
                        Documento: {{ $detalle?->usuario?->email ?? '-' }}
                    </p>
                </div>

                {{-- Subir archivos --}}
                <div class="bg-gray-50 p-4 rounded-md border">
                    <h4 class="text-sm font-medium text-gray-700">Subir archivos</h4>
                    {{-- 
                        <label class="block text-xs mt-3 mb-1">Tipo</label>
                        <select wire:model="tipo" class="w-full border rounded px-2 py-1 text-sm">
                            @foreach ($tipos as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>                    

                        <div class="mt-3">
                            <x-file-pond wire:model="files" multiple accepted-file-types="['application/pdf','image/*']"
                                allow-reorder label-idle="Arrastra y suelta tus archivos o haz click">
                            </x-file-pond>
                        </div>
                    --}}

                    <!-- Zona Drag & Drop -->
                    <div class="mt-3 border-2 border-dashed border-indigo-500 rounded-lg p-6 text-center cursor-pointer hover:bg-indigo-50 transition"
                        x-data
                        @dragover.prevent
                        @drop.prevent="
                            const dt = $event.dataTransfer;
                            const input = $refs.fileInput;
                            // Combinar archivos existentes + nuevos
                            const dataTransfer = new DataTransfer();
                            for (let i = 0; i < input.files.length; i++) {
                                dataTransfer.items.add(input.files[i]);
                            }
                            for (let i = 0; i < dt.files.length; i++) {
                                dataTransfer.items.add(dt.files[i]);
                            }
                            input.files = dataTransfer.files;
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        ">

                        <p class="text-gray-500 text-sm">Arrastra tus archivos aquí o haz clic para seleccionarlos</p>

                        <input type="file" multiple wire:model="files" accept="application/pdf,image/*" 
                            class="hidden" x-ref="fileInput" wire:key="files-{{ $detalleId }}" />

                        <button type="button" 
                            class="mt-2 px-3 py-1 bg-indigo-600 text-white rounded text-xs"
                            @click="$refs.fileInput.click()">
                            Seleccionar archivos
                        </button>
                    </div>

                    @error('files.*') 
                        <span class="text-red-500 text-xs">{{ $message }}</span> 
                    @enderror

                    <!-- Previews -->
                    @if ($files)
                        <div class="mt-4 grid grid-cols-3 gap-3">
                            @foreach ($files as $file)
                                <div class="border rounded p-2 text-xs">
                                    @if (str_contains($file->getMimeType(), 'image'))
                                        <img src="{{ $file->temporaryUrl() }}" 
                                            class="w-full h-24 object-cover rounded" />
                                    @else
                                        <div class="flex items-center justify-center h-24 bg-gray-100 text-xs text-gray-600">
                                            {{ strtoupper($file->getClientOriginalExtension()) }}
                                        </div>
                                    @endif
                                    <p class="mt-1 truncate">{{ $file->getClientOriginalName() }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>

                {{-- Lista de archivos --}}
                <div class="bg-white p-4 rounded-md border shadow-sm overflow-hidden">
                    <h3 class="font-semibold text-sm mb-3 text-gray-700">
                        Archivos ({{ $detalle?->archivos?->count() ?? 0 }})
                    </h3>

                    @if ($detalle?->archivos?->isNotEmpty())
                        <div class="space-y-4">
                            @foreach ($detalle->archivos->groupBy('tipo') as $tipo => $archivosTipo)
                                <div>
                                    <h4 class="text-xs font-medium uppercase text-gray-500 mb-2">{{ ucfirst($tipo) }}</h4>
                                    <div class="space-y-2">
                                        @foreach ($archivosTipo as $archivo)
                                            <div class="border rounded p-2 hover:bg-gray-50">
                                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                                    
                                                    {{-- Izquierda: imagen + nombre --}}
                                                    <div class="flex items-center space-x-3 min-w-0 flex-1">
                                                        @if (in_array(strtolower($archivo->extension), ['jpg', 'jpeg', 'png', 'gif']))
                                                            <img src="{{ Storage::url($archivo->ruta) }}" alt="thumb"
                                                                class="w-12 h-12 object-cover rounded" />
                                                        @else
                                                            <div class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded text-xs text-gray-600">
                                                                PDF
                                                            </div>
                                                        @endif

                                                        <div class="text-sm min-w-0 flex-1">
                                                            <div class="font-medium text-gray-800 break-words line-clamp-2 leading-tight">
                                                                {{ $archivo->nombre }}
                                                            </div>
                                                            <div class="text-xs text-gray-500">
                                                                Subido: {{ $archivo->created_at?->format('d/m/Y H:i') ?? '-' }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Derecha: acciones --}}
                                                    <div class="flex flex-wrap md:flex-nowrap items-center gap-2 flex-shrink-0">
                                                        <select class="text-xs border rounded px-2 py-1"
                                                            wire:change="updateTipo({{ $archivo->id }}, $event.target.value)">
                                                            @foreach ($tipos as $k => $v)
                                                                <option value="{{ $k }}" @if ($archivo->tipo === $k) selected @endif>
                                                                    {{ $v }}
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                        <a href="{{ Storage::url($archivo->ruta) }}" target="_blank"
                                                            class="px-2 py-1 border rounded text-xs whitespace-nowrap">Ver</a>
                                                        <a href="{{ Storage::url($archivo->ruta) }}" download
                                                            class="px-2 py-1 border rounded text-xs whitespace-nowrap">Descargar</a>
                                                        <button wire:click="deleteArchivo({{ $archivo->id }})"
                                                            class="px-2 py-1 bg-red-500 text-white rounded text-xs whitespace-nowrap">
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-gray-500">No hay archivos aún.</div>
                    @endif
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('open', false)" class="mx-2">
                Cerrar
            </x-jet-secondary-button>
            <x-jet-button wire:click="upload" wire:loading.attr="disabled" wire:target="upload,files">
                Guardar {{ count($files) ? '(' . count($files) . ')' : '' }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>