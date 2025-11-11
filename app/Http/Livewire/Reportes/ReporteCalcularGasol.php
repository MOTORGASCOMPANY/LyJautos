<?php

namespace App\Http\Livewire\Reportes;

use App\Exports\ReporteTallerRsmnExport;
use Livewire\Component;
use App\Models\ServiciosImportados;
use App\Models\Taller;
use App\Exports\ReporteCalcularExport2;
use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\Contado;
use App\Models\Desmontes;
use App\Models\PrecioInspector;
use App\Models\TallerInspector;
use App\Models\User;
use App\Services\DataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ReporteCalcularGasol extends Component
{

    //VARIABLES PARA REPORTE 1
    public $fechaInicio, $fechaFin, $resultados, $talleres, $inspectores, $certis;
    public $ins = [], $taller = [];
    public $grupoinspectores;
    public $tabla, $diferencias, $importados, $aux, $precios = [];
    public $tabla2;

    //VARIABLES PARA REPORTE 2
    public $servicio;
    public $mtg, $discrepancias, $gasol, $asistir;
    public $mtg2, $mtg3;
    public $semanales, $diarios;

    //VARIABLES PARA REPORTE 3
    public $tablaFinalContados;

    protected $dataService;

    protected $listeners = ['exportarExcelExterno', 'exportarExcelRsmn'];

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
        $this->talleres = Taller::orderBy('nombre')->get();
    }
    public function render()
    {
        return view('livewire.reportes.reporte-calcular-gasol');
    }

    //JUNTAR REPORTES
    public function reportes()
    {
        $this->validate();
        $this->procesar();
        $this->procesar2();
        $this->procesarContados();
    }


    //FUNCIONES PARA REPORTE 1
    /*public function procesar()
    {
        //$this->validate();
        //Carga datos de certificacion
        $this->tabla = $this->generaData();
        //Carga datos de Servicios Importados
        $this->importados = $this->cargaServiciosGasolution();
        //Trim para eliminar espacios por inspector y taller
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
        //Diferencias entre importados y tabla
        $this->diferencias = $this->encontrarDiferenciaPorPlaca($this->importados, $this->tabla, true);
        // Combinamos tabla con diferencias
        $this->tabla2 = $this->tabla->merge($this->diferencias);
        // Obtenemos los inspectores que son externos
        $inspectoresExternos = User::role(['inspector'])->where('externo', 1)->pluck('name')->toArray();
        // Lista de inspectores que realizan servicios de taller y externos
        $inspectoresAdicionales = [
            'Cristhian David Saenz Nuñez',
            'Luis Alberto Esteban Torres',
            'Elvis Alexander Matto Perez',
            'Jhonatan Michael Basilio Soncco'
        ];
        //Filtrar servicios especificos
        $serviciosFiltrados = ['Duplicado GNV', 'Activación de chip (Anual)', 'Conversión a GNV + Chip', 'Conversión a GNV', 'Revisión anual GNV', 'Desmonte de Cilindro'];
        //Falta definir los , 'Pre-inicial GNV' => 'Conversión a GNV'  como serviciosFiltrados
        // Filtrar únicamente los registros de inspectores adicionales con los tipos de modelo y externos especificados
        $registrosExternos = $this->tabla2->filter(function ($item) use ($inspectoresExternos, $serviciosFiltrados) {
            return in_array($item['inspector'], $inspectoresExternos) &&
            in_array($item['servicio'], $serviciosFiltrados);
        });
         // Filtrar registros específicos de inspectores adicionales con condiciones de tipo_modelo y externo
        $registrosAdicionalesFiltrados = $this->tabla2->filter(function ($item) use ($inspectoresAdicionales) {
            return in_array($item['inspector'], $inspectoresAdicionales) &&
                ($item['tipo_modelo'] == 'App\Models\Certificacion' || $item['tipo_modelo'] == 'App\Models\CertificacionPendiente') &&
                $item['externo'] == 1;
        });
        // Combinar ambas colecciones
        $this->tabla2 = $registrosExternos->merge($registrosAdicionalesFiltrados);
        // Agrupar y ordenar los resultados por inspector
        $this->aux = $this->tabla2->groupBy('inspector')->sortBy(function ($item, $key) {
            return $key;
        });

        $this->sumaPrecios();
    }*/
    public function procesar()
    {
        $this->tabla2 = $this->dataService->procesar($this->ins, $this->taller, $this->servicio, $this->fechaInicio, $this->fechaFin);
        // Filtros
        $datosFiltrados = $this->aplicarFiltros($this->tabla2);
        //dd($datosFiltrados);
        // Agrupar y ordenar los resultados por inspector
        $this->aux = $datosFiltrados->groupBy('inspector')->sortBy(fn($item, $key) => $key);
        // Calcular sumas de precios con los datos filtrados
        $this->sumaPrecios($this->aux);
    }

    public function aplicarFiltros($datos)
    {
        // Obtenemos los inspectores que son externos
        $inspectoresExternos = User::role(['inspector'])->where('externo', 1)->pluck('name')->toArray();
        // Lista de inspectores que realizan servicios de taller y externos
        $inspectoresAdicionales = [
            'Cristhian David Saenz Nuñez',
            'Luis Alberto Esteban Torres',
            'Elvis Alexander Matto Perez',
            'Jhonatan Michael Basilio Soncco',
            'Cristhian Smith Huanay Condor',
            'Javier Alfredo Chevez Parcano',
            'Raul Llata Pacheco',
        ];
        // Servicios especificos
        $serviciosFiltrados = ['Duplicado GNV', 'Activación de chip (Anual)', 'Conversión a GNV + Chip', 'Conversión a GNV', 'Revisión anual GNV', 'Desmonte de Cilindro', 'Chip por deterioro', 'Pre-inicial GNV', 'Conversión a GNV OVERHUL'];
        // Modelos permitidos que requieren `externo == 1`
        $modelosExternoRequerido  = ['App\Models\Certificacion', 'App\Models\CertificacionPendiente', 'App\Models\Desmontes', 'App\Models\CertificacionTemporal']; // agregar el modelo temporal
        // Modelo que no requiere `externo == 1`
        $modeloSinFiltroExterno = 'App\Models\ServiciosImportados';
        // Filtrar únicamente los registros de inspectores externps con los servicios especificos
        $registrosExternos = $datos->filter(
            fn($item) =>
                /*in_array($item['inspector'], $inspectoresExternos) &&
                in_array($item['servicio'], $serviciosFiltrados)*/
                in_array($item['inspector'], $inspectoresExternos) &&
                in_array($item['servicio'], $serviciosFiltrados) &&
                (
                    in_array($item['tipo_modelo'], $modelosExternoRequerido) && $item['externo'] == 1 ||
                    $item['tipo_modelo'] === $modeloSinFiltroExterno
                )
        );
        // Filtrar registros específicos de inspectores adicionales con condiciones de tipo_modelo y externo
        $registrosAdicionalesFiltrados = $datos->filter(
            fn($item) =>
            /*in_array($item['inspector'], $inspectoresAdicionales) &&
                ($item['tipo_modelo'] == 'App\Models\Certificacion' || $item['tipo_modelo'] == 'App\Models\CertificacionPendiente') &&
                $item['externo'] == 1*/
                in_array($item['inspector'], $inspectoresAdicionales) &&
                in_array($item['tipo_modelo'], $modelosExternoRequerido) &&
                $item['externo'] == 1
        );

        // Eliminar registros de `registrosAdicionalesFiltrados` que ya están en `registrosExternos`
        $registrosAdicionalesFiltrados = $registrosAdicionalesFiltrados->reject(
            fn($item) => $registrosExternos->contains('id', $item['id'])
        );

        // Combinar ambas colecciones
        //$this->datos = $registrosExternos->merge($registrosAdicionalesFiltrados);
        return $registrosExternos->merge($registrosAdicionalesFiltrados)->unique('id');
    }
    public function cuentaServicios($data)
    {
        $cantidades = [];
        $mapeoServicios = [
            'Activación de chip (Anual)' => 'Revisión anual GNV',
            'Conversión a GNV + Chip' => 'Conversión a GNV',
            'Pre-inicial GNV' => 'Conversión a GNV',
            'Conversión a GNV OVERHUL' => 'Conversión a GNV'
        ];
        // Transformamos los nombres de los servicios en base al mapeo
        $todo = collect($data)->map(function ($item) use ($mapeoServicios) {
            // Si el servicio tiene un nombre que debe mapearse, lo reemplazamos
            $item['servicio'] = $mapeoServicios[$item['servicio']] ?? $item['servicio'];
            return $item;
        });

        // Agrupamos y contamos los servicios
        $agrupados = $todo->groupBy('servicio')->sortBy('servicio');
        foreach ($agrupados as $servicio => $items) {
            $cantidades[$servicio] = $items->count();
        }
        return $cantidades;
    }
    public function sumaPrecios($datosFiltrados)
    {
        foreach ($datosFiltrados as $inspector => $items) {
            $precioTotal = $items->sum('precio'); // Sumar directamente el campo 'precio'
            $this->precios[$inspector] = $precioTotal; // Guardar el total por inspector
        }
    }    


    //FUNCIONES PARA REPORTE 2
    /*public function procesar2()
    {
        //$this->validate();
        $this->mtg = $this->generaData();
        $this->gasol = $this->cargaServiciosGasolution();
        $this->mtg = $this->mtg->map(function ($item) {
            $item['placa'] = trim($item['placa']);
            $item['inspector'] = trim($item['inspector']);
            $item['taller'] = trim($item['taller']);
            return $item;
        });
        $this->gasol = $this->gasol->map(function ($item) {
            $item['placa'] = trim($item['placa']);
            $item['inspector'] = trim($item['inspector']);
            $item['taller'] = trim($item['taller']);
            return $item;
        });
        $this->discrepancias = $this->encontrarDiferenciaPorPlaca($this->gasol, $this->mtg, false);
        //Merge para combinar mtg y discrepancias -  strtolower para ignorar Mayusculas y Minusculas 
        $this->mtg2 = $this->mtg->merge($this->discrepancias, function ($item1, $item2) {
            $inspector1 = strtolower($item1['inspector']);
            $inspector2 = strtolower($item2['inspector']);
            $taller1 = strtolower($item1['taller']);
            $taller2 = strtolower($item2['taller']);
            $comparison = strcasecmp($inspector1 . $taller1, $inspector2 . $taller2);
            return $comparison;
        });
        // Filtrar excluir externos a gascar
        $this->mtg3 = $this->filtrarExternos($this->mtg2);
        // Agrupar por taller y filtrar por inspectores designados
        $this->asistir = $this->agruparTalleresConsolidar($this->mtg3);
        // Filtrar los talleres según los nuevos campos es_diario y es_semanal
        $this->semanales = $this->filtrarPorFrecuencia($this->asistir, 'es_semanal');
        $this->diarios = $this->filtrarPorFrecuencia($this->asistir, 'es_diario');
    }*/
    public function procesar2()
    {
        // Filtrar excluir externos a gascar
        $this->mtg2 = $this->dataService->procesar($this->ins, $this->taller, $this->servicio, $this->fechaInicio, $this->fechaFin);
        $this->mtg3 = $this->filtrarExternos($this->mtg2);
        // Agrupar por taller y filtrar por inspectores designados
        $this->asistir = $this->agruparTalleresConsolidar($this->mtg3);
        // Filtrar los talleres según los nuevos campos es_diario y es_semanal
        $this->semanales = $this->filtrarPorFrecuencia($this->asistir, 'es_semanal');
        $this->diarios = $this->filtrarPorFrecuencia($this->asistir, 'es_diario');
    }
    private function filtrarExternos($tabla)
    {
        $excluirTalleres = ['GASCAR CONVERSIONES S.A.C', 'REYCICAR S.A.C.', 'AUTOTRONICA JOEL CARS'];

        return $tabla->filter(function ($item) use ($excluirTalleres) {
            return !(
                in_array($item['tipo_modelo'], ['App\Models\Certificacion', 'App\Models\CertificacionPendiente']) &&
                in_array($item['taller'], $excluirTalleres) &&
                $item['externo'] == 1
            );
        });
    }
    private function agruparTalleresConsolidar($tabla)
    {
        // Definir mapa de consolidaciones
        $consolidaciones = [
            'AUTOTRONICA JOEL CARS' => 'AUTOTRONICA JOEL CARS E.I.R.L. - II',
            //'UNIGAS CONVERSIONES S.A.C.' => 'UNIGAS HOME S.A.C.',
            'AUTOGAS GREEN CAR E.I.R.L. - II' => 'WILTON MOTORS E.I.R.L -II'
        ];
        return $tabla->groupBy('taller')->map(function ($items) {
            $taller = $items->first()['taller'];

            $inspectoresDesignados = $this->obtenerInspectoresDesignados($taller);

            $itemsFiltrados = $inspectoresDesignados
                ? $items->filter(fn($item) => in_array($item['inspector'], $inspectoresDesignados))
                : $items;

            return [
                'taller' => $taller,
                'encargado' => $itemsFiltrados->first()['representante'] ?? null,
                'total' => $itemsFiltrados->sum('precio'),
            ];
        })->filter(fn($data) => $data['total'] > 0)
        ->groupBy(function ($item) use ($consolidaciones) {
            // Verificar si el taller debe consolidarse en otro nombre
            return $consolidaciones[$item['taller']] ?? $item['taller'];
        })
        ->map(fn($groupedItems, $taller) => [
            'taller' => $taller,
            'encargado' => $groupedItems->first()['encargado'],
            'total' => $groupedItems->sum('total'),
        ])->filter(fn($data) => $data['total'] > 0)
        ->sortBy('taller');
    }
    private function obtenerInspectoresDesignados($taller)
    {
        $inspectoresIds = TallerInspector::where('taller_id', Taller::where('nombre', $taller)->value('id'))
            ->pluck('inspector_id')
            ->toArray();

        return User::whereIn('id', $inspectoresIds)->pluck('name')->toArray();
    }
    private function filtrarPorFrecuencia($tabla, $frecuencia)
    {
        return $tabla->filter(function ($item) use ($frecuencia) {
            return Taller::where('nombre', $item['taller'])->value($frecuencia) == 1;
        });
    }

    //FUNCIONES PARA REPORTE 3
    public function procesarContados()
    {
        $tablaConta = $this->cargarContados();
        //dd($tablaConta);
        $this->tablaFinalContados = $this->filtrarContados($tablaConta);
        //dd($this->tablaFinalContados);
    }
    public function filtrarContados($tabla)
    {
        // Agrupamos por inspector y sumamos el precio donde pagado es 0
        $agrupadoPorInspector = $tabla->groupBy('inspector')->map(function ($data) {
            //$totalPrecio = $data->where('pagado', 0)->sum('precio');
            $totalPrecio = $data->whereIn('pagado', [0, 2])->sum('precio');
            return [
                'inspector' => $data->first()['inspector'], // Inspector del data
                'total_precio' => $totalPrecio, // Suma de los precios con pagado = 0
            ];
        })->filter(function ($fila) {
            return $fila['total_precio'] > 0; // Eliminamos filas con total_precio = 0
        });

        return $agrupadoPorInspector->sortBy('inspector')->values();
    }
    public function cargarContados()
    {
        return Contado::RangoFecha($this->fechaInicio, $this->fechaFin)
            ->get()
            ->map(function ($contad) {
                return [
                    "id" => $contad->id,
                    "inspector" => $contad->salida->usuarioAsignado->name,
                    "precio" => $contad->precio,
                    "pagado" => $contad->pagado,
                    "estado" => $contad->estado,
                    "tipo_modelo" => $contad::class,
                    "fecha" => $contad->created_at,
                ];
            });
    }


    public function exportarExcelExterno($datae)
    {
        return Excel::download(new ReporteCalcularExport2($datae), 'reporte_Externos.xlsx');
    }
    public function exportarExcelRsmn($data)
    {
        return Excel::download(new ReporteTallerRsmnExport($data), 'reporte_TallerResumen.xlsx');
    }
}