<?php

namespace App\Http\Livewire\Reportes;

use App\Exports\ReporteSemanalExport;
use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\Desmontes;
use App\Models\ServiciosImportados;
use App\Models\Taller;
use App\Models\TipoServicio;
use App\Models\User;
use App\Services\DataService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

use Livewire\Component;

class ReporteCalcular extends Component
{
    //public $inspectorTotals;
    //public $reporteTaller, $vertaller, $totalPrecio;
    //public $tipoServicios;

    public $fechaInicio, $fechaFin, $resultados, $talleres, $inspectores;
    public $ins = [], $taller = [] , $servicio;
    public $grupoTipo;
    public $tabla, $diferencias, $importados;
    public $tabla2;

    protected $dataService;

    protected $listeners = ['exportarExcel'];


    protected $rules = [
        "fechaInicio" => 'required|date',
        "fechaFin" => 'required|date',
    ];

    public function __construct()
    {
        parent::__construct(); // Asegúrate de llamar al constructor de la clase padre
        $this->dataService = app(DataService::class); // Inyección de servicio
    }

    public function mount()
    {
        $this->inspectores = User::role(['inspector', 'supervisor'])->orderBy('name')->get();
        $this->talleres = Taller::all()->sortBy('nombre');
    }

    public function render()
    {
        return view('livewire.reportes.reporte-calcular');
    }

    /*public function procesar()
    {
        $this->validate();
        $this->tabla = $this->generaData();
        //$this->grupoTipo = $this->tabla->groupBy('servicio');
        $this->importados = $this->cargaServiciosGasolution();
        //TRIM PARA ELIMINAR ESPACIOS 
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
        //$this->diferencias = $this->encontrarDiferenciaPorPlaca($this->tabla, $this->importados);
        $this->diferencias = $this->encontrarDiferenciaPorPlaca($this->importados, $this->tabla);
        //$serviciosPermitidos = ['Conversión a GLP', 'Revisión anual GLP', 'Modificación', 'Duplicado GNV', 'Conversión a GNV + Chip', 'Chip por deterioro', 'Pre-inicial GNV', 'Pre-inicial GLP'];
        //$servisrestantes = $this->diferencias->filter(function ($item) use ($serviciosPermitidos) {
        //   return in_array($item['servicio'], $serviciosPermitidos);
        //});

        //$this->tabla2 = $this->importados->merge($servisrestantes);
        $this->tabla2 = $this->tabla->merge($this->diferencias, function ($item1, $item2) {
            $inspector1 = strtolower($item1['inspector']);
            $inspector2 = strtolower($item2['inspector']);
            $taller1 = strtolower($item1['taller']);
            $taller2 = strtolower($item2['taller']);
            $comparison = strcasecmp($inspector1 . $taller1, $inspector2 . $taller2);
            return $comparison;
        });

        $this->grupoTipo = $this->tabla2->groupBy('servicio')->map(function ($servicio) {
            return $servicio->groupBy(function ($certificacion) {
                return Carbon::parse($certificacion['fecha'])->format('l');
            });
        });
        $this->grupoTipo = $this->completarEstructuraDatos($this->grupoTipo);
    }*/

    public function procesar()
    {
        $this->validate();
        $this->tabla2 = $this->dataService->procesar($this->ins, $this->taller, $this->servicio, $this->fechaInicio, $this->fechaFin);
        $this->grupoTipo = $this->tabla2->groupBy('servicio')->map(function ($servicio) {
            return $servicio->groupBy(function ($certificacion) {
                return Carbon::parse($certificacion['fecha'])->format('l');
            });
        });
        $this->grupoTipo = $this->completarEstructuraDatos($this->grupoTipo);
    }

    public function exportarExcel($data)
    {
        //dd($data);
        return Excel::download(new ReporteSemanalExport($data), 'reporte_semanal.xlsx');
    }

    public function completarEstructuraDatos($grupoTipo)
    {
        $diasSemana = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($grupoTipo as $servicio => $certificacionesPorDia) {
            $filaServicio = [
                'servicio' => $servicio,
                'Lunes' => [],
                'Martes' => [],
                'Miércoles' => [],
                'Jueves' => [],
                'Viernes' => [],
                'Sábado' => [],
                'Domingo' => [],
                'Total' => 0,
            ];
            foreach ($diasSemana as $dia) {
                if (isset($certificacionesPorDia[$dia])) {
                    $filaServicio[$dia] = $certificacionesPorDia[$dia];
                    $filaServicio['Total'] += count($certificacionesPorDia[$dia]);
                }
            }
            $grupoTipo[$servicio] = $filaServicio;
        }
        return $grupoTipo;
    }

    /*public function generaData()
    {
        $tabla = new Collection();
        //TODO CERTIFICACIONES:
        $certificaciones = Certificacion::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            // Excluir al inspector con id = 201 
            ->whereHas('Inspector', function ($query) {
                $query->whereNotIn('id', [37, 117, 201]);
            })
            ->rangoFecha($this->fechaInicio, $this->fechaFin)
            //->where('pagado', 0)
            ->whereIn('pagado', [0, 2])
            ->whereIn('estado', [3, 1])
            ->get();

        //TODO CER-PENDIENTES ESO MANO
        $cerPendiente = CertificacionPendiente::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->whereHas('Inspector', function ($query) {
                $query->whereNotIn('id', [37, 117, 201]);
            })
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
            ->rangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();

        //unificando certificaciones     
        foreach ($certificaciones as $certi) {
            //modelo preliminar
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
                "servicio" => 'Activación de chip (Anual)', // es ese tipo de servicio por defecto
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $cert_pend->precio,
                "pagado" => $cert_pend->pagado,
                "estado" => $cert_pend->estado,
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
    }
    public function cargaServiciosGasolution()
    {
        $disc = new Collection();
        $dis = ServiciosImportados::Talleres($this->taller)
            ->Inspectores($this->ins)
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
                "tipo_modelo" => $registro::class,
                "fecha" => $registro->fecha,
            ];
            $disc->push($data);
        }
        return $disc;
    }
    public function encontrarDiferenciaPorPlaca($lista1, $lista2)
    {
        //$diferencias = [];
        $diferencias = collect();

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
    }*/
}
