<?php

namespace App\Http\Livewire;

use App\Models\Contado;
use Livewire\Component;

class EditarContados extends Component
{
    public $idContado, $contado;
    protected $listeners = ["refrescaContado"];

    public function mount()
    {
        $this->contado = Contado::find($this->idContado);
    }

    public function render()
    {
        return view('livewire.editar-contados');
    }

    public function refrescaContado()
    {
        $this->contado->refresh();
    }
}
