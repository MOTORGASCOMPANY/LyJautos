<?php

namespace App\Http\Livewire;

use App\Models\Contado;
use App\Models\ContadoArchivo;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateContadoArchivo extends Component
{
    use WithFileUploads;
    public $idContado, $contado;
    public $addDocument = false;
    public $documentos = [], $nombres = [];
    //public $idContados = '';

    public function mount()
    {
        $this->contado = Contado::find($this->idContado);
    }

    public function render()
    {
        return view('livewire.create-contado-archivo');
    }

    public function updatedDocumentos()
    {
        // Inicialice la matriz de nombres según la cantidad de archivos cargados
        $this->nombres = array_fill(0, count($this->documentos), '');
    }

    public function agregarDocumento()
    {
        $this->validate([
            'documentos.*' => 'required|mimes:jpg,jpeg,png|max:2048',
            'nombres.*' => 'required|string|max:255'
        ]);

        // Obtener contado
        $contado = Contado::find($this->idContado);

        foreach ($this->documentos as $index => $documento) {
            $nombreInput = $this->nombres[$index];

            $nombre2 = $contado->salida->usuarioAsignado->name;

            // Construir el nombre antes de agregar el nuevo nombre del input
            $antesdenombre = $contado->id . '-' . $nombre2;

            // Construir el nombre completo del archivo
            $nombreArchivo = $antesdenombre . '-' . $nombreInput;

            ContadoArchivo::create([
                'idContado' => $this->idContado,
                'nombre' => $nombreInput,
                'ruta' => $documento->storeAs('public/docsContados', $nombreArchivo . '.' . $documento->extension()),
                'extension' => $documento->extension(),
            ]);

            // Lógica para actualizar pagago = 2
            $this->actualizarServicios($this->idContado);
        }

        $this->emitTo('editar-contados', 'refrescaContado');
        $this->emitTo('contados-archivos', 'resetContado');
        $this->reset(['documentos', 'nombres', 'addDocument']);
        $this->emit("CustomAlert", ["titulo" => "BUEN TRABAJO!", "mensaje" => "Se ingresaron correctamente los documentos", "icono" => "success"]);
    }

    // una vez que se sube el documento se debe actualizar del modelo contado la columna pagado = 2
    private function actualizarServicios($idContado)
    {
        // Buscar el registro de Contado correspondiente
        $contado = Contado::find($idContado);

        // Verificar si se encontró el registro
        if ($contado) {
            // Actualizar el campo pagado a 2
            $contado->pagado = 2;
            $contado->save();

            // Emitir un evento opcional para indicar que el campo fue actualizado
            $this->emit("CustomAlert", [
                "titulo" => "Contado Actualizado",
                "mensaje" => "El campo pagado se actualizó correctamente a resuelto",
                "icono" => "success"
            ]);
        } else {
            // Emitir un evento de error si no se encuentra el registro de contado
            $this->emit("CustomAlert", [
                "titulo" => "Error",
                "mensaje" => "No se encontró el registro de contado con ID {$idContado}",
                "icono" => "error"
            ]);
        }
    }
}

/*public function agregarDocumento()
    {
        $this->validate([
            'documentos.*' => 'required|mimes:jpg,jpeg,png|max:2048', // Add 'pdf' if needed
            'nombres.*' => 'required|string|max:255',
            'idContados' => 'nullable|string', // Validar que sea una cadena de texto si es proporcionado
        ]);

        // Convertir $idContados a un array si no es nulo
        $idContados = $this->idContados ? array_map('trim', explode(',', $this->idContados)) : [$this->idContado];

        foreach ($idContados as $idContado) {
            // Obtener contado
            $contado = Contado::find($idContado);

            if (!$contado) continue; // Si contado no existe, continuar con el siguiente ID

            foreach ($this->documentos as $index => $documento) {
                $nombreInput = $this->nombres[$index];

                if ($contado->taller == null) {
                    $nombre2 = $contado->Certificador->name;
                } else {
                    $nombre2 = $contado->Taller->nombre;
                }

                // Construir el nombre antes de agregar el nuevo nombre del input
                $antesdenombre = $contado->id . '-' . $contado->identificador . '-' . $nombre2;

                // Construir el nombre completo del archivo
                $nombreArchivo = $antesdenombre . '-' . $nombreInput;

                ContadoArchivo::create([
                    'idContado' => $idContado,
                    'nombre' => $nombreInput,
                    'ruta' => $documento->storeAs('public/docsBoletas', $nombreArchivo . '.' . $documento->extension()),
                    'extension' => $documento->extension(),
                ]);
            }
            // Lógica para actualizar los servicios relacionados
            //$this->actualizarServicios($idBoleta);
        }

        // Emitir eventos para actualizar componentes relacionados
        //$this->emitTo('editar-boleta', 'refrescaBoleta');
        //$this->emitTo('boletas-archivos', 'resetBoleta');
        $this->reset(['documentos', 'nombres', 'addDocument', 'idContados']);
        $this->emit("CustomAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "Se ingresaron correctamente los documentos", "icono" => "success"]);
    }
*/
