<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Boleta; // Modelo de Boleta
use Carbon\Carbon;

class InsertWeeklyReportToBoleta implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    //private $fechaInicio;
    //private $fechaFin;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->fechaFin = Carbon::now()->subDay(); // Domingo de la semana pasada
        //$this->fechaInicio = $this->fechaFin->copy()->subDays(6); // Lunes de la semana pasada
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    /*public function handle()
    {
        // Llama a la función `procesar` del componente o mueve la lógica aquí
        $reporte = $this->generarReporte($this->fechaInicio, $this->fechaFin);

        foreach ($reporte as $fila) {
            Boleta::create([
                'taller' => null,
                'fechaInicio' => $this->fechaInicio->toDateString(),
                'fechaFin' => $this->fechaFin->toDateString(),
                'monto' => $fila['monto'],
                'observacion' => null,
                'certificador' => $fila['inspector'], // Mapea el ID del inspector aquí
                'anual' => $fila['anual'],
                'duplicado' => $fila['duplicado'],
                'inicial' => $fila['inicial'],
                'desmonte' => $fila['desmonte'],
                'identificador' => null,
                'auditoria' => 0,
            ]);
        }
    }*/

    /*private function generarReporte($fechaInicio, $fechaFin)
    {
        // Aquí puedes invocar la lógica de tu método `procesar` o moverla directamente
        // Por simplicidad, devuelve una colección simulada
        return collect([
            [
                'inspector_id' => 1,
                'anual' => 16,
                'duplicado' => 0,
                'inicial' => 0,
                'desmonte' => 0,
                'monto' => 160.00,
            ],
        ]);
    }*/
}
