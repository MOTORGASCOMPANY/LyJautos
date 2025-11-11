<?php

namespace App\Console\Commands;

use App\Models\BoletaArchivo;
use App\Traits\ImageTrait;
use Illuminate\Console\Command;

class MigrarBoletaArchivos extends Command
{
    use ImageTrait;

    protected $signature = 'migrar:boletas';

    protected $description = 'Migra todos documentos de boletas a un object storage en Digital Ocean';

    public function handle()
    {
        $this->info('Iniciando migración de archivos de boletas...');

        BoletaArchivo::where('migrado', 0)->chunk(1000, function ($archivos) {
            foreach ($archivos as $archivo) {
                $this->migrarArchivoBoleta($archivo);
            }
        });

        $this->info('Migración de archivos de boletas completada.');
    }
}
