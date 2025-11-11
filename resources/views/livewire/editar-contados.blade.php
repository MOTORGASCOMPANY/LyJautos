<div class="container mx-auto py-12 mt-4">
    <div class="flex flex-row  justify-between items-center">
        <h2 class="mt-8 font-bold text-lg text-indigo-600"> Comprobantes de formatos al contado de 
            {{ $contado->salida->usuarioAsignado->name }} entregados {{ $contado->created_at }}
        </h2>

        <div class="flex space-x-2">
            @livewire('create-contado-archivo', ['idContado' => $contado->id])
            {{-- FALTA CREAR EL PDF --}}
            <a href="{{ route('generaPdfContado', ['id' => $contado->id]) }}" target="_blank"
                class="group flex py-4 px-4 text-center rounded-md bg-indigo-300 font-bold text-white cursor-pointer hover:bg-indigo-400 hover:animate-pulse">
                <i class="fa-solid fa-file-pdf"></i>
                <span
                    class="group-hover:opacity-100 transition-opacity bg-gray-800 px-1 text-sm text-gray-100 rounded-md absolute left-1/2-translate-x-1/2 translate-y-full opacity-0 m-4 mx-auto z-100">
                    Pdf
                </span>
            </a>
        </div>
    </div>
    <hr class="my-4">
    <div>
        @livewire('contados-archivos', ['idContado' => $contado->id], key($contado->idContado . '-' . $contado->id))
    </div>

</div>
