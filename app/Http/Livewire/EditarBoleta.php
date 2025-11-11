<?php

namespace App\Http\Livewire;

use App\Models\Boleta;
use App\Models\BoletaServicio;
use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\Desmontes;
use App\Models\ServiciosImportados;
use Livewire\Component;
use Illuminate\Support\Facades\Redirect;

class EditarBoleta extends Component
{
    public $idBoleta, $boleta;
    public $estado; //para auditoria
    protected $listeners = ["refrescaBoleta"];

    public function mount()
    {
        $this->boleta = Boleta::find($this->idBoleta);
        $this->estado = $this->boleta->estado;
    }

    public function render()
    {
        return view('livewire.editar-boleta');
    }

    public function refrescaBoleta()
    {
        $this->boleta->refresh();
        $this->estado = $this->boleta->estado;
    }

    public function regresar()
    {
        return Redirect::to('/Listaboletas');
    }

    public function generatePdf()
    {
        return redirect()->route('generaPdfBoleta', ['id' => $this->idBoleta]);
    }

    public function updatedEstado($value)
    {
        $this->boleta->estado = $value ? 1 : 0;
        $this->boleta->save();
        // Solo actualiza los servicios si el estado se activa (1)
        if ($this->boleta->estado == 1) {
            $this->actualizarServicios($this->idBoleta);
        }
    }

    // Lógica para actualizar los servicios relacionados
    private function actualizarServicios($idBoleta)
    {
        // Buscar la relación en BoletaServicio
        $boletaServicio = BoletaServicio::where('boleta_id', $idBoleta)->first();

        if (!$boletaServicio) return;

        // Si servicio_ids ya es un array, úsalo directamente; si es una cadena, decodifícalo
        $servicioIds = is_array($boletaServicio->servicio_ids)
            ? $boletaServicio->servicio_ids
            : json_decode($boletaServicio->servicio_ids, true);

        if (!is_array($servicioIds)) {
            throw new \Exception('El campo servicio_ids no es un array válido.');
        }

        // Mapeo de modelos y sus clases
        $modelosPermitidos = [
            'App\\Models\\Certificacion' => Certificacion::class,
            'App\\Models\\CertificacionPendiente' => CertificacionPendiente::class,
            'App\\Models\\Desmontes' => Desmontes::class,
            'App\\Models\\ServiciosImportados' => ServiciosImportados::class,
        ];

        foreach ($servicioIds as $servicio) {
            // Validar el formato del servicio
            if (strpos($servicio, '=>') === false) {
                throw new \Exception("Formato de servicio inválido: {$servicio}");
            }

            [$modelo, $idsString] = explode('=>', $servicio);
            $modelo = trim($modelo);

            // Validar si el modelo está permitido
            if (!array_key_exists($modelo, $modelosPermitidos)) {
                throw new \Exception("Modelo desconocido o no permitido: {$modelo}");
            }

            // Convertir los IDs en un array
            $idsArray = array_map('trim', explode(',', $idsString));

            // Actualizar los registros del modelo correspondiente
            $modelosPermitidos[$modelo]::whereIn('id', $idsArray)->update(['pagado' => 2]);
        }
    }
}
