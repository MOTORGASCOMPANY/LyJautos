<?php

namespace App\Traits;

use App\Models\BoletaArchivo;
use App\Models\ContadoArchivo;
use App\Models\Imagen;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\StorageAttributes;

trait ImageTrait
{
    // migrar imagenes que actualmente están almacenados en el VPS en public/storage/expedientes hacia el object storage en DigitalOcean
    private function migrarArchivoDeExpedienteLocal(Imagen $file)
    {
        //$rutaActual = $file->ruta;
        try {
            // Obtener información del archivo
            $nombreArchivo = $file->nombre;
            $extensionArchivo = $file->extension;
            $rutaArchivoLocal = public_path('storage/expedientes/' . $nombreArchivo . '.' . $extensionArchivo);

            // Verificar si el archivo local existe
            if (!file_exists($rutaArchivoLocal)) {
                throw new \Exception("El archivo local no existe en la ruta: $rutaArchivoLocal");
            }

            // Leer el contenido del archivo
            $contenidoArchivo = file_get_contents($rutaArchivoLocal);

            // Subir el archivo a DigitalOcean Spaces
            $nuevaRuta = 'expedientes/' . $nombreArchivo . '.' . $extensionArchivo;
            Storage::disk('do')->put($nuevaRuta, $contenidoArchivo);
            Storage::disk('do')->setVisibility($nuevaRuta, 'private');

            /*
            // Verificar que el archivo se haya subido correctamente
            if (!Storage::disk('do')->exists($nuevaRuta)) {
                throw new \Exception("No se pudo subir el archivo a DigitalOcean Spaces.");
            }*/

            // Alternativa segura en vez de exists()
            try {
                // Intenta generar la URL como forma de validar
                Storage::disk('do')->url($nuevaRuta);
            } catch (\Exception $e) {
                throw new \Exception("No se pudo verificar el archivo subido a DigitalOcean Spaces: " . $e->getMessage());
            }

            // Eliminar el archivo local
            unlink($rutaArchivoLocal);

            // Actualizar los metadatos en la base de datos
            $file->update([
                'ruta' => $nuevaRuta,
                'migrado' => 1,
                // Actualiza otros metadatos según sea necesario
            ]);

            // Registrar la migración exitosa
            Log::channel('migracion_expedientes')->notice("Se migró correctamente el archivo con ID: $file->id hacia Digital Ocean con la ruta: $nuevaRuta");

            return $nuevaRuta; // Devolver la nueva ruta del archivo en DigitalOcean Spaces
        } catch (\Exception $e) {
            // Manejar cualquier error que pueda ocurrir durante la migración
            Log::channel('migracion_expedientes')->error("Error al migrar archivo con ID: $file->id. Error: " . $e->getMessage());
            return false;
        }
    }

    // migrar imagenes que actualmente están almacenados en el VPS en public/storage/docsContados hacia el object storage en DigitalOcean
    private function migrarArchivoContado(ContadoArchivo $file)
    {
        try {
            $rutaRelativaStorage = str_replace('public/', '', $file->ruta); // docsContados/1-JuanPerez-Cedula.jpg
            $rutaArchivoLocal = public_path('storage/' . $rutaRelativaStorage);

            if (!file_exists($rutaArchivoLocal)) {
                throw new \Exception("El archivo local no existe en la ruta: $rutaArchivoLocal");
            }

            $contenidoArchivo = file_get_contents($rutaArchivoLocal);

            $nuevaRuta = $rutaRelativaStorage;
            Storage::disk('do')->put($nuevaRuta, $contenidoArchivo);
            Storage::disk('do')->setVisibility($nuevaRuta, 'private');

            try {
                Storage::disk('do')->url($nuevaRuta);
            } catch (\Exception $e) {
                throw new \Exception("No se pudo verificar el archivo subido a DigitalOcean Spaces: " . $e->getMessage());
            }

            unlink($rutaArchivoLocal);

            $file->update([
                'ruta' => $nuevaRuta,
                'migrado' => 1,
            ]);

            Log::channel('migracion_docsContados')->notice("Migración exitosa de archivo CONTADO ID: $file->id a: $nuevaRuta");

            return $nuevaRuta;
        } catch (\Exception $e) {
            Log::channel('migracion_docsContados')->error("Error al migrar CONTADO ID: $file->id. " . $e->getMessage());
            return false;
        }
    }

    // migrar imagenes que actualmente están almacenados en el VPS en public/storage/docsContados hacia el object storage en DigitalOcean
    private function migrarArchivoBoleta(BoletaArchivo $file)
    {
        try {
            // en el campo ruta se esta guardando de esta manera public/docsBoletas/6374-1-MISHAEL PERU S.A.C.-comprobante.jpg
            $rutaRelativaStorage = str_replace('public/', '', $file->ruta);
            $rutaArchivoLocal = public_path('storage/' . $rutaRelativaStorage);

            if (!file_exists($rutaArchivoLocal)) {
                throw new \Exception("El archivo local no existe en la ruta: $rutaArchivoLocal");
            }

            $contenidoArchivo = file_get_contents($rutaArchivoLocal);

            $nuevaRuta = $rutaRelativaStorage;
            Storage::disk('do')->put($nuevaRuta, $contenidoArchivo);
            Storage::disk('do')->setVisibility($nuevaRuta, 'private');

            try {
                Storage::disk('do')->url($nuevaRuta);
            } catch (\Exception $e) {
                throw new \Exception("No se pudo verificar el archivo subido a DigitalOcean Spaces: " . $e->getMessage());
            }

            unlink($rutaArchivoLocal);

            $file->update([
                'ruta' => $nuevaRuta,
                'migrado' => 1,
            ]);

            Log::channel('migracion_docsBoletas')->notice("Migración exitosa de archivo BOLETA ID: $file->id a: $nuevaRuta");

            return $nuevaRuta;
        } catch (\Exception $e) {
            Log::channel('migracion_docsBoletas')->error("Error al migrar BOLETA ID: $file->id. " . $e->getMessage());
            return false;
        }
    }
}
