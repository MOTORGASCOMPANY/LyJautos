<div class="container mx-auto mt-8">
    <div class="block justify-center mt-2 pt-8 max-h-max pb-8">
        <h1 class="text-center text-xl my-2 font-bold text-indigo-600">Configuración del Candado</h1>

        <div x-data="{ candadoActivo: @entangle('candadoActivo') }" class="rounded-xl m-4 bg-white p-8 mx-auto max-w-max shadow-lg">
            <div class="flex items-center justify-center space-x-4 mb-6">
                <!-- Icono del candado cerrado -->
                <i class="fas fa-lock text-green-500 transition-all duration-500 ease-in-out"
                   style="font-size: 3rem;"
                   x-show="candadoActivo"></i>

                <!-- Icono del candado abierto -->
                <i class="fas fa-lock-open text-red-500 transition-all duration-500 ease-in-out"
                   style="font-size: 3rem;"
                   x-show="!candadoActivo"></i>

                <!-- Checkbox para activar/desactivar el candado -->
                <x-jet-label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" wire:model="candadoActivo" wire:change="actualizarEstado" class="form-checkbox text-indigo-600">
                    <span class="text-gray-700" x-text="candadoActivo ? 'Desactivar validación de los 7 días' : 'Activar validación de los 7 días'"></span>
                </x-jet-label>
            </div>

            <!-- Botón para guardar cambios -->
            {{-- 
            <div class="flex items-center justify-center">
                <button class="p-3 bg-indigo-500 rounded-xl text-white text-sm hover:font-bold hover:bg-indigo-700"
                        @click="$wire.actualizarEstado()">Guardar Cambios</button>
            </div>
            --}}
        </div>
    </div>
</div>
