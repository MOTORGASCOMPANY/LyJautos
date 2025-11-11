<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ContratoTrabajo;
use App\Models\PlanillaDetalle;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CrearPlanilla extends Component
{
    public $open = false; // para controlar el modal
    public $periodo;
    public $inspectores = [];
    public $editable = [];

    public $searchInspector = ''; // campo para buscar
    public $resultados = []; // resultados de búsqueda

    protected $listeners = ['abrirCrearPlanilla' => 'abrir'];

    public function abrir()
    {
        $this->reset(['periodo', 'inspectores', 'editable', 'searchInspector', 'resultados']);
        $this->open = true;
    }

    // --- Buscador simple por name en users ---
    public function updatedSearchInspector()
    {
        if (strlen($this->searchInspector) > 2) {
            $this->resultados = User::where('name', 'like', "%{$this->searchInspector}%")
                ->limit(10)
                ->get();
        } else {
            $this->resultados = [];
        }
    }

    // --- Agregar inspector manual (sin contrato) ---
    public function agregarInspector($id)
    {
        $user = User::find($id);
        if (!$user) return;

        // 1) evitar duplicados por user_id
        foreach ($this->editable as $r) {
            if (isset($r['user_id']) && $r['user_id'] == $user->id) {
                // ya agregado -> salimos (puedes emitir notificación si quieres)
                $this->emit('minAlert', ["titulo" => "Atención", "mensaje" => "Inspector ya agregado.", "icono" => "warning"]);
                $this->searchInspector = '';
                $this->resultados = [];
                return;
            }
        }

        // 2) añadimos
        $this->editable[] = [
            'contrato_id'  => null,
            'user_id'      => $user->id,
            'nombre'       => $user->name,
            'sueldo_base'  => 0,
            'horas_extras' => 0,
            'otros'        => 0,
            'pasajes'      => 0,
            'descuentos'   => 0,
            'observacion'  => '',
            'total_pago'   => 0,
        ];

        // 3) recalcular la ultima fila añadida
        $index = count($this->editable) - 1;
        $this->recalcular($index);

        // 4) limpiar buscador
        $this->searchInspector = '';
        $this->resultados = [];
    }

    // quitar fila (usado para inspectores manuales)
    public function quitarInspector($index)
    {
        if (!isset($this->editable[$index])) return;
        array_splice($this->editable, $index, 1);
    }

    // --- Al cambiar periodo: cargar contratos + mantener manuales ---    
    public function updatedPeriodo()
    {
        // contratos válidos
        $contratos = ContratoTrabajo::where(function ($q) {
            $q->whereNull('cont_externo')
                ->orWhere('cont_externo', 0);
        })
            ->whereHas('empleado', function ($q) {
                // Excluimos rol Inhabilitar
                $q->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'Inhabilitar');
                });
            })
            ->with('empleado')
            ->get()
            ->sortBy(fn($contrato) => optional($contrato->empleado)->name);

        $contractRows = $contratos->map(function ($contrato) {
            return [
                'contrato_id'  => $contrato->id,
                'user_id'      => $contrato->idUser, // importante para evitar duplicados
                'nombre'       => optional($contrato->empleado)->name,
                'sueldo_base'  => (float) ($contrato->sueldo_neto / 2),
                'horas_extras' => 0,
                'otros'        => 0,
                'pasajes'      => 0,
                'descuentos'   => 0,
                'observacion'  => '',
                'total_pago'   => (float) ($contrato->sueldo_neto / 2),
            ];
        })->values()->toArray();

        // conservar inspectores manuales previos (los que tienen user_id y sin contrato),
        // pero evitando duplicar si el usuario ya aparece en contractRows
        $manualRows = [];
        $contractUserIds = array_column($contractRows, 'user_id');

        foreach ($this->editable as $r) {
            if (!empty($r['user_id']) && empty($r['contrato_id'])) {
                if (!in_array($r['user_id'], $contractUserIds)) {
                    $manualRows[] = $r;
                }
            }
        }

        $this->inspectores = $contractRows;
        // Unión: contratos primero, luego manuales
        $this->editable = array_merge($contractRows, $manualRows);
    }

    // --- recalcular total (maneja strings/vacíos) ---    
    public function recalcular($index)
    {
        if (!isset($this->editable[$index])) return;

        $row = $this->editable[$index];

        $sueldoBase = (float) ($row['sueldo_base'] ?? 0);
        $horas = (float) ($row['horas_extras'] ?? 0);
        $otros = (float) ($row['otros'] ?? 0);
        $pasajes = (float) ($row['pasajes'] ?? 0);
        $descuentos = (float) ($row['descuentos'] ?? 0);

        $this->editable[$index]['total_pago'] = $sueldoBase + $horas + $otros + $pasajes - $descuentos;
    }

    public function save()
    {
        $this->validate([
            'periodo' => 'required|date',
        ]);

        DB::transaction(function () {
            foreach ($this->editable as $row) {
                PlanillaDetalle::create([
                    'contrato_id'  => $row['contrato_id'] ?? null,   // puede ser null
                    'user_id'      => $row['user_id'] ?? null,       // para inspectores apoyo eventual
                    'periodo'      => $this->periodo,
                    'sueldo_base'  => $row['sueldo_base'],
                    'horas_extras' => $row['horas_extras'],
                    'otros'        => $row['otros'],
                    'pasajes'      => $row['pasajes'],
                    'descuentos'   => $row['descuentos'],
                    'observacion'  => $row['observacion'],
                    'total_pago'   => $row['total_pago'],
                ]);
            }
        });

        $this->emitUp('planillaCreada');
        $this->emit("minAlert", ["titulo" => "BUEN TRABAJO!", "mensaje" => "Planilla creada correctamente.", "icono" => "success"]);
        $this->reset(['periodo', 'inspectores', 'editable', 'searchInspector', 'resultados', 'open']);
    }

    public function render()
    {
        return view('livewire.crear-planilla');
    }
}


/*public function updatedPeriodo()
    {
        $this->inspectores = ContratoTrabajo::where(function ($q) {
            $q->whereNull('cont_externo')
                ->orWhere('cont_externo', 0);
        })
            ->whereHas('empleado', function ($q) {
                // Excluimos usuarios con rol Inhabilitar
                $q->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'Inhabilitar');
                });
            })
            ->with('empleado') // cargamos la relación
            ->get()
            ->sortBy(fn($contrato) => optional($contrato->empleado)->name) // ordenamos por nombre
            ->map(function ($contrato) {
                return [
                    'contrato_id'  => $contrato->id,
                    'nombre'       => optional($contrato->empleado)->name,
                    'sueldo_base'  => $contrato->sueldo_neto / 2,
                    'horas_extras' => 0,
                    'otros'        => 0,
                    'pasajes'      => 0,
                    'descuentos'   => 0,
                    'observacion'  => '',
                    'total_pago'   => $contrato->sueldo_neto / 2,
                ];
            })->values() // reindexamos los keys
            ->toArray();

        $this->editable = $this->inspectores;
    }
*/


/*public function recalcular($index)
    {
        $row = $this->editable[$index];

        $this->editable[$index]['total_pago'] =
            (float) ($row['sueldo_base'] ?? 0) +
            (float) ($row['horas_extras'] ?? 0) +
            (float) ($row['otros'] ?? 0) +
            (float) ($row['pasajes'] ?? 0) -
            (float) ($row['descuentos'] ?? 0);
    }
*/