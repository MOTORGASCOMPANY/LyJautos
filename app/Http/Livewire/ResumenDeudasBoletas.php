<?php

namespace App\Http\Livewire;

use App\Models\Boleta;
use App\Models\Contado;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class ResumenDeudasBoletas extends Component
{
    public $semanaInicial = '2024-12-09 00:00:00'; //Semana de inicio
    // Variables para talleres
    public $tablaFinalTalleres, $totalTalleres, $semorderTalle;
    // Variables para inspectores    
    public $tablaFinalInspectores, $totalInspectores, $semorderIns;
    // Variables para contados
    public $tablaFinalContados, $totalContados;
    // Variable para cierre general
    public $cierre;

    protected $listeners = ['exportarPdf'];

    public function render()
    {
        return view('livewire.resumen-deudas-boletas');
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
        $this->procesarTalleres();
        $this->procesarInspectores();
        $this->procesarContados();
        $this->calcularTotales();
    }

    public function calcularTotales()
    {
        $this->totalTalleres = collect($this->tablaFinalTalleres)->sum('Total');
        $this->totalInspectores = collect($this->tablaFinalInspectores)->sum('Total');
        $this->totalContados = collect($this->tablaFinalContados)->sum('total_precio');
        $this->cierre = $this->totalTalleres + $this->totalInspectores + $this->totalContados;
    }

    public function procesarTalleres()
    {
        $rangosSemanas = $this->obtenerRangosSemanas($this->semanaInicial, Carbon::now()->format('Y-m-d'));
        $datosTalleres = [];
        //$this->sumaTotalTalleres = 0; // Reiniciar la suma antes de calcular

        foreach ($rangosSemanas as $semana) {
            $tablaBoleta = $this->generaData($semana['inicio'], $semana['fin']);

            foreach ($tablaBoleta as $item) {
                if (!isset($item['taller']) || empty(trim($item['taller']))) {
                    continue; // Omitimos talleres vacíos
                }

                $taller = $item['taller'];

                if (!isset($datosTalleres[$taller])) {
                    $datosTalleres[$taller] = [];
                }

                // Guardar el monto por semana
                $datosTalleres[$taller][$semana['rango']] = ($datosTalleres[$taller][$semana['rango']] ?? 0) + $item['monto'];
            }
        }

        // Calcular el total por taller
        foreach ($datosTalleres as $taller => $montos) {
            $datosTalleres[$taller]['Total'] = array_sum($montos);
            //$this->sumaTotalTalleres += $datosTalleres[$taller]['Total'];
        }

        // Ordenar semanas
        $this->semorderTalle = $this->ordenarSemanas($datosTalleres);

        $this->tablaFinalTalleres = collect($datosTalleres)->sortKeys()->toArray();
    }

    public function procesarInspectores()
    {
        $rangosSemanas = $this->obtenerRangosSemanas($this->semanaInicial, Carbon::now()->format('Y-m-d'));
        $datosInspectores = [];
        //$this->sumaTotalInspectores = 0; // Reiniciar la suma antes de calcular

        foreach ($rangosSemanas as $semana) {
            $tablaBoleta = $this->generaData($semana['inicio'], $semana['fin']);

            foreach ($tablaBoleta as $item) {
                // Validar que el certificador tenga datos y que el taller sea null
                if (
                    !isset($item['certificador']) || empty(trim($item['certificador'])) ||
                    isset($item['taller']) // Si el taller tiene datos, se omite el registro
                ) {
                    continue;
                }

                $inspector = $item['certificador'];

                if (!isset($datosInspectores[$inspector])) {
                    $datosInspectores[$inspector] = [];
                }

                // Guardar el monto por semana
                $datosInspectores[$inspector][$semana['rango']] = ($datosInspectores[$inspector][$semana['rango']] ?? 0) + $item['monto'];
            }
        }

        // Calcular el total por inspector
        foreach ($datosInspectores as $inspector => $montos) {
            $datosInspectores[$inspector]['Total'] = array_sum($montos);
            //$this->sumaTotalInspectores += $datosInspectores[$inspector]['Total'];
        }

        // Ordenar semanas
        $this->semorderIns = $this->ordenarSemanas($datosInspectores);
        //$this->tablaFinalInspectores = $datosInspectores;
        $this->tablaFinalInspectores = collect($datosInspectores)->sortKeys()->toArray();
        //dd($this->tablaFinalInspectores);
    }

    private function ordenarSemanas($datos)
    {
        return collect($datos)
            ->collapse()
            ->keys()
            ->reject(fn($semana) => $semana === 'Total')
            ->sortBy(function ($semana) {
                // Extraer día de inicio, día de fin y mes
                preg_match('/(\d{1,2}) al (\d{1,2}) (\w+)/', $semana, $matches);
                if (count($matches) === 4) {
                    $diaInicio = $matches[1];
                    $diaFin = $matches[2];
                    $mesNombre = $matches[3];

                    // Convertir el mes a número
                    $mesNumero = Carbon::parse("1 $mesNombre")->month;

                    // Determinar el año correctamente
                    $añoActual = Carbon::now()->year;
                    $año = ($mesNumero == 12) ? $añoActual - 1 : $añoActual; // Diciembre pertenece al año anterior

                    // Crear la fecha para ordenar
                    return Carbon::create($año, $mesNumero, $diaInicio)->timestamp;
                }
                return PHP_INT_MAX;
            })
            ->push('Total')
            ->values()
            ->toArray();
    }

    private function obtenerRangosSemanas($semanaInicial, $fechaFinal)
    {
        $inicio = Carbon::parse($semanaInicial);
        $fin = Carbon::parse($fechaFinal);
        $rangos = [];

        while ($inicio <= $fin) {
            $finSemana = $inicio->copy()->endOfWeek();  // Fin de la semana
            // Excluir semanas incompletas
            if ($finSemana > $fin) {
                break;
            }

            $rangos[] = [
                'inicio' => $inicio->toDateString(), // Incluye fecha y hora
                'fin' => $finSemana->toDateString(), // Incluye fecha y hora
                'rango' => $inicio->format('d') . " al " . $finSemana->format('d') . " " . $inicio->format('F'),
            ];
            $inicio->addWeek();  // Sumar una semana
        }
        return $rangos;
    }

    public function generaData($semanaInicio, $semanaFin)
    {
        return Boleta::RangoFecha2($semanaInicio, $semanaFin)
            ->whereNotIn('estado', [1])
            ->get()
            ->map(function ($bol) {
                return [
                    "id" => $bol->id,
                    "taller" => $bol->Taller->nombre ?? null,
                    "certificador" => $bol->Certificador->name ?? null,
                    "fechaInicio" => $bol->fechaInicio,
                    "fechaFin" => $bol->fechaFin,
                    "monto" => $bol->monto - ($bol->monto_pagado ?? 0),
                    "estado" => $bol->estado,
                    "tipo_modelo" => $bol::class,
                ];
            });
    }

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
        return Contado::RangoFecha($this->semanaInicial, $diaFinal)
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
}



/*
public function procesar()
    {
        $this->tablaFinalTalleres = $this->procesarEntidad('taller');
        $this->tablaFinalInspectores = $this->procesarEntidad('certificador', true);
        $this->calcularTotales();
    }
*/

/*
public function procesarTalleres()
    {
        $rangosSemanas = $this->obtenerRangosSemanas($this->semanaInicial, Carbon::now()->format('Y-m-d'));
        $datosTalleres = [];
        $this->sumaTotalTalleres = 0; // Reiniciar la suma antes de calcular

        foreach ($rangosSemanas as $semana) {
            $tablaBoleta = $this->generaData($semana['inicio'], $semana['fin']);

            foreach ($tablaBoleta as $item) {
                // Validar que el taller no sea nulo o una cadena vacía
                if (!isset($item['taller']) || empty(trim($item['taller']))) {
                    continue; // Omitimos este registro
                }

                $taller = $item['taller'];

                if (!isset($datosTalleres[$taller])) {
                    $datosTalleres[$taller] = [];
                }

                // Guardar el monto por semana
                $datosTalleres[$taller][$semana['rango']] = ($datosTalleres[$taller][$semana['rango']] ?? 0) + $item['monto'];
            }
        }

        // Calcular el total por taller
        foreach ($datosTalleres as $taller => $montos) {
            $datosTalleres[$taller]['Total'] = array_sum($montos);
            $this->sumaTotalTalleres += $datosTalleres[$taller]['Total'];
        }

        //$this->tablaFinalTalleres = $datosTalleres;
        $this->tablaFinalTalleres = collect($datosTalleres)->sortKeys()->toArray();
        //dd($this->tablaFinalTalleres);
    }
public function procesarInspectores()
    {
        $rangosSemanas = $this->obtenerRangosSemanas($this->semanaInicial, Carbon::now()->format('Y-m-d'));
        $datosInspectores = [];
        $this->sumaTotalInspectores = 0; // Reiniciar la suma antes de calcular

        foreach ($rangosSemanas as $semana) {
            $tablaBoleta = $this->generaData($semana['inicio'], $semana['fin']);

            foreach ($tablaBoleta as $item) {
                // Validar que el certificador tenga datos y que el taller sea null
                if (
                    !isset($item['certificador']) || empty(trim($item['certificador'])) ||
                    isset($item['taller']) // Si el taller tiene datos, se omite el registro
                ) {
                    continue;
                }

                $inspector = $item['certificador'];

                if (!isset($datosInspectores[$inspector])) {
                    $datosInspectores[$inspector] = [];
                }

                // Guardar el monto por semana
                $datosInspectores[$inspector][$semana['rango']] = ($datosInspectores[$inspector][$semana['rango']] ?? 0) + $item['monto'];
            }
        }

        // Calcular el total por inspector
        foreach ($datosInspectores as $inspector => $montos) {
            $datosInspectores[$inspector]['Total'] = array_sum($montos);
            $this->sumaTotalInspectores += $datosInspectores[$inspector]['Total'];
        }

        //$this->tablaFinalInspectores = $datosInspectores;
        $this->tablaFinalInspectores = collect($datosInspectores)->sortKeys()->toArray();
    }
*/

/*
private function procesarEntidad($campo, $excluirTalleres = false)
    {
        $rangosSemanas = $this->obtenerRangosSemanas($this->semanaInicial, Carbon::now()->format('Y-m-d'));
        $datosEntidad = [];

        foreach ($rangosSemanas as $semana) {
            $tablaBoleta = $this->generaData($semana['inicio'], $semana['fin']);

            foreach ($tablaBoleta as $item) {
                //if (!isset($item[$campo]) || empty(trim($item[$campo]))) {
                //    continue;
                //}

                //if ($excluirTalleres && isset($item['taller'])) {
                //    continue; // Si es inspector y tiene taller, lo omitimos
                //}
                // Si es "taller", validamos que no sea nulo o vacío
                if ($campo === 'taller' && (!isset($item['taller']) || empty(trim($item['taller'])))) {
                    continue; // Omitimos este registro
                }

                // Si es "certificador" y además el campo "taller" tiene datos, lo omitimos
                if ($excluirTalleres && (!isset($item['certificador']) || empty(trim($item['certificador'])) || isset($item['taller']))) {
                    continue; // Omitimos este registro
                }

                $clave = $item[$campo];

                if (!isset($datosEntidad[$clave])) {
                    $datosEntidad[$clave] = [];
                }

                // Guardar el monto por semana
                $datosEntidad[$clave][$semana['rango']] = ($datosEntidad[$clave][$semana['rango']] ?? 0) + $item['monto'];
            }
        }

        foreach ($datosEntidad as $clave => $montos) {
            $datosEntidad[$clave]['Total'] = array_sum($montos);
        }

        // Ordenar semanas
        $this->semanasOrdenadas = $this->ordenarSemanas($datosEntidad);
        return collect($datosEntidad)->sortKeys()->toArray();
    }
*/

/*public function generaData($semanaInicio, $semanaFin)
    {
        $tabla = new Collection();
        $boletas = Boleta::RangoFecha2($semanaInicio, $semanaFin)
            ->whereNotIn('estado', [1])
            ->get();

        //unificando certificaciones     
        foreach ($boletas as $bol) {
            $montoRest = $bol->monto - ($bol->monto_pagado ?? 0);

            $data = [
                "id" => $bol->id,
                "taller" => $bol->Taller->nombre ?? null,
                "certificador" => $bol->Certificador->name ?? null,
                "fechaInicio" => $bol->fechaInicio,
                "fechaFin" => $bol->fechaFin,
                "monto" => $montoRest,
                "estado" => $bol->estado,
                "tipo_modelo" => $bol::class,

            ];
            $tabla->push($data);
        }
        return $tabla;
    }
*/