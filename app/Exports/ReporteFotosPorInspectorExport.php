<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Expediente;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteFotosPorInspectorExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles, WithColumnFormatting, WithStrictNullComparison
{
    use Exportable;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        //dd($this->data); 
        return $this->data;
    }

    public function title(): string
    {
        return 'Reporte de Fotos por Inspector';
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_NUMBER,
        ];
    }


    public function headings(): array
    {
        return [
            '#',
            'Inspector',
            'Realizó GNV',
            'Subió GNV',
            'Porcentaje GNV',
            'Realizó GLP',
            'Subió GLP',
            'Porcentaje GLP',
            'Realizó MODI',
            'Subió MODI',
            'Porcentaje MODI',
        ];
    }


    public function map($data): array
    {
        static $index = 1;

        return [
            $index++,
            $data['nombreInspector'],
            $data['expRealizadosGNV'],
            $data['expSubidosGNV'],
            $data['porcentajeGNV'],
            $data['expRealizadosGLP'],
            $data['expSubidosGLP'],
            $data['porcentajeGLP'],
            $data['expRealizadosMODI'],
            $data['expSubidosMODI'],
            $data['porcentajeMODI'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // bordes a todas las celdas
        $sheet->getStyle('A1:K' . $sheet->getHighestRow())
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // encabezados sea negrita
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        return [
            // 
        ];
    }
}
