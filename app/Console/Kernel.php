<?php

namespace App\Console;

use App\Jobs\CambiarEstadoDeDocumentosTaller;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('app:cambia_estado_documentos')->everyMinute();
        $schedule->job(new CambiarEstadoDeDocumentosTaller)->daily();
        //$schedule->command('migrar:documentos')->weeklyOn(6, '0:00');
        //$schedule->command('migrar:documentos')->dailyAt('21:00'); // migrar documentos
        $schedule->command('guardar:reporteExt')->mondays()->at('12:00'); // Reporte externos semanal

        // Ejecutar todos los dias para actualizar lo que externos digitan despues
        $schedule->command('guardar:reporteExt')
            ->days([2, 3, 4, 5, 6]) // Martes (2) a Sábado (6)
            ->at('22:00');

        $schedule->command('guardar:reporteTalSema')->mondays()->at('12:15'); // Reporte talleres semanal

        // Reporte diario de talleres, excepto lunes
        $schedule->command('guardar:reporteTalDia')->dailyAt('09:30')
            ->when(function () {
                $today = now()->dayOfWeek; // 0 = Domingo, 1 = Lunes, ..., 6 = Sábado
                return $today !== 1; // Excluir lunes
            });

        // Enviar correos de fotos incompletas a inspectores cada lunes
        $schedule->command('verificar:fotos-inspectores')->mondays()->at('09:00')
             ->withoutOverlapping()
             ->runInBackground()
             ->appendOutputTo(storage_path('logs/fotos_incompletas.log'));

        // Crear memorandos y notificar de fotos incompletas a inspectores cada lunes
        /*$schedule->command('procesar:memorandos-fotos')->mondays()->at('10:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/memorandos_fotos.log'));*/
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    protected function scheduleTimezone()
    {
        return 'America/Lima';
    }
}
