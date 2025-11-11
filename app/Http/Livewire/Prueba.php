<?php

namespace App\Http\Livewire;

use App\Http\Controllers\PdfController;
use App\Jobs\guardarArchivosEnExpediente;
use App\Models\CertifiacionExpediente;
use App\Models\Certificacion;
use App\Models\Duplicado;
use App\Models\Expediente;
use App\Models\Imagen;
use App\Models\Material;
use App\Models\ModificacionDetalle;
use App\Models\PrecioInspector;
use App\Models\Servicio;
use App\Models\ServicioMaterial;
use App\Models\Taller;
use App\Models\TipoImagen;
use App\Models\vehiculo;
use App\Traits\pdfTrait;
use Illuminate\Queue\Listener;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class Prueba extends Component
{
    use pdfTrait;
    use WithFileUploads;

    //VARIABLES DEL SERVICIO
    public $talleres, $servicios, $taller, $servicio, $tipoServicio, $numSugerido,
        $estado = "esperando", $busquedaCert, $placa, $certificaciones, $fechaCerti, $certificado, $chip;
    public $externo = false;
    // variable para placa antigua
    public $placaantigua;
    // variable para externo 
    public $serviexterno = false;
    public $imagenes = [];
    public $servicioExterno, $tallerExterno, $fechaExterno;
    public Certificacion $certificacion, $duplicado;
    public $tallerAuto; //Para talleres autorizados
    //public $precioexterno;
    //variables del certi
    public $vehiculo;
    //variable para fecha
    public $fechaCertificacion;
    public $minDate, $maxDate;
    // para ver si los inspector son externos y activar checkbox
    public $inspectorexterno = 0;
    //variable temporal para taller inter e inspector 
    public $insp;
    public $esModiMotor = false; // checkbox para identificar si es modificación de motor

    // para clasificar imagenes IA
    public $tipos_imagen = []; // lista de tipos de imagenes
    public $tiposFotosNuevas = [];
    public $confidences = [];


    protected $rules = ["placa" => "required|min:3|max:7"];

    public function mount()
    {
        $this->talleres = Taller::all()->sortBy('nombre');
        //$this->estado = "esperando";
        //$this->certificacion=new Certificacion();
        $today = Carbon::today(); //para obtener la fecha de hoy
        $this->maxDate = $today->toDateString(); //maximo dia de hoy
        $this->minDate = $today->subDays(3)->toDateString(); //minimo 3 dias anteriores al dia de hoy
        // Obtener el inspector actual
        $this->insp = Auth::user();   
        $insptr = Auth::user(); //     
        // Verificar si el inspector es externo
        $this->inspectorexterno = $insptr->externo == true ? true : null;
        // Si el inspector es externo, activar el checkbox de serviexterno
        $this->serviexterno = $this->inspectorexterno;

        $this->tipos_imagen = TipoImagen::all();
    }

    protected $listeners = ['cargaVehiculo' => 'carga', "refrescaVehiculo" => "refrescaVe"];

    public function render()
    {
        return view('livewire.prueba');
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
        $this->vehiculo = vehiculo::find($id);
    }

    public function updatedTaller($val)
    {
        if ($val) {
            $this->servicios = Servicio::where("taller_idtaller", $val)
                ->where("estado", 1) // Agregue esto para que muestre solo estado 1
                //->whereNotIn("tipoServicio_idtipoServicio", [3, 4])
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
            $this->sugeridoSegunTipo($this->tipoServicio->id);
            if ($this->tipoServicio->id == 10 || $this->tipoServicio->id == 14) { //agregue tipoServicio 14 para conversion a gnv overhul
                $this->chip = $this->obtieneChip();
            }
            $this->reset(["externo", "estado"]);
        } else {
            $this->tipoServicio = null;
        }
    }

    public function sugeridoSegunTipo($tipoServ)
    {
        $formatoGnv = 1;
        $formatoGlp = 3;
        $formatoModi = 4;
        if ($tipoServ) {
            switch ($tipoServ) {
                case 1:
                    $this->numSugerido = $this->obtieneFormato($formatoGnv);
                    break;
                case 2:
                    $this->numSugerido = $this->obtieneFormato($formatoGnv);
                    break;
                case 3:
                    $this->numSugerido = $this->obtieneFormato($formatoGlp);
                    break;
                case 4:
                    $this->numSugerido = $this->obtieneFormato($formatoGlp);
                    break;
                case 5:
                    $this->numSugerido = $this->obtieneFormato($formatoModi);
                    break;
                case 8:
                    $this->numSugerido = $this->obtieneFormato($formatoGnv);
                    break;
                case 9:
                    $this->numSugerido = $this->obtieneFormato($formatoGlp);
                    break;
                case 10:
                    $this->numSugerido = $this->obtieneFormato($formatoGnv);
                    break;
                case 12:
                    $this->numSugerido = $this->obtieneFormato($formatoGnv);
                    break;
                case 13:
                    $this->numSugerido = $this->obtieneFormato($formatoGlp);
                    break;
                case 14:
                    $this->numSugerido = $this->obtieneFormato($formatoGnv);
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
            ['idUsuario', Auth::id()],
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
            case 1:
                $hoja = Material::where([['numSerie', $serie], ['idTipoMaterial', 1], ['estado', 3], ['idUsuario', Auth::id()]])->first();
                return $hoja;
                break;

            case 2:
                $hoja = Material::where([['numSerie', $serie], ['idTipoMaterial', 1], ['estado', 3], ['idUsuario', Auth::id()]])->first();
                return $hoja;
                break;

            case 3:
                $hoja = Material::where([['numSerie', $serie], ['idTipoMaterial', 3], ['estado', 3], ['idUsuario', Auth::id()]])->first();
                return $hoja;
                break;

            case 4:
                $hoja = Material::where([['numSerie', $serie], ['idTipoMaterial', 3], ['estado', 3], ['idUsuario', Auth::id()]])->first();
                return $hoja;
                break;
                //para modificacion
            case 5:
                $hoja = Material::where([['numSerie', $serie], ['idTipoMaterial', 4], ['estado', 3], ['idUsuario', Auth::id()]])->first();
                return $hoja;
                break;

            case 8:
                $hoja = Material::where([['numSerie', $serie], ['idTipoMaterial', 1], ['estado', 3], ['idUsuario', Auth::id()]])->first();
                return $hoja;
                break;
                //Para duplicado glp
            case 9:
                $hoja = Material::where([['numSerie', $serie], ['idTipoMaterial', 3], ['estado', 3], ['idUsuario', Auth::id()]])->first();
                return $hoja;
                break;
            case 10:
                $hoja = Material::where([['numSerie', $serie], ['idTipoMaterial', 1], ['estado', 3], ['idUsuario', Auth::id()]])->first();
                return $hoja;
                break;
            case 12:
                $hoja = Material::where([['numSerie', $serie], ['idTipoMaterial', 1], ['estado', 3], ['idUsuario', Auth::id()]])->first();
                return $hoja;
                break;
            case 13:
                $hoja = Material::where([['numSerie', $serie], ['idTipoMaterial', 3], ['estado', 3], ['idUsuario', Auth::id()]])->first();
                return $hoja;
                break;
            case 14:
                $hoja = Material::where([['numSerie', $serie], ['idTipoMaterial', 1], ['estado', 3], ['idUsuario', Auth::id()]])->first();
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

    public function certificar()
    {
        $taller = Taller::findOrFail($this->taller);
        $servicio = Servicio::findOrFail($this->servicio);
        $hoja = $this->procesaFormato($this->numSugerido, $servicio->tipoServicio->id);
        if (!$hoja) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
            return;
        }

        if (!isset($this->vehiculo)) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes ingresar un vehículo válido para poder certificar", "icono" => "warning"]);
            return;
        }

        $idTipoServicio = $servicio->tipoServicio->id;
        // Condición para verificar el servicio y llamar a la función correspondiente
        if (in_array($idTipoServicio, [1, 2, 7, 8, 10, 12])) {
            if (!$this->vehiculo->esCertificableGnv) {
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes completar los datos de los equipos para poder certificar", "icono" => "warning"]);
                return;
            }
        } elseif (in_array($idTipoServicio, [3, 4, 9])) {
            if (!$this->vehiculo->esCertificableGlp) {
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes completar los datos de los equipos para poder certificar", "icono" => "warning"]);
                return;
            }
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "El Vehículo no es válido para la certificación, complete los datos de equipos para continuar", "icono" => "warning"]);
            return;
        }


        // Validar y establecer fecha de certificación
        $fechaCertificacion = $this->fechaCertificacion ? Carbon::parse($this->fechaCertificacion) : now();
        // Obtener la hora actual
        $horaActual = now()->format('H:i:s');
        // Combina la fecha proporcionada con la hora actual
        $fechaConHora = $fechaCertificacion->format('Y-m-d') . ' ' . $horaActual;
        // Validar si la fecha de certificación está dentro del rango permitido
        if ($this->fechaCertificacion && ($fechaCertificacion->lt(Carbon::today()->subDays(3)) || $fechaCertificacion->gt(Carbon::today()))) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "La fecha de certificación debe estar dentro de los últimos tres días", "icono" => "warning"]);
            return;
        }

        $certi = Certificacion::certificarGnv($taller, $servicio, $hoja, $this->vehiculo, Auth::user(), $this->serviexterno, $this->placaantigua);

        if ($certi) {
            $this->estado = "certificado";
            $this->certificacion = $certi;

            $expe = Expediente::create([
                "placa" => $this->vehiculo->placa,
                "certificado" => $hoja->numSerie,
                "estado" => 1,
                "idTaller" => $taller->id,
                'usuario_idusuario' => Auth::id(),
                'servicio_idservicio' => $servicio->id,
            ]);

            // Actualiza el campo 'created_at' solo si se selecciona manualmente
            if ($this->fechaCertificacion) {
                $certi->update(['created_at' => $fechaConHora]);
            }

            $this->guardarFotos($expe);
            guardarArchivosEnExpediente::dispatch($expe, $certi);

            $certEx = CertifiacionExpediente::create(["idCertificacion" => $certi->id, "idExpediente" => $expe->id]);

            $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->Hoja->numSerie . " está listo.", "icono" => "success"]);
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
        }
    }

    public function certificarGlp()
    {
        $taller = Taller::findOrFail($this->taller);
        $tallerAuto = Taller::findOrFail($this->tallerAuto);
        $servicio = Servicio::findOrFail($this->servicio);
        $hoja = $this->procesaFormato($this->numSugerido, $servicio->tipoServicio->id);

        if (!$hoja) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "El número de formato no es valido.", "icono" => "warning"]);
            return;
        }

        if (!isset($this->vehiculo)) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes ingresar un vehículo válido para poder certificar", "icono" => "warning"]);
            return;
        }

        $idTipoServicio = $servicio->tipoServicio->id;
        // Condición para verificar el servicio y llamar a la función correspondiente
        if (in_array($idTipoServicio, [1, 2, 7, 8, 10, 12])) {
            if (!$this->vehiculo->esCertificableGnv) {
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes completar los datos de los equipos para poder certificar", "icono" => "warning"]);
                return;
            }
        } elseif (in_array($idTipoServicio, [3, 4, 9])) {
            if (!$this->vehiculo->esCertificableGlp) {
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes completar los datos de los equipos para poder certificar", "icono" => "warning"]);
                return;
            }
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "El Vehículo no es válido para la certificación, complete los datos de equipos para continuar", "icono" => "warning"]);
            return;
        }

        // Validar y establecer fecha de certificación
        $fechaCertificacion = $this->fechaCertificacion ? Carbon::parse($this->fechaCertificacion) : now();
        // Obtener la hora actual
        $horaActual = now()->format('H:i:s');
        // Combina la fecha proporcionada con la hora actual
        $fechaConHora = $fechaCertificacion->format('Y-m-d') . ' ' . $horaActual;
        // Validar si la fecha de certificación está dentro del rango permitido
        /*if ($this->fechaCertificacion && ($fechaCertificacion->lt(Carbon::today()->subDays(3)) || $fechaCertificacion->gt(Carbon::today()))) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "La fecha de certificación debe estar dentro de los últimos tres días", "icono" => "warning"]);
            return;
        }*/

        $certi = Certificacion::certificarGlp($taller, $tallerAuto, $servicio, $hoja, $this->vehiculo, Auth::user(), $this->serviexterno);

        if ($certi) {
            $this->estado = "certificado";
            $this->certificacion = $certi;

            $expe = Expediente::create([
                "placa" => $this->vehiculo->placa,
                "certificado" => $hoja->numSerie,
                "estado" => 1,
                "idTaller" => $taller->id,
                'usuario_idusuario' => Auth::id(),
                'servicio_idservicio' => $servicio->id,
            ]);

            // Actualiza el campo 'created_at' solo si se selecciona manualmente
            if ($this->fechaCertificacion) {
                $certi->update(['created_at' => $fechaConHora]);
            }

            $this->guardarFotos($expe);
            guardarArchivosEnExpediente::dispatch($expe, $certi);

            $certEx = CertifiacionExpediente::create(["idCertificacion" => $certi->id, "idExpediente" => $expe->id]);

            $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->Hoja->numSerie . " está listo.", "icono" => "success"]);
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
        }
    }

    public function certificarmodi()
    {
        $taller = Taller::findOrFail($this->taller);
        $servicio = Servicio::findOrFail($this->servicio);
        $hoja = $this->procesaFormato($this->numSugerido, $servicio->tipoServicio->id);
        if (!$hoja) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
            return;
        }

        if (!isset($this->vehiculo)) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes ingresar un vehículo válido para poder certificar", "icono" => "warning"]);
            return;
        }

        // Validar y establecer fecha de certificación
        $fechaCertificacion = $this->fechaCertificacion ? Carbon::parse($this->fechaCertificacion) : now();
        // Obtener la hora actual
        $horaActual = now()->format('H:i:s');
        // Combina la fecha proporcionada con la hora actual
        $fechaConHora = $fechaCertificacion->format('Y-m-d') . ' ' . $horaActual;
        // Validar si la fecha de certificación está dentro del rango permitido
        if ($this->fechaCertificacion && ($fechaCertificacion->lt(Carbon::today()->subDays(3)) || $fechaCertificacion->gt(Carbon::today()))) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "La fecha de certificación debe estar dentro de los últimos tres días", "icono" => "warning"]);
            return;
        }

        $certi = Certificacion::certificarModi($taller, $servicio, $hoja, $this->vehiculo, Auth::user(), $this->serviexterno, $this->esModiMotor);

        if ($certi) {
            $this->estado = "certificado";
            $this->certificacion = $certi;

            $expe = Expediente::create([
                "placa" => $this->vehiculo->placa,
                "certificado" => $hoja->numSerie,
                "estado" => 1,
                "idTaller" => $taller->id,
                'usuario_idusuario' => Auth::id(),
                'servicio_idservicio' => $servicio->id,
            ]);

            /*/ Guardar el tipo de modificación
            $modMotor =  ModificacionDetalle::create([
                'certificacion_id' => $certi->id,
                'tipo_modificacion' => $this->esModiMotor ? 'motor' : 'normal',
            ]);*/

            // Agrega la fecha al modelo Certificacion
            if ($this->fechaCertificacion) {
                $certi->update(['created_at' => $fechaConHora]);
            }

            $this->guardarFotos($expe);
            guardarArchivosEnExpediente::dispatch($expe, $certi);

            $certEx = CertifiacionExpediente::create(["idCertificacion" => $certi->id, "idExpediente" => $expe->id]);


            $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->Hoja->numSerie . " está listo.", "icono" => "success"]);
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
        }
    }

    /* public function certificarChipDeterioro(){
        
        $taller = Taller::findOrFail($this->taller);
        $servicio = Servicio::findOrFail($this->servicio);
        $chip = $this->chip;


        if (!isset($this->vehiculo)) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes ingresar un vehículo válido para poder certificar", "icono" => "warning"]);
            return;

        }

        $certi = Certificacion::certificarChipDeterioro($taller, $servicio, $chip, $this->vehiculo, Auth::user());

        if ($certi) {
            $this->estado = "certificado";
            $this->certificacion = $certi;

            // Agrega la fecha al modelo Certificacion
            $certi->update(['created_at' => $this->fechaCertificacion]);

            $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi. " está listo.", "icono" => "success"]);
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
        }
    }*/

    public function certificarPreconver()
    {
        $taller = Taller::findOrFail($this->taller);
        $servicio = Servicio::findOrFail($this->servicio);
        $hoja = $this->procesaFormato($this->numSugerido, $servicio->tipoServicio->id);

        if ($hoja != null) {
            if (isset($this->vehiculo)) {
                if ($this->vehiculo->esCertificableGnv) {
                    $certi = Certificacion::certificarGnvPre($taller, $servicio, $hoja, $this->vehiculo, Auth::user(), $this->serviexterno);
                    if ($certi) {
                        $this->estado = "certificado";
                        $this->certificacion = $certi;
                        // Agrega la fecha al modelo Certificacion
                        //$certi->update(['created_at' => $this->fechaCertificacion]);


                        $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->Hoja->numSerie . " esta listo.", "icono" => "success"]);
                    } else {
                        $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
                    }
                } else {
                    $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes completar los datos de los equipos para poder certificar", "icono" => "warning"]);
                }
            } else {
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes ingresar un vehículo valido para poder certificar", "icono" => "warning"]);
            }
        }
    }

    public function certificarPreconverGlp()
    {
        $taller = Taller::findOrFail($this->taller);
        $tallerAuto = Taller::findOrFail($this->tallerAuto);
        $servicio = Servicio::findOrFail($this->servicio);
        $hoja = $this->procesaFormato($this->numSugerido, $servicio->tipoServicio->id);



        if ($hoja != null) {
            if (isset($this->vehiculo)) {
                if ($this->vehiculo->esCertificableGlp) {
                    $certi = Certificacion::certificarGlpPre($taller, $tallerAuto, $servicio, $hoja, $this->vehiculo, Auth::user(), $this->serviexterno);
                    if ($certi) {
                        $this->estado = "certificado";
                        $this->certificacion = $certi;
                        $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->Hoja->numSerie . " esta listo.", "icono" => "success"]);

                        // Agrega la fecha al modelo Certificacion
                        //$certi->update(['created_at' => $this->fechaCertificacion]);
                    } else {
                        $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
                    }
                } else {
                    $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes completar los datos", "icono" => "warning"]);
                }
            } else {
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes ingresar un vehículo valido para poder certificar", "icono" => "warning"]);
            }
        }
    }

    public function obtieneChip()
    {
        $chip = Material::where([["idUsuario", Auth::id()], ["estado", 3], ["idTipoMaterial", 2]])->first();
        return $chip;
    }

    /*
    public function certificarConChip()
    {
        $taller = Taller::findOrFail($this->taller);
        $servicio = Servicio::findOrFail($this->servicio);
        $hoja = $this->procesaFormato($this->numSugerido, $servicio->tipoServicio->id);
        $chip = $this->chip;

        if ($hoja != null) {
            if ($chip != null) {
                if (isset($this->vehiculo)) {
                    if (!$this->vehiculo->esCertificableGnv) {
                        $certi = Certificacion::certificarGnvConChip($taller, $servicio, $hoja, $this->vehiculo, Auth::user(), $chip);
                        if ($certi) {
                            $this->estado = "certificado";
                            $this->certificacion = $certi;
                            $expe = Expediente::create([
                                "placa" => $this->vehiculo->placa,
                                "certificado" => $hoja->numSerie,
                                "estado" => 1,
                                "idTaller" => $taller->id,
                                'usuario_idusuario' => Auth::id(),
                                'servicio_idservicio' => $servicio->id,
                            ]);
                            $this->guardarFotos($expe);
                            guardarArchivosEnExpediente::dispatch($expe, $certi);
                            $certEx = CertifiacionExpediente::create(["idCertificacion" => $certi->id, "idExpediente" => $expe->id]);
                            $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->Hoja->numSerie . " esta listo.", "icono" => "success"]);
                        } else {
                            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
                        }
                    } else {
                        $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes completar los datos de los equipos para poder certificar", "icono" => "warning"]);
                    }
                } else {
                    $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes ingresar un vehículo valido para poder certificar", "icono" => "warning"]);
                }
            } else {
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No cuentas con chips disponibles para realizar este servicio", "icono" => "warning"]);
            }
        }
    }
    */

    public function certificarConChip()
    {
        try {
            $taller = Taller::findOrFail($this->taller);
            $servicio = Servicio::findOrFail($this->servicio);
            $hoja = $this->procesaFormato($this->numSugerido, $servicio->tipoServicio->id);
            $chip = $this->chip;

            if ($hoja != null) {
                if (!empty($chip)) {
                    if (!empty($this->vehiculo) && $this->vehiculo->esCertificableGnv) {
                        $certi = Certificacion::certificarGnvConChip($taller, $servicio, $hoja, $this->vehiculo, Auth::user(), $chip, $this->serviexterno);

                        if ($certi) {
                            $this->estado = "certificado";
                            $this->certificacion = $certi;

                            $expe = Expediente::create([
                                "placa" => $this->vehiculo->placa,
                                "certificado" => $hoja->numSerie,
                                "estado" => 1,
                                "idTaller" => $taller->id,
                                'usuario_idusuario' => Auth::id(),
                                'servicio_idservicio' => $servicio->id,
                            ]);

                            $this->guardarFotos($expe);
                            guardarArchivosEnExpediente::dispatch($expe, $certi);

                            $certEx = CertifiacionExpediente::create(["idCertificacion" => $certi->id, "idExpediente" => $expe->id]);

                            $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->Hoja->numSerie . " está listo.", "icono" => "success"]);
                        } else {
                            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
                        }
                    } else {
                        $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes completar los datos de los equipos para poder certificar", "icono" => "warning"]);
                    }
                } else {
                    $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No cuentas con chips disponibles para realizar este servicio", "icono" => "warning"]);
                }
            } else {
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No se pudo procesar el formato para la certificación", "icono" => "warning"]);
            }
        } catch (\Exception $e) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Ocurrió un error: " . $e->getMessage(), "icono" => "error"]);
        }
    }

    public function certificarOverhul()
    {
        try {
            $taller = Taller::findOrFail($this->taller);
            $servicio = Servicio::findOrFail($this->servicio);
            $hoja = $this->procesaFormato($this->numSugerido, $servicio->tipoServicio->id);
            $chip = $this->chip;

            if ($hoja != null) {
                if (!empty($chip)) {
                    if (!empty($this->vehiculo) && $this->vehiculo->esCertificableGnv) {
                        $certi = Certificacion::certificarGnvOverhul($taller, $servicio, $hoja, $this->vehiculo, Auth::user(), $chip, $this->serviexterno);

                        if ($certi) {
                            $this->estado = "certificado";
                            $this->certificacion = $certi;

                            $expe = Expediente::create([
                                "placa" => $this->vehiculo->placa,
                                "certificado" => $hoja->numSerie,
                                "estado" => 1,
                                "idTaller" => $taller->id,
                                'usuario_idusuario' => Auth::id(),
                                'servicio_idservicio' => $servicio->id,
                            ]);

                            $this->guardarFotos($expe);
                            guardarArchivosEnExpediente::dispatch($expe, $certi);

                            $certEx = CertifiacionExpediente::create(["idCertificacion" => $certi->id, "idExpediente" => $expe->id]);

                            $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->Hoja->numSerie . " está listo.", "icono" => "success"]);
                        } else {
                            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
                        }
                    } else {
                        $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes completar los datos de los equipos para poder certificar", "icono" => "warning"]);
                    }
                } else {
                    $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No cuentas con chips disponibles para realizar este servicio", "icono" => "warning"]);
                }
            } else {
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No se pudo procesar el formato para la certificación", "icono" => "warning"]);
            }
        } catch (\Exception $e) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Ocurrió un error: " . $e->getMessage(), "icono" => "error"]);
        }
    }

    /*public function guardarFotos(Expediente $expe)
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
    }*/
    private function classifyImagesWithIA($paths)
    {
        try {
            $url = "http://127.0.0.1:8000/classify"; // Microservicio IA
            $response = Http::timeout(60)->post($url, ['paths' => $paths]);

            if ($response->failed()) {
                Log::channel('ia_laravel')->error("[IA] Error HTTP al comunicarse con el servidor IA: " . $response->status());
                return [];
            }

            $data = $response->json();
            Log::channel('ia_laravel')->info("[IA] Respuesta recibida del servidor IA:", $data);

            return $data['results'] ?? [];
        } catch (\Throwable $e) {
            Log::channel('ia_laravel')->error("[IA] Excepción al consultar el servidor IA: " . $e->getMessage());
            return [];
        }
    }
    public function updatedImagenes()
    {
        try {
            // Reiniciar arrays
            $this->tiposFotosNuevas = [];
            $this->confidences = [];

            // Guardar temporalmente las imágenes nuevas y construir rutas absolutas
            $paths = [];
            $tempPaths = [];

            foreach ($this->imagenes as $key => $file) {
                $tempPath = $file->store('temp');
                $fullPath = storage_path('app/' . $tempPath);
                $paths[$key] = $fullPath;
                $tempPaths[$key] = $tempPath;
            }

            // Llamar al microservicio IA (FastAPI)
            $results = $this->classifyImagesWithIA(array_values($paths));

            // Procesar resultados
            foreach ($results as $i => $res) {
                $predictedClass = $res['class'] ?? null;
                $confidence = $res['confidence'] ?? 0;
                $path = $res['path'] ?? '';

                // Buscar el tipo de imagen según el código
                $tipo = null;
                if ($predictedClass) {
                    $tipo = TipoImagen::where('codigo', $predictedClass)->first();
                }

                // Buscar índice original por ruta
                $index = array_search($path, $paths);

                if ($index !== false) {
                    $this->tiposFotosNuevas[$index] = $tipo?->id;
                    $this->confidences[$index] = round($confidence, 2);

                    Log::channel('ia_laravel')->info("[IA] Imagen clasificada", [
                        'archivo' => $path,
                        'tipo' => $predictedClass,
                        'confianza' => $confidence,
                        'tipo_id' => $tipo?->id,
                    ]);
                }
            }

            // Limpiar archivos temporales
            foreach ($tempPaths as $tp) {
                Storage::delete($tp);
            }

            // Forzar actualización Livewire
            $this->tiposFotosNuevas = array_values($this->tiposFotosNuevas);
            $this->confidences = array_values($this->confidences);
            $this->imagenes = array_values($this->imagenes);

            Log::channel('ia_laravel')->info("[IA] Clasificación completada en Prueba", [
                'tipos' => $this->tiposFotosNuevas,
                'confidences' => $this->confidences,
            ]);

        } catch (\Throwable $e) {
            Log::channel('ia_laravel')->error("[IA] Error al procesar imágenes en Prueba: " . $e->getMessage());
        }
    }
    public function guardarFotos(Expediente $expe)
    {
        $this->validate(["imagenes" => "nullable|array", "imagenes.*" => "image"]);

        if (count($this->imagenes)) {
            foreach ($this->imagenes as $key => $file) {
                $nombre = $expe->placa . '-foto' . ($key + 1) . '-' . $expe->certificado;
                $ruta = $file->storeAs('public/expedientes', $nombre . '.' . $file->extension());

                Imagen::create([
                    'nombre' => $nombre,
                    'ruta' => $ruta,
                    'extension' => $file->extension(),
                    'Expediente_idExpediente' => $expe->id,
                    'tipo_imagen_id' => $this->tiposFotosNuevas[$key] ?? null,
                    'confidence' => $this->confidences[$key] ?? null,
                    'clasificado_por_ia' => isset($this->tiposFotosNuevas[$key]) ? 1 : 0,
                ]);
            }
        }
        $this->reset(["imagenes", "tiposFotosNuevas", "confidences"]);
    }

    public function guardaDocumentos()
    {
    }

    /*public function refrescaVe()
    {
        $this->vehiculo->refresh();
    }*/
    public function refrescaVe()
    {
        if ($this->vehiculo) {
            $this->vehiculo->refresh();
        }
    }

    // Busca certificacion Glp
    public function buscarCertificacionGLP()
    {
        $this->validate(['placa' => 'required|min:3|max:7']);

        //implementar un switch o if else segun el servicio
        $certis = Certificacion::PlacaVehiculo($this->placa)
            ->orderBy('created_at', 'desc')
            ->get();

        $certs = $certis->whereBetween("tipo_servicio", [3, 4]);

        if ($certs->count() > 0) {
            $this->busquedaCert = true;
            $this->certificaciones = $certs;
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No se encontro ningúna certificación con la placa ingresada", "icono" => "warning"]);
        }
    }

    public function duplicarCertificado()
    {
        $taller = Taller::findOrFail($this->taller);
        $servicio = Servicio::find($this->servicio);
        $hoja = $this->procesaFormato($this->numSugerido, $servicio->tipoServicio->id);


        if ($hoja != null) {
            if ($this->externo) {
                if (isset($this->vehiculo)) {
                    if ($this->vehiculo->esCertificableGnv) {
                        $servicio = Servicio::find($this->servicio);

                        $duplicado = $this->creaDuplicadoExterno();
                        $certi = Certificacion::duplicarCertificadoExternoGnv(Auth::user(), $this->vehiculo, $servicio, $taller, $hoja, $duplicado, $this->serviexterno);
                        $this->estado = "certificado";
                        $this->certificacion = $certi;
                        $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->Hoja->numSerie . " esta listo.", "icono" => "success"]);
                    } else {
                        $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes completar los datos de los equipos para poder certificar", "icono" => "warning"]);
                    }
                } else {
                    $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes ingresar un vehículo valido para poder certificar", "icono" => "warning"]);
                }
            } else {

                if ($this->certificado && $servicio) {
                    $dupli = $this->creaDuplicado($this->certificado);
                    $certi = Certificacion::duplicarCertificadoGnv($dupli, $taller, Auth::user(), $servicio, $hoja, $this->serviexterno);
                    $this->estado = "certificado";
                    $this->certificacion = $certi;
                    $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->Hoja->numSerie . " esta listo.", "icono" => "success"]);
                } else {
                    $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes buscar una certificacion para poder duplicar.", "icono" => "warning"]);
                }
            }
        }
    }

    public function creaDuplicadoExterno()
    {
        $this->validate(
            [
                "tallerExterno" => "required|min:3",
                "fechaExterno" => "required|date",
                "servicioExterno" => "required|min:1",
            ]
        );
        $dupli = Duplicado::create([
            "servicio" => $this->servicioExterno,
            "taller" => $this->tallerExterno,
            "externo" => 1,
            "fecha" => $this->fechaExterno,
        ]);
        if ($dupli) {
            return $dupli;
        } else {
            return null;
        }
    }
    public function creaDuplicadoExternoGlp()
    {
        $this->validate(
            [
                "tallerExterno" => "required",
                "fechaExterno" => "required|date",
                "servicioExterno" => "required|min:1",
            ]
        );
        $dupli = Duplicado::create([
            "servicio" => $this->servicioExterno,
            "taller" => $this->tallerExterno,
            "externo" => 1,
            "fecha" => $this->fechaExterno,
        ]);
        if ($dupli) {
            return $dupli;
        } else {
            return null;
        }
    }

    public function creaDuplicado(Certificacion $anterior)
    {
        $dupli = Duplicado::create([
            "servicio" => $anterior->Servicio->tipoServicio->id,
            "taller" => $anterior->Taller->id,
            "externo" => 0,
            "fecha" => $anterior->created_at,
            "idAnterior" => $anterior->id,
        ]);

        return $dupli;
    }

    //Funciones para duplicado GLP
    public function duplicarCertificadoGLP()
    {
        $taller = Taller::findOrFail($this->taller);
        //$tallerAuto = Taller::findOrFail($this->tallerAuto);
        $servicio = Servicio::find($this->servicio);
        $hoja = $this->procesaFormato($this->numSugerido, $servicio->tipoServicio->id);


        if ($hoja != null) {
            if ($this->externo) {
                if (isset($this->vehiculo)) {
                    if ($this->vehiculo->esCertificableGlp) {
                        $servicio = Servicio::find($this->servicio);

                        $duplicado = $this->creaDuplicadoExternoGlp();
                        $certi = Certificacion::duplicarCertificadoExternoGlp(Auth::user(), $this->vehiculo, $servicio, $taller, $hoja, $duplicado, $this->serviexterno);
                        $this->estado = "certificado";
                        $this->certificacion = $certi;
                        $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->Hoja->numSerie . " esta listo.", "icono" => "success"]);
                    } else {
                        $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes completar los datos de los equipos para poder certificar", "icono" => "warning"]);
                    }
                } else {
                    $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes ingresar un vehículo valido para poder certificar", "icono" => "warning"]);
                }
            } else {

                if ($this->certificado && $servicio) {
                    $dupli = $this->creaDuplicado($this->certificado);
                    $certi = Certificacion::duplicarCertificadoGlp($dupli, $taller, Auth::user(), $servicio, $hoja, $this->serviexterno);
                    $this->estado = "certificado";
                    $this->certificacion = $certi;
                    $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->Hoja->numSerie . " esta listo.", "icono" => "success"]);
                } else {
                    $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Debes buscar una certificacion para poder duplicar.", "icono" => "warning"]);
                }
            }
        }
    }


    
}
