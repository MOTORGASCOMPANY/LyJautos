<?php

namespace App\Http\Livewire;

use App\Models\PlanillaDetalle;
use Livewire\WithPagination;
use Carbon\Carbon;
use Livewire\Component;

class Planillas extends Component
{
    use WithPagination;

    public $periodoSeleccionado;
    public $cant = 20, $sort = 'created_at', $direction = 'desc';

    public $openEdit = false;
    public $detalleId;
    //public $sueldo_base, $horas_extras, $otros, $pasajes, $descuentos, $total_pago, $observacion, $taller, $planilla;
        
    public $form = [
        'sueldo_base' => null,
        'horas_extras' => null,
        'otros' => null,
        'pasajes' => null,
        'descuentos' => null,
        'total_pago' => null,
        'observacion' => null,
        'taller' => null,
        'planilla' => null,
    ];

    protected $listeners = ['planillaCreada' => 'render'];

    public function updatingPeriodoSeleccionado()
    {
        $this->resetPage();
    }

    public function order($sort)
    {
        if ($this->sort == $sort) {
            $this->direction = $this->direction === 'desc' ? 'asc' : 'desc';
        } else {
            $this->sort = $sort;
            $this->direction = 'asc';
        }
    }

    public function togglePago($detalleId)
    {
        $detalle = PlanillaDetalle::find($detalleId);

        if ($detalle) {
            $detalle->pagado = !$detalle->pagado;
            $detalle->fecha_pago = $detalle->pagado ? Carbon::now() : null;
            $detalle->save();
        }
    }

    public function edit($id)
    {
        $detalle = PlanillaDetalle::findOrFail($id);
        $this->detalleId = $detalle->id;

        $this->form = $detalle->only([
            'sueldo_base',
            'horas_extras',
            'otros',
            'pasajes',
            'descuentos',
            'total_pago',
            'observacion',
            'taller',
            'planilla',
        ]);

        $this->openEdit = true;
    }

    public function update()
    {
        $this->validate([
            'form.sueldo_base' => 'required|numeric|min:0',
            'form.horas_extras' => 'nullable|numeric|min:0',
            'form.otros' => 'nullable|numeric|min:0',
            'form.pasajes' => 'nullable|numeric|min:0',
            'form.descuentos' => 'nullable|numeric|min:0',
            'form.total_pago' => 'required|numeric|min:0',
            'form.observacion' => 'nullable|string',
            'form.taller' => 'nullable|string|max:255',
            'form.planilla' => 'nullable|string|max:255',
        ]);

        PlanillaDetalle::findOrFail($this->detalleId)->update($this->form);

        $this->reset(['openEdit', 'detalleId', 'form']);
        //$this->emitSelf('render');
        $this->emit("minAlert", ["titulo" => "BUEN TRABAJO!", "mensaje" => "Planilla actualizada correctamente", "icono" => "success"]);
    }

    public function updatedForm($value, $key)
    {
        // Campos que deben disparar el recalculo
        if (in_array($key, ['sueldo_base', 'horas_extras', 'otros', 'pasajes', 'descuentos'])) {
            $sueldo_base = floatval($this->form['sueldo_base'] ?? 0);
            $horas_extras = floatval($this->form['horas_extras'] ?? 0);
            $otros = floatval($this->form['otros'] ?? 0);
            $pasajes = floatval($this->form['pasajes'] ?? 0);
            $descuentos = floatval($this->form['descuentos'] ?? 0);

            $total = $sueldo_base + $horas_extras + $otros + $pasajes - $descuentos;

            $this->form['total_pago'] = number_format($total, 2, '.', '');
        }
    }


    public function render()
    {
        $detalles = collect();
        $totalPlanilla = 0;

        if ($this->periodoSeleccionado) {
            $detalles = PlanillaDetalle::with(['contrato.empleado', 'usuario'])
                ->where('periodo', $this->periodoSeleccionado)
                ->orderBy($this->sort, $this->direction)
                ->paginate($this->cant);

            $totalPlanilla = PlanillaDetalle::where('periodo', $this->periodoSeleccionado)
                ->sum('total_pago');
        }

        // lista de periodos disponibles para el filtro
        $periodos = PlanillaDetalle::select('periodo')
            ->distinct()
            ->orderBy('periodo', 'desc')
            ->pluck('periodo');

        return view('livewire.planillas', compact('detalles', 'periodos', 'totalPlanilla'));
    }
}
