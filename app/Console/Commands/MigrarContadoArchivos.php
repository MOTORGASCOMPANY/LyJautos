<?php

namespace App\Console\Commands;

use App\Models\ContadoArchivo;
use App\Traits\ImageTrait;
use Illuminate\Console\Command;

class MigrarContadoArchivos extends Command
{
    use ImageTrait;

    protected $signature = 'migrar:contados';

    protected $description = 'Migra todos documentos de contados a un object storage en Digital Ocean';

    public function handle()
    {
        $this->info('Iniciando migración de archivos de contados...');

        ContadoArchivo::where('migrado', 0)->chunk(1000, function ($archivos) {
            foreach ($archivos as $archivo) {
                $this->migrarArchivoContado($archivo);
            }
        });

        $this->info('Migración de archivos de contados completada.');
    }
}
