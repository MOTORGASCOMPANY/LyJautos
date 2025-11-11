<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;

class InformeCilindrosExport implements FromView, ShouldAutoSize, WithStyles
{
    public $datae;

    public function __construct($datae)
    {
        $this->datae = $datae;
    }

    public function view(): View
    {
        return view('reporteInforCilindros', ['data' => new HtmlString($this->datae)]);
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // 1. Combinar de A1 a J1
        $sheet->mergeCells('A1:J1');

        // 2. Negrita y centrado para A1:J1
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // 3. Negrita para fila 2 (A2:J2)
        $sheet->getStyle('A2:J2')->getFont()->setBold(true);

        // 4. Bordes para todo el contenido (A1:J{última fila})
        $sheet->getStyle('A1:J' . $highestRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return [];
    }
}
