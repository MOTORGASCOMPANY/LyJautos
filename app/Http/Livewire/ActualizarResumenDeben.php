<?php

namespace App\Http\Livewire;

use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\Desmontes;
use App\Models\ServiciosImportados;
use App\Models\Taller;
use App\Models\TipoServicio;
use App\Models\User;
use App\Services\DataService;
use Livewire\Component;

class ActualizarResumenDeben extends Component
{
    public $fechaInicio, $fechaFin, $talleres, $inspectores, $tipos;
    public $ins = [], $taller = [], $servicio;
    public $certificaciones;
    public $selectedRows = [];
    public $selectAll = false;

    protected $dataService;

    protected $rules = [
        "fechaInicio" => 'required|date',
        "fechaFin" => 'required|date',
    ];

    public function __construct()
    {
        parent::__construct(); // Asegúrate de llamar al constructor de la clase padre
        $this->dataService = app(DataService::class); // Inyección de servicio
    }

    public function mount()
    {
        $this->inspectores = User::role(['inspector', 'supervisor'])->orderBy('name')->get();
        $this->talleres = Taller::orderBy('nombre')->get();
        $this->tipos = TipoServicio::all();
    }

    public function render()
    {
        return view('livewire.actualizar-resumen-deben');
    }

    public function procesar()
    {
        $this->validate();
        if ($this->fechaInicio > $this->fechaFin) {
            $this->emit("minAlert", ["titulo" => "Error de Fechas", "mensaje" => "La fecha de inicio no puede ser mayor que la fecha final.", "icono" => "error"]);
            return;
        }

        $datos = $this->dataService->procesar($this->ins, $this->taller, $this->servicio, $this->fechaInicio, $this->fechaFin);
        // Filtra las certificaciones con 'pagado' igual a 0
        $this->certificaciones = $datos->where('pagado', 0);
        //dd($this->certificaciones);

    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = $this->certificaciones->pluck('id')->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function actualizarPagado()
    {
        // Validar que haya filas seleccionadas
        if (empty($this->selectedRows)) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No se han seleccionado filas para actualizar.", "icono" => "warning"]);
            return;
        }

        try {
            // Procesar las IDs seleccionadas
            foreach ($this->selectedRows as $id) {
                // Buscar en los modelos y actualizar
                if ($record = CertificacionPendiente::find($id)) {
                    $record->update(['pagado' => 2]);
                } elseif ($record = Desmontes::find($id)) {
                    $record->update(['pagado' => 2]);
                } elseif ($record = ServiciosImportados::find($id)) {
                    $record->update(['pagado' => 2]);
                } elseif ($record = Certificacion::find($id)) {
                    $record->update(['pagado' => 2]);
                }
            }
            /*
            foreach ($this->certificaciones as $certificacion) {
                if (in_array($certificacion['id'], $this->selectedRows)) {
                    // Determinar el modelo correcto
                    $modelo = $certificacion['tipo_modelo'];
                    if (class_exists($modelo)) {
                        $registro = $modelo::find($certificacion['id']);
                        if ($registro) {
                            $registro->update(['pagado' => 2]);
                        }
                    }
                }
            }
            */

            // Resetear los checkboxes
            $this->selectedRows = [];
            $this->selectAll = false;

            // Mensaje de éxito
            $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Registros actualizados correctamente.", "icono" => "success"]);
        } catch (\Exception $e) {
            // Capturar cualquier error y mostrar un mensaje de alerta
            $this->emit("minAlert", [
                "titulo" => "ERROR DEL SISTEMA",
                "mensaje" => "Ocurrió un error al intentar actualizar los registros: " . $e->getMessage(),
                "icono" => "error"
            ]);
        }
    }
}


/*public function procesar()
    {
        $this->validate();
        if ($this->fechaInicio > $this->fechaFin) {
            $this->emit("minAlert", ["titulo" => "Error de Fechas", "mensaje" => "La fecha de inicio no puede ser mayor que la fecha final.", "icono" => "error"]);
            return;
        }

        $tabla = $this->generaData();
        $importados = $this->cargaServiciosGasolution();
        //TRIM PARA ELIMINAR ESPACIOS 
        $tabla = $tabla->map(function ($item) {
            $item['placa'] = trim($item['placa']);
            $item['inspector'] = trim($item['inspector']);
            $item['taller'] = trim($item['taller']);
            return $item;
        });
        $importados = $importados->map(function ($item) {
            $item['placa'] = trim($item['placa']);
            $item['inspector'] = trim($item['inspector']);
            $item['taller'] = trim($item['taller']);
            return $item;
        });

        $diferencias = $this->encontrarDiferenciaPorPlaca($importados, $tabla);
        //$this->certificaciones = $tabla->merge($diferencias);
        // Realiza el merge de las colecciones
        $merged = $tabla->merge($diferencias);
        // Filtra las certificaciones con 'pagado' igual a 0
        $this->certificaciones = $merged->where('pagado', 0);
    }
*/

/*public function generaData()
    {
        $tabla = new Collection();
        $certificaciones = Certificacion::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->IdTipoServicio($this->servicio)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            //->where('pagado', 0)
            ->whereIn('pagado', [0, 2])
            ->whereNotIn('estado', [2])
            ->get();
        $cerPendiente = CertificacionPendiente::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->IdTipoServicios($this->servicio)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            //->where('pagado', 0)
            ->get();
        $desmontes = Desmontes::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->IdTipoServicios($this->servicio)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            //->where('pagado', 0)
            ->get();

        //unificando certificaciones     
        foreach ($certificaciones as $certi) {
            $data = [
                "id" => $certi->id,
                "placa" => $certi->Vehiculo->placa,
                "taller" => $certi->Taller->nombre,
                "inspector" => $certi->Inspector->name,
                "servicio" => $certi->Servicio->tipoServicio->descripcion,
                "num_hoja" => $certi->NumHoja,
                "ubi_hoja" => $certi->UbicacionHoja,
                "precio" => $certi->precio,
                "pagado" => $certi->pagado,
                "estado" => $certi->estado,
                "externo" => $certi->externo,
                "tipo_modelo" => $certi::class,
                "fecha" => $certi->created_at,

            ];
            $tabla->push($data);
        }

        foreach ($cerPendiente as $cert_pend) {
            //modelo preliminar
            $data = [
                "id" => $cert_pend->id,
                "placa" => $cert_pend->Vehiculo->placa,
                "taller" => $cert_pend->Taller->nombre,
                "inspector" => $cert_pend->Inspector->name,
                "servicio" => 'Activación de chip (Anual)',
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $cert_pend->precio,
                "pagado" => $cert_pend->pagado,
                "estado" => $cert_pend->estado,
                "externo" => $cert_pend->externo,
                "tipo_modelo" => $cert_pend::class,
                "fecha" => $cert_pend->created_at,
            ];
            $tabla->push($data);
        }

        foreach ($desmontes as $des) {
            $data = [
                "id" => $des->id,
                "placa" => $des->placa,
                "taller" => $des->Taller->nombre,
                "representante" => $des->Taller->representante,
                "inspector" => $des->Inspector->name,
                "servicio" => $des->Servicio->tipoServicio->descripcion,
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $des->precio,
                "pagado" => $des->pagado,
                "estado" => $des->estado,
                "externo" => $des->externo,
                "tipo_modelo" => $des::class,
                "fecha" => $des->created_at,
            ];
            $tabla->push($data);
        }
        return $tabla;
    }
    public function cargaServiciosGasolution()
    {
        $disc = new Collection();

        $dis = ServiciosImportados::Talleres($this->taller)
            ->Inspectores($this->ins)
            ->TipoServicio($this->servicio)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            //->where('pagado', 0)
            ->get();

        foreach ($dis as $registro) {
            $data = [
                "id" => $registro->id,
                "placa" => $registro->placa,
                "taller" => $registro->taller,
                "inspector" => $registro->certificador,
                "servicio" => $registro->TipoServicio->descripcion,
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $registro->precio,
                "pagado" => $registro->pagado,
                "estado" => $registro->estado,
                "externo" => Null,
                "tipo_modelo" => $registro::class,
                "fecha" => $registro->fecha,
            ];
            $disc->push($data);
        }
        return $disc;
    }
    public function encontrarDiferenciaPorPlaca($lista1, $lista2)
    {
        $diferencias = [];

        foreach ($lista1 as $elemento1) {
            $placa1 = $elemento1['placa'];
            $inspector1 = $elemento1['inspector'];
            $servicio1 = $elemento1['servicio'];
            $taller1 = $elemento1['taller'];
            $encontrado = false;
            // Excluir el servicio 'Revisión anual GNV' para que no muestre como discrepancia 'Activación de chip (Anual)'
            foreach ($lista2 as $elemento2) {
                $placa2 = $elemento2['placa'];
                $inspector2 = $elemento2['inspector'];
                $servicio2 = $elemento2['servicio'];
                $taller2 = $elemento2['taller'];

                if ($placa1 === $placa2 && $inspector1 === $inspector2 && $taller1 === $taller2) {
                    if (
                        ($elemento2['tipo_modelo'] == 'App\Models\CertificacionPendiente' && $servicio1 == 'Revisión anual GNV') ||
                        ($servicio2 == 'Conversión a GNV + Chip' && $servicio1 == 'Conversión a GNV') ||
                        ($servicio2 == 'Pre-inicial GNV' && $servicio1 == 'Conversión a GNV') ||
                        ($servicio2 == 'Conversión a GNV OVERHUL' && $servicio1 == 'Conversión a GNV')
                    ) {
                        $encontrado = true;
                        break;
                    } else if ($servicio1 === $servicio2) {
                        $encontrado = true;
                        break;
                    }
                }
            }

            if (!$encontrado) {
                $diferencias[] = $elemento1;
            }
        }
        return $diferencias;
    }
*/
