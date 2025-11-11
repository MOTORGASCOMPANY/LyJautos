<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\VerificaFotosTrait;
use App\Models\Memorando;
use App\Models\User;
use App\Notifications\MemorandoSolicitud;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcesarMemorandosFotosCommand extends Command
{
    use VerificaFotosTrait;

    protected $signature = 'procesar:memorandos-fotos';
    protected $description = 'Crea memorandos y notifica automáticamente a los inspectores con expedientes con fotos incompletas';
    
    public function handle()
    {
        // Calcular rango (semana anterior)
        $inicio = Carbon::now()->subWeek()->startOfWeek(); // lunes pasado
        $fin = Carbon::now()->subWeek()->endOfWeek();     // domingo pasado
        $this->info("Procesando memorandos desde {$inicio->toDateString()} hasta {$fin->toDateString()}...");

        try {
            // Obtener inspectores con expedientes incompletos
            $agrupados = $this->agruparExpedientesIncompletosPorInspector([
                'fecIni' => $inicio->toDateString(),
                'fecFin' => $fin->toDateString(),
            ]);

            if (empty($agrupados)) {
                $this->info('No se encontraron expedientes incompletos para generar memorandos.');
                return Command::SUCCESS;
            }

            foreach ($agrupados as $inspectorId => $data) {
                $inspector = $data['inspector'];
                $expedientes = $data['expedientes'];

                if (!$inspector) {
                    Log::warning("Inspector no encontrado: ID {$inspectorId}");
                    continue;
                }

                // Información base del memorando
                $remitente = 'MOTORGAS COMPANY'; // o LOPEZ HENRIQUEZ SPASOJE BRATZO           
                $cargoremi = 'Sistema de Control Automático'; // o INGENIERO SUPERVISOR
                $cargo = 'INSPECTOR';
                $motivo = "Se detectaron " . count($expedientes) . " expedientes con fotos incompletas correspondientes a la semana del {$inicio->toDateString()} al {$fin->toDateString()}.";
                $fecha = Carbon::now()->toDateString();

                // Mostrar detalle en consola
                $celularInspector = $inspector->celular ?? 'N/A';
                $this->info("Inspector: {$inspector->name} (ID: {$inspectorId}) | Celular: {$celularInspector}");
                $this->line("Expedientes con fotos incompletas: " . count($expedientes));

                foreach ($expedientes as $item) {
                    $exp = $item['expediente'];
                    $faltantes = implode(', ', $item['faltantes_codigos']);
                    $placa = $exp->placa ?? 'N/A';
                    $certificado = $exp->certificado ?? 'N/A';
                    $this->line("- Expediente #{$exp->id} | Placa: {$placa} | Certificado: {$certificado} | Faltantes: {$faltantes}");
                }

                $this->line('------------------------------------');

                // Crear memorando
                $nuevoMemorando = Memorando::create([
                    'idUser' => $inspector->id,
                    'remitente' => $remitente,
                    'cargo' => $cargo,
                    'cargoremi' => $cargoremi,
                    'motivo' => $motivo,
                    'fecha' => $fecha,
                ]);

                // Enviar notificación
                Notification::send($inspector, new MemorandoSolicitud($nuevoMemorando));

                $this->info("Memorando generado y notificación enviada a {$inspector->name} ({$inspector->email})");
            }

            $this->info('Procesamiento de memorandos completado correctamente.');
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            Log::error('Error en ProcesarMemorandosFotosCommand: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
