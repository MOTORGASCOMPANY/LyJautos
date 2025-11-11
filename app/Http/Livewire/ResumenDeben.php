<?php

namespace App\Http\Livewire;

use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\Contado;
use App\Models\Desmontes;
use App\Models\ServiciosImportados;
use App\Models\Taller;
use App\Models\TallerInspector;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class ResumenDeben extends Component
{
    public $semanaInicial = '2024-12-09 00:00:00'; //Semana de inicio
    public $tablaFinalInspectores; // variables para inspectores
    public $tablaFinalTalleres; // variables para talleres
    public $tablaFinalContados;

    public $totalTalleres = 0;
    public $totalExternos = 0;
    public $totalContados = 0;
    public $cierre;

    protected $listeners = ['exportarPdf'];

    public function render()
    {
        return view('livewire.resumen-deben');
    }

    public function exportarPdf($data)
    {
        //dd($data);
        
        $data = "
        <style>
            body {
                font-size: 13px; /* Tamaño de fuente más pequeño */
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 6px;
                text-align: left;
            }
            h4{
                text-align: center; /* Centrar el texto en h4 */
                font-size: 16px; /* Ajustar el tamaño de la fuente de h4, si es necesario */
            }
        </style>
        " . $data;

        // Generar el PDF con el HTML recibido
        $pdf = Pdf::loadHTML($data)->setPaper('a4', 'portrait');

        // Descargar el archivo PDF
        return response()->streamDownload(
            fn() => print($pdf->output()),
            'reporte_Deudas.pdf'
        );
    }

    public function procesar()
    {
        $this->procesarInspectores();
        $this->procesarTalleres();
        $this->procesarContados();
        $this->calcularTotales();
    }

    public function calcularTotales()
    {
        $this->totalTalleres = collect($this->tablaFinalTalleres)->sum('total_taller');
        $this->totalExternos = collect($this->tablaFinalInspectores)->sum('total_inspector');
        $this->totalContados = collect($this->tablaFinalContados)->sum('total_precio');
        $this->cierre = $this->totalTalleres + $this->totalExternos + $this->totalContados;
        //dd($this->cierre);
    }

    /*public function procesarContados()
    {
        $tablaFinalContados = collect();
        // Obtener los rangos de semana desde semanaInicial hasta la fecha actual
        $rangosSemanas = $this->obtenerRangosSemanas($this->semanaInicial, Carbon::now()->format('Y-m-d'));
        //dd('Rangos de semanas:', $rangosSemanas);
        foreach ($rangosSemanas as $semana) {
            $tablaConta = $this->cargarContados($semana['inicio'], $semana['fin']);
            //dd('Datos cargados para la semana:', $semana, $tablaConta->toArray());
            foreach ($tablaConta as $item) {
                $tablaFinalContados->push(array_merge($item, ['semana' => $semana['rango']]));
            }
        }
        $this->tablaFinalContados = $this->filtrarContados($tablaFinalContados);
        //dd('Datos finales:', $tablaFinalContados->toArray());
    }
    public function filtrarContados($tabla)
    {
        // Agrupamos por inspector
        $agrupadoPorInspector = $tabla->groupBy('inspector');
        // Generamos el resultado procesado
        $resultado = $agrupadoPorInspector->map(function ($registros, $inspector) {
            // Inicializamos los totales por semana
            $totalesPorSemana = [];
            $sumaTotal = 0;

            // Iteramos sobre los registros del inspector
            foreach ($registros as $registro) {
                $semana = $registro['semana'];

                // Solo sumamos si `pagado` es 0
                if ($registro['pagado'] == 0) {
                    // Sumar el precio a la suma total
                    $sumaTotal += $registro['precio'];
                    // Sumar el precio al total de la semana correspondiente
                    if (!isset($totalesPorSemana[$semana])) {
                        $totalesPorSemana[$semana] = 0;
                    }
                    $totalesPorSemana[$semana] += $registro['precio'];
                }
            }

            return [
                'inspector' => $inspector,
                'totales' => collect($totalesPorSemana),
                'total_inspector' => $sumaTotal, 
            ];
        });

        return $resultado->values();
    }
    public function cargarContados($semanaInicio, $semanaFin)
    {
        $cont = Contado::RangoFecha($semanaInicio, $semanaFin)->get();
        return $cont->map(function ($contad) {
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
    }*/

    public function procesarContados()
    {
        $tablaConta = $this->cargarContados();
        //dd($tablaConta);
        $this->tablaFinalContados = $this->filtrarContados($tablaConta);
    }
    public function filtrarContados($tabla)
    {
        // Agrupamos por inspector y sumamos el precio donde pagado es 0
        $agrupadoPorInspector = $tabla->groupBy('inspector')->map(function ($data) {
            $totalPrecio = $data->where('pagado', 0)->sum('precio');
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
        $diaFinal = now()->format('Y-m-d') . ' 23:59:59';
        //dd($diaFinal);
        $cont = Contado::RangoFecha($this->semanaInicial, $diaFinal)->get();
        return $cont->map(function ($contad) {
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

    // Funciones para talleres (procesarTalleres, filtrarExternos, filtrarPorFrecuencia, procesarDatosIniciales y obtenerInspectoresDesignados).
    /*public function procesarTalleres()
    {
        $tablaFinalTalleres = collect();

        // Obtener los rangos de semana desde semanaInicial hasta la fecha actual
        $rangosSemanas = $this->obtenerRangosSemanas($this->semanaInicial, Carbon::now()->format('Y-m-d'));

        foreach ($rangosSemanas as $semana) {
            $tablaMtg = $this->generaData($semana['inicio'], $semana['fin']);
            $tablaGasol = $this->cargaServiciosGasolution($semana['inicio'], $semana['fin']);
            
            $diferencias = $this->encontrarDiferenciaPorPlaca($tablaGasol, $tablaMtg);
            $tablaSemana = $tablaMtg->merge($diferencias);

            foreach ($tablaSemana as $item) {
                $tablaFinalTalleres->push(array_merge($item, ['semana' => $semana['rango']]));  // Agregar semana al item
            }
        }

        // Filtrar los servicios externos antes de procesar la tabla
        $tablaFiltrada = $this->filtrarExternos($tablaFinalTalleres);

        $tablaProcesada = $this->procesarDatosIniciales($tablaFiltrada, $rangosSemanas);  // Pasar los rangos de semana a procesarDatosIniciales
        $this->tablaFinalTalleres = $this->filtrarPorFrecuencia($tablaProcesada, 'es_semanal');
        //dd($this->tablaFinalTalleres);
    }*/
    public function procesarTalleres()
    {
        $tablaFinalTalleres = $this->procesarRangosSemanas(function ($tablaSemana, $semana) {
            // Personalización específica para talleres: agregar 'semana' a cada elemento
            return $tablaSemana->map(fn($item) => array_merge($item, ['semana' => $semana['rango']]));
        });

        $tablaFiltrada = $this->filtrarExternos($tablaFinalTalleres);
        $tablaProcesada = $this->filtrarPorFrecuencia($tablaFiltrada, 'es_semanal');
        $this->tablaFinalTalleres = $this->procesarDatosIniciales($tablaProcesada, $this->obtenerRangosSemanas($this->semanaInicial, Carbon::now()->format('Y-m-d')));
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
    private function filtrarPorFrecuencia($tabla, $frecuencia)
    {
        $talleresFrecuencia = Taller::where($frecuencia, 1)->pluck('id', 'nombre');

        return $tabla->filter(function ($item) use ($talleresFrecuencia) {
            return $talleresFrecuencia->has($item['taller']);
        });
    }
    private function procesarDatosIniciales($tabla, $rangosSemanas)
    {
        $consolidaciones = [
            'AUTOTRONICA JOEL CARS' => 'AUTOTRONICA JOEL CARS E.I.R.L. - II',
            //'UNIGAS CONVERSIONES S.A.C.' => 'UNIGAS HOME S.A.C.',
            'AUTOGAS GREEN CAR E.I.R.L. - II' => 'WILTON MOTORS E.I.R.L -II'
        ];

        $procesadot = $tabla->groupBy(fn($item) => $consolidaciones[$item['taller']] ?? $item['taller'])
            ->map(function ($items, $tallerConsolidado) use ($rangosSemanas) {
                $inspectoresDesignados = $this->obtenerInspectoresDesignados($tallerConsolidado);
                $itemsFiltrados = $inspectoresDesignados
                    ? $items->filter(fn($item) => in_array($item['inspector'], $inspectoresDesignados) && $item['pagado'] == 0)
                    : $items->filter(fn($item) => $item['pagado'] == 0);

                $totalesPorSemana = [];
                foreach ($rangosSemanas as $semana) {
                    $itemsSemana = $itemsFiltrados->filter(fn($item) => Carbon::parse($item['fecha'])->between($semana['inicio'], $semana['fin']));
                    $totalesPorSemana[$semana['rango']] = $itemsSemana->sum('precio');
                }
                // Calcular el monto total por inspector
                $totalPorTaller = $itemsFiltrados->sum('precio');

                return [
                    'taller' => $tallerConsolidado,
                    'totales' => $totalesPorSemana,
                    'total_taller' => $totalPorTaller,
                ];
            });

        // Filtrar columnas con totales de 0 en todas las filas
        $totalesPorSemanas = $procesadot->pluck('totales')->reduce(function ($carry, $item) {
            foreach ($item as $semana => $total) {
                $carry[$semana] = ($carry[$semana] ?? 0) + $total;
            }
            return $carry;
        }, []);

        // Filtrar semanas donde el total es 0
        $semanasValidas = collect($totalesPorSemanas)->filter(fn($total) => $total > 0)->keys();
        //dd($totalesPorSemanas, $semanasValidas);

        return $procesadot->map(function ($data) use ($semanasValidas) {
            // Filtrar las semanas que no tienen datos
            $data['totales'] = collect($data['totales'])->only($semanasValidas);
            return $data;
        })
            ->filter(fn($data) => $data['totales']->sum() > 0)
            ->sortBy('taller');
    }
    private function obtenerInspectoresDesignados($taller)
    {
        $inspectoresIds = TallerInspector::where('taller_id', Taller::where('nombre', $taller)->value('id'))
            ->pluck('inspector_id')
            ->toArray();

        return User::whereIn('id', $inspectoresIds)->pluck('name')->toArray();
    }


    // Funciones para inspectores (procesarinspectores, procesarDatos y aplicarFiltros).
    /*public function procesarInspectores()
    {
        $tablaFinalInspectores = collect();

        // Obtener los rangos de semana desde semanaInicial hasta la fecha actual
        $rangosSemanas = $this->obtenerRangosSemanas($this->semanaInicial, Carbon::now()->format('Y-m-d'));

        foreach ($rangosSemanas as $semana) {
            $tablaMtg = $this->generaData($semana['inicio'], $semana['fin']);
            $tablaGasol = $this->cargaServiciosGasolution($semana['inicio'], $semana['fin']);
            $diferencias = $this->encontrarDiferenciaPorPlaca($tablaGasol, $tablaMtg);
            $tablaSemana = $tablaMtg->merge($diferencias);

            foreach ($tablaSemana as $item) {
                $tablaFinalInspectores->push(array_merge($item, ['semana' => $semana['rango']]));  // Agregar semana al item
            }
        }

        // Aplicar Filtros
        $tablaFiltrada = $this->aplicarFiltros($tablaFinalInspectores);
        //dd($tablaFiltrada);        
        $this->tablaFinalInspectores = $this->procesarDatos($tablaFiltrada, $rangosSemanas);
        //dd($this->tablaFinalInspectores);
    }*/
    public function procesarInspectores()
    {
        $tablaFinalInspectores = $this->procesarRangosSemanas(function ($tablaSemana, $semana) {
            // Personalización específica para inspectores: simplemente retorna los datos sin agregar campos
            return $tablaSemana;
        });
        $tablaFiltrada = $this->aplicarFiltros($tablaFinalInspectores);
        $this->tablaFinalInspectores = $this->procesarDatos($tablaFiltrada, $this->obtenerRangosSemanas($this->semanaInicial, Carbon::now()->format('Y-m-d')));
    }
    private function procesarDatos($tabla, $rangosSemanas)
    {

        //return $tabla
        $procesado = $tabla
            ->groupBy('inspector')
            ->map(function ($items, $inspector) use ($rangosSemanas) {
                // Filtrar items no pagados
                $itemsFiltrados = $items->filter(fn($item) => $item['pagado'] == 0);
                // Inicializar el array para los totales por semana
                $totalesPorSemana = [];

                foreach ($rangosSemanas as $semana) {
                    // Filtrar los items dentro de cada semana y calcular el total
                    $itemsSemana = $itemsFiltrados->filter(fn($item) =>
                    Carbon::parse($item['fecha'])->between($semana['inicio'], $semana['fin']));

                    $totalesPorSemana[$semana['rango']] = $itemsSemana->sum('precio');
                }
                // Calcular el monto total por inspector
                $totalPorInspector = $itemsFiltrados->sum('precio');

                return [
                    'inspector' => $inspector,
                    'totales' => $totalesPorSemana,
                    'total_inspector' => $totalPorInspector,
                ];
            });

        // Calcular totales por semana
        $totalesPorSemanas = $procesado->pluck('totales')->reduce(function ($carry, $item) {
            foreach ($item as $semana => $total) {
                $carry[$semana] = ($carry[$semana] ?? 0) + $total;
            }
            return $carry;
        }, []);

        // Filtrar semanas donde el total es 0
        $semanasValidas = collect($totalesPorSemanas)->filter(fn($total) => $total > 0)->keys();
        //dd($totalesPorSemanas, $semanasValidas);

        /*->filter(fn($data) => collect($data['totales'])->sum() > 0) // Excluir totales en cero
            ->sortBy('inspector');*/

        return $procesado->map(function ($data) use ($semanasValidas) {
            // Filtrar las semanas que no tienen datos
            $data['totales'] = collect($data['totales'])->only($semanasValidas);
            return $data;
        })
            ->filter(fn($data) => $data['totales']->sum() > 0)
            ->sortBy('inspector');
    }
    public function aplicarFiltros($tabla)
    {
        // Obtenemos los inspectores que son externos
        $inspectoresExternos = User::role(['inspector'])->where('externo', 1)->pluck('name')->toArray();
        // Lista de inspectores que realizan servicios de taller y externos
        $inspectoresAdicionales = [
            'Cristhian David Saenz Nuñez',
            'Luis Alberto Esteban Torres',
            'Elvis Alexander Matto Perez',
            'Jhonatan Michael Basilio Soncco',
            'Cristhian Smith Huanay Condor'
        ];
        // Servicios especificos
        $serviciosFiltrados = ['Duplicado GNV', 'Activación de chip (Anual)', 'Conversión a GNV + Chip', 'Conversión a GNV', 'Conversión a GNV OVERHUL', 'Revisión anual GNV', 'Desmonte de Cilindro'];
        // Filtrar únicamente los registros de inspectores externps con los servicios especificos
        $registrosExternos = $tabla->filter(
            fn($item) =>
            in_array($item['inspector'], $inspectoresExternos) &&
                in_array($item['servicio'], $serviciosFiltrados)
        );
        // Filtrar registros específicos de inspectores adicionales con condiciones de tipo_modelo y externo
        $registrosAdicionalesFiltrados = $tabla->filter(
            fn($item) =>
            in_array($item['inspector'], $inspectoresAdicionales) &&
                ($item['tipo_modelo'] == 'App\Models\Certificacion' || $item['tipo_modelo'] == 'App\Models\CertificacionPendiente') &&
                $item['externo'] == 1
        );
        // Combinar ambas colecciones
        //$tabla = $registrosExternos->merge($registrosAdicionalesFiltrados);
        return $registrosExternos->merge($registrosAdicionalesFiltrados);
    }


    // Funciones para ambos reportes (procesarRangosSemanas, obtenerRangosSemanas, generaData, cargaServiciosGasolution y encontrarDiferenciaPorPlaca).
    private function procesarRangosSemanas(callable $callback)
    {
        $resultadoFinal = collect();
        $rangosSemanas = $this->obtenerRangosSemanas($this->semanaInicial, Carbon::now()->format('Y-m-d'));

        foreach ($rangosSemanas as $semana) {
            //cargamos los datos de generaData y sus parametros $semana['inicio'], $semana['fin']
            $tablaMtg = $this->generaData($semana['inicio'], $semana['fin']);
            //cargamos los datos de cargaServiciosGasolution y sus parametros $semana['inicio'], $semana['fin']
            $tablaGasol = $this->cargaServiciosGasolution($semana['inicio'], $semana['fin']);
            // Eliminamos espacios en blanco por placa, inspector y taller
            $tablaMtg = $tablaMtg->map(function ($item) {
                $item['placa'] = trim($item['placa']);
                $item['inspector'] = trim($item['inspector']);
                $item['taller'] = trim($item['taller']);
                return $item;
            });

            $tablaGasol = $tablaGasol->map(function ($item) {
                $item['placa'] = trim($item['placa']);
                $item['inspector'] = trim($item['inspector']);
                $item['taller'] = trim($item['taller']);
                return $item;
            });

            // buscar diferencias entre tablaGasol y tablaMtg
            $diferencias = $this->encontrarDiferenciaPorPlaca($tablaGasol, $tablaMtg);
            // combinar tablaMtg con las diferencias
            $tablaSemana = $tablaMtg->merge($diferencias);

            // Llamamos al callback para personalizar el procesamiento
            $resultadoFinal = $resultadoFinal->merge($callback($tablaSemana, $semana));
        }

        return $resultadoFinal;
    }
    private function obtenerRangosSemanas($semanaInicial, $fechaFinal)
    {
        $inicio = Carbon::parse($semanaInicial);
        $fin = Carbon::parse($fechaFinal);

        $rangos = [];
        while ($inicio <= $fin) {
            $finSemana = $inicio->copy()->endOfWeek();  // Fin de la semana
            //$finSemana = $inicio->copy()->endOfWeek()->endOfDay(); // Fin de la semana (fin del día)
            // Excluir semanas incompletas
            if ($finSemana > $fin) {
                break;
            }
            /*/ Asegurarnos de no exceder la fecha final
            if ($finSemana > $fin) {
                $finSemana = $fin; // Ajustar al último día y hora disponibles
            }*/

            $rangos[] = [
                //'inicio' => $inicio->format('Y-m-d'),
                //'fin' => $finSemana->format('Y-m-d'),
                'inicio' => $inicio->toDateTimeString(), // Incluye fecha y hora
                'fin' => $finSemana->toDateTimeString(), // Incluye fecha y hora
                'rango' => $inicio->format('d') . " al " . $finSemana->format('d') . " " . $inicio->format('F'),
            ];
            $inicio->addWeek();  // Sumar una semana
            //$inicio->addWeek()->startOfDay(); // Sumar una semana y ajustar al inicio del día
        }

        return $rangos;
        //($rangos);
    }
    public function generaData($semanaInicio, $semanaFin)
    {
        $tabla = new Collection();
        $certificaciones = Certificacion::RangoFecha($semanaInicio, $semanaFin)
            ->whereIn('pagado', [0, 2])
            ->whereNotIn('estado', [2])
            ->where(function ($query) {
                $query->whereNull('placaantigua')
                      ->orWhere('placaantigua', 0);
            })
            ->get();
        $cerPendiente = CertificacionPendiente::RangoFecha($semanaInicio, $semanaFin)->get();
        $desmontes = Desmontes::RangoFecha($semanaInicio, $semanaFin)->get();

        //unificando certificaciones     
        foreach ($certificaciones as $certi) {
            $data = [
                "id" => $certi->id,
                "placa" => $certi->Vehiculo->placa,
                "taller" => $certi->Taller->nombre,
                "inspector" => $certi->Inspector->name,
                "servicio" => $certi->Servicio->tipoServicio->descripcion,
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
                "representante" => $des->Taller->representante,
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
    public function cargaServiciosGasolution($semanaInicio, $semanaFin)
    {
        $disc = new Collection();

        $dis = ServiciosImportados::RangoFecha($semanaInicio, $semanaFin)->get();
        foreach ($dis as $registro) {
            $data = [
                "id" => $registro->id,
                "placa" => $registro->placa,
                "taller" => $registro->taller,
                "inspector" => $registro->certificador,
                "servicio" => $registro->TipoServicio->descripcion,
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
    public function encontrarDiferenciaPorPlaca($lista1, $lista2)
    {
        $diferencias = [];
        //$tabla = new Collection();
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
    }
}
