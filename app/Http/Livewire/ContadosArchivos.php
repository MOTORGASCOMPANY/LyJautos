<?php

namespace App\Http\Livewire;

use App\Models\Contado;
use App\Models\ContadoArchivo;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class ContadosArchivos extends Component
{
    use WithFileUploads;

    public $contado;
    public $idContado;
    public $openEdit = false, $documento, $nuevoPdf;
    protected $listeners = ["eliminar" => "delete", "resetContado" => "refrescaContado"];

    protected $rules = [
        "documento.nombre" => "required",
    ];

    public function mount()
    {
        $this->cargarContadoConArchivos();
    }

    private function cargarContadoConArchivos()
    {
        $this->contado = Contado::with('contadoarchivo')->find($this->idContado);

        foreach ($this->contado->contadoarchivo as $doc) {
            if ($doc->migrado == 1) {
                $doc->url_temporal = Storage::disk('do')->temporaryUrl($doc->ruta, now()->addMinutes(5));
            } else {
                $doc->url_temporal = Storage::url($doc->ruta);
            }
        }
    }

    public function render()
    {
        return view('livewire.contados-archivos');
    }

    public function refrescaContado()
    {
        $this->contado->refresh();
    }

    public function delete($id)
    {
        $documento = ContadoArchivo::findOrFail($id);
        Storage::delete([$documento->ruta]);
        $documento->delete();
        $this->refrescaContado();
    }

    public function abrirModal($id)
    {
        $this->documento = ContadoArchivo::findOrFail($id);
        $this->openEdit = true;
    }

    public function editarDocumento()
    {
        $this->validate();

        if ($this->nuevoPdf) {
            $nombre = rand() . '-doc-' . rand();
            $nuevaRuta = $this->guardaNuevoArchivo($nombre, $this->nuevoPdf);
            Storage::delete([$this->documento->ruta]);
            $this->documento->ruta = $nuevaRuta;
            $this->documento->extension = $this->nuevoPdf->extension();
        }

        $this->documento->save();

        $this->emit("CustomAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "Documento actualizado correctamente", "icono" => "success"]);
        $this->reset(['openEdit', 'nuevoPdf']);
        $this->refrescaContado();
    }

    private function guardaNuevoArchivo($nombre, $file)
    {
        return $file->storeAs('public/docsContados', $nombre . '.' . $file->extension());
    }
}
