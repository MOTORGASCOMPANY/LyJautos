<?php

namespace App\Console\Commands;

use App\Models\Imagen;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportDataset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan export:dataset
     */
    protected $signature = 'export:dataset {--path=dataset}';

    protected $description = 'Exporta las imágenes clasificadas en carpetas según su tipo_imagen_id';

    public function handle()
    {
        $basePath = storage_path('app/' . $this->option('path'));

        // Asegurar carpeta base
        if (!is_dir($basePath)) {
            mkdir($basePath, 0777, true);
        }

        $imagenes = Imagen::with('TipoImagen')
            ->whereNotNull('tipo_imagen_id')
            ->get();

        $this->info("Se encontraron " . $imagenes->count() . " imágenes para exportar...");

        foreach ($imagenes as $imagen) {
            $tipo = $imagen->TipoImagen ? $imagen->TipoImagen->codigo : $imagen->tipo_imagen_id;

            // Carpeta destino (ejemplo: dataset/1_frontal)
            $folder = $basePath . '/' . $tipo;
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            // Normalizamos la ruta quitando "public/"
            $ruta = preg_replace('/^public\//', '', $imagen->ruta);

            // Ruta absoluta al archivo original
            $origen = Storage::disk('public')->path($ruta);

            // Archivo destino
            $destino = $folder . '/' . $imagen->nombre . '.' . $imagen->extension;

            if (file_exists($origen)) {
                copy($origen, $destino);
                $this->info("✅ Copiada: {$imagen->nombre}.{$imagen->extension} -> {$tipo}");
            } else {
                $this->warn("⚠️ No encontrada: {$origen}");
            }
        }

        $this->info("Exportación completada. Dataset en: " . $basePath);
    }
}
