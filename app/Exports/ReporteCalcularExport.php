<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
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

class ReporteCalcularExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles, WithColumnFormatting, WithStrictNullComparison
{
    use Exportable;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }


    public function title(): string
    {
        return 'Reporte Calcular MTC';
    }

    public function columnFormats(): array
    {
        return [
            'B' =>  NumberFormat::FORMAT_DATE_DDMMYYYY,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER_00,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'FECHA',
            'N° CERTIFICADO',
            'TALLER',
            'INSPECTOR',
            'PLACA',
            'SERVICIO',
            'FAC O BOLT',
            'OBSERVACIONES',
            'MONTO',
        ];
    }

    public function map($data): array
    {
        static $rowNumber = 1;  // Static variable to keep track of row numbers

        $fecha = date('Y-m-d h:i:s', strtotime($data['fecha']));
        $precio = number_format($data['precio'], 2);
        $secondPart = '';

        /*/PARA PLACA 
        if ($data['servicio'] == 'Chip por deterioro') {
            $ubicacionParts = explode('/', $data['ubi_hoja']);
            $secondPart = isset($ubicacionParts[1]) ? trim($ubicacionParts[1]) : 'N.A';
        } else {
            $secondPart = $data['placa'] ?? 'EN TRAMITE';
        }*/

        // EXTERNO Y ANULADO para modelo certificacion
        $externoyanulado = null;
        if (isset($data['externo']) && $data['externo'] == 1) {
            $externoyanulado = 'Externo';
        }
        if ($data['estado'] == 2) {
            if ($externoyanulado !== null) {
                $externoyanulado .= ', Anulado';
            } else {
                $externoyanulado = 'Anulado';
            }
        }

        // externo para modelo certificacion_pendiente
        $externo = isset($data['externo']) ? ($data['externo'] == 1 ? 'Externo' : null) : null;

        // Definir el valor en la columna "Observaciones" para diferenciar si solo está en Certificacion
        $observacion = isset($data['solo_en_certificacion']) && $data['solo_en_certificacion'] ? 'MTG' : '';

        // Concatenar externoyanulado y observacion modelo certificacion
        /*$observacionFinal = trim($externoyanulado . ($externoyanulado && $observacion ? ', ' : '') . $observacion);
        $observacionFinal = $observacionFinal !== '' ? $observacionFinal : null; // Si está vacío, poner null*/
        // Nueva lógica para validar primero placaantigua
        if (isset($data['placaantigua']) && $data['placaantigua'] == 1) {
            $observacionFinal = 'Placa antigua';
        } else {
            // Si placaantigua no es 1, aplicar la lógica existente
            $observacionFinal = trim($externoyanulado . ($externoyanulado && $observacion ? ', ' : '') . $observacion);
            $observacionFinal = $observacionFinal !== '' ? $observacionFinal : null; // Si está vacío, poner null
        }

        // Concatenar externo y observacion modelo certificacion_pendiente
        $obserFinalPendi = trim($externo . ($externo && $observacion ? ', ' : '') . $observacion);
        $obserFinalPendi = $obserFinalPendi !== '' ? $obserFinalPendi : null; // Si está vacío, poner null

        $mappedData = [];

        switch ($data['tipo_modelo']) {
            case 'App\Models\Certificacion':
                $mappedData = [
                    $rowNumber++,  // Increment the row number for each row
                    $fecha ?? 'S.F',
                    $data['num_hoja'] ?? 'N.E',
                    $data['taller'] ?? 'N.A',
                    $data['inspector'] ?? 'N.A',
                    $data['placa'] ?? 'EN TRAMITE',
                    $data['servicio'] ?? 'N.A',
                    '',
                    $observacionFinal ?? null,
                    $precio ?? 'S.P',
                    'certificacion',
                ];
                break;
            case 'App\Models\CertificacionPendiente':
                $mappedData = [
                    $rowNumber++,  // Increment the row number for each row
                    $fecha ?? 'S.F',
                    $data['num_hoja'] ?? 'N.E',
                    $data['taller'] ?? 'N.A',
                    $data['inspector'] ?? 'N.A',
                    $data['placa'] ?? 'EN TRAMITE',
                    $data['servicio'] ?? 'N.A',
                    '',
                    $obserFinalPendi ?? null,
                    $precio ?? 'S.P',
                    'certificacion pendiente',
                ];
                break;
            case 'App\Models\ServiciosImportados':
                $mappedData = [
                    $rowNumber++,  // Increment the row number for each row
                    $fecha ?? 'S.F',
                    $data['num_hoja'] ?? 'N.E',
                    $data['taller'] ?? 'N.A',
                    $data['inspector'] ?? 'N.A',
                    $data['placa'] ?? 'EN TRAMITE',
                    $data['servicio'] ?? 'N.A',
                    '',
                    '',
                    $precio ?? 'S.P',
                    'discrepancia'
                ];
                break;
            default:
                $mappedData = [
                    $rowNumber++,  // Increment the row number for each row
                    $fecha ?? 'S.F',
                    $data['num_hoja'] ?? 'N.E',
                    $data['taller'] ?? 'N.A',
                    $data['inspector'] ?? 'N.A',
                    $data['placa'] ?? 'EN TRAMITE',
                    $data['servicio'] ?? 'N.A',
                    '',
                    '',
                    $precio ?? 'S.P',
                    'certificacion',
                ];
                break;
        }

        return $mappedData;
    }

    /*public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // Aplicar estilos condicionales
        for ($i = 2; $i <= $lastRow; $i++) {
            // Obtener el valor de la columna I (Observaciones)
        $observaciones = $sheet->getCell('I' . $i)->getValue();

        // Verificar si la observación contiene 'MTG'
        if (strpos($observaciones, 'MTG') !== false) {
            // Pintar la fila de color plomo (gris)
            $sheet->getStyle('A' . $i . ':J' . $i)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D3D3D3'], // Código de color para plomo (gris claro)
                ],
            ]);
        }


            $style = $sheet->getCell('K' . $i)->getValue();
            if ($style === 'certificacion') {
                $sheet->getStyle('A1:J' . $sheet->getHighestRow())
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            } elseif ($style === 'discrepancia') {
                $sheet->getStyle('A' . $i . ':J' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFC0CB'],
                    ],
                ])
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }

            // Colorear de amarillo las filas de certificaciones anuladas
            $dataRow = $this->data[$i - 2]; // Ajustar índice por la fila de encabezado
            if ($dataRow['tipo_modelo'] === 'App\Models\Certificacion' && $dataRow['estado'] == 2) {
                $sheet->getStyle('A' . $i . ':J' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00'],
                    ],
                ]);
            }
        }

        // Aplicar estilos a los encabezados
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);

        // Agregar la fórmula de suma en la columna J, después de la última fila de datos
        $lastDataRow = $lastRow + 1;
        $sumFormula = "=SUM(J2:J{$lastRow})";

        $excludeConditions = [];
        foreach ($this->data as $index => $item) {
            if ($item['tipo_modelo'] === 'App\Models\Certificacion' && $item['estado'] == 2) {
                $rowIndex = $index + 2;
                $excludeConditions[] = "J{$rowIndex}";
            }
        }

        if (!empty($excludeConditions)) {
            $excludeFormula = implode(",", $excludeConditions);
            $sumFormula = "=SUM(J2:J{$lastRow}) - SUM({$excludeFormula})";
        }

        $sheet->setCellValue("J{$lastDataRow}", $sumFormula);

        $sheet->getStyle("J{$lastDataRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        // Aplicar estilos a la celda de total
        $sheet->getStyle("J{$lastDataRow}")->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        return [];
    }*/

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        for ($i = 2; $i <= $lastRow; $i++) {
            $observaciones = $sheet->getCell('I' . $i)->getValue();

            if (strpos($observaciones, 'MTG') !== false) {
                $sheet->getStyle('A' . $i . ':J' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D3D3D3'],
                    ],
                ]);
            }

            if (strpos($observaciones, 'Placa antigua') !== false) {
                $sheet->getStyle('A' . $i . ':J' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'ffbf00'],
                    ],
                ]);
            }

            $style = $sheet->getCell('K' . $i)->getValue();

            // Cambios en este bloque:
            if ($style === 'certificacion') {
                // Primero aplica el borde general
                $sheet->getStyle('A1:J' . $sheet->getHighestRow())
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            } elseif ($style === 'discrepancia') {
                // Primero aplica el color de fondo
                $sheet->getStyle('A' . $i . ':J' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFC0CB'],
                    ],
                ]);
                // Luego aplica los bordes
                $sheet->getStyle('A' . $i . ':J' . $i)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }

            // Cambios en la lógica de tipo_modelo y estado
            $dataRow = $this->data[$i - 2];
            if ($dataRow['tipo_modelo'] === 'App\Models\Certificacion' && $dataRow['estado'] == 2) {
                $sheet->getStyle('A' . $i . ':J' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00'],
                    ],
                ]);
            }
        }

        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);

        $lastDataRow = $lastRow + 1;
        // Inicializamos el subtotal solo en la columna K, que ya excluye los valores anulados
        $sumFormula = "=SUBTOTAL(9, K2:K{$lastRow})";

        $excludeConditions = [];
        foreach ($this->data as $index => $item) {
            $rowIndex = $index + 2;
            // Si la certificación está anulada, en K ponemos 0, de lo contrario ponemos el valor de J
            if ($item['tipo_modelo'] === 'App\Models\Certificacion' && $item['estado'] == 2) {
                $sheet->setCellValue("K{$rowIndex}", 0);
            } else {
                $sheet->setCellValue("K{$rowIndex}", "=J{$rowIndex}");
            }
        }        

        // Ajuste para la fórmula de exclusión
        /*if (!empty($excludeConditions)) {
            $excludeFormula = implode(",", $excludeConditions);
            //$sumFormula = "=SUBTOTAL(9, J2:J{$lastRow}) - SUM({$excludeFormula})";
            $sumFormula = "=SUM(J2:J{$lastRow}) - SUM({$excludeFormula})";
        }*/

        // Aplicamos un formato de texto en blanco en la columna K
        $sheet->getStyle("K2:K{$lastRow}")->getFont()->getColor()->setRGB('FFFFFF'); // Cambia el texto a blanco

        $sheet->setCellValue("J{$lastDataRow}", $sumFormula);
        $sheet->getStyle("J{$lastDataRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle("J{$lastDataRow}")->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);
        // Oculta la columna K
        //$sheet->getColumnDimension('K')->setVisible(false);

        return [];
    }
}
