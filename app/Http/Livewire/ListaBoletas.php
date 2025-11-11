<?php

namespace App\Http\Livewire;

use App\Models\Boleta;
use App\Models\Taller;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ListaBoletas extends Component
{
    use WithPagination;
    public $sort = 'id', $direction = 'asc', $cant = 50, $search = '';
    public $fechaInicio, $fechaFin;
    public $openEdit = false, $boleta;
    //para filtros
    public $inspectores, $talleres, $ins, $ta;
    protected $listeners = ['render', 'eliminarBoleta'];

    public $auditoria = [];

    //para autenticados
    public $user;

    protected $rules = [
        "boleta.taller" => "nullable",
        "boleta.certificador" => "nullable",
        "boleta.fechaInicio" => "required",
        "boleta.fechaFin" => "required",
        "boleta.monto" => "required",
        "boleta.anual" => "nullable",
        "boleta.duplicado" => "nullable",
        "boleta.inicial" => "nullable",
        "boleta.desmonte" => "nullable",
        "boleta.observacion" => "nullable",
    ];

    public function mount()
    {
        /*$this->direction = 'asc';
        $this->sort = 'id';
        $this->cant = 50;*/
        //Para filtros
        $this->inspectores = User::role(['inspector', 'supervisor'])->orderBy('name')->get();
        $this->talleres = Taller::all()->sortBy('nombre');
        // Inicializa la propiedad $auditoria con los valores de la base de datos
        $this->auditoria = Boleta::pluck('auditoria', 'id')->toArray();

        //Para autenticados
        $this->user = Auth::user();
    }

    public function order($sort)
    {
        $this->direction = $this->sort === $sort && $this->direction === 'desc' ? 'asc' : 'desc';
        $this->sort = $sort;
    }

    public function updatedAuditoria($value, $boletaId)
    {
        Boleta::find($boletaId)->update(['auditoria' => $value ? 1 : 0]);
        $this->auditoria[$boletaId] = $value ? 1 : 0;
    }

    /*public function render()
    {
        // Iniciar la consulta
        $query = Boleta::query();

        // Si el usuario es inspector, filtrar por su ID
        if ($this->user->hasRole('inspector')) {
            $query->where('certificador', $this->user->id);
        }

        // Si el usuario es administrador de taller, filtrar por el taller asignado al usuario
        if ($this->user->hasRole('Administrador taller') && $this->user->taller) {
            $query->where('taller', $this->user->taller); // Filtra las boletas por el taller asignado
        }

        // Aplicar filtros de taller e inspector si están presentes
        if (!empty($this->ta)) {
            $query->Talleres($this->ta);
        }
        if (!empty($this->ins)) {
            $query->Inspectores($this->ins);
        }

        // Verificar si las fechas no son nulas
        if ($this->fechaInicio && $this->fechaFin) {
            $query->RangoFecha($this->fechaInicio, $this->fechaFin);
        }

        // Buscar por texto en taller o certificador
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->whereHas('taller', function ($q2) {
                    $q2->where('nombre', 'like', '%' . $this->search . '%');
                })->orWhereHas('certificador', function ($q3) {
                    $q3->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Ordenar y paginar los resultados
        $boletas = $query->orderBy($this->sort, $this->direction)->paginate($this->cant);

        return view('livewire.lista-boletas', compact('boletas'));
    }*/

    public function render()
    {
        $boletas = $this->getFilteredBoletas();
        return view('livewire.lista-boletas', compact('boletas'));
    }

    protected function getFilteredBoletas()
    {
        $query = Boleta::query();

        if ($this->user->hasRole('inspector')) {
            $query->where('certificador', $this->user->id);
        }

        if ($this->user->hasRole('Administrador taller') && $this->user->taller) {
            $query->where('taller', $this->user->taller->id);
        }

        if ($this->ta) {
            $query->Talleres($this->ta);
        }

        if ($this->ins) {
            $query->Inspectores($this->ins);
        }

        if ($this->fechaInicio && $this->fechaFin) {
            $query->RangoFecha($this->fechaInicio, $this->fechaFin);
        }

        if ($this->search) {
            $query->searchByTallerOrCertificador($this->search);
        }

        return $query->orderBy($this->sort, $this->direction)->paginate($this->cant);
    }

    public function abrirModal($id)
    {
        $this->boleta = Boleta::findOrFail($id);
        $this->boleta->setDefaultsIfNeeded();
        $this->openEdit = true;
    }

    public function editarBoleta()
    {
        $this->validate();
        $this->boleta->save();
        $this->emit("CustomAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "Boleata actualizada correctamente", "icono" => "success"]);
        $this->reset(['openEdit', 'boleta']);
        //$this->refrescaBoleta();
    }

    public function redirectBoletas($idBoleta)
    {
        return Redirect::to("Boletas/{$idBoleta}");
    }

    public function eliminarBoleta($idBoleta)
    {
        $boleta = Boleta::findOrFail($idBoleta);
        // Eliminar archivos relacionados
        $boleta->boletaarchivo()->delete();
        // Eliminar la boleta
        $boleta->delete();
        $this->emit('render');
    }

    public function agregar()
    {
        return redirect()->route('ImportarBoletas');
    }
}
