<?php

namespace App\Http\Livewire;

use App\Models\Certificacion;
use App\Models\Taller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ActualizarExternos extends Component
{
    public $inspec, $talle;
    public $talleres, $inspectores;
    public $resultados = [];
    public $consultaRealizada = false;

    public $openEdit = false, $externo;
    public $seleccionados = [];

    public $tipoBusqueda = 'placa'; // valores posibles: 'placa', 'numSerie', 'ubicacion'
    public $valorBusqueda;

    protected function rules()
    {
        return [
            'valorBusqueda' => 'required',
        ];
    }

    public function mount()
    {
        $this->inspectores = User::role(['inspector', 'supervisor'])->where('id', '!=', Auth::id())->orderBy('name')->get();
        $this->talleres = Taller::orderBy('nombre')->get();
    }

    public function render()
    {
        return view('livewire.actualizar-externos');
    }

    public function buscar()
    {
        $this->validate();
        $this->valorBusqueda = strtoupper($this->valorBusqueda);
        // Llamar a la función que maneja la consulta
        $this->resultados = $this->obtenerResultados();
        // Marcar consulta como realizada
        $this->consultaRealizada = true;
    }

    private function obtenerResultados()
    {
        return Certificacion::query()

            // Buscar por placa
            ->when($this->tipoBusqueda === 'placa', function ($query) {
                $query->whereHas('Vehiculo', fn($q) =>
                    $q->where('placa', 'like', '%' . $this->valorBusqueda . '%')
                );
            })

            // Buscar por numSerie (en relación materiales)
            ->when($this->tipoBusqueda === 'numSerie', function ($query) {
                $query->whereHas('Materiales', fn($q) =>
                    //$q->where('numSerie', 'like', '%' . $this->valorBusqueda . '%')
                    $q->where('numSerie', $this->valorBusqueda)
                );
            })

            // Buscar por ubicacion (en relación materiales, usando SUBSTRING_INDEX)
            ->when($this->tipoBusqueda === 'ubicacion', function ($query) {
                $query->whereHas('Materiales', function ($q) {
                    $q->whereRaw("SUBSTRING_INDEX(ubicacion, '/', -1) LIKE ?", ['%' . $this->valorBusqueda . '%']);
                });
            })

            // Filtros adicionales
            ->when($this->inspec, fn($query) =>
                $query->where('idInspector', $this->inspec)
            )
            ->when($this->talle, fn($query) =>
                $query->where('idTaller', $this->talle)
            )

            // Relaciones necesarias
            ->with(['Vehiculo', 'Inspector', 'Taller', 'Servicio'])
            ->get();
    }

    public function abrirModal()
    {
        $this->openEdit = true;
    }

    public function actualizar()
    {
        $this->validate([
            'externo' => 'required|in:0,1',
        ]);

        if (empty($this->seleccionados)) {
            $this->emit("CustomAlert", ["titulo" => "¡ERROR!", "mensaje" => "No has seleccionado ningún registro", "icono" => "error"]);
            return;
        }

        // Actualizar solo los registros seleccionados
        Certificacion::whereIn('id', $this->seleccionados)->update(['externo' => $this->externo]);

        // Refrescar resultados
        $this->resultados = $this->obtenerResultados();

        $this->emit("CustomAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "Se actualizo correctamente", "icono" => "success"]);
        $this->reset(['openEdit', 'externo', 'seleccionados']);
    }
}
