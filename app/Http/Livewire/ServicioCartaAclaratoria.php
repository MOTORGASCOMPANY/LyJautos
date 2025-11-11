<?php

namespace App\Http\Livewire;

use App\Models\CartaAclaratoria;
use App\Models\Material;
use App\Models\TipoMaterial;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\pdfTrait;

class ServicioCartaAclaratoria extends Component
{

    use pdfTrait;
    use WithFileUploads;

    //VARIABLES DEL SERVICIO DE CARTA ACLARATORIA
    public $materiales, $material, $numSugerido, $pertenece;
    public $estado = null;
    public $certificacion = null;
    public $titulo, $partida, $placa;
    public $diceData = [];
    public $debeDecirData = [];
    public $diceModificacion;
    public $debeDecirModificacion;
    public $parrafo = false;


    public function mount()
    {
        $this->materiales = TipoMaterial::whereIn('id', [1, 3, 4])->get();
        //$this->diceData = [['numero' => '', 'titulo' => '', 'descripcion' => '']];
        //$this->debeDecirData = [['numero' => '', 'titulo' => '', 'descripcion' => '']];
        $this->diceData = []; // Inicializar como array vacío
        $this->debeDecirData = []; // Inicializar como array vacío
    }

    public function updatedMaterial($value)
    {
        $this->estado = $this->material ? 'esperando' : null;
    }

    public function updatednumSugerido($val)
    {
        $this->pertenece = $this->obtienePertenece($val);
    }

    public function obtienePertenece($val)
    {
        if ($val && $this->material) {
            $material = Material::where("numSerie", $val)
                ->where("idTipoMaterial", $this->material)
                ->where("estado", 3)
                ->first();
            if (!$material) {
                return "No existe";
            } elseif (is_null($material->idUsuario)) {
                return "No está asignado";
            } elseif ($material->estado == 4) {
                return "Formato Consumido";
            } else {
                return User::find($material->idUsuario)->name;
            }
        }
        return null;
    }

    public function render()
    {
        return view('livewire.servicio-carta-aclaratoria');
    }

    public function certificarta()
    {

        $material = Material::where("numSerie", $this->numSugerido)
            ->where("idTipoMaterial", $this->material)
            ->where("estado", 3)
            ->first();
        if (!$material) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Material no encontrado", "icono" => "warning"]);
            return;
        }

        $usuario = User::find($material->idUsuario);
        if (!$usuario) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "El material no esta asignado a ningun Inspector", "icono" => "warning"]);
            return;
        }

        /*if ($this->material == 4) {
            $this->diceData = null;
            $this->debeDecirData = null;
        } elseif (empty($this->diceData) || empty($this->debeDecirData)) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Por favor complete los datos de DICE y DEBE DECIR.", "icono" => "warning"]);
            return;
        }*/

        // Validación para guardar null si los campos están vacíos
        $diceData = !empty(array_filter($this->diceData, fn($data) => array_filter($data))) ? $this->diceData : null;
        $debeDecirData = !empty(array_filter($this->debeDecirData, fn($data) => array_filter($data))) ? $this->debeDecirData : null;
        $diceModificacion = trim($this->diceModificacion) !== '' ? $this->diceModificacion : null;
        $debeDecirModificacion = trim($this->debeDecirModificacion) !== '' ? $this->debeDecirModificacion : null;

        $certi = CartaAclaratoria::certificarCartAclaratoria(
            $material,
            $usuario,
            $this->titulo,
            $this->partida,
            $this->placa,
            $diceData,
            $debeDecirData,
            $diceModificacion,
            $debeDecirModificacion,
            $this->parrafo
        );

        if ($certi) {
            $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Tu certificado N°: " . $certi->material->numSerie . " está listo.", "icono" => "success"]);
            $this->certificacion = $certi;
            $this->estado = 'certificado';
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No fue posible certificar", "icono" => "warning"]);
        }
    }

    //NUEVOS PARA DICE Y DEBE DECIR

    public function addData()
    {
        $this->diceData[] = ['numero' => '', 'titulo' => '', 'descripcion' => ''];
        $this->debeDecirData[] = ['numero' => '', 'titulo' => '', 'descripcion' => ''];
    }

    public function removeData()
    {
        if (!empty($this->diceData) && !empty($this->debeDecirData)) {
            // Eliminamos el último elemento de ambos arrays
            array_pop($this->diceData);
            array_pop($this->debeDecirData);
        }
    }



    /*public function removeDiceData($index)
    {
        unset($this->diceData[$index]);
        $this->diceData = array_values($this->diceData);
    }

    public function removeDebeDecirData($index)
    {
        unset($this->debeDecirData[$index]);
        $this->debeDecirData = array_values($this->debeDecirData);
    }*/
}
