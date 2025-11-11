<?php

namespace App\Http\Livewire;

use App\Models\Boleta;
use App\Models\BoletaArchivo;
use App\Models\BoletaServicio;
use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\Desmontes;
use App\Models\ServiciosImportados;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateBoletaArchivo extends Component
{
    use WithFileUploads;
    public $idBoleta, $boleta;
    public $addDocument = false;
    //public  $documento, $nombre;
    public $documentos = [], $nombres = [];
    public $idBoletas = '';

    public $mostrarMonto = false; // Controla el estado del checkbox para ingresar monto_pagado
    public $monto_pagado; 


    public function mount()
    {
        $this->boleta = Boleta::find($this->idBoleta);
        //$this->nombre = ''; 
    }

    public function render()
    {
        return view('livewire.create-boleta-archivo');
    }

    public function updatedDocumentos()
    {
        // Inicialice la matriz de nombres según la cantidad de archivos cargados
        $this->nombres = array_fill(0, count($this->documentos), '');
    }

    /*public function agregarDocumento()
    {
        $this->validate([
            'documento' => 'required|mimes:jpg,jpeg,png|max:2048', //pdf,
            'nombre' => 'required|string|max:255'
        ]);

        // Obtener la boleta
        $boleta = Boleta::find($this->idBoleta);

        if ($boleta->taller == null) {
            $nombre2 = $boleta->certificador;
        } elseif ($boleta->certificador == null) {
            $nombre2 = $boleta->taller;
        } else {
            $nombre2 = '';
        }             
        
        // Construir el nombre antes de agregar el nuevo nombre del input
        $antesdenombre = $boleta->id . '-' . $nombre2;

        // Construir el nombre completo del archivo
        $nombreArchivo = $antesdenombre . '-' . $this->nombre;

        BoletaArchivo::create([
            'boleta_id' => $this->idBoleta,
            'nombre' => $this->nombre,
            'ruta' => $this->documento->storeAs('public/docsBoletas', $nombreArchivo . '.' . $this->documento->extension()),
            'extension' => $this->documento->extension(),
        ]);
        $this->emitTo('editar-boleta', 'refrescaBoleta');
        $this->emitTo('boletas-archivos', 'resetBoleta');
        $this->reset(['documento', 'nombre', 'addDocument']);
        $this->emit("CustomAlert", ["titulo" => "BUEN TRABAJO!", "mensaje" => "Se ingreso correctamente un nuevo documento", "icono" => "success"]);
    }*/

    /*public function agregarDocumento()
    {
        $this->validate([
            'documentos.*' => 'required|mimes:jpg,jpeg,png|max:2048', // Add 'pdf' if needed
            'nombres.*' => 'required|string|max:255'
        ]);

        // Obtener la boleta
        $boleta = Boleta::find($this->idBoleta);

        foreach ($this->documentos as $index => $documento) {
            $nombreInput = $this->nombres[$index];

            // Determinar el nombre2 basado en la lógica proporcionada
            if ($boleta->taller == null) {
                $nombre2 = $boleta->certificador;
            } elseif ($boleta->certificador == null) {
                $nombre2 = $boleta->taller;
            } else {
                $nombre2 = '';
            }

            // Construir el nombre antes de agregar el nuevo nombre del input
            $antesdenombre = $boleta->identificador . '-' . $nombre2;

            // Construir el nombre completo del archivo
            $nombreArchivo = $antesdenombre . '-' . $nombreInput;

            BoletaArchivo::create([
                'boleta_id' => $this->idBoleta,
                'nombre' => $this->nombres[$index],
                'ruta' => $documento->storeAs('public/docsBoletas', $nombreArchivo . '.' . $documento->extension()),
                'extension' => $documento->extension(),
            ]);
        }

        $this->emitTo('editar-boleta', 'refrescaBoleta');
        $this->emitTo('boletas-archivos', 'resetBoleta');
        $this->reset(['documentos', 'nombres', 'addDocument']);
        $this->emit("CustomAlert", ["titulo" => "BUEN TRABAJO!", "mensaje" => "Se ingresaron correctamente los documentos", "icono" => "success"]);
    }*/

    public function agregarDocumento()
    {
        $this->validate([
            'documentos.*' => 'required|mimes:jpg,jpeg,png|max:2048', // Add 'pdf' if needed
            'nombres.*' => 'required|string|max:255',
            'idBoletas' => 'nullable|string', // Validar que sea una cadena de texto si es proporcionado
            'monto_pagado' => 'nullable|numeric|min:0',
        ]);

        // Convertir $idBoletas a un array si no es nulo
        $idBoletas = $this->idBoletas ? array_map('trim', explode(',', $this->idBoletas)) : [$this->idBoleta];

        foreach ($idBoletas as $idBoleta) {
            // Obtener la boleta
            $boleta = Boleta::find($idBoleta);

            if (!$boleta) continue; // Si la boleta no existe, continuar con el siguiente ID

            foreach ($this->documentos as $index => $documento) {
                $nombreInput = $this->nombres[$index];

                /* Determinar el nombre basado en la lógica proporcionada
                if ($boleta->taller == null) {
                    $nombre2 = $boleta->certificador;
                } elseif ($boleta->certificador == null) {
                    $nombre2 = $boleta->taller;
                } else {
                    $nombre2 = '';
                }*/

                if ($boleta->taller == null) {
                    $nombre2 = $boleta->Certificador->name;
                } else {
                    $nombre2 = $boleta->Taller->nombre;
                }

                // Construir el nombre antes de agregar el nuevo nombre del input
                $antesdenombre = $boleta->id . '-' . $boleta->identificador . '-' . $nombre2;

                // Construir el nombre completo del archivo
                $nombreArchivo = $antesdenombre . '-' . $nombreInput;

                BoletaArchivo::create([
                    'boleta_id' => $idBoleta,
                    'nombre' => $nombreInput,
                    'ruta' => $documento->storeAs('public/docsBoletas', $nombreArchivo . '.' . $documento->extension()),
                    'extension' => $documento->extension(),
                ]);
            }

            // Si se ingresó un monto_pagado, actualizar el campo en la boleta
            if ($this->monto_pagado !== null && $this->monto_pagado !== '') {
                $boleta->update(['monto_pagado' => $this->monto_pagado]);
            }

            // Lógica para actualizar los servicios relacionados
            //$this->actualizarServicios($idBoleta);
        }

        // Emitir eventos para actualizar componentes relacionados
        $this->emitTo('editar-boleta', 'refrescaBoleta');
        $this->emitTo('boletas-archivos', 'resetBoleta');
        $this->reset(['documentos', 'nombres', 'addDocument', 'idBoletas', 'monto_pagado', 'mostrarMonto']);
        $this->emit("CustomAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "Se ingresaron correctamente los documentos", "icono" => "success"]);
    }

    /*private function actualizarServicios($idBoleta)
    {
        // Buscar la relación en BoletaServicio
        $boletaServicio = BoletaServicio::where('boleta_id', $idBoleta)->first();

        if (!$boletaServicio) return;

        // Si servicio_ids ya es un array, úsalo directamente; si es una cadena, decodifícalo
        $servicioIds = is_array($boletaServicio->servicio_ids)
        ? $boletaServicio->servicio_ids
        : json_decode($boletaServicio->servicio_ids, true);

        if (!is_array($servicioIds)) {
            throw new \Exception('El campo servicio_ids no es un array válido.');
        }

        // Mapeo de modelos y sus clases
        $modelosPermitidos = [
            'App\\Models\\Certificacion' => Certificacion::class,
            'App\\Models\\CertificacionPendiente' => CertificacionPendiente::class,
            'App\\Models\\Desmontes' => Desmontes::class,
            'App\\Models\\ServiciosImportados' => ServiciosImportados::class,
        ];

        foreach ($servicioIds as $servicio) {
            // Validar el formato del servicio
            if (strpos($servicio, '=>') === false) {
                throw new \Exception("Formato de servicio inválido: {$servicio}");
            }

            [$modelo, $idsString] = explode('=>', $servicio);
            $modelo = trim($modelo);

            // Validar si el modelo está permitido
            if (!array_key_exists($modelo, $modelosPermitidos)) {
                throw new \Exception("Modelo desconocido o no permitido: {$modelo}");
            }

            // Convertir los IDs en un array
            $idsArray = array_map('trim', explode(',', $idsString));

            // Actualizar los registros del modelo correspondiente
            $modelosPermitidos[$modelo]::whereIn('id', $idsArray)->update(['pagado' => 2]);
        }
    }*/
}
