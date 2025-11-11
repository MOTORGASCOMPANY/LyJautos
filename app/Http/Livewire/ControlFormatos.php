<?php

namespace App\Http\Livewire;

use App\Exports\ControlFormatosExport;
use App\Models\Boleta;
use App\Models\BoletaArchivo;
use App\Models\Material;
use App\Models\TipoMaterial;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ControlFormatos extends Component
{
    public $grupoSeleccionado = '';
    public $materiales = [];

    public $tipoSeleccionado = '';
    public $grupos = [];

    public $tipos;

    public $anioSeleccionado;

    protected $listeners = ['exportarExcelFormatos'];

    public function mount()
    {
        //$this->tipos = TipoMaterial::whereIn('id', [1, 3, 4])->get();
        $this->tipos = TipoMaterial::get();
    }

    // Nombre del tipo material
    public function getNombreTipoProperty()
    {
        return $this->tipos->firstWhere('id', $this->tipoSeleccionado)?->descripcion ?? '';
    }
    // Minimo y Maximo numSerie
    public function getRangoSeriesProperty()
    {
        if (empty($this->materiales) || count($this->materiales) === 0) {
            return '';
        }

        $numeros = collect($this->materiales)->pluck('numSerie')->filter()->sort()->values();

        $min = $numeros->first();
        $max = $numeros->last();

        return "($min - $max)";
    }

    public function updatedAnioSeleccionado()
    {
        $this->updatedTipoSeleccionado();
    }

    public function updatedTipoSeleccionado()
    {
        $this->grupoSeleccionado = ''; // Reiniciamos grupo
        $this->materiales = [];

        /*$this->grupos = Material::where('idTipoMaterial', $this->tipoSeleccionado)
            ->select('grupo', DB::raw('MIN(numSerie) as minSerie'), DB::raw('MAX(numSerie) as maxSerie'))
            ->groupBy('grupo')
            ->orderBy('grupo')
            ->get()
            ->toArray();*/
        $query = Material::where('idTipoMaterial', $this->tipoSeleccionado);

        // Solo filtrar por año si el tipo seleccionado no es 2 (chip)
        if ($this->anioSeleccionado && $this->tipoSeleccionado != 2) {
            $query->where('añoActivo', $this->anioSeleccionado);
        }

        $this->grupos = $query
            ->select('grupo', DB::raw('MIN(numSerie) as minSerie'), DB::raw('MAX(numSerie) as maxSerie'))
            ->groupBy('grupo')
            ->orderBy('grupo')
            ->get()
            ->toArray();
    }

    public function updatedGrupoSeleccionado()
    {
        /*$this->materiales = Material::with([
            'Inspector',
            'certificaciones.Taller',
            'certificaciones.Vehiculo',
            'detalleSalidas.salida',
        ])
        ->where('idTipoMaterial', $this->tipoSeleccionado)
        ->where('grupo', $this->grupoSeleccionado)
        ->orderBy('numSerie', 'asc')
        ->get();*/
        $query = Material::with([
            /*'Inspector',
                'certificaciones.Taller',
                'certificaciones.Vehiculo',
                'certificaciones.Servicio.tipoServicio',
                'detalleSalidas.salida',*/
            'Inspector',
            // Cargamos certificaciones y sus sub-relaciones
            'certificaciones' => function ($query) {
                $query->with(['Taller', 'Vehiculo', 'Servicio.tipoServicio']);
            },
            'latestSalida.salida', // Usamos la nueva relación optimizada
            'servicioMaterial',
            // Usamos los nombres de relación que ya tienes en los modelos
            //'latestBoletaPayment.boletaarchivo',
            //'latestContadoPayment.contadoarchivo',
        ])
        ->where('idTipoMaterial', $this->tipoSeleccionado)
        ->where('grupo', $this->grupoSeleccionado);

        // Solo filtrar por año si el tipo seleccionado no es 2 (chip)
        if ($this->anioSeleccionado && $this->tipoSeleccionado != 2) {
            $query->where('añoActivo', $this->anioSeleccionado);
        }

        $this->materiales = $query->orderBy('numSerie', 'asc')->get();
    }
    /*public function updatedGrupoSeleccionado()
    {
        $materialesBase = Material::with([
            'Inspector',
            'detalleSalidas.salida',
            'servicioMaterial:id,idMaterial,idCertificacion',
            'certificaciones:id,externo,idTaller,idVehiculo',
            'certificaciones.Taller:id,nombre',
            'certificaciones.Vehiculo:id,placa',
        ])
            ->where('idTipoMaterial', $this->tipoSeleccionado)
            ->where('grupo', $this->grupoSeleccionado)
            ->orderBy('numSerie', 'asc')
            ->get();

        // Cachea boletas válidas (estado = 1)
        $boletasValidas = Boleta::where('estado', 1)->pluck('id')->toArray();

        // Cachea boleta_servicio (una vez)
        $boletaServicio = DB::table('boleta_servicio')->get();

        // Cachea boleta_archivos agrupados por boleta_id
        $archivos = BoletaArchivo::select('boleta_id', 'created_at')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('boleta_id');

        // Recorrer materiales para inyectar data "manual" (evita accessors)
        foreach ($materialesBase as $material) {
            $idCert = $material->servicioMaterial->idCertificacion ?? null;

            if ($idCert) {
                // Buscar boleta_id desde boleta_servicio
                foreach ($boletaServicio as $registro) {
                    $servicios = json_decode($registro->servicio_ids, true);
                    if (!is_array($servicios)) continue;

                    foreach ($servicios as $entrada) {
                        if (str_starts_with($entrada, 'App\\Models\\Certificacion =>')) {
                            [$_, $idsTexto] = explode('=>', $entrada);
                            $ids = array_map('trim', explode(',', $idsTexto));
                            if (in_array((string) $idCert, $ids)) {
                                $boletaId = $registro->boleta_id;

                                if (in_array($boletaId, $boletasValidas)) {
                                    $material->fecha_pago = optional($archivos[$boletaId]->first())->created_at;
                                }

                                break 2; // Salir de ambos foreach si ya encontró
                            }
                        }
                    }
                }
            }

            // Extra info sin usar accessors:
            $material->taller_nombre = optional($material->certificaciones->first()?->Taller)->nombre;
            $material->tipo_origen = optional($material->certificaciones->first())->externo === 1 ? 'EXTERNO' : 'TALLER';
            $material->placa_texto = optional($material->certificaciones->first()?->Vehiculo)->placa;
            $material->fecha_entrega_real = optional($material->detalleSalidas->first()?->salida)->created_at;
        }

        $this->materiales = $materialesBase;
    }*/

    public function render()
    {
        return view('livewire.control-formatos');
    }

    public function exportarExcelFormatos($datae)
    {
        return Excel::download(new ControlFormatosExport($datae), 'control-formatos.xlsx');
    }
}
