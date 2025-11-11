<?php

namespace App\Http\Livewire\Reportes;

use App\Exports\ReporteTallerExport;
use App\Models\Boleta;
use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\CertificacionTaller;
use App\Models\CertificacionTemporal;
use App\Models\Desmontes;
use App\Models\Material;
use App\Models\ServiciosImportados;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Taller;
use App\Models\TipoServicio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;

class ReporteCalcularChip extends Component
{
    public $fechaInicio, $fechaFin, $resultados, $talleres, $inspectores, $certis, $tipos;
    public $ins = [], $taller = [];
    public $servicio;
    public $grupotalleres;
    public $tabla, $diferencias, $importados, $aux;
    public $tabla2;
    public $inspectoresPorTaller = [];
    public $boletas, $grupoboletas;
    public $boleta, $idBoleta;


    protected $listeners = ['exportarExcel'];

    protected $rules = [
        "fechaInicio" => 'required|date',
        "fechaFin" => 'required|date',
    ];

    public function mount()
    {
        $this->inspectores = User::role(['inspector', 'supervisor'])->orderBy('name')->get();
        $this->talleres = Taller::orderBy('nombre')->get();
        $this->tipos = TipoServicio::all();
        $this->boleta = Boleta::find($this->idBoleta);
    }

    public function render()
    {
        return view('livewire.reportes.reporte-calcular-chip');
    }

    public function procesar()
    {
        $this->validate();
        $this->tabla = $this->generaData();
        $this->importados = $this->cargaServiciosGasolution();
        //$this->boletas = $this->cargaBoletas();
        //dd($this->boletas);
        $this->tabla = $this->tabla->map(function ($item) {
            $item['placa'] = trim($item['placa']);
            $item['inspector'] = trim($item['inspector']);
            $item['taller'] = trim($item['taller']);
            return $item;
        });
        $this->importados = $this->importados->map(function ($item) {
            $item['placa'] = trim($item['placa']);
            $item['inspector'] = trim($item['inspector']);
            $item['taller'] = trim($item['taller']);
            return $item;
        });
        //$this->diferencias = $this->encontrarDiferenciaPorPlaca($this->importados, $this->tabla);
        // Filtrar los elementos de tabla que tengan estado 1 o 3
        $tablaFiltrada = $this->tabla->filter(function ($item) {
            if ($item['tipo_modelo'] === 'App\Models\Certificacion') {
                return $item['estado'] !== 2;
            }
            return true; // para CertificacionPendiente y Desmontes
        });
        $this->diferencias = $this->encontrarDiferenciaPorPlaca($this->importados, $tablaFiltrada);

        //$this->tabla2 = $this->tabla->merge($this->diferencias);
        //Merge para combinar tabla y diferencias -  strtolower para ignorar Mayusculas y Minusculas 
        $this->tabla2 = $this->tabla->merge($this->diferencias, function ($item1, $item2) {
            $inspector1 = strtolower($item1['inspector']);
            $inspector2 = strtolower($item2['inspector']);
            $taller1 = strtolower($item1['taller']);
            $taller2 = strtolower($item2['taller']);
            $comparison = strcasecmp($inspector1 . $taller1, $inspector2 . $taller2);
            return $comparison;
        });

        //$this->aux = $this->tabla2->groupBy('taller');
        $this->aux = $this->tabla2->groupBy('taller')->sortBy(function ($item, $key) { //para ordenar 
            return $key;
        });
        $this->generarInspectoresPorTaller();
    }

    public function generarInspectoresPorTaller()
    {
        $inspectoresPorTaller = [];

        foreach ($this->aux as $nombreTaller => $certificaciones) {
            $inspectoresPorTaller[$nombreTaller] = $certificaciones->pluck('inspector')->unique()->toArray();
        }

        $this->inspectoresPorTaller = $inspectoresPorTaller;
    }

    public function exportarExcel($data)
    {
        //dd($data);
        return Excel::download(new ReporteTallerExport($data), 'reporte_calculoTaller.xlsx');
    }

    public function generaData()
    {
        $tabla = new Collection();
        $importados = $this->cargaServiciosGasolution();
        //TODO CERTIFICACIONES:
        $certificaciones = Certificacion::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->IdTipoServicio($this->servicio)
            // Excluir al inspector con id = 201 
            ->whereHas('Inspector', function ($query) {
                $query->whereNotIn('id', [37, 117, 201]);
            })
            ->rangoFecha($this->fechaInicio, $this->fechaFin)
            //->where('pagado', 0)
            ->whereIn('pagado', [0, 2])
            //->whereIn('estado', [3, 1])
            ->get();

        //TODO CER-PENDIENTES:
        $cerPendiente = CertificacionPendiente::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->whereHas('Inspector', function ($query) {
                $query->whereNotIn('id', [37, 117, 201]);
            })
            ->IdTipoServicios($this->servicio)
            ->rangoFecha($this->fechaInicio, $this->fechaFin)
            //->where('estado', 1)
            //->whereNull('idCertificacion')
            ->get();

        //TODO DESMONTES:
        $desmontes = Desmontes::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->whereHas('Inspector', function ($query) {
                $query->whereNotIn('id', [37, 117, 201]);
            })
            ->IdTipoServicios($this->servicio)
            ->rangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();
        
        //TODO CERT TEMPORALES:
        $cerTemp = CertificacionTemporal::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->IdTipoServicios($this->servicio)
            ->rangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();

        //unificando certificaciones     
        foreach ($certificaciones as $certi) {
            $existeEnImportados = false;
            // Verificar si la certificación actual está en ServiciosImportados
            foreach ($importados as $importado) {
                // Comparación por placa e inspector
                if (
                    /*$certi->Vehiculo->placa == $importado['placa'] &&
                    $certi->Inspector->name == $importado['inspector'] &&
                    $certi->Taller->nombre == $importado['taller']*/
                    trim($certi->Vehiculo->placa) == trim($importado['placa']) &&
                    trim($certi->Inspector->name) == trim($importado['inspector']) &&
                    trim($certi->Taller->nombre) == trim($importado['taller'])
                ) {
                    // Verificar si el servicio coincide o si es el caso especial
                    if (
                        $certi->Servicio->tipoServicio->descripcion == $importado['servicio'] ||
                        (
                            $importado['servicio'] == 'Conversión a GNV' &&
                            $certi->Servicio->tipoServicio->descripcion == 'Conversión a GNV + Chip'
                        )
                    ) {
                        $existeEnImportados = true;
    
                        // Actualizar el servicio a 'Conversión a GNV + Chip' en caso de coincidencia especial
                        if ($importado['servicio'] == 'Conversión a GNV') {
                            $importado['servicio'] = 'Conversión a GNV + Chip';
                        }
    
                        break;
                    }
                }
            }
            // Lógica para determinar si la observación será 'null' solo para los servicios específicos
            $serviciosEspecificos = ['Revisión anual GNV', 'Conversión a GNV', 'Activación de chip (Anual)', 'Conversión a GNV + Chip'];
            $soloEnCertificacion = !$existeEnImportados && in_array($certi->Servicio->tipoServicio->descripcion, $serviciosEspecificos);

            // Calcular el precio según el estado o si la placa es antigua
            $precio = number_format(($certi->estado == 2 || $certi->placaantigua == 1) ? 0 : $certi->precio, 2, '.', '');

            // descripcion servicio para modi motor
            $descripcionServicio = $certi->Servicio->tipoServicio->descripcion;
            //if ($certi->Servicio->tipoServicio->id == 5 && optional($certi->modificacionDetalle)->tipo_modificacion === 'motor') {
            if ($certi->Servicio->tipoServicio->id == 5 && $certi->descripcion === 'Modificación Motor') {
                $descripcionServicio = 'Modificación Motor';
            }

            // placa para chip por deterioro
            $esChipDeterioro = $descripcionServicio === 'Chip por deterioro';
            // Determinar la placa correcta para mostrar (chip por deterioro)
            if ($esChipDeterioro) {
                if (!is_null($certi->descripcion)) {
                    $placaFinal = Str::afterLast($certi->descripcion, '/') ?? 'N.A';
                } else {
                    $placaFinal = Str::afterLast($certi->UbicacionHoja, '/') ?? 'N.A';
                }
            } else {
                $placaFinal = $certi->Vehiculo->placa;
            }

            //modelo preliminar
            $data = [
                "id" => $certi->id,
                "placa" => $certi->Vehiculo->placa,
                "placa" => $placaFinal, //$certi->Vehiculo->placa,
                "taller" => $certi->Taller->nombre,
                "inspector" => $certi->Inspector->name,
                "servicio" => $descripcionServicio,
                "num_hoja" => $certi->NumHoja,
                "ubi_hoja" => $certi->UbicacionHoja,
                "precio" => $precio,
                "pagado" => $certi->pagado,
                "estado" => $certi->estado,
                "externo" => $certi->externo,
                "placaantigua" => $certi->placaantigua,
                "tipo_modelo" => $certi::class,
                "fecha" => $certi->created_at,
                "solo_en_certificacion" => $soloEnCertificacion

            ];
            $tabla->push($data);
        }

        foreach ($cerPendiente as $cert_pend) {
            // Inicializamos la bandera de si está en ServiciosImportados
            $existeEnImportados = false;
            // Verificar si la certificación actual está en ServiciosImportados
            foreach ($importados as $importado) {
                if (
                    /*$cert_pend->Vehiculo->placa == $importado['placa'] &&
                    $cert_pend->Inspector->name == $importado['inspector'] &&
                    $cert_pend->Taller->nombre == $importado['taller']*/
                    trim($cert_pend->Vehiculo->placa) == trim($importado['placa']) &&
                    trim($cert_pend->Inspector->name) == trim($importado['inspector']) &&
                    trim($cert_pend->Taller->nombre) == trim($importado['taller'])
                ) {
                    if (
                        $importado['servicio'] == 'Revisión anual GNV' ||
                        $importado['servicio'] == 'Activación de chip (Anual)'
                    ) {
                        $existeEnImportados = true;
    
                        // Actualizar el servicio en caso de coincidencia especial
                        if ($importado['servicio'] == 'Revisión anual GNV') {
                            $importado['servicio'] = 'Activación de chip (Anual)';
                        }
    
                        break;
                    }
                }
            }
            //modelo preliminar
            $data = [
                "id" => $cert_pend->id,
                "placa" => $cert_pend->Vehiculo->placa,
                "taller" => $cert_pend->Taller->nombre,
                "inspector" => $cert_pend->Inspector->name,
                "servicio" => 'Activación de chip (Anual)', // es ese tipo de servicio por defecto
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $cert_pend->precio,
                "pagado" => $cert_pend->pagado,
                "estado" => $cert_pend->estado,
                "externo" => $cert_pend->externo,
                "tipo_modelo" => $cert_pend::class,
                "fecha" => $cert_pend->created_at,
                "solo_en_certificacion" => !$existeEnImportados
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

        foreach ($cerTemp as $ctemp) {
            $data = [
                "id" => $ctemp->id,
                "placa" => $ctemp->placa,
                "taller" => $ctemp->Taller->nombre,
                "inspector" => $ctemp->Inspector->name,
                "servicio" => $ctemp->Servicio->tipoServicio->descripcion,
                "num_hoja" => $ctemp->numSerie,
                "ubi_hoja" => Null,
                "precio" => $ctemp->precio,
                "pagado" => $ctemp->pagado,
                "estado" => $ctemp->estado,
                "externo" => $ctemp->externo,
                "tipo_modelo" => $ctemp::class,
                "fecha" => $ctemp->created_at,
            ];
            $tabla->push($data);
        }

        //$this->grupotalleres = $tabla->groupBy('taller');
        return $tabla;
    }    

    public function encontrarDiferenciaPorPlaca($lista1, $lista2)
    {
        $diferencias = [];

        foreach ($lista1 as $elemento1) {
            $placa1 = $elemento1['placa'];
            $inspector1 = $elemento1['inspector'];
            $servicio1 = $elemento1['servicio'];
            $taller1 = $elemento1['taller'];
            $encontrado = false;

            // Excluir el servicio 'Revisión anual GNV' para que no muestre como discrepancia 'Activación de chip (Anual)'

            foreach ($lista2 as $elemento2) {
                $placa2 = $elemento2['placa'];
                $inspector2 = $elemento2['inspector'];
                $servicio2 = $elemento2['servicio'];
                $taller2 = $elemento2['taller'];

                /*if ($elemento2['tipo_modelo'] == 'App\Models\CertificacionPendiente') {
                    if ($placa1 === $placa2 && $inspector1 === $inspector2 && $servicio1 == 'Revisión anual GNV') {

                        $encontrado = true;
                        break;
                    }
                } else {
                    if ($placa1 === $placa2 && $inspector1 === $inspector2 && $servicio1 === $servicio2) {
                        $encontrado = true;
                        break;
                    }
                }*/
                if ($placa1 === $placa2 && $inspector1 === $inspector2 && $taller1 === $taller2) {
                    if (
                        ($elemento2['tipo_modelo'] == 'App\Models\CertificacionPendiente' && $servicio1 == 'Revisión anual GNV') ||
                        ($servicio2 == 'Conversión a GNV + Chip' && $servicio1 == 'Conversión a GNV') ||
                        ($servicio2 == 'Pre-inicial GNV' && $servicio1 == 'Conversión a GNV') ||
                        ($servicio2 == 'Conversión a GNV OVERHUL' && $servicio1 == 'Conversión a GNV')
                    ) {
                        $encontrado = true;
                        break;
                    } else if ($servicio1 === $servicio2) {
                        $encontrado = true;
                        break;
                    }
                }
            }

            if (!$encontrado) {
                $diferencias[] = $elemento1;
            }
        }

        return $diferencias;
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
                "externo" => Null,
                "tipo_modelo" => $registro::class,
                "fecha" => $registro->fecha,
            ];
            $disc->push($data);
        }
        return $disc;
    }

    public function cargaBoletas()
    {
        // Obtener la colección de boletas según el taller y el rango de fechas
        $boletas = Boleta::Talleres($this->taller)
            ->Inspectores($this->ins)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();

        // Crear una nueva colección para almacenar los datos procesados
        $bol = collect();


        /*$bol = new Collection();
        $bol = Boleta::Talleres($this->taller)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();*/

        foreach ($boletas as $registro) {
            $data = [
                "id" => $registro->id,
                "placa" => null,
                "taller" => $registro->taller,
                "inspector" => $registro->certificador,
                "servicio" => null,
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $registro->monto,
                "pagado" => null,
                "estado" => null,
                "externo" =>  $this->generatePdfUrl($registro->id),
                "tipo_modelo" => $registro::class,
                "fecha" => $registro->fechaInicio . " hasta " . $registro->fechaFin,
            ];
            $bol->push($data);
        }
        $this->grupoboletas = $bol;
        //dd($this->grupoboletas);
        return $bol;
    }

    
}

/*public function encontrarDiferenciaPorPlaca($lista1, $lista2)
    {
        $diferencias = [];

        foreach ($lista1 as $elemento1) {
            $placa1 = $elemento1['placa'];
            $encontrado = false;

            foreach ($lista2 as $elemento2) {
                $placa2 = $elemento2['placa'];

                if ($placa1 === $placa2) {
                    $encontrado = true;
                    break;
                }
            }

            if (!$encontrado) {
                $diferencias[] = $elemento1;
            }
        }

        return $diferencias;
    }
*/

/*public function encontrarDiferenciaPorPlaca($lista1, $lista2)
    {
        $diferencias = [];
        foreach ($lista1 as $elemento1) {
            $placa1 = $elemento1['placa'];
            $inspector1 = $elemento1['inspector'];
            //$servicio1 = $elemento1['servicio'];  //manejar esto , por el momento es para activacion pero tendria que estar para los demas servicios
            $encontrado = false;

            foreach ($lista2 as $elemento2) {
                $placa2 = $elemento2['placa'];
                $inspector2 = $elemento2['inspector'];
                //$servicio2 = $elemento2['servicio'];

                // Verificar si la placa, el inspector y el servicio son iguales
                if ($placa1 === $placa2 && $inspector1 === $inspector2) { // && $servicio1 === $servicio2
                    $encontrado = true;
                    break;
                }
            }

            if (!$encontrado) {
                $diferencias[] = $elemento1;
            }
        }

        return $diferencias;
    }
*/

/*public function generatePdfUrl($boletaId)
    {
        return route('generaPdfBoleta', ['id' => $boletaId]);
    }
*/