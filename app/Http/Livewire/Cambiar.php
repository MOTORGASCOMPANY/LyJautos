<?php

namespace App\Http\Livewire;

use App\Models\Boleta;
use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\Desmontes;
use App\Models\ServiciosImportados;
use App\Models\Taller;
use App\Models\User;
use App\Services\DataService;
use Illuminate\Support\Collection;
use Livewire\Component;

class Cambiar extends Component
{
    public $fechaInicio, $fechaFin, $talleres, $inspectores;
    public $ins = [], $taller = [], $servicio;
    public $tabla, $diferencias, $importados, $aux, $precios = [];
    public $tabla2;
    public $cantidades = [];
    public $porcentajePagados;

    // variables para Dataservices
    public $datos;
    protected $dataService;

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
        return view('livewire.cambiar');
    }

    public function procesar()
    {
        $this->validate();
        $this->datos = $this->dataService->procesar($this->ins, $this->taller, $this->servicio, $this->fechaInicio, $this->fechaFin);
        // Filtros
        $datosFiltrados = $this->aplicarFiltros($this->datos);
        // Procesar cantidades por servicio
        $this->cantidades = $this->cuentaServicios($datosFiltrados);
        // Agrupar y ordenar los resultados por inspector
        $this->aux = $datosFiltrados->groupBy('inspector')->sortBy(fn($item, $key) => $key);
        // Calcular sumas de precios con los datos filtrados
        $this->sumaPrecios($this->aux);
        // Calcular porcentaje de servicios pagados por inspector
        $this->porcentajePagados = $this->calcularPorcentajePagados($this->aux);
        // Agregar IDs de boletas a los inspectores agrupados
        $this->aux = $this->buscarBoletasPorInspector($this->aux);
    }

    // en la funcion cambiamos $this->tabla2 por $this->datos ya que ahora se empleara el DataServices
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
        $modelosExternoRequerido  = ['App\Models\Certificacion', 'App\Models\CertificacionPendiente', 'App\Models\Desmontes'];
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
    //cuenta los servicios segun el tipo servicio
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
        // Agrupamos por servicio e inspector y contamos
        foreach ($todo as $item) {
            $inspector = $item['inspector'];
            $servicio = $item['servicio'];
            if (!isset($cantidades[$servicio])) {
                $cantidades[$servicio] = [];
            }
            if (!isset($cantidades[$servicio][$inspector])) {
                $cantidades[$servicio][$inspector] = 0;
            }
            $cantidades[$servicio][$inspector]++;
        }

        return $cantidades;
    }
    //suma el total
    /*public function sumaPrecios()
    {
        //$precios = [];
        $servicios = ['Revisión anual GNV', 'Conversión a GNV', 'Desmonte de Cilindro', 'Duplicado GNV', 'Activación de chip (Anual)', 'Conversión a GNV + Chip'];
        $todo = $this->aux;
        //dd($todo);
        foreach ($todo as $servicio => $item) {
            $precio = 0;
            foreach ($item as $target) {
                // Verificar si la clave 'servicio' existe en el item
                if (isset($target['servicio']) && in_array($target['servicio'], $servicios)) {
                    $precio += $target['precio'];
                }                
            }
            $this->precios[$servicio] = $precio;
        }
        // return $precios;
    }*/
    public function sumaPrecios($datosFiltrados)
    {
        foreach ($datosFiltrados as $inspector => $items) {
            $precioTotal = $items->sum('precio'); // Sumar directamente el campo 'precio'
            $this->precios[$inspector] = $precioTotal; // Guardar el total por inspector
        }
    }


    public function toggleAuditoria($boletaId)
    {
        $boleta = Boleta::find($boletaId);
        if ($boleta) {
            $boleta->auditoria = !$boleta->auditoria;
            $boleta->save();
            $this->emit("minAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "Auditoría actualizado exitosamente.", "icono" => "success"]);
        } else {
            $this->emit("minAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "No se encontró la boleta.", "icono" => "warning"]);
        }
    }

    protected function buscarBoletasPorInspector($inspectores)
    {
        foreach ($inspectores as $inspector => $items) {
            // Buscar el ID del inspector en el modelo User
            $inspectorId = User::where('name', $inspector)->value('id');

            if (!$inspectorId) {
                // Si no se encuentra el inspector, continuar con el siguiente
                $inspectores[$inspector]['boletas'] = 'No hay boletas';
                continue;
            }

            // Buscar boletas que coincidan con el ID del inspector y el rango de fechas
            $boletas = Boleta::where('certificador', $inspectorId)
                ->whereNull('taller')
                ->whereBetween('fechaInicio', [$this->fechaInicio, $this->fechaFin])
                //->pluck('id');
                ->get(['id', 'auditoria']);

            // Asignar boletas al grupo una sola vez
            /*$inspectores[$inspector]['boletas'] = $boletas->isNotEmpty()
            ? $boletas->implode(', ')
            : null;*/
            // Preparar el resultado para incluir ambos campos
            $inspectores[$inspector]['boletas'] = $boletas->isNotEmpty()
                ? $boletas->map(function ($boleta) {
                    return [
                        'id' => $boleta->id,
                        'auditoria' => $boleta->auditoria,
                    ];
                })->toArray()
                : null;
        }

        return $inspectores;
    }

    public function calcularPorcentajePagados($data)
    {
        $porcentajePagados = [];

        foreach ($data as $inspector => $items) {
            // Inicializamos el contador para cada inspector
            $porcentajePagados[$inspector] = [
                'total' => 0,
                'pagados' => 0
            ];

            /*foreach ($items as $item) {
                // Contamos el total de servicios y los servicios pagados
                $porcentajePagados[$inspector]['total']++;
                if ($item['pagado'] == 1) {
                    $porcentajePagados[$inspector]['pagados']++;
                }
            }*/
            foreach ($items as $item) {
                // Contamos el total de servicios
                $porcentajePagados[$inspector]['total']++;

                // Verificamos si la clave 'pagado' existe y si el valor es 2
                if (isset($item['pagado']) && $item['pagado'] == 2) {
                    $porcentajePagados[$inspector]['pagados']++;
                }
            }
        }

        // Calcular el porcentaje de servicios pagados para cada inspector
        foreach ($porcentajePagados as $inspector => $data) {
            if ($data['total'] > 0) {
                $porcentajePagados[$inspector]['porcentaje'] = ($data['pagados'] / $data['total']) * 100;
            } else {
                $porcentajePagados[$inspector]['porcentaje'] = 0;
            }
        }

        return $porcentajePagados;
    }

    /*public function procesar()
    {
        $this->validate();
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
        $this->diferencias = $this->encontrarDiferenciaPorPlaca($this->importados, $this->tabla);
        // Combinamos tabla con diferencias
        $this->tabla2 = $this->tabla->merge($this->diferencias);
        // Filtros
        $this->aplicarFiltros();
        // Agrupar y ordenar los resultados por inspector
        $this->aux = $this->tabla2->groupBy('inspector')->sortBy(fn($item, $key) => $key);        
        //dd($this->aux);
        // Procesar cantidades por servicio
        $this->cantidades = $this->cuentaServicios($this->tabla2);
        // Calcular sumas de precios
        $this->sumaPrecios();
        // Calcular porcentaje de servicios pagados por inspector
        $this->porcentajePagados = $this->calcularPorcentajePagados($this->aux);
        //dd($this->porcentajePagados);
        // Agregar IDs de boletas a los inspectores agrupados
        $this->aux = $this->buscarBoletasPorInspector($this->aux);
        
    }*/

    /*public function generaData()
    {
        $tabla = new Collection();
        // TODO CERTIFICACIONES
        $certificaciones = Certificacion::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->rangoFecha($this->fechaInicio, $this->fechaFin)
            ->whereIn('pagado', [0, 2])
            ->whereIn('estado', [3, 1])
            ->get();
        // TODO CERTIFICACIONES PENDIENTES
        $cerPendiente = CertificacionPendiente::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->rangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();
        //TODO DESMONTES PARA OFICINA:
        $desmontes = Desmontes::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->rangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();
        // UNIFICANDO CERTIFICACIONES
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
                "externo" => Null, //aun no se identifican los externos
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

            foreach ($lista2 as $elemento2) {
                $placa2 = $elemento2['placa'];
                $inspector2 = $elemento2['inspector'];
                $servicio2 = $elemento2['servicio'];
                $taller2 = $elemento2['taller'];

                // Verificar si las placas e inspectores son iguales
                if ($placa1 === $placa2 && $inspector1 === $inspector2 && $taller1 === $taller2) {
                    // Si estamos en el caso de procesar2, aplicar la lógica de exclusión especial
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

            // Si no fue encontrado, agregar a la lista de diferencias
            if (!$encontrado) {
                $diferencias[] = $elemento1;
            }
        }

        return $diferencias;
    }*/
}
