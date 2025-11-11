<?php

namespace App\Http\Livewire;

use App\Exports\InformeCilindrosExport;
use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;

class InformeCilindros extends Component
{
    public $desde, $hasta;
    public $resultado ;
    public $inspectores, $ins;

    protected $listeners = ['exportarExcelCilindro'];

    protected $rules = [
        'desde' => 'required|date',
        'hasta' => 'required|date|after_or_equal:desde',
    ];

    public function mount()
    {
        $this->inspectores = User::role(['inspector', 'supervisor'])->where('id', '!=', Auth::id())->orderBy('name')->get();
    }
    public function render()
    {
        return view('livewire.informe-cilindros');
    }

    /*public function buscar()
    {
        $this->validate();

        $this->resultado = Certificacion::with([
                'Inspector',
                'Vehiculo.Equipos',
                'Servicio'
            ])
            ->whereBetween('created_at', [
                Carbon::parse($this->desde)->startOfDay(),
                Carbon::parse($this->hasta)->endOfDay()
            ])
            ->where('estado', 1) // Se agrega esta línea para filtrar por estado igual a 1
            ->whereHas('servicio', function ($query) {
                $query->where('tipoServicio_idtipoServicio', 2);
            })
            ->get();
    }*/    

    public function buscar()
    {
        $this->validate();

        // Limpiar los resultados previos
        $this->resultado = collect();

        $desde = Carbon::parse($this->desde)->startOfDay();
        $hasta = Carbon::parse($this->hasta)->endOfDay();

        $query1 = DB::table('certificacion')
            ->select(
                'id',
                'idServicio',
                'idInspector',
                'idVehiculo',
                'estado',
                'created_at',
                DB::raw('"App\\\\Models\\\\Certificacion" as tipo_modelo') // identificador
            )
            ->whereBetween('created_at', [$desde, $hasta])
            ->where('estado', 1)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('servicio')
                    ->whereRaw('certificacion.idServicio = servicio.id')
                    ->where('tipoServicio_idtipoServicio', 2);
            });

        $query2 = DB::table('certificados_pendientes')
            ->select(
                'id',
                'idServicio',
                'idInspector',
                'idVehiculo',
                'estado',
                'created_at',
                DB::raw('"App\\\\Models\\\\CertificacionPendiente" as tipo_modelo')
            )
            ->whereBetween('created_at', [$desde, $hasta])
            ->where('estado', 1)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('servicio')
                    ->whereRaw('certificados_pendientes.idServicio = servicio.id')
                    ->where('tipoServicio_idtipoServicio', 7);
            });

        // Si se seleccionó un inspector, se aplica el filtro en ambas consultas
        if ($this->ins) {
            $query1->where('idInspector', $this->ins);
            $query2->where('idInspector', $this->ins);
        }

        $resultados = $query1->unionAll($query2)->get();

        // Cargar modelos reales
        //$this->resultado = collect();

        foreach ($resultados as $item) {
            $modelo = $item->tipo_modelo;
            $registro = $modelo::with([
                'Inspector',
                'Vehiculo.Equipos',
                'Servicio'
            ])->find($item->id);

            if ($registro) {
                $this->resultado->push($registro);
            }
        }
    }

    public function exportarExcelCilindro($datae)
    {
        return Excel::download(new InformeCilindrosExport($datae), 'Informe_Cilindros.xlsx');
    }
}
