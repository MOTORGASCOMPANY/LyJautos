<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\VerificaFotosTrait;
use App\Mail\FotosIncompletasMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VerificarFotosInspectoresCommand extends Command
{
    use VerificaFotosTrait;

    protected $signature = 'verificar:fotos-inspectores';

    protected $description = 'Verifica los expedientes con fotos incompletas y notifica a los inspectores por correo';

    public function handle()
    {
        //$days = (int) $this->option('days');
        //$this->info("Iniciando verificación de fotos de los últimos {$days} días...");
        // Calcular el rango de la semana anterior
        $inicio = Carbon::now()->subWeek()->startOfWeek(); // lunes pasado
        $fin = Carbon::now()->subWeek()->endOfWeek();     // domingo pasado
        $this->info("Verificando expedientes desde {$inicio->toDateString()} hasta {$fin->toDateString()}...");

        try {
            // Obtener expedientes agrupados por inspector
            /*$agrupados = $this->agruparExpedientesIncompletosPorInspector([
                'sinceDays' => $days,
            ]);*/
            $agrupados = $this->agruparExpedientesIncompletosPorInspector([
                'fecIni' => $inicio->toDateString(),
                'fecFin' => $fin->toDateString(),
                //'ins' => 117,
            ]);

            if (empty($agrupados)) {
                $this->info('No se encontraron expedientes incompletos.');
                return Command::SUCCESS;
            }

            // Recorrer cada inspector
            foreach ($agrupados as $inspectorId => $data) {
                $inspector = $data['inspector'];

                // Saltar si no tiene correo
                if (!$inspector || empty($inspector->email)) {
                    Log::warning("Inspector sin correo: ID {$inspectorId}");
                    continue;
                }

                $expedientes = $data['expedientes'];
                
                // celular del inspector
                $celularInspector = $inspector->celular ?? 'N/A';
                //$this->info("Inspector: {$inspector->name} (ID: {$inspectorId})");
                $this->info("Inspector: {$inspector->name} (ID: {$inspectorId}) | Celular: {$celularInspector}");
                $this->line('Expedientes con fotos incompletas: ' . count($expedientes));

                foreach ($expedientes as $item) {
                    $exp = $item['expediente'];
                    $faltantes = implode(', ', $item['faltantes_codigos']);

                    // placa y certificado
                    $placa = $exp->placa ?? 'N/A';
                    $certificado = $exp->certificado ?? 'N/A';

                    //$this->line("- Expediente #{$exp->id} | Faltantes: {$faltantes}");
                    $this->line("- Expediente #{$exp->id} | Placa: {$placa} | Certificado: {$certificado} | Faltantes: {$faltantes}");

                }

                $this->line('------------------------------------');

                // Enviar correo
                //Mail::to($inspector->email)->queue(new FotosIncompletasMail($inspector, $expedientes));
                Mail::to($inspector->email)->send(new FotosIncompletasMail($inspector, $expedientes));

                $this->info("Correo enviado a {$inspector->name} ({$inspector->email}) con " . count($expedientes) . " expedientes incompletos.");
            }

            $this->info('Verificación completada correctamente.');
            return Command::SUCCESS;
            
        } catch (\Throwable $e) {
            Log::error('Error en VerificarFotosInspectoresCommand: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
