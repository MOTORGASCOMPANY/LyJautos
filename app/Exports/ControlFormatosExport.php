<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;

class ControlFormatosExport implements FromView, ShouldAutoSize, WithStyles
{
    /*protected $materiales;
    public function __construct(Collection $materiales)
    {
        $this->materiales = $materiales;
    }
    public function collection()
    {
        return $this->materiales->map(function ($m) {
            return [
                'numSerie' => $m->numSerie,
                'inspector' => $m->estado == 1 ? 'STOCK ALMACEN MOTORGAS' : ($m->estado == 2 ? $m->ubicacion : ($m->Inspector->name ?? null)),
                'taller' => $m->taller,
                'taller_o_externo' => $m->taller_o_externo,
                'fecha_entrega' => optional($m->fecha_entrega)->format('d/m/Y'),
                'fecha_pago' => optional($m->fecha_ultima_foto_boleta)->format('d/m/Y'),
                'estado' => match ($m->estado) {
                    1 => 'MOTORGAS',
                    2 => 'ENVIO',
                    3 => 'POSESIÓN',
                    4 => 'CONSUMIDO',
                    5 => 'ANULADO',
                    default => '',
                },
                'placa' => $m->placa,
            ];
        });
    }
    public function headings(): array
    {
        return [
            'N° DE FORMATO',
            'INSPECTOR',
            'TALLER',
            'TALLER / EXTERNO',
            'FECHA DE ENTREGA',
            'FECHA DE PAGO',
            'ESTADO',
            'PLACA',
        ];
    }*/

    public $datae;

    public function __construct($datae)
    {
        $this->datae = $datae;
    }

    public function view(): View
    {
        return view('reporteControlFormatos', ['data' => new HtmlString($this->datae)]);
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // 1. Combinar de A1 a J1
        $sheet->mergeCells('A1:H1');

        // 2. Negrita y centrado para A1:J1
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // 3. Negrita para fila 2 (A2:J2)
        $sheet->getStyle('A2:H2')->getFont()->setBold(true);

        // 4. Bordes para todo el contenido (A1:J{última fila})
        $sheet->getStyle('A1:H' . $highestRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return [];
    }
}
