<?php

namespace App\Http\Livewire;

use App\Models\ServiciosImportados;
use Livewire\Component;
use Illuminate\Support\Collection;
use App\Models\Taller;
use App\Models\TipoServicio;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ActualizarPreciosGasol extends Component
{
    public $fechaInicio, $fechaFin, $talleres, $inspectores;
    public $ins = [], $taller = [], $tipos, $servicio;
    public $grupoinspectores, $resultadosdetalle;
    public $certificacionIds = [];
    public $editando, $tiposServicios = [], $updatedPrices = [];

    // para checkboxces
    public $selectedCertificaciones = [];


    protected $listeners = ['preciosActualizados' => 'recargarDatos'];

    protected $rules = [
        "fechaInicio" => 'required|date',
        "fechaFin" => 'required|date',
    ];

    public function mount()
    {
        $this->inspectores = User::role(['inspector', 'supervisor'])->orderBy('name')->get();
        $this->talleres = Taller::orderBy('nombre')->get();
        $this->tipos = TipoServicio::all();
    }

    public function render()
    {
        return view('livewire.actualizar-precios-gasol');
    }
    public function calcularReporte()
    {
        $this->validate();
        $this->resultadosdetalle = $this->cargaServiciosGasolution();
    }
    public function cargaServiciosGasolution()
    {
        $disc = new Collection();
        $dis = ServiciosImportados::Talleres($this->taller)
            ->Inspectores($this->ins)
            ->TipoServicio($this->servicio)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();

        foreach ($dis as $registro) {
            $data = [
                "id" => $registro->id,
                "placa" => $registro->placa,
                "taller" => $registro->taller,
                "inspector" => $registro->certificador,
                "servicio" => $registro->TipoServicio->descripcion,
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $registro->precio,
                "pagado" => $registro->pagado,
                "estado" => $registro->estado,
                "tipo_modelo" => $registro::class,
                "fecha" => $registro->fecha,
            ];
            $disc->push($data);
        }
        $this->grupoinspectores = $disc->groupBy('inspector');
        return $disc;
    }

    public function ver($certificacionIds, $tiposServicios)
    {
        $this->certificacionIds = $certificacionIds;
        $this->tiposServicios = $tiposServicios;
        $this->editando = true;
    }
    public function updatePrecios()
    {
        if (count($this->updatedPrices) > 0) {
            if (count($this->selectedCertificaciones) == 0) {
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes seleccionar al menos un servicio.", "icono" => "warning"]);
                return;
            }

            foreach ($this->updatedPrices as $tipoServicio => $nuevoPrecio) {
                //$certificacionIds = $this->certificacionIds;
                $certificacionIds = $this->selectedCertificaciones;
                switch ($tipoServicio) {
                    case 'Conversión a GNV':
                    case 'Revisión anual GNV':
                    case 'Desmonte de Cilindro':
                        ServiciosImportados::whereIn('id', $certificacionIds)
                            ->whereHas('tipoServicio', function ($query) use ($tipoServicio) {
                                    $query->where('descripcion', $tipoServicio);
                                })
                            ->update(['precio' => $nuevoPrecio]);
                        break;
                    default:
                        // Manejo de error 
                        break;
                }
            }

            $this->emit('preciosActualizados');
            $this->reset(['updatedPrices', 'certificacionIds', 'selectedCertificaciones']);
            $this->editando = false;
        }
    }
    public function recargarDatos()
    {
        $this->calcularReporte();
    }

}
