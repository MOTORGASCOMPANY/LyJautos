<?php

namespace App\Http\Livewire;

use App\Models\Contado;
use App\Models\SalidaDetalle;
use Livewire\WithPagination;
use Livewire\Component;
use Carbon\Carbon;

class ListaContados extends Component
{
    use WithPagination;
    public $sort = 'id', $direction = 'asc', $cant = 50, $search = '';
    public $fechaInicio, $fechaFin;
    public $openEdit = false, $contado, $nombreInspector;

    protected $rules = [
        "contado.idSalida" => "nullable",
        "contado.precio" => "nullable",
        "contado.pagado" => "nullable",
        "contado.observacion" => "nullable",
    ];

    public function mount()
    {
        $this->direction = 'desc';
        $this->sort = 'id';
        $this->cant = 10;
    }

    public function order($sort)
    {
        $this->direction = $this->sort === $sort && $this->direction === 'desc' ? 'asc' : 'desc';
        $this->sort = $sort;
    }


    /*public function render()
    {
        $contados = Contado::with('salida')
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant);
        
        // Agregar la cantidad de materiales a cada registro
        $contados->getCollection()->transform(function ($item) {
            $item->cantidad = $item->salida
                ? SalidaDetalle::where('idSalida', $item->salida->id)->count()
                : 0;
            return $item;
        });

        return view('livewire.lista-contados', compact("contados"));
    }*/

    public function render()
    {
        $contados = Contado::with(['salida.usuarioAsignado'])
            ->when($this->search, function ($query) {
                $query->whereHas('salida.usuarioAsignado', function ($q) {
                    $q->where('name', 'LIKE', '%' . $this->search . '%');
                });
            })
            ->when($this->fechaInicio, function ($query) {
                $query->whereDate('created_at', '>=', Carbon::parse($this->fechaInicio));
            })
            ->when($this->fechaFin, function ($query) {
                $query->whereDate('created_at', '<=', Carbon::parse($this->fechaFin));
            })
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant);
            
        // Agregar la cantidad de materiales a cada registro
        $contados->getCollection()->transform(function ($item) {
            $item->cantidad = $item->salida
                ? SalidaDetalle::where('idSalida', $item->salida->id)->count()
                : 0;
            return $item;
        });

        return view('livewire.lista-contados', compact("contados"));
    }

    public function abrirModal($id)
    {
        $this->contado = Contado::findOrFail($id);
        // Obtener el nombre del inspector a través de las relaciones
        $this->nombreInspector = $this->contado->salida->usuarioAsignado->name ?? null;
        //dd($this->nombreInspector);
        $this->openEdit = true;
    }

    public function editarContado()
    {
        $this->validate();
        $this->contado->save();
        $this->emit("CustomAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "Contado actualizado correctamente", "icono" => "success"]);
        $this->reset(['openEdit', 'contado', 'nombreInspector']);
    }
}