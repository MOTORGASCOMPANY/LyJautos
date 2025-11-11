<div class="bg-gray-200  px-8 py-4 rounded-xl w-full">
    <div class=" items-center md:block sm:block">
        <!-- TITULO DE LA TABLA -->
        <div class="p-2 w-64 my-4 md:w-full">
            {{ $titulo }}
        </div>
        <div class="w-full items-center md:flex  md:justify-between">

            <div class="flex bg-gray-50 items-center p-2 rounded-md mb-4">
                <span>Mostrar</span>
                <select wire:model="cant"
                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 block h-10">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>registros</span>
            </div>
            <div class="flex bg-gray-50 items-center p-2 rounded-md mb-4">
                <span>Periodo: </span>
                <select wire:model="periodoSeleccionado"
                    class="bg-gray-50 mx-2 border-indigo-500 rounded-md outline-none ml-1 flex-1 h-10">
                    <option value="">Seleccione</option>
                    @foreach ($periodos as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center p-2 rounded-md mb-4 bg-gray-50 w-fit">
                <i class="fas fa-search h-5 w-5 text-indigo-600 mr-2"></i>
                <input type="text" wire:model.debounce.300ms="search" placeholder="Buscar..."
                    class="bg-gray-50 outline-none rounded-md border border-indigo-500 px-2 h-10 w-96">
            </div>


            <!-- BOTON PRINCIPAL -->
            <div class="flex mb-4">
                {{ $btnAgregar }}
            </div>


        </div>
    </div>
    {{ $contenido }}

</div>
