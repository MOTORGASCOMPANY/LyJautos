<?php

namespace App\Http\Livewire;

use App\Models\Anulacion;
use App\Models\Archivo;
use App\Models\Certificacion;
use App\Models\Eliminacion;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AnulacionSolicitud as NotificationsCreateSolicitud;
use App\Notifications\SolicitudEliminacion;
use App\Services\CandadoService;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;


class ListaCertificaciones extends Component
{
    use WithPagination;

    public $search, $sort, $direction, $cant, $user;
    public $modal = false, $eliminar, $motivo, $nombre, $imagen, $certiId;
    protected $candadoService;
    use WithFileUploads;

    public function __construct()
    {
        parent::__construct(); // Asegúrate de llamar al constructor de la clase padre
        $this->candadoService = app(CandadoService::class); // Inyección de servicio
    }

    protected $queryString = [
        'cant' => ['except' => '10'],
        'sort' => ['except' => 'certificacion.id'],
        'direction' => ['except' => 'desc'],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->user = Auth::id();
        $this->cant = "10";
        $this->sort = 'certificacion.id';
        $this->direction = "desc";
    }

    public function render()
    {

        $certificaciones = Certificacion::numFormato($this->search)
            ->placaVehiculo($this->search)
            ->numSerieVehiculo($this->search) //para buscar preiniciales
            ->idInspector(Auth::id())
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant);

        return view('livewire.lista-certificaciones', compact('certificaciones'));
    }

    public function order($sort)
    {
        if ($this->sort = $sort) {
            if ($this->direction == 'desc') {
                $this->direction = 'asc';
            } else {
                $this->direction = 'desc';
            }
        } else {
            $this->sort = $sort;
            $this->direction = 'asc';
        }
    }


    public function generarRuta($cer)
    {
        $certificacion = Certificacion::find($cer);
        $ver = "";
        $descargar = "";
        if ($certificacion) {
            $tipoSer = $certificacion->Servicio->tipoServicio->id;
            switch ($tipoSer) {
                case 1:
                    $ver = route('certificadoInicial', ['id' => $certificacion->id]);
                    break;
                case 2:
                    $ver = route('certificado', ['id' => $certificacion->id]);
                    break;
                default:
                    # code...
                    break;
            }
        }

        return $ver;
    }
    public function generarRutaDescarga($cer)
    {
        $certificacion = Certificacion::find($cer);
        $descargar = "";
        if ($certificacion) {
            $tipoSer = $certificacion->Servicio->tipoServicio->id;
            switch ($tipoSer) {
                case 1:
                    $descargar = route('descargarInicial', ['id' => $certificacion->id]);
                    break;
                case 2:
                    $descargar = route('descargarCertificado', ['id' => $certificacion->id]);
                    break;
                default:
                    # code...
                    break;
            }
        }

        return $descargar;
    }


    public function obtieneNumeroHoja($id)
    {
        $certificacion = Certificacion::find($id);
        $hoja = $certificacion->Materiales->where('idTipoMaterial', 1)->first();
        if ($hoja->numSerie != null) {
            return $hoja->numSerie;
        } else {
            return 0;
        }
    }

    public function finalizarPreconversion(Certificacion $certi)
    {
        $ruta = route('finalizarPreconver', ["idCertificacion" => $certi->id]);
        return redirect()->to($ruta);
    }

    public function solicitarAnulacion($certificationId)
    {
        $this->modal = true;
        $this->certiId = $certificationId;
    }

    /*public function guardarSolicitudAnulacion()
    {
        $this->validate([
            'motivo' => 'required',
            'imagen' => 'required|image',
        ]);

        try {
            $certificacion = Certificacion::find($this->certiId);
            $placa = $certificacion->Vehiculo->placa ?? 'SinPlaca'; 
            $nombreArchivo = $placa . '-' . time() . '.' . $this->imagen->getClientOriginalExtension();

            // Especifica el disco 'public' explícitamente
            $rutaImagen = $this->imagen->storeAs('anular', $nombreArchivo, 'public');

            // Verifica si la rutaImagen se generó correctamente
            if (!$rutaImagen) {
                throw new \Exception('Error al almacenar la imagen.');
            }

            // Crear la solicitud de anulación
            $solicitudAnulacion = Anulacion::create([
                'motivo' => $this->motivo,
            ]);

            // Crear la entrada en la tabla de imágenes
            $imagen = Archivo::create([
                'nombre' => $nombreArchivo,
                'ruta' => $rutaImagen,
                'extension' => $this->imagen->getClientOriginalExtension(),
                'idDocReferenciado' => $solicitudAnulacion->id,
            ]);

            // Emitir una notificación de éxito
            $this->emit("CustomAlert", ["titulo" => "Solicitud de anulación enviada", "mensaje" => "Su solicitud de anulación ha sido enviada con éxito.", "icono" => "success"]);

            // Notificar a los usuarios administradores
            $users = User::role(['administrador'])->get();
            Notification::send($users, new NotificationsCreateSolicitud($solicitudAnulacion, $certificacion, Auth::user()));

            // Cierra el modal y resetea las propiedades
            $this->reset(['motivo', 'imagen', 'certiId', 'modal']);
            $this->emit('closeModal');
            //return redirect('Listado-Certificaciones');
        } catch (\Exception $e) {
            // Manejar cualquier excepción y emitir una notificación de error
            $this->emit("CustomAlert", [
                "titulo" => "Error",
                "mensaje" => $e->getMessage(),
                "icono" => "error"
            ]);

            // Loguear el error para revisarlo más tarde
            Log::error('Error al guardar la solicitud de anulación: ' . $e->getMessage());

            return redirect()->back();
        }
    }*/

    public function guardarSolicitudAnulacion()
    {
        $this->validate([
            'motivo' => 'required',
            'imagen' => 'required|image',
        ]);

        try {
            $certificacion = Certificacion::find($this->certiId);

            if (!$certificacion) {
                throw new \Exception('Certificación no encontrada.');
            }

            if (!$this->candadoService->validarRangoDias($certificacion->created_at)) {
                throw new \Exception('La solicitud no puede enviarse, ya que excede el rango permitido.');
            }

            $placa = $certificacion->Vehiculo->placa ?? 'SinPlaca';
            $nombreArchivo = $placa . '-' . time() . '.' . $this->imagen->getClientOriginalExtension();

            // Especifica el disco 'public' explícitamente
            $rutaImagen = $this->imagen->storeAs('anular', $nombreArchivo, 'public');

            // Verifica si la rutaImagen se generó correctamente
            if (!$rutaImagen) {
                throw new \Exception('Error al almacenar la imagen.');
            }

            // Crear la solicitud de anulación
            $solicitudAnulacion = Anulacion::create([
                'motivo' => $this->motivo,
            ]);

            // Crear la entrada en la tabla de imágenes
            $imagen = Archivo::create([
                'nombre' => $nombreArchivo,
                'ruta' => $rutaImagen,
                'extension' => $this->imagen->getClientOriginalExtension(),
                'idDocReferenciado' => $solicitudAnulacion->id,
            ]);
            
            // Notificar a los usuarios administradores
            $users = User::role(['administrador'])->get();
            Notification::send($users, new NotificationsCreateSolicitud($solicitudAnulacion, $certificacion, Auth::user()));

            // Cierra el modal y resetea las propiedades
            $this->reset(['motivo', 'imagen', 'certiId', 'modal']);
            $this->emit('closeModal');
            // Emitir una notificación de éxito
            $this->emit("CustomAlert", ["titulo" => "Solicitud de anulación enviada", "mensaje" => "Su solicitud de anulación ha sido enviada con éxito.", "icono" => "success"]);
        } catch (\Exception $e) {
            // Manejar cualquier excepción y emitir una notificación de error
            $this->emit("CustomAlert", [
                "titulo" => "Error",
                "mensaje" => $e->getMessage(),
                "icono" => "error"
            ]);

            // Loguear el error para revisarlo más tarde
            Log::error('Error al guardar la solicitud de anulación: ' . $e->getMessage());

            return redirect()->back();
        }
    }

    /*public function solicitarEliminacion($certificationId)
    {
        $solicitudAnulacion = Eliminacion::create([
            
        ]);    
        $this->emit("CustomAlert", [
            "titulo" => "Solicitud de eliminación enviada",
            "mensaje" => "Su solicitud de eliminación ha sido enviada con éxito.",
            "icono" => "success",
        ]);
    
        // Crea una notificación
        $certificacion = Certificacion::find($certificationId);
        $users = User::role(['administrador'])->get();
        Notification::send($users, new SolicitudEliminacion( $solicitudAnulacion,$certificacion, Auth::user()));
        return redirect('Listado-Certificaciones');
        
    }*/

    public function solicitarEliminacion($certificationId)
    {
        $certificacion = Certificacion::find($certificationId);

        if (!$certificacion) {
            $this->emit("CustomAlert", ["titulo" => "Error", "mensaje" => "La certificación no existe.", "icono" => "error",]);
            return;
        }
        
        if (!$this->candadoService->validarRangoDias($certificacion->created_at)) {
            $this->emit("CustomAlert", [
                "titulo" => "Solicitud no permitida",
                "mensaje" => "No se puede enviar la solicitud porque está fuera del rango de días.",
                "icono" => "error",
            ]);
            return;
        }

        // Si está dentro del rango, crea la solicitud
        $solicitudEliminacion  = Eliminacion::create([
            'placa' => $certificacion->placa ?? null,
            'numSerie' => $certificacion->Hoja->numSerie ?? null,
            'tipoServicio' => $certificacion->Servicio->tipoServicio->descripcion ?? null,
        ]);

        $users = User::role(['administrador'])->get();
        Notification::send($users, new SolicitudEliminacion($solicitudEliminacion, $certificacion, Auth::user()));
        $this->emit("CustomAlert", ["titulo" => "Solicitud de eliminación enviada", "mensaje" => "Su solicitud de eliminación ha sido enviada con éxito.", "icono" => "success",]);
        //return redirect('Listado-Certificaciones');
    }
}
