<?php

namespace App\Http\Livewire\Reportes;

use App\Exports\ReporteFotosExport;
use App\Models\Expediente;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReporteFotosPorInspector extends Component
{
    use WithPagination;

    public $orderField = 'id', $orderDirection = 'desc', $perPage = 10;

    public $tiposGNV = [1, 2, 7, 10, 14];
    public $tiposGLP = [3, 4];
    public $imagenesGNV = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    public $imagenesGLP = [1, 2, 3, 4, 5, 6, 8, 9, 10, 11];

    public $inspectores, $ins;
    public $fecIni, $fecFin;
    public $estado = '';

    public $openModal = false;
    public $detalles = [];

    public function mount()
    {
        $this->inspectores = User::role(['inspector', 'supervisor'])->where('id', '!=', Auth::id())->orderBy('name')->get();
    }
    public function order($field)
    {
        if ($this->orderField === $field) {
            $this->orderDirection = $this->orderDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->orderField = $field;
            $this->orderDirection = 'asc';
        }
    }

    private function getRequeridas($tipoServicioId)
    {
        if (in_array($tipoServicioId, $this->tiposGNV)) {

            // caso especial servicio 7
            if ($tipoServicioId == 7) {
                return array_values(array_diff($this->imagenesGNV, [1, 2]));
            }

            return $this->imagenesGNV;
        }

        if (in_array($tipoServicioId, $this->tiposGLP)) {
            return $this->imagenesGLP;
        }

        return [];
    }

    private function generarResumen()
    {
        $query = Expediente::with(['Inspector', 'Servicio.tipoServicio', 'Archivos'])
            ->when($this->ins, fn($q) => $q->where('usuario_idusuario', $this->ins))
            ->when(
                $this->fecIni && $this->fecFin,
                fn($q) =>
                $q->whereBetween('created_at', [
                    $this->fecIni . " 00:00:00",
                    $this->fecFin . " 23:59:59"
                ])
            )
            ->when(
                $this->fecIni && !$this->fecFin,
                fn($q) =>
                $q->whereDate('created_at', ">=", $this->fecIni)
            )
            ->when(
                !$this->fecIni && $this->fecFin,
                fn($q) =>
                $q->whereDate('created_at', "<=", $this->fecFin)
            )
            ->get();

        $resumen = [];

        foreach ($query as $exp) {

            $inspectorId = $exp->usuario_idusuario;
            $inspectorNombre = $exp->Inspector->name ?? 'SIN NOMBRE';

            if (!isset($resumen[$inspectorId])) {
                $resumen[$inspectorId] = [
                    'inspector'   => $inspectorNombre,
                    'gnv_comp'    => 0,
                    'gnv_incomp'  => 0,
                    'glp_comp'    => 0,
                    'glp_incomp'  => 0,
                    'detalles_gnv'   => [],
                    'detalles_glp'   => [],
                ];
            }

            $tipoServicioId = $exp->Servicio->tipoServicio->id ?? null;

            $requeridas = $this->getRequeridas($tipoServicioId);

            $imagenesValidas = $exp->Archivos->filter(
                fn($a) =>
                in_array(strtolower($a->extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])
            );

            $presentes = $imagenesValidas->pluck('tipo_imagen_id')->toArray();

            $faltantes = array_diff($requeridas, $presentes);

            $completo = count($faltantes) === 0;

            // Clasificar por tipo
            /*if (in_array($tipoServicioId, $this->tiposGNV)) {
                $completo
                    ? $resumen[$inspectorId]['gnv_comp']++
                    : $resumen[$inspectorId]['gnv_incomp']++;
            }

            if (in_array($tipoServicioId, $this->tiposGLP)) {
                $completo
                    ? $resumen[$inspectorId]['glp_comp']++
                    : $resumen[$inspectorId]['glp_incomp']++;
            }*/
            if (in_array($tipoServicioId, $this->tiposGNV)) {
                if ($completo) {
                    $resumen[$inspectorId]['gnv_comp']++;
                } else {
                    $resumen[$inspectorId]['gnv_incomp']++;

                    // Guardar detalles para modal
                    $resumen[$inspectorId]['detalles_gnv'][] = [
                        'placa'       => $exp->placa,
                        'certificado' => $exp->certificado,
                    ];
                }
            }

            if (in_array($tipoServicioId, $this->tiposGLP)) {
                if ($completo) {
                    $resumen[$inspectorId]['glp_comp']++;
                } else {
                    $resumen[$inspectorId]['glp_incomp']++;

                    // Guardar detalles para modal
                    $resumen[$inspectorId]['detalles_glp'][] = [
                        'placa'       => $exp->placa,
                        'certificado' => $exp->certificado,
                    ];
                }
            }

        }

        // Calcular porcentajes
        foreach ($resumen as &$r) {
            $totalGNV = $r['gnv_comp'] + $r['gnv_incomp'];
            $totalGLP = $r['glp_comp'] + $r['glp_incomp'];

            $r['gnv_pct'] = $totalGNV > 0 ? round(($r['gnv_comp'] / $totalGNV) * 100, 1) : 0;
            $r['glp_pct'] = $totalGLP > 0 ? round(($r['glp_comp'] / $totalGLP) * 100, 1) : 0;

            // Nuevas columnas
            $r['gnv_tot'] = $totalGNV;
            $r['glp_tot'] = $totalGLP;
        }

        return collect($resumen)
            ->sortBy('inspector')
            ->values();
    }

    public function verDetalles($inspectorNombre)
    {
        $resumen = $this->generarResumen();

        // Buscar por nombre directamente
        $fila = $resumen->firstWhere('inspector', $inspectorNombre);

        if (!$fila) {
            $this->detalles = ['gnv' => [], 'glp' => []];
        } else {
            $this->detalles = [
                'gnv' => $fila['detalles_gnv'],
                'glp' => $fila['detalles_glp'],
            ];
        }

        $this->openModal = true;
    }

    public function render()
    {
        $mostrarTabla = $this->fecIni && $this->fecFin;

        $resumenInspectores = $mostrarTabla
            ? $this->generarResumen()
            : collect([]);

        return view('livewire.reportes.reporte-fotos-por-inspector', [
            'resumen'      => $resumenInspectores,
            'mostrarTabla' => $mostrarTabla
        ]);
    }

    public function exportarExcel()
    {
        $nombreArchivo = 'reporte_fotos_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new ReporteFotosExport($this->ins, $this->fecIni, $this->fecFin, $this->estado), $nombreArchivo);
    }
}

/*
    public $fechaInicio, $fechaFin, $inspectoresConFotos;

    protected $rules = [
        "fechaInicio" => 'required|date',
        "fechaFin" => 'required|date',
    ];

    public function generarReporte2()
    {
        $this->validate();

        // Obtener expedientes en el rango de fechas con relaciones cargadas
        $expedientes = Expediente::with(['Inspector', 'Archivos', 'Servicio'])
            ->whereBetween('created_at', [$this->fechaInicio . ' 00:00:00', $this->fechaFin . ' 23:59:59'])
            ->get();

        $gnvIds = [1, 2, 7, 8, 10, 12, 14];
        $glpIds = [3, 4, 13];
        $modiIds = [5];

        // Agrupar expedientes por inspector
        $agrupadoPorInspector = $expedientes->groupBy('usuario_idusuario');

        $this->inspectoresConFotos = $agrupadoPorInspector->map(function (Collection $expedientesDelInspector, $inspectorId) use ($gnvIds, $glpIds, $modiIds) {
            $nombre = optional($expedientesDelInspector->first()->Inspector)->name ?? 'Sin Nombre';

            // Filtrar por tipo
            $expGNV = $expedientesDelInspector->filter(function ($exp) use ($gnvIds) {
                return in_array(optional($exp->Servicio)->tipoServicio_idtipoServicio, $gnvIds);
            });

            $expGLP = $expedientesDelInspector->filter(function ($exp) use ($glpIds) {
                return in_array(optional($exp->Servicio)->tipoServicio_idtipoServicio, $glpIds);
            });

            $expMODI = $expedientesDelInspector->filter(function ($exp) use ($modiIds) {
                return in_array(optional($exp->Servicio)->tipoServicio_idtipoServicio, $modiIds);
            });

            // Contar subidos con fotos
            $subidosGNV = $expGNV->filter(function ($exp) {
                return $exp->Archivos->contains(function ($archivo) {
                    return in_array(strtolower($archivo->extension), ['jpg', 'jpeg', 'png', 'bmp', 'webp']);
                });
            });

            $subidosGLP = $expGLP->filter(function ($exp) {
                return $exp->Archivos->contains(function ($archivo) {
                    return in_array(strtolower($archivo->extension), ['jpg', 'jpeg', 'png', 'bmp', 'webp']);
                });
            });

            $subidosMODI = $expMODI->filter(function ($exp) {
                return $exp->Archivos->contains(function ($archivo) {
                    return in_array(strtolower($archivo->extension), ['jpg', 'jpeg', 'png', 'bmp', 'webp']);
                });
            });

            // Cálculos
            $totalGNV = $expGNV->count();
            $totalGLP = $expGLP->count();
            $totalMODI = $expMODI->count();

            $conFotosGNV = $subidosGNV->count();
            $conFotosGLP = $subidosGLP->count();
            $conFotosMODI = $subidosMODI->count();

            $porcentajeGNV = $totalGNV > 0 ? round(($conFotosGNV / $totalGNV) * 100) : 0;
            $porcentajeGLP = $totalGLP > 0 ? round(($conFotosGLP / $totalGLP) * 100) : 0;
            $porcentajeMODI = $totalMODI > 0 ? round(($conFotosMODI / $totalMODI) * 100) : 0;

            return [
                'nombreInspector' => $nombre,
                'expRealizadosGNV' => $totalGNV,
                'expRealizadosGLP' => $totalGLP,
                'expRealizadosMODI' => $totalMODI,

                'expSubidosGNV' => $conFotosGNV,
                'expSubidosGLP' => $conFotosGLP,
                'expSubidosMODI' => $conFotosMODI,

                'porcentajeGNV' => "{$porcentajeGNV}%",
                'porcentajeGLP' => "{$porcentajeGLP}%",
                'porcentajeMODI' => "{$porcentajeMODI}%",
            ];
        })
        ->sortBy('nombreInspector')
        ->values();
        Cache::put('inspectoresConFotos_copy', $this->inspectoresConFotos, now()->addMinutes(10));
    }

    public function exportarExcel()
    {
        $data = Cache::get('inspectoresConFotos_copy');

        if ($data) {
            $fecha = now()->format('d-m-Y');
            return Excel::download(new ReporteFotosPorInspectorExport($data), 'Reporte_fotos_por_inspector_' . $fecha . '.xlsx');
        }
    }
*/
