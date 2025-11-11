<?php

namespace App\Exports;

use App\Models\Expediente;
use App\Models\TipoImagen;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteFotosExport implements FromView, ShouldAutoSize, WithStyles
{
    public $ins, $fecIni, $fecFin, $estado;

    // mismas listas que el componente
    private $tiposGNV = [1, 2, 7, 10, 14];
    private $tiposGLP = [3, 4];

    private $imagenesGNV = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    private $imagenesGLP = [1, 2, 3, 4, 5, 6, 8, 9, 10, 11];

    public function __construct($ins, $fecIni, $fecFin, $estado)
    {
        $this->ins = $ins;
        $this->fecIni = $fecIni;
        $this->fecFin = $fecFin;
        $this->estado = $estado;
    }

    private function getRequeridas($tipoServicioId)
    {
        if (in_array($tipoServicioId, $this->tiposGNV)) {

            // caso especial servicio 7, excluir imágenes 1 y 2
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
                fn($q) => $q->whereBetween('created_at', [
                    $this->fecIni . " 00:00:00",
                    $this->fecFin . " 23:59:59"
                ])
            )
            ->when(
                $this->fecIni && !$this->fecFin,
                fn($q) => $q->whereDate('created_at', '>=', $this->fecIni)
            )
            ->when(
                !$this->fecIni && $this->fecFin,
                fn($q) => $q->whereDate('created_at', '<=', $this->fecFin)
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

                    'detalles'    => [],   // <-- NUEVO

                    'gnv_tot'     => 0,
                    'glp_tot'     => 0,
                ];
            }

            $tipoServicioId = $exp->Servicio->tipoServicio->id ?? null;

            $requeridas = $this->getRequeridas($tipoServicioId);

            $imagenesValidas = $exp->Archivos->filter(
                fn($a) =>   in_array(strtolower($a->extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])
            );

            $presentes = $imagenesValidas->pluck('tipo_imagen_id')->toArray();

            $faltantes = array_diff($requeridas, $presentes);

            $completo = count($faltantes) === 0;

            // Clasificación por tipo
            if (in_array($tipoServicioId, $this->tiposGNV)) {
                /*$completo
                    ? $resumen[$inspectorId]['gnv_comp']++
                    : $resumen[$inspectorId]['gnv_incomp']++;*/
                if ($completo) {
                    $resumen[$inspectorId]['gnv_comp']++;
                } else {
                    $resumen[$inspectorId]['gnv_incomp']++;
                }

                // total real = completos + incompletos
                $resumen[$inspectorId]['gnv_tot'] =
                    $resumen[$inspectorId]['gnv_comp'] +
                    $resumen[$inspectorId]['gnv_incomp'];
            }

            if (in_array($tipoServicioId, $this->tiposGLP)) {
                /*$completo
                    ? $resumen[$inspectorId]['glp_comp']++
                    : $resumen[$inspectorId]['glp_incomp']++;*/
                if ($completo) {
                    $resumen[$inspectorId]['glp_comp']++;
                } else {
                    $resumen[$inspectorId]['glp_incomp']++;
                }

                // total real
                $resumen[$inspectorId]['glp_tot'] =
                    $resumen[$inspectorId]['glp_comp'] +
                    $resumen[$inspectorId]['glp_incomp'];
            }

            // almacenar detalles
            if (!$completo) {
                $resumen[$inspectorId]['detalles'][] = [
                    'placa'       => $exp->placa,
                    'certificado' => $exp->certificado,
                    'tipo'        => in_array($tipoServicioId, $this->tiposGNV) ? 'GNV' : 'GLP',
                    //'faltantes'   => $faltantes,
                    'faltantes'   => TipoImagen::whereIn('id', $faltantes)->pluck('codigo')->toArray(),
                ];
            }

        }        

        // Calcular porcentajes
        foreach ($resumen as &$r) {
            $totalGNV = $r['gnv_tot'];
            $totalGLP = $r['glp_tot'];

            $r['gnv_pct'] = $totalGNV > 0
                ? round(($r['gnv_comp'] / $totalGNV) * 100, 1)
                : 0;

            $r['glp_pct'] = $totalGLP > 0
                ? round(($r['glp_comp'] / $totalGLP) * 100, 1)
                : 0;
        }

        return collect($resumen)->sortBy('inspector')->values();
    }

    public function view(): View
    {
        return view('reporte-fotos', [
            'resumen' => $this->generarResumen()
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()->setFillType('solid')->getStartColor()->setRGB('E0E0E0');

        $sheet->getStyle('A1:H' . ($sheet->getHighestRow()))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Centrar el texto vertical y horizontalmente
        $sheet->getStyle('A1:H' . ($sheet->getHighestRow()))
            ->getAlignment()
            ->setVertical('center')
            ->setHorizontal('center');
    }
}
