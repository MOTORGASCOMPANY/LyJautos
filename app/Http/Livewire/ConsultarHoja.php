<?php

namespace App\Http\Livewire;

use App\Models\TipoMaterial;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ConsultarHoja extends Component
{
    public $numSerie, $resultados;
    public $desde, $hasta, $tipoMaterial, $anioActivo;
    public $openEdit = false;
    public $estado, $ubicacion;
    public $consultaRealizada = false;

    protected $rules = [
        'desde' => 'required',
        'hasta' => 'required',
        'tipoMaterial' => 'required',
        'estado' => 'required',
        'ubicacion' => 'required',
    ];

    public function render()
    {
        return view('livewire.consultar-hoja');
    }

    public function buscar()
    {
        $this->validate([
            'desde' => 'required',
            'hasta' => 'required',
            'tipoMaterial' => 'required',
        ]);

        $query = DB::table('material as m')
            ->leftJoin('users as u', 'm.idUsuario', '=', 'u.id')
            ->leftJoin('tipomaterial as tm', 'm.idTipoMaterial', '=', 'tm.id')
            ->leftJoin('serviciomaterial as sm', 'm.id', '=', 'sm.idMaterial')
            ->leftJoin('certificacion as c', 'sm.idCertificacion', '=', 'c.id')
            ->leftJoin('detallesalida as ds', 'm.id', '=', 'ds.idMaterial')
            ->leftJoin('contado as co', 'ds.idSalida', '=', 'co.idSalida')
            ->leftJoin('vehiculo as v', 'c.idVehiculo', '=', 'v.id')
            /*->select(
                'm.*',
                'u.name as inspector',
                'tm.descripcion as tipo_material',
                'v.placa',
                'c.pagado as pagado_certificacion',
                'co.id as pagado_contado'
            )
            ->whereBetween('m.numSerie', [$this->desde, $this->hasta]);*/
            ->select(
                'm.id',
                'm.numSerie',
                'm.estado',
                'm.añoActivo',
                'm.grupo',
                'm.ubicacion',
                'm.devuelto',
                'u.name as inspector',
                'tm.descripcion as tipo_material',
                'v.placa',
                DB::raw('MAX(c.pagado) as pagado_certificacion'),
                DB::raw('MAX(co.id) as pagado_contado') // MAX para evitar duplicados
            )
            ->whereBetween('m.numSerie', [$this->desde, $this->hasta])
            ->groupBy('m.id', 'm.numSerie', 'm.estado', 'm.añoActivo', 'm.grupo', 'm.ubicacion', 'm.devuelto', 'u.name', 'tm.descripcion', 'v.placa');

        // Aplicar filtros opcionales
        if ($this->tipoMaterial) {
            $query->where('m.idTipoMaterial', $this->tipoMaterial);
        }
        if ($this->anioActivo) {
            $query->where('m.añoActivo', $this->anioActivo);
        }

        $resultados = $query->get();

        // Asignar etiquetas de pago
        foreach ($resultados as $material) {
            if ($material->estado == 4) {
                if (!is_null($material->pagado_contado)) {
                    $material->estado_pago = 'Pagado al contado';
                } elseif ($material->pagado_certificacion == 2) {
                    $material->estado_pago = 'Pagado en certificacion';
                } else {
                    $material->estado_pago = 'NE';
                }
            } else {
                $material->estado_pago = 'NE';
            }
        }

        $this->resultados = $resultados;
        $this->consultaRealizada = true;
    }

    public function abrirModal()
    {
        if (isset($this->resultados) && $this->resultados->count()) {
            $this->openEdit = true;
        }
    }

    public function actualizar()
    {
        $this->validate([
            'estado' => 'required',
            'ubicacion' => 'required',
        ]);

        $query = DB::table('material')
            ->whereBetween('numSerie', [$this->desde, $this->hasta])
            ->where('idTipoMaterial', $this->tipoMaterial);

        if ($this->anioActivo) {
            $query->where('añoActivo', $this->anioActivo);
        }

        $query->update([
            'estado' => $this->estado,
            'ubicacion' => $this->ubicacion,
        ]);

        $this->emit("CustomAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "Se cambió el estado correctamente", "icono" => "success"]);
        $this->reset(['openEdit', 'estado', 'ubicacion']);
    }
}

/*public function buscar()
    {
        $this->validate([
            'desde' => 'required',
            'hasta' => 'required',
            'tipoMaterial' => 'required',
        ]);

        $query = DB::table('material')
            ->leftJoin('users', 'material.idUsuario', '=', 'users.id')
            ->leftJoin('tipomaterial', 'material.idTipoMaterial', '=', 'tipomaterial.id')
            ->leftJoin('serviciomaterial', 'material.id', '=', 'serviciomaterial.idMaterial')
            ->leftJoin('certificacion', 'serviciomaterial.idCertificacion', '=', 'certificacion.id')
            ->leftJoin('vehiculo', 'certificacion.idVehiculo', '=', 'vehiculo.id')
            ->select(
                'material.*',
                'users.name as nombreUsuario',
                'tipomaterial.descripcion as descripcionTipoMaterial',
                'vehiculo.placa as placa',
            )
            ->whereBetween('material.numSerie', [$this->desde, $this->hasta]);

        if ($this->tipoMaterial) {
            $query->where('material.idTipoMaterial', $this->tipoMaterial);
        }

        if ($this->anioActivo) {
            $query->where('material.añoActivo', $this->anioActivo);
        }

        $this->resultados = $query->get();       
        $this->consultaRealizada = true;
    }
*/

/*
public function actualizar()
{
    $this->validate([
        'estado' => 'required',
        'ubicacion' => 'required',
    ]);

    if (!$this->consultaRealizada || empty($this->resultados)) {
        $this->emit("CustomAlert", ["titulo" => "Error", "mensaje" => "No hay registros para actualizar", "icono" => "error"]);
        return;
    }

    try {
        // Obtener los IDs de los materiales filtrados en la búsqueda
        $idsMateriales = $this->resultados->pluck('id')->toArray();

        if (empty($idsMateriales)) {
            $this->emit("CustomAlert", ["titulo" => "Error", "mensaje" => "No hay registros válidos para actualizar", "icono" => "error"]);
            return;
        }

        // Actualizar solo los registros que coincidan con la búsqueda
        DB::table('material')
            ->whereIn('id', $idsMateriales) // Filtra por IDs obtenidos en la búsqueda
            ->update([
                'estado' => $this->estado,
                'ubicacion' => $this->ubicacion,
            ]);

        $this->emit("CustomAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "Se cambió el estado correctamente", "icono" => "success"]);
        $this->reset(['openEdit', 'estado', 'ubicacion']);

    } catch (\Exception $e) {
        $this->emit("CustomAlert", ["titulo" => "Error", "mensaje" => "No se pudo actualizar: " . $e->getMessage(), "icono" => "error"]);
    }
}
*/