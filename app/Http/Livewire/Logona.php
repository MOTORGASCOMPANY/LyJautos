<?php

namespace App\Http\Livewire;

use App\Exports\ReporteTallerRsmnExport;
use App\Models\Boleta;
use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\Desmontes;
use App\Models\ServiciosImportados;
use App\Models\Taller;
use App\Models\TallerInspector;
use App\Models\User;
use App\Services\DataService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;

class Logona extends Component
{
    public $fechaInicio, $fechaFin, $talleres, $inspectores, $servicio;
    public $ins = [], $taller = [];
    public $tabla, $diferencias, $importados, $aux;
    public $tabla2, $tabla3;
    public $semanales, $diarios;

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
        $this->talleres = Taller::orderBy('nombre')->get();
    }

    public function render()
    {
        return view('livewire.logona');
    }

    /*public function procesar()
    {
        $this->validate();
        $this->tabla = $this->generaData();
        $this->importados = $this->cargaServiciosGasolution();
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
        $this->diferencias = $this->encontrarDiferenciaPorPlaca($this->importados, $this->tabla);
        //Merge para combinar tabla y diferencias -  strtolower para ignorar Mayusculas y Minusculas 
        $this->tabla2 = $this->tabla->merge($this->diferencias, function ($item1, $item2) {
            $inspector1 = strtolower($item1['inspector']);
            $inspector2 = strtolower($item2['inspector']);
            $taller1 = strtolower($item1['taller']);
            $taller2 = strtolower($item2['taller']);
            $comparison = strcasecmp($inspector1 . $taller1, $inspector2 . $taller2);
            return $comparison;
        });
        $this->tabla3 = $this->filtrarExternos($this->tabla2);
        $this->aux = $this->agruparTalleresConsolidar($this->tabla3);
        // Agregar IDs de boletas
        // Uso en tu lógica principal
        $this->aux = $this->asociarBoletasConTaller($this->aux);
        //dd($this->aux);
        $this->semanales = $this->filtrarPorFrecuencia($this->aux, 'es_semanal');
        $this->diarios = $this->filtrarPorFrecuencia($this->aux, 'es_diario');
    }*/

    public function procesar()
    {
        $this->validate();
        // Traemos todos las certificaciones del DataService
        $datos = $this->dataService->procesar($this->ins, $this->taller, $this->servicio, $this->fechaInicio, $this->fechaFin);
        // excluir registros externos de ciertos talleres
        $this->tabla3 = $this->filtrarExternos($datos);
        // Agrupar y consolidar talleres
        $this->aux = $this->agruparTalleresConsolidar($this->tabla3);
        // Asociar boletas con talleres y rango de fechas
        $this->aux = $this->asociarBoletasConTaller($this->aux);
        //dd($this->aux);
        // Pasamos a la vista talleres que son semanales
        $this->semanales = $this->filtrarPorFrecuencia($this->aux, 'es_semanal');
        // Pasamos a la vista talleres que son diarios
        $this->diarios = $this->filtrarPorFrecuencia($this->aux, 'es_diario');
    }

    // Muestra y actualiza auditoria
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
    // Nuevo método: ciclo de auditoría
    /*public function cycleAuditoria($boletaId)
    {
        $boleta = Boleta::find($boletaId);

        if ($boleta) {
            // Si no tiene valor, lo tratamos como 0
            $estadoActual = $boleta->auditoria ?? 0;

            // Ciclar: 0 → 1 → 2 → 0
            $nuevoEstado = ($estadoActual + 1) % 3;

            $boleta->auditoria = $nuevoEstado;
            $boleta->save();

            $this->emit("minAlert", [
                "titulo" => "¡BUEN TRABAJO!",
                "mensaje" => "Auditoría cambiada a estado $nuevoEstado.",
                "icono"   => "success"
            ]);
        } else {
            $this->emit("minAlert", [
                "titulo" => "¡ATENCIÓN!",
                "mensaje" => "No se encontró la boleta.",
                "icono"   => "warning"
            ]);
        }
    }*/


    // excluir registros externos de ciertos talleres
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

    // Agrupar talleres y consolidar ciertos talleres en uno solo
    private function agruparTalleresConsolidar($tabla)
    {
        // Definir mapa de consolidaciones
        $consolidaciones = [
            'AUTOTRONICA JOEL CARS' => 'AUTOTRONICA JOEL CARS E.I.R.L. - II',
            //'UNIGAS CONVERSIONES S.A.C.' => 'UNIGAS HOME S.A.C.',
            'AUTOGAS GREEN CAR E.I.R.L. - II' => 'WILTON MOTORS E.I.R.L -II'
        ];
        
        // Agrupar por taller y calcular totales
        return $tabla->groupBy('taller')->map(function ($items) {
            $taller = $items->first()['taller'];
            // Obtener inspectores designados para el taller
            $inspectoresDesignados = $this->obtenerInspectoresDesignados($taller);
            // Filtrar items por inspectores designados si existen
            $itemsFiltrados = $inspectoresDesignados
                ? $items->filter(fn($item) => in_array($item['inspector'], $inspectoresDesignados))
                : $items;
            // Calcular totales y porcentaje pagado
            $totalServicios = $itemsFiltrados->count(); // Total de servicios agrupados
            $pagados = $itemsFiltrados->where('pagado', 2)->count(); // Servicios pagados
            $porcentajePagado = $totalServicios > 0 ? round(($pagados / $totalServicios) * 100, 2) : 0;
            // Retornar datos del taller
            return [
                'taller' => $taller,
                'encargado' => $itemsFiltrados->first()['representante'] ?? null,
                'total' => $itemsFiltrados->sum('precio'),
                'porcentaje_pagado' => $porcentajePagado,
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
                'porcentaje_pagado' => $groupedItems->first()['porcentaje_pagado'],
            ])->filter(fn($data) => $data['total'] > 0)
            ->sortBy('taller');
    }
    // Obtener inspectores designados para un taller
    private function obtenerInspectoresDesignados($taller)
    {
        $inspectoresIds = TallerInspector::where('taller_id', Taller::where('nombre', $taller)->value('id'))
            ->pluck('inspector_id')
            ->toArray();

        return User::whereIn('id', $inspectoresIds)->pluck('name')->toArray();
    }

    // Función para asociar boletas con talleres y rango de fechas
    private function asociarBoletasConTaller($aux)
    {
        // Limpiar y extraer nombres únicos de los talleres
        $nombresTalleres = $aux->pluck('taller')
            ->map(fn($nombre) => strtolower(trim($nombre))) // Limpiar cada nombre
            ->unique()
            ->toArray();

        // Obtener IDs de talleres en base a los nombres
        $talleresIds = $this->obtenerIdsTalleres($nombresTalleres);

        return $aux->map(function ($item) use ($talleresIds) {
            $idTaller = $talleresIds[strtolower(trim($item['taller']))] ?? null; // Obtener el ID del taller limpio

            if (!$idTaller) {
                $item['boletas_ids'] = [];
                return $item;
            }

            // Verificar rango de fechas en las boletas
            $fechaInicioFiltro = $this->fechaInicio; // Fecha de inicio del filtro
            $fechaFinFiltro = $this->fechaFin; // Fecha de fin del filtro

            $item['boletas_ids'] = Boleta::where('taller', $idTaller)
                ->where(function ($query) use ($fechaInicioFiltro, $fechaFinFiltro) {
                    $query->whereBetween('fechaInicio', [$fechaInicioFiltro, $fechaFinFiltro])
                        ->orWhereBetween('fechaFin', [$fechaInicioFiltro, $fechaFinFiltro])
                        ->orWhere(function ($subQuery) use ($fechaInicioFiltro, $fechaFinFiltro) {
                            $subQuery->where('fechaInicio', '<=', $fechaInicioFiltro)
                                ->where('fechaFin', '>=', $fechaFinFiltro);
                        });
                })
                //->pluck('id')
                //->get(['id', 'auditoria'])
                ->get(['id', 'auditoria', 'fechaInicio'])
                ->toArray();

            return $item;
        });
    }
    // Función para obtener IDs de talleres basados en los nombres
    private function obtenerIdsTalleres(array $nombresTalleres): array
    {
        // Eliminar espacios y convertir a minúsculas para uniformar la comparación
        $nombresTalleres = array_map(fn($nombre) => strtolower(trim($nombre)), $nombresTalleres);

        return Taller::all() // Obtener todos los talleres para procesamiento
            ->mapWithKeys(function ($taller) {
                $nombreLimpio = strtolower(trim($taller->nombre)); // Limpiar nombre en DB
                return [$nombreLimpio => $taller->id];
            })
            ->only($nombresTalleres) // Filtrar solo los talleres coincidentes
            ->toArray();
    }

    // Filtrar por frecuencia (semanal o diario)
    private function filtrarPorFrecuencia($tabla, $frecuencia)
    {
        return $tabla->filter(function ($item) use ($frecuencia) {
            return Taller::where('nombre', $item['taller'])->value($frecuencia) == 1;
        });
    }

    public function exportarExcel($data)
    {
        return Excel::download(new ReporteTallerRsmnExport($data), 'reporte_TallerResumen.xlsx');
    }

    /*public function generaData()
    {
        $tabla = new Collection();
        //TODO CERTIFICACIONES:
        $certificaciones = Certificacion::IdTalleres($this->taller)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            ->whereIn('pagado', [0, 2])
            ->whereNotIn('estado', [2])
            ->get();

        //TODO CER-PENDIENTES:
        $cerPendiente = CertificacionPendiente::IdTalleres($this->taller)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();

        //TODO DESMONTES:
        $desmontes = Desmontes::IdTalleres($this->taller)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();

        //unificando certificaciones     
        foreach ($certificaciones as $certi) {
            $data = [
                "id" => $certi->id,
                "placa" => $certi->Vehiculo->placa,
                "taller" => $certi->Taller->nombre,
                "representante" => $certi->Taller->representante,
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
                "representante" => $cert_pend->Taller->representante,
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
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();

        foreach ($dis as $registro) {
            $data = [
                "id" => $registro->id,
                "placa" => $registro->placa,
                "taller" => $registro->taller,
                "representante" => $registro->representante,
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
    }*/
}
