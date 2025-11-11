<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;

class FormatosContadoExport implements FromView, ShouldAutoSize, WithStyles
{
    public $datae;

    public function __construct($datae)
    {
        $this->datae = $datae;
    }

    public function view(): View
    {
        return view('reporteContado', ['data' => new HtmlString($this->datae)]);
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el título en la primera fila (opcional)
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);

        // Aplicar bordes y negrita desde la tercera fila en adelante
        $highestRow = $sheet->getHighestRow(); // Obtener la última fila con datos

        // Aplicar bordes a todas las celdas desde la tercera fila
        $sheet->getStyle('A1:I' . $highestRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Hacer negrita la tercera fila (encabezados)
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);


        return [];
    }
}
