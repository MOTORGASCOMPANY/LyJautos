<?php

namespace App\Http\Livewire;

use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\Taller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ActualizarFechas extends Component
{
    public $modelo;
    public $inspec, $talle;
    public $talleres, $inspectores;
    public $resultados = [];
    public $consultaRealizada = false;

    public $openEdit = false, $created_at;

    public $seleccionados = [];

    //public $buscarPorNumSerie = false;
    public $tipoBusqueda = 'placa'; // valores posibles: 'placa', 'numSerie', 'ubicacion'
    public $valorBusqueda;

    /*public function updatedBuscarPorNumSerie()
    {
        $this->resetValidation();
        $this->reset(['valorBusqueda']);
    }*/
    protected function rules()
    {
        return [
            'valorBusqueda' => 'required',
        ];
    }

    /*protected $rules = [
        'placa' => 'required|min:6|max:6',
    ];*/

    public function mount()
    {
        //Para filtros
        $this->inspectores = User::role(['inspector', 'supervisor'])->where('id', '!=', Auth::id())->orderBy('name')->get();
        $this->talleres = Taller::orderBy('nombre')->get();
    }

    public function render()
    {
        return view('livewire.actualizar-fechas');
    }
    
    public function buscar()
    {
        $this->validate();
        $this->valorBusqueda = strtoupper($this->valorBusqueda);
        // Llamar a la función que maneja la consulta
        $this->resultados = $this->obtenerResultados($this->modelo);
        // Marcar consulta como realizada
        $this->consultaRealizada = true;
    }

    private function obtenerResultados($modelo)
    {
        $modeloClass = $modelo === 'certificados' ? Certificacion::class : (
            $modelo === 'cer_pendientes' ? CertificacionPendiente::class : null
        );

        if (!$modeloClass) return [];

        $query = $modeloClass::query()

            // Buscar por placa
            ->when($this->tipoBusqueda === 'placa', function ($query) {
                $query->whereHas('Vehiculo', fn($q) => $q->where('placa', 'like', '%' . $this->valorBusqueda . '%'));
            })

            // Buscar por numSerie
            ->when($this->tipoBusqueda === 'numSerie' && $this->modelo === 'certificados', function ($query) {
                $query->whereHas('Materiales', fn($q) => $q->where('numSerie', 'like', '%' . $this->valorBusqueda . '%'));
            })

            // Buscar por ubicacion
            ->when($this->tipoBusqueda === 'ubicacion' && $this->modelo === 'certificados', function ($query) {
                //$query->whereHas('Materiales', fn($q) => $q->where('ubicacion', 'like', '%' . $this->valorBusqueda . '%'));
                $query->whereHas('Materiales', function ($q) {
                    $q->whereRaw("SUBSTRING_INDEX(ubicacion, '/', -1) LIKE ?", ['%' . $this->valorBusqueda . '%']);
                });
            })

            ->when($this->inspec, fn($query) => $query->where('idInspector', $this->inspec))
            ->when($this->talle, fn($query) => $query->where('idTaller', $this->talle));

        // Relaciones comunes
        $with = ['Vehiculo', 'Inspector', 'Taller', 'Servicio'];

        // Agregar 'Materiales' solo si es certificados
        if ($this->modelo === 'certificados') {
            $with[] = 'Materiales';
        }

        return $query->with($with)->get();
    }

    /*private function obtenerResultados($modelo)
    {
        // Determinar el modelo a usar
        $modeloClass = $modelo === 'certificados' ? Certificacion::class : ($modelo === 'cer_pendientes' ? CertificacionPendiente::class : null);

        if (!$modeloClass) {
            return []; // Retornar vacío si el modelo no es válido
        }

        return $modeloClass::whereHas('Vehiculo', function ($query) {
            $query->where('placa', 'like', '%' . $this->placa . '%');
        })
            ->when($this->inspec, fn($query) => $query->where('idInspector', $this->inspec))
            ->when($this->talle, fn($query) => $query->where('idTaller', $this->talle))
            ->with(['Vehiculo', 'Inspector', 'Taller', 'Servicio'])
            ->get();
    }*/

    public function abrirModal()
    {
        $this->openEdit = true;
    }

    public function actualizar()
    {
        $this->validate([
            'created_at' => 'required|date',
        ]);

        if (empty($this->seleccionados)) {
            $this->emit("CustomAlert", ["titulo" => "¡ERROR!", "mensaje" => "No has seleccionado ningún registro", "icono" => "error"]);
            return;
        }

        // Determinar el modelo a usar
        $modeloClass = $this->modelo === 'certificados' ? Certificacion::class : ($this->modelo === 'cer_pendientes' ? CertificacionPendiente::class : null);

        if (!$modeloClass) {
            $this->emit("CustomAlert", ["titulo" => "¡ERROR!", "mensaje" => "Modelo no válido", "icono" => "error"]);
            return;
        }

        // Extraer solo la fecha seleccionada por el usuario
        $fechaSeleccionada = Carbon::parse($this->created_at)->format('Y-m-d');
        // Obtener la hora actual del servidor
        $horaActual = now()->format('H:i:s');
        // Combinar fecha seleccionada con la hora actual
        //$this->created_at = "$fechaSeleccionada $horaActual";
        $nuevaFecha = "$fechaSeleccionada $horaActual";

        /*
        // Actualizar la fecha en los registros filtrados
        $modeloClass::whereHas('Vehiculo', function ($query) {
            $query->where('placa', 'like', '%' . $this->placa . '%');
        })
            ->when($this->inspec, fn($query) => $query->where('idInspector', $this->inspec))
            ->when($this->talle, fn($query) => $query->where('idTaller', $this->talle))
            ->update(['created_at' => $this->created_at]);*/

        // Actualizar solo los registros seleccionados
        $modeloClass::whereIn('id', $this->seleccionados)->update(['created_at' => $nuevaFecha]);

        $this->emit("CustomAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "Se cambió la fecha correctamente", "icono" => "success"]);
        $this->reset(['openEdit', 'created_at', 'seleccionados']);
    }
}
