<?php

namespace App\Imports;

use App\Models\ServiciosImportados;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ImportacionDeConversiones implements ToModel, WithHeadingRow, WithUpserts
{
    /**
     * @param Collection $collection
     */
    public function uniqueBy()
    {
        return 'placa_serie';
    }

    public function model(array $row)
    {
        //dd($row);   
        $fecha = $this->getFechaValida($row['fecha_conversion']);

        return new ServiciosImportados([
            "placa" => $row['placa'],
            //"serie"=>\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_conversion'])->format('Y'),
            "serie" => $fecha ? $fecha->format('Y') : null,
            //"certificador" => $row['certificador'],
            "certificador" => trim($row['certificador']),
            //"taller" => $row['taller'],
            "taller" => trim($row['taller']),
            "fecha" => $fecha,
            //"fecha" => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_conversion']),
            //"fecha" =>Carbon::parse($row['fecha_conversion'])->format('Y-m-d H:i:s'),
            "precio" => null,
            "tipoServicio" => 1,
            "estado" => 1,
            "pagado" => false,
        ]);
    }

    private function getFechaValida($fecha)
    {
        try {
            if (is_numeric($fecha)) {
                // Convertir números en formato Excel a fecha
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fecha);
            } elseif (strtotime($fecha)) {
                // Convertir cadenas reconocibles como fechas
                return Carbon::parse($fecha);
            }
        } catch (\Exception $e) {
            // Manejar errores (puedes registrar el error si es necesario)
        }

        return null; // Fecha no válida
    }

    public function headingRow(): int
    {
        return 7;
    }




    public function customValidationAttributes()
    {
        return ['2' => 'placa'];
    }
}
