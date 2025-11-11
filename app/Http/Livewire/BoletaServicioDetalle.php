<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\BoletaServicio;
use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\ServiciosImportados;
use App\Models\CertificacionTemporal;

class BoletaServicioDetalle extends Component
{
    public $openModal = false;
    public $detallesServicios = [];

    protected $listeners = ['mostrarDetalleBoleta'];

    public function mostrarDetalleBoleta($boletaId)
    {
        $boletaServicio = BoletaServicio::where('boleta_id', $boletaId)->first();

        if (!$boletaServicio) {
            $this->detallesServicios = [];
            $this->openModal = true;
            return;
        }

        $this->detallesServicios = $this->procesarServicios($boletaServicio->servicio_ids);
        $this->openModal = true;
    }

    private function procesarServicios($items)
    {
        $resultados = [];

        foreach ($items as $item) {

            // ejemplo "App\\Models\\Certificacion => 10233, 10238, 10239"
            [$modelo, $ids] = explode(' => ', $item);

            $modelo = trim($modelo);
            $ids = array_map('trim', explode(',', $ids));

            foreach ($ids as $id) {
                $data = $this->mapearRegistro($modelo, $id);
                if ($data) {
                    $resultados[] = $data;
                }
            }
        }

        return $resultados;
    }

    private function mapearRegistro($modelo, $id)
    {
        switch ($modelo) {

            case Certificacion::class:
                $x = Certificacion::with(['Vehiculo', 'Taller', 'Inspector', 'Servicio.tipoServicio'])->find($id);

                if (!$x) return null;

                return [
                    'taller' => optional($x->Taller)->nombre,
                    'inspector' => optional($x->Inspector)->name,
                    'vehiculo' => optional($x->Vehiculo)->placa,
                    'servicio' => optional($x->Servicio->tipoServicio)->descripcion,
                    'fecha' => $x->created_at,
                    'estado' => $x->estado,
                    'precio' => $x->precio
                ];

            case CertificacionPendiente::class:
                $x = CertificacionPendiente::with(['Vehiculo', 'Taller', 'Inspector', 'Servicio.tipoServicio'])->find($id);

                if (!$x) return null;

                return [
                    'taller' => optional($x->Taller)->nombre,
                    'inspector' => optional($x->Inspector)->name,
                    'vehiculo' => optional($x->Vehiculo)->placa,
                    'servicio' => optional($x->Servicio->tipoServicio)->descripcion,
                    'fecha' => $x->created_at,
                    'estado' => $x->estado,
                    'precio' => $x->precio
                ];

            case ServiciosImportados::class:
                $x = ServiciosImportados::with(['TipoServicio'])->find($id);
                if (!$x) return null;

                return [
                    'taller' => $x->taller,
                    'inspector' => $x->certificador,
                    'vehiculo' => $x->placa,
                    'servicio' => $x->TipoServicio->descripcion,
                    'fecha' => $x->fecha,
                    'estado' => $x->estado,
                    'precio' => $x->precio
                ];

            case CertificacionTemporal::class:
                $x = CertificacionTemporal::with(['taller', 'inspector', 'servicio.tipoServicio'])
                    ->find($id);

                if (!$x) return null;

                return [
                    'taller' => optional($x->taller)->nombre,
                    'inspector' => optional($x->inspector)->name,
                    'vehiculo' => $x->placa,
                    'servicio' => optional($x->servicio->tipoServicio)->descripcion,
                    'fecha' => $x->created_at,
                    'estado' => $x->estado,
                    'precio' => $x->precio
                ];
        }

        return null;
    }

    public function render()
    {
        return view('livewire.boleta-servicio-detalle');
    }
}
