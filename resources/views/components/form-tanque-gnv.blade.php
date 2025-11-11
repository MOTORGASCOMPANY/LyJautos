@props(['equipofechCaducaAplica'])
<div>
    <div class="mb-4">
        <x-jet-label value="Marca:" />
        <x-jet-input type="text" class="w-full" wire:model="equipoMarca" />
        <x-jet-input-error for="equipoMarca"/>
    </div> 
    <div class="mb-4">
        <x-jet-label value="N° de serie:"/>
        <x-jet-input type="text" class="w-full"  wire:model="equipoSerie"/>
        <x-jet-input-error for="equipoSerie"/>
    </div> 
    <div class="mb-4">
        <x-jet-label value="Capacidad:"/>
        <x-jet-input type="number" class="w-full" wire:model="equipoCapacidad" inputmode="numeric"  pattern="[0-9]*"/>
        <x-jet-input-error for="equipoCapacidad"/>
    </div>   
    <div class="mb-4">
        <x-jet-label value="Fecha de Fabricación:"/>
        <x-jet-input type="date" class="w-full" wire:model="equipoFechaFab" />
        <x-jet-input-error for="equipoFechaFab"/>
    </div>
    <div class="mb-4">
        <x-jet-label value="¿Aplica fecha fin de vida útil?" />
        <label class="flex items-center space-x-2">
            <input type="checkbox" wire:model="equipofechCaducaAplica" class="rounded border-gray-300 text-indigo-600 shadow-sm">
            <span class="text-sm text-gray-700">Sí, este equipo tiene fecha de caducidad</span>
        </label>
    </div>
    @if($equipofechCaducaAplica)
        <div class="mb-4">
            <x-jet-label value="Fecha fin de vida útil:"/>
            <x-jet-input type="date" class="w-full" wire:model="equipofechaCaducidad" />
            <x-jet-input-error for="equipofechaCaducidad"/>
        </div>
    @endif
    <div class="mb-4">
        <x-jet-label value="Peso:"/>
        <x-jet-input type="number" class="w-full" wire:model="equipoPeso" inputmode="numeric"  pattern="[0-9]*"/>
        <x-jet-input-error for="equipoPeso"/>
    </div>
    <div class="mb-4">
        <x-jet-label value="Clase GNV"/>
        <select wire:model="equipoclaseGnv"
                    class="bg-gray-50 border-indigo-500 rounded-md outline-none block w-full ">
                    <option value="">Seleccione</option>
                    <option value="I">Tipo I</option>
                    <option value="II">Tipo II</option>
                    <option value="III">Tipo III</option>
                    <option value="IV">Tipo IV</option>
                    <option value="V">Tipo V</option>
                </select>
        <x-jet-input-error for="equipoclaseGnv"/>
    </div>    
</div>