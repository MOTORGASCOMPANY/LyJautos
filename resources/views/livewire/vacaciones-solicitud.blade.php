<div>
    <button data-tooltip-target="tooltip-dark" type="button" wire:click="$set('addSolicitud',true)"
        class="group flex py-4 px-4 text-center rounded-md bg-yellow-300 font-bold text-white cursor-pointer hover:bg-yellow-400 hover:animate-pulse">
        <i class="fa-solid fa-calendar-plus"></i>
        <span
            class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-100">
            Solicitar Vacación
        </span>
    </button>

    <x-jet-dialog-modal wire:model="addSolicitud">
        <x-slot name="title">
            <h1 class="text-xl font-bold">Nueva Solicitud de Vacaciones</h1>
        </x-slot>

        <x-slot name="content">
            <div class="space-y-3">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-jet-label for="f_inicio_deseado" value="Inicio Deseado:" />
                        <x-jet-input type="date" id="f_inicio_deseado" wire:model="f_inicio_deseado" class="w-full" />
                        <x-jet-input-error for="f_inicio_deseado" />
                    </div>
                    <div>
                        <x-jet-label for="f_termino_deseado" value="Término Deseado:" />
                        <x-jet-input type="date" id="f_termino_deseado" wire:model="f_termino_deseado" class="w-full" />
                        <x-jet-input-error for="f_termino_deseado" />
                    </div>
                </div>
                <div>
                    <x-jet-label value="Comentario (opcional):" />
                    <x-textarea wire:model="comentario" rows="3" class="w-full bg-gray-50 border-indigo-500 rounded-md" />
                    <x-jet-input-error for="comentario" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('addSolicitud', false)" class="mx-2">
                Cancelar
            </x-jet-secondary-button>

            <x-jet-button  wire:click="solicitarVacacion" wire:target="solicitarVacacion" wire:loading.attr="disabled">
                Guardar
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
