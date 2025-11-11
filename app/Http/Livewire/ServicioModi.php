<?php

namespace App\Http\Livewire;

use App\Jobs\guardarArchivosEnExpediente;
use App\Models\CertifiacionExpediente;
use App\Models\Certificacion;
use App\Models\Expediente;
use App\Models\Imagen;
use App\Models\Material;
use App\Models\ModificacionDetalle;
use App\Models\Servicio;
use App\Models\Taller;
use App\Models\User;
use App\Models\vehiculo;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\pdfTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ServicioModi extends Component
{
    use pdfTrait;
    use WithFileUploads;

    //VARIABLES DEL SERVICIO
    public $talleres, $servicios, $taller, $servicio, $tipoServicio, $numSugerido,
        $estado = "esperando", $busquedaCert, $placa, $certificaciones, $fechaCerti, $certificado;
    public $externo = false;
    public $serviexterno = false;
    public $imagenes = [];
    public $servicioExterno, $tallerExterno, $fechaExterno;
    public Certificacion $certificacion;
    //variables del certi
    public $vehiculo;
    //variable para fecha
    public $fechaCertificacion, $pertenece;
    public $esModiMotor = false; // checkbox para identificar si es modificación de motor

    protected $rules = ["placa" => "required|min:3|max:7"];

    public function mount()
    {
        $this->talleres = Taller::all()->sortBy('nombre');   
    }

    protected $listeners = ['cargaVehiculo' => 'carga', "refrescaVehiculo" => "refrescaVe"];

    public function updatednumSugerido($val)
    {
        //dd($this->obtienePertenece($val));
        //$this->pertenece = $this->obtienePertenece($val);
        // Verifica si el tipo de servicio es "Modificación" antes de obtener el propietario
        if ($this->tipoServicio && $this->tipoServicio->id == 5) {
            $this->pertenece = $this->obtienePertenece($val);
            // Verificamos si el inspector del material es externo y actualizamos `serviexterno`
            $this->serviexterno = $this->esInspectorExterno($val);
        }
    }
    // Funcion para ver si el inspector es externo y asi la variable $this->serviexterno (checkbox) figure predeterminado segun el inspector
    public function esInspectorExterno($numSerie)
    {
        $material = Material::where('numSerie', $numSerie)
                            ->where('idTipoMaterial', 4)
                            ->where('estado', 3)
                            ->first();

        if ($material && $material->idUsuario) {
            $inspector = User::find($material->idUsuario);
            return $inspector->externo ?? false; // Si el inspector es externo, retorna `true`
        }

        return false;
    }

    public function obtienePertenece($val)
    {
        if ($val) {
            $m = Material::where("numSerie", $val)->where("idTipoMaterial", 4)->where("estado", 3)->first();
            // return User::find(Material::where([["numSerie", $val],["idTipoMaterial", 4]] )->first()->idUsuario);
            if ($m == null) {
                return "No existe";
            } else {
                if ($m->idUsuario == null) {
                    return "No esta asignado";
                } else {
                    if ($m->estado == 4) {
                        return "Formato Consumido";
                    } else {
                        return User::find($m->idUsuario)->name;
                    }
                }
            }
            //return User::find($m)->name;
        } else {
            return null;
        }
    }

    public function updatedExterno()
    {
        if ($this->certificado) {
            $this->certificado = null;
        }
        $this->reset(["tallerExterno", "fechaExterno", "servicioExterno"]);
    }

    public function carga($id)
    {
        //dd($id);
        $this->vehiculo = vehiculo::find($id);
    }

    /*public function updatedTaller($val)
    {
        if ($val) {
            $this->servicios = Servicio::where("taller_idtaller", $val)->get();
            $this->servicio = "";
        } else {
            $this->reset(["servicios", "servicio"]);
        }
    }*/

    public function updatedTaller($val)
    {
        if ($val) {
            $this->servicios = Servicio::where('taller_idtaller', $val)
                ->whereHas('tipoServicio', function ($query) {
                    $query->where('descripcion', 'Modificación');
                })
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
            //dd($this->tipoServicio);
            $this->sugeridoSegunTipo($this->tipoServicio->id);
            $this->reset(["externo", "estado"]);
        } else {
            $this->tipoServicio = null;
        }
    }

    public function render()
    {
        return view('livewire.servicio-modi');
    }

    /*public function render()
    {
        return view('livewire.servicio-modi', [
            'servicios' => $this->servicios->where('tipoServicio.descripcion', 'Modificación')
        ]);
    }*/


    public function sugeridoSegunTipo($tipoServ)
    {
        $formatoGnv = 1;
        $formatoGlp = 3;
        $formatoModi = 4;
        if ($tipoServ) {
            switch ($tipoServ) {

                case 5:
                    $this->numSugerido = $this->obtieneFormato($formatoModi);
                    break;
                default:
                    $this->numSugerido = 0;
                    break;
            }
        }
    }

    public function obtieneFormato($tipo)
    {
        $formato = Material::where([
            ["idTipoMaterial", $tipo],
            //['idUsuario', Auth::id()],
            ["estado", 3],
        ])
            ->orderBy('numSerie', 'asc')
            ->min("numSerie");
        if (isset($formato)) {
            return $formato;
        } else {
            return null;
        }
    }

    //Selecciona una hoja segun el tipo de servicio
    public function seleccionaHojaSegunServicio($serie, $tipo)
    {
        $hoja = null;
        switch ($tipo) {
                //para modificacion
            case 5:
                $hoja = Material::where([['numSerie', $serie], ['idTipoMaterial', 4], ['estado', 3]])->first(); //['idUsuario', Auth::id()]
                return $hoja;
                break;
            default:
                return $hoja;
                break;
        }
    }
    //Buscar una certificacion
    public function buscarCertificacion()
    {
        $this->validate(['placa' => 'required|min:3|max:7']);

        //implementar un switch o if else segun el servicio
        $certis = Certificacion::PlacaVehiculo($this->placa)
            ->orderBy('created_at', 'desc')
            ->get();

        $certs = $certis->whereBetween("tipo_servicio", [1, 2]);

        if ($certs->count() > 0) {
            $this->busquedaCert = true;
            $this->certificaciones = $certs;
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No se encontro ningúna certificación con la placa ingresada", "icono" => "warning"]);
        }
    }

    //Resetear busqueda de Certificado
    public function reseteaBusquedaCert()
    {
        $this->certificado = null;
    }

    public function calculaFecha($fecha)
    {
        $dif = null;

        $hoy = Carbon::now();

        $dif = $fecha->diffInDays($hoy);

        return $dif;
    }
    public function seleccionaCertificacion($id)
    {
        $certi = $this->certificaciones[$id];
        $this->certificado = $certi;
        $this->fechaCerti = $this->calculaFecha($certi->created_at);
        $this->certificaciones = null;
        $this->busquedaCert = false;
        $this->reset(['placa']);
    }

    public function procesaFormato($numSerieFormato, $servicio)
    {
        if ($numSerieFormato) {
            $hoja = $this->seleccionaHojaSegunServicio($numSerieFormato, $servicio);
            if ($hoja != null) {
                return $hoja;
            } else {
                $this->emit("CustomAlert", ["titulo" => "ERROR", "mensaje" => "El número de serie ingresado no corresponde con ningún formato en su poder", "icono" => "error"]);
                return null;
            }
        } else {
            $this->emit("CustomAlert", ["titulo" => "ERROR", "mensaje" => "Número de serie no válido.", "icono" => "error"]);
            return null;
        }
    }


    public function certificarmodificacion()
    {
        $m = Material::where("numSerie", $this->numSugerido)->where("idTipoMaterial", 4)->where("estado", 3)->first();
        $taller = Taller::findOrFail($this->taller);
        $servicio = Servicio::findOrFail($this->servicio);
        $hoja = $this->procesaFormato($this->numSugerido, $servicio->tipoServicio->id);
        $usuario = User::find($m->idUsuario);        
        if (!$hoja) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
            return;
        }

        if (!$this->vehiculo) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes ingresar un vehículo válido para poder certificar", "icono" => "warning"]);
            return;
        }

        if (!$usuario) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Dueño de hoja invalido", "icono" => "warning"]);
            return;
        }

        $certi = Certificacion::certificarModi($taller, $servicio, $hoja, $this->vehiculo, $usuario, $this->serviexterno, $this->esModiMotor); //Auth::user()

        if ($certi) {
            $this->estado = "certificado";
            $this->certificacion = $certi;

            $expe = Expediente::create([
                "placa" => $this->vehiculo->placa,
                "certificado" => $hoja->numSerie,
                "estado" => 1,
                "idTaller" => $taller->id,
                'usuario_idusuario' => Auth::id(), // $usuario->id,
                'servicio_idservicio' => $servicio->id,
            ]);

            /*/ Guardar el tipo de modificación
            $modMotor =  ModificacionDetalle::create([
                'certificacion_id' => $certi->id,
                'tipo_modificacion' => $this->esModiMotor ? 'motor' : 'normal',
            ]);*/

            // Verifica si se seleccionó una fecha manualmente
            if ($this->fechaCertificacion) {
                $certi->update(['created_at' => Carbon::parse($this->fechaCertificacion)]);
            }

            $this->guardarFotos($expe);
            guardarArchivosEnExpediente::dispatch($expe, $certi);

            $certEx = CertifiacionExpediente::create(["idCertificacion" => $certi->id, "idExpediente" => $expe->id]);


            $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->Hoja->numSerie . " está listo.", "icono" => "success"]);
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
        }
    }

    public function guardarFotos(Expediente $expe)
    {
        $this->validate(["imagenes" => "nullable|array", "imagenes.*" => "image"]);
        if (count($this->imagenes)) {
            foreach ($this->imagenes as $key => $file) {
                $nombre = $expe->placa . '-foto' . ($key + 1) . '-' . $expe->certificado;
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

    public function refrescaVe()
    {
        $this->vehiculo->refresh();
    }
}
