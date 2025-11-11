<?php

namespace App\Http\Livewire;

use App\Models\CertificacionPendiente;
use App\Models\Expediente;
use App\Models\Imagen;
use App\Models\PrecioInspector;
use App\Models\Servicio;
use App\Models\TipoServicio;
use App\Models\vehiculo;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ActivacionDeChips extends Component
{
    use WithFileUploads;


    public $tipoServicio, $vehiculo, $idTaller, $estado;
    public $imagenes = [];
    public $serviexterno = false;
    // para ver si los inspector son externos y activar checkbox
    public $inspectorexterno = 0;

    //variable para fecha
    public $fechaCertificacion;

    protected $listeners = ['cargaVehiculo' => 'carga', "refrescaVehiculo" => "refrescaVe"];

    public function mount()
    {
        // Obtener el inspector actual
        $insptr = Auth::user();        
        // Verificar si el inspector es externo
        $this->inspectorexterno = $insptr->externo == true ? true : null;
        // Si el inspector es externo, activar el checkbox de serviexterno
        $this->serviexterno = $this->inspectorexterno;
    }


    public function render()
    {
        return view('livewire.activacion-de-chips');
    }

    public function refrescaVe()
    {
        $this->vehiculo->refresh();
    }

    public function carga($id)
    {
        $this->vehiculo = vehiculo::find($id);
    }

    public function guardar()
    {
        if (isset($this->vehiculo)) {
            if ($this->vehiculo->esCertificableGnv) {
                $serv = Servicio::where([["taller_idtaller", $this->idTaller], ["tipoServicio_idtipoServicio", 7]])->first(); //cambie el 7*2 para que guarde como activacion 

                $precio = 0;
                //Condicion para jalar el precio de la tabla servicios o precios_inspector                
                if ($this->serviexterno == false) {
                    //$precio = $serv->precio; 
                    $precio = Servicio::where([['tipoServicio_idtipoServicio', 7], ["taller_idtaller", $this->idTaller]])->first()->precio;
                } elseif ($this->serviexterno == true) {
                    $precio = PrecioInspector::where([['idServicio', 7], ['idUsers', Auth::id()]])->first()->precio;
                }

                if ($serv != null) {
                    $certi = CertificacionPendiente::create([
                        "idInspector" => Auth::id(),
                        "idServicio" => $serv->id,
                        "idTaller" => $this->idTaller,
                        "idVehiculo" => $this->vehiculo->id,
                        "estado" => 1,
                        "pagado" => 0,
                        "precio" => $precio, //
                        "externo" => $this->serviexterno,
                    ]);

                    // Si se seleccionó una fecha, actualizar created_at con fecha y hora personalizada
                    /*if ($this->fechaCertificacion) {
                        $certi->update(["created_at" => $this->fechaCertificacion . ' ' . now()->format('H:i:s'),]);
                    }*/

                    $expe = $this->crearExpediente($certi, $this->vehiculo);
                    $certi->update(["idExpediente" => $expe->id]);
                    $this->guardarFotos($expe);
                    $this->estado = "realizado";
                    $this->emit("minAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "Se agrego correctamente una certificacion pendiente para este vehículo", "icono" => "success"]);
                } else {
                    $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Completa los datos de los equipos para poder continuar", "icono" => "warning"]);
                }
            } else {
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Completa los datos de los equipos para poder continuar", "icono" => "warning"]);
            }
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Ingresa un vehículo valido para poder continuar", "icono" => "warning"]);
        }
    }


    public function crearExpediente(CertificacionPendiente $certi, vehiculo $vehiculo)
    {
        $serv = Servicio::where([["taller_idtaller", $certi->idTaller], ["tipoServicio_idtipoServicio", 7]])->first();
        $ex = Expediente::create([
            "placa" => $vehiculo->placa,
            "certificado" => "No Data",
            "estado" => 1,
            "idTaller" => $certi->idTaller,
            "usuario_idusuario" => $certi->idInspector,
            "servicio_idservicio" => $serv->id,
        ]);
        return $ex;
    }


    public function guardarFotos(Expediente $expe)
    {
        $this->validate(["imagenes" => "nullable|array", "imagenes.*" => "image"]);
        if (count($this->imagenes)) {
            foreach ($this->imagenes as $key => $file) {
                $nombre = $expe->placa . '-foto' . ($key + 1) . (rand()) . '-' . $expe->certificado;
                $file_save = Imagen::create([
                    'nombre' => $nombre,
                    'ruta' => $file->storeAs('public/expedientes', $nombre . '.' . $file->extension()),
                    'extension' => $file->extension(),
                    'Expediente_idExpediente' => $expe->id,
                ]);
            }
        }
        $this->reset(["imagenes"]);
    }
}
