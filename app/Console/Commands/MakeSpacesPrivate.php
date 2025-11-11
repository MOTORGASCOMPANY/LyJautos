<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MakeSpacesPrivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spaces:make-private';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Establecer todos los archivos en DigitalOcean Spaces como privados';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Obtiene todos los archivos en el Space
        $files = Storage::disk('do')->allFiles('expedientes');

        foreach ($files as $file) {
            // Cambia cada archivo a privado
            Storage::disk('do')->setVisibility($file, 'private');
            $this->info("Set {$file} to private");
        }

        $this->info('Todos los archivos se han configurado como privados.');
    }
}
