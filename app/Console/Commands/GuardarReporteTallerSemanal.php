<?php

namespace App\Console\Commands;

use App\Models\Boleta;
use App\Models\BoletaServicio;
use App\Models\Taller;
use App\Traits\reporteTallerSemanalTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GuardarReporteTallerSemanal extends Command
{
    use reporteTallerSemanalTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guardar:reporteTalSema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generar el reporte de taller semanal en segundo plano y el resultado lo ingresa en la tabla boleta';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        $fechaFin = now()->subWeek()->endOfWeek()->format('Y-m-d');
        $fechaInicio = now()->subWeek()->startOfWeek()->format('Y-m-d');
        $this->info("Iniciando generación del reporte: {$fechaInicio} al {$fechaFin}");
        Log::channel('reporte_Taller_Semanal')->info("Iniciando generación del reporte: {$fechaInicio} al {$fechaFin}");

        try {
            $resultados = $this->procesar();
            //dd($resultados);
            $contador = 1; // Inicializamos el contador

            foreach ($resultados['semanales'] as $taller) {
                // Buscar el ID del taller en la base de datos
                $tallerId = Taller::where('nombre', $taller['taller'])->value('id');

                if (!$tallerId) {
                    Log::channel('reporte_Taller_Semanal')->warning("No se encontró el ID para el taller {$taller['taller']}. Este registro será ignorado.");
                    continue; // Saltar este taller si no se encuentra
                }

                // Verificar si ya existe un registro para el taller y las fechas
                $existingBoleta = Boleta::where('taller', $tallerId)
                    ->whereDate('fechaInicio', $fechaInicio)
                    ->where('fechaFin', $fechaFin)
                    ->first();

                if ($existingBoleta) {
                    // Si existe, actualizar el monto
                    $existingBoleta->update([
                        'monto' => $taller['total'],
                    ]);
                    Log::channel('reporte_Taller_Semanal')->info("Se actualizó el monto para el taller {$taller['taller']} con nuevo monto {$taller['total']}.");
                } else {
                    // Si no existe, crear un nuevo registro
                    $boleta = Boleta::create([
                        'taller' => $tallerId,
                        'fechaInicio' => $fechaInicio,
                        'fechaFin' => $fechaFin,
                        'monto' => $taller['total'],
                        'observacion' => null,
                        'certificador' => null,
                        'anual' => null,
                        'duplicado' => null,
                        'inicial' => null,
                        'desmonte' => null,
                        'identificador' => $contador,
                        'auditoria' => 0,
                    ]);


                    // Verificación de los datos de 'ids' antes de intentar guardar
                    // Crear la entrada en BoletaServicio
                    if (!empty($taller['ids'])) {
                        BoletaServicio::create([
                            'boleta_id' => $boleta->id,
                            'servicio_ids' => $taller['ids'], // Asegurarse de que sea un array
                        ]);
                    } else {
                        Log::channel('reporte_Taller_Semanal')->warning("No se encontraron servicios asociados para el taller {$taller['taller']}.");
                    }

                    Log::channel('reporte_Taller_Semanal')->info("Se guardó en BD para el taller {$taller['taller']} con monto {$taller['total']}.");
                    $contador++;
                }
            }

            $this->info('Reporte generado e insertado en la tabla Boleta con éxito.');
            Log::channel('reporte_Taller_Semanal')->info('Reporte generado e insertado en la tabla Boleta con éxito.');
        } catch (\Exception $e) {
            $this->error('Error al generar el reporte: ' . $e->getMessage());
            Log::channel('reporte_Taller_Semanal')->error('Error al generar el reporte: ' . $e->getMessage());
        }
    }
}
