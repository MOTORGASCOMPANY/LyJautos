<div>
    <div class="mb-4">
        <x-jet-label value="Marca:" />
        <x-jet-input type="text" class="w-full" wire:model="equipo.marca" />
        <x-jet-input-error for="equipo.marca"/>
    </div> 
    <div class="mb-4">
        <x-jet-label value="N° de serie:"/>
        <x-jet-input type="text" class="w-full"  wire:model="equipo.numSerie"/>
        <x-jet-input-error for="equipo.numSerie"/>
    </div> 
    <div class="mb-4">
        <x-jet-label value="Capacidad:"/>
        <x-jet-input type="number" class="w-full" wire:model="equipo.capacidad" inputmode="numeric"  pattern="[0-9]*"/>
        <x-jet-input-error for="equipo.capacidad"/>
    </div>   
    <div class="mb-4">
        <x-jet-label value="Fecha de Fabricación:"/>
        <x-jet-input type="date" class="w-full" wire:model="equipo.fechaFab" />
        <x-jet-input-error for="equipo.fechaFab"/>
    </div>
    <div class="mb-4">
        <x-jet-label value="¿Aplica fecha fin de vida útil?" />
        <label class="flex items-center space-x-2">
            <input type="checkbox" wire:model="equipo.fechaCaducidad_aplica" class="rounded border-gray-300 text-indigo-600 shadow-sm">
            <span class="text-sm text-gray-700">Sí, este equipo tiene fecha de caducidad</span>
        </label>
    </div>
    <div class="mb-4">
        <x-jet-label value="Fecha fin de vida util:"/>
        <x-jet-input type="date" class="w-full" wire:model="equipo.fechaCaducidad" />
        <x-jet-input-error for="equipo.fechaCaducidad"/>
    </div>
    <div class="mb-4">
        <x-jet-label value="Peso:"/>
        <x-jet-input type="number" class="w-full" wire:model="equipo.peso" inputmode="numeric"/>
        <x-jet-input-error for="equipo.peso"/>
    </div>
    <div class="mb-4">
        <x-jet-label value="Clase GNV"/>
        {{--<x-jet-input type="text" class="w-full"  wire:model="equipo.tipo"/>--}}
        <select wire:model="equipo.claseGnv"
                    class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full ">
                    <option value="">Seleccione</option>
                    <option value="I">Tipo I</option>
                    <option value="II">Tipo II</option>
                    <option value="III">Tipo III</option>
                    <option value="IV">Tipo IV</option>
                    <option value="V">Tipo V</option>
                </select>
        <x-jet-input-error for="equipo.claseGnv"/>
    </div>    
</div>