<?php

namespace App\Http\Livewire;

use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\Desmontes;
use App\Models\Taller;
use App\Models\TipoServicio;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;

class ActualizarPrecios extends Component
{
    public $fechaInicio, $fechaFin, $talleres, $inspectores;
    public $ins = [], $taller = [], $tipos, $servicio;
    public $reportePorInspector, $certificacionIds = [];
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
        return view('livewire.actualizar-precios');
    }

    public function calcularReporte()
    {
        $this->validate();
        $cargarDatos = $this->datosMostrar();
        //dd($cargarDatos);
        $this->reportePorInspector = $cargarDatos;
    }

    private function datosMostrar()
    {
        $tabla = new Collection();
        //TODO CERTIFICACIONES:
        $certificaciones = Certificacion::IdTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->IdTipoServicio($this->servicio)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            ->whereIn('pagado', [0, 2])
            ->whereNotIn('estado', [2])
            ->get();

        //TODO CER-PENDIENTES:
        $cerPendiente = CertificacionPendiente::IdTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();

        //TODO DESMONTES:
        $desmontes = Desmontes::IdTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();

        //unificando certificaciones     
        foreach ($certificaciones as $certi) {
            $data = [
                "id" => $certi->id,
                "placa" => $certi->Vehiculo->placa,
                "taller" => $certi->Taller->nombre,
                "inspector" => $certi->Inspector->name,
                "servicio" => $certi->Servicio->tipoServicio->descripcion,
                "num_hoja" => $certi->NumHoja,
                "ubi_hoja" => $certi->UbicacionHoja,
                "precio" => $certi->precio,
                "pagado" => $certi->pagado,
                "estado" => $certi->estado,
                "externo" => $certi->externo,
                "tipo_modelo" => $certi::class,
                "fecha" => $certi->created_at,

            ];
            $tabla->push($data);
        }

        foreach ($cerPendiente as $cert_pend) {
            //modelo preliminar
            $data = [
                "id" => $cert_pend->id,
                "placa" => $cert_pend->Vehiculo->placa,
                "taller" => $cert_pend->Taller->nombre,
                "inspector" => $cert_pend->Inspector->name,
                "servicio" => 'Activación de chip (Anual)',
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $cert_pend->precio,
                "pagado" => $cert_pend->pagado,
                "estado" => $cert_pend->estado,
                "externo" => $cert_pend->externo,
                "tipo_modelo" => $cert_pend::class,
                "fecha" => $cert_pend->created_at,
            ];
            $tabla->push($data);
        }

        foreach ($desmontes as $des) {
            $data = [
                "id" => $des->id,
                "placa" => $des->placa,
                "taller" => $des->Taller->nombre,
                "inspector" => $des->Inspector->name,
                "servicio" => $des->Servicio->tipoServicio->descripcion,
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $des->precio,
                "pagado" => $des->pagado,
                "estado" => $des->estado,
                "externo" => $des->externo,
                "tipo_modelo" => $des::class,
                "fecha" => $des->created_at,
            ];
            $tabla->push($data);
        }
        return $tabla;
        //return $tabla->groupBy('inspector');
    }

    public function ver($certificacionIds, $tiposServicios)
    {
        $this->certificacionIds = $certificacionIds;
        $this->tiposServicios = $tiposServicios;
        //dd($certificacionIds, $tiposServicios);
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
                    case 'Conversión a GLP':
                    case 'Revisión anual GLP':
                    case 'Modificación':
                    case 'Duplicado GNV':
                    case 'Duplicado GLP':
                    case 'Conversión a GNV + Chip':
                    case 'Conversión a GNV OVERHUL':
                    case 'Chip por deterioro': //revisar chip por deterioro
                    case 'Pre-conversión GNV':
                    case 'Pre-conversión GLP':
                        Certificacion::whereIn('id', $certificacionIds)
                            ->whereHas('servicio', function ($query) use ($tipoServicio) {
                                $query->whereHas('tipoServicio', function ($query) use ($tipoServicio) {
                                    $query->where('descripcion', $tipoServicio);
                                });
                            })
                            ->update(['precio' => $nuevoPrecio]);
                        break;

                    case 'Activación de chip (Anual)':
                        CertificacionPendiente::whereIn('id', $certificacionIds)
                            ->update(['precio' => $nuevoPrecio]);
                        break;

                    case 'Desmonte de Cilindro':
                        Desmontes::whereIn('id', $certificacionIds)
                            ->update(['precio' => $nuevoPrecio]);
                        break;

                    default:
                        // Manejo de error 
                        break;
                }
            }

            // Emitir evento para indicar que los precios han sido actualizados
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


/*private function datosMostrar()
    {

        $certificaciones = DB::table('certificacion')
            ->select(
                'certificacion.id',
                'certificacion.idTaller',
                'certificacion.idInspector',
                'certificacion.idVehiculo',
                'certificacion.idServicio',
                'certificacion.estado',
                'certificacion.created_at',
                'certificacion.precio',
                'certificacion.pagado',
                'users.name as nombre',
                'taller.nombre as taller',
                'vehiculo.placa as placa',
                'tiposervicio.descripcion as tiposervicio',
                DB::raw('(SELECT material.numSerie FROM serviciomaterial 
                LEFT JOIN material ON serviciomaterial.idMaterial = material.id 
                WHERE serviciomaterial.idCertificacion = certificacion.id LIMIT 1) as matenumSerie'),
                DB::raw('(SELECT material.ubicacion FROM serviciomaterial 
                LEFT JOIN material ON serviciomaterial.idMaterial = material.id 
                WHERE serviciomaterial.idCertificacion = certificacion.id LIMIT 1) as mateubicacion')


            )
            ->join('users', 'certificacion.idInspector', '=', 'users.id')
            ->join('taller', 'certificacion.idTaller', '=', 'taller.id')
            ->join('vehiculo', 'certificacion.idVehiculo', '=', 'vehiculo.id')
            ->join('servicio', 'certificacion.idServicio', '=', 'servicio.id')
            ->join('tiposervicio', 'servicio.tipoServicio_idtipoServicio', '=', 'tiposervicio.id')
            ->whereIn('certificacion.pagado', [0, 2])
            ->whereIn('certificacion.estado', [3, 1])
            ->where(fn($query) => $this->agregarFiltros($query))
            ->where(fn($query) => $this->filtrarPorFecha($query, 'certificacion'))
            ->get();

        $certificadosPendientes = DB::table('certificados_pendientes')
            ->select(
                'certificados_pendientes.id',
                'certificados_pendientes.idTaller',
                'certificados_pendientes.idInspector',
                'certificados_pendientes.idVehiculo',
                'certificados_pendientes.idServicio',
                'certificados_pendientes.estado',
                'certificados_pendientes.created_at',
                'certificados_pendientes.pagado',
                'certificados_pendientes.precio',
                'users.name as nombre',
                'taller.nombre as taller',
                'vehiculo.placa as placa',
                DB::raw("'Activación de chip (Anual)' as tiposervicio")
            )
            ->Join('users', 'certificados_pendientes.idInspector', '=', 'users.id')
            ->Join('taller', 'certificados_pendientes.idTaller', '=', 'taller.id')
            ->Join('vehiculo', 'certificados_pendientes.idVehiculo', '=', 'vehiculo.id')
            ->Join('servicio', 'certificados_pendientes.idServicio', '=', 'servicio.id')
            ->where(fn($query) => $this->agregarFiltros($query))
            ->where(fn($query) => $this->filtrarPorFecha($query, 'certificados_pendientes'))
            ->get();

        $desmonte = DB::table('desmontes')
            ->select(
                'desmontes.id',
                'desmontes.placa',
                'desmontes.idTaller',
                'desmontes.idInspector',
                'desmontes.idServicio',
                'desmontes.estado',
                'desmontes.created_at',
                'desmontes.pagado',
                'desmontes.precio',
                'users.name as nombre',
                'taller.nombre as taller',
                DB::raw("'Desmonte de Cilindro' as tiposervicio")
            )
            ->Join('users', 'desmontes.idInspector', '=', 'users.id')
            ->Join('taller', 'desmontes.idTaller', '=', 'taller.id')
            ->Join('servicio', 'desmontes.idServicio', '=', 'servicio.id')
            ->where('desmontes.estado', 1)
            ->where(fn($query) => $this->agregarFiltros($query))
            ->where(fn($query) => $this->filtrarPorFecha($query, 'desmontes'))
            ->get();


        $resultadosdetalle = $certificaciones->concat($certificadosPendientes)->concat($desmonte);

        return $resultadosdetalle->groupBy('nombre');
        //return $resultadosdetalle->groupBy('nombre')->map(function ($grupo) {
        //    return $grupo->map(fn($item) => (object) $item);
        //});
        
    }

    private function filtrarPorFecha($query, $tabla)
    {
        return $query->whereBetween("{$tabla}.created_at", [
            $this->fechaInicio . ' 00:00:00',
            $this->fechaFin . ' 23:59:59'
        ]);
    }

    private function agregarFiltros($query)
    {
        if (!empty($this->ins)) {
            $query->whereIn('idInspector', $this->ins);
        }

        if (!empty($this->taller)) {
            $query->whereIn('idTaller', $this->taller);
        }
    }*/