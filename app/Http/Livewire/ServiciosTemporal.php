<?php

namespace App\Http\Livewire;


use App\Models\CertificacionTemporal;
use App\Models\Servicio;
use App\Models\Taller;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ServiciosTemporal extends Component
{    
    // VARIABLES DEL SERVICIO
    public $talleres, $servicios, $taller, $servicio, $tipoServicio, $certificacion = null;
    public $estado = "esperando", $placa, $numSerie;

    protected $rules = [
        "placa" => "required|min:3|max:6",
        "numSerie" => "required"
    ];

    public function mount()
    {
        $this->talleres = Taller::all()->sortBy('nombre');
    }

    public function render()
    {
        return view('livewire.servicios-temporal');
    }

    public function updatedTaller($val)
    {
        if ($val) {
            $this->servicios = Servicio::where("taller_idtaller", $val)
                ->where("estado", 1)
                ->whereIn("tipoServicio_idtipoServicio", [3, 4]) // solo servicios de GLP
                ->get();
            $this->servicio = "";
        } else {
            $this->reset(["servicios", "servicio"]);
        }
    }

    public function updatedServicio($val)
    {
        if ($val) {
            $this->tipoServicio = Servicio::find($val)->tipoServicio;
            $this->reset(["estado"]);
        } else {
            $this->tipoServicio = null;
        }
    }

    public function certificarGlp()
    {
        $this->validate();
        $taller = Taller::findOrFail($this->taller);
        $servicio = Servicio::findOrFail($this->servicio);
        $certi = CertificacionTemporal::certificarGlp($taller, $servicio, Auth::user(), $this->placa, $this->numSerie);

        if ($certi) {
            $this->estado = "certificado";
            $this->certificacion = $certi;
            $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->numSerie . " está listo.", "icono" => "success"]);
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
        }
    }
}
