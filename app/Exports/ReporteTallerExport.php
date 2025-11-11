<?php

namespace App\Exports;

// Importación necesaria para la clase Worksheet
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;

class ReporteTallerExport implements FromView, ShouldAutoSize, WithStyles 
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    public function view(): View
    {
        return view('reporteTaller', ['data' => new HtmlString($this->data)]);
    }

    public function styles(Worksheet $sheet)
    {
        // bordes a todas las celdas
        $sheet->getStyle('A1:J' . $sheet->getHighestRow())
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // encabezados sea negrita
        $sheet->getStyle('1:1')->getFont()->setBold(true);        

        // obtener la última fila con datos
        $highestRow = $sheet->getHighestRow(); 

        // Aplicar fondo rojo a filas que cumplan con la condición
        for ($row = 4; $row <= $highestRow; $row++) { 
            $modelo = $sheet->getCell("K{$row}")->getValue(); 
            if ($modelo === 'App\Models\ServiciosImportados') {
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFC0CB'] // Color rojo
                    ]
                ]);
            }
            // Verificar y aplicar color plomo a las celdas de la columna I que contengan "MTG"
            $observacion = $sheet->getCell("I{$row}")->getValue();
            if (stripos($observacion, 'MTG') !== false) {
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D3D3D3'] // Color plomo
                    ]
                ]);
            }
            //Verificar modelo y estado para color amarillo
            $modeloc = $sheet->getCell("K{$row}")->getValue();
            $estado = $sheet->getCell("I{$row}")->getValue();
            if ($modeloc === 'App\Models\Certificacion' && stripos($estado, 'Anulado') !== false) {
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00'] // Color amarillo
                    ]
                ]);
            }
        }     

        // Aplicamos un formato de texto en blanco en la columna K
        $sheet->getStyle("K4:K{$highestRow}")->getFont()->getColor()->setRGB('FFFFFF'); // Cambia el texto a blanco

        return [];
    }
}
