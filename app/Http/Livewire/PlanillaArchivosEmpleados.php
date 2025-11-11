<?php

namespace App\Http\Livewire;

use App\Models\PlanillaDetalle;
use Livewire\Component;

class PlanillaArchivosEmpleados extends Component
{
    public $periodoSeleccionado;
    public $detalles;

    public function mount()
    {
        $userId = auth()->id();

        // Buscar el último periodo de planilla para este usuario (contrato o apoyo eventual)
        $this->periodoSeleccionado = PlanillaDetalle::where(function ($query) use ($userId) {
            $query->where('user_id', $userId) // apoyo eventual
                ->orWhereHas('contrato', function ($q) use ($userId) {
                    $q->where('idUser', $userId); // empleados con contrato
                });
        })
            ->max('periodo');
    }

    /*public function render()
    {
        $userId = auth()->id();

        $this->detalles = PlanillaDetalle::with('archivos')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('contrato', function ($q) use ($userId) {
                        $q->where('idUser', $userId);
                    });
            })
            ->where('periodo', $this->periodoSeleccionado)
            ->get();

        $periodos = PlanillaDetalle::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhereHas('contrato', function ($q) use ($userId) {
                    $q->where('idUser', $userId);
                });
        })
            ->distinct()
            ->orderBy('periodo', 'desc')
            ->pluck('periodo');

        return view('livewire.planilla-archivos-empleados', compact('periodos'));
    }*/

    public function render()
    {
        $userId = auth()->id();

        // Todos los periodos disponibles para este usuario
        $periodos = PlanillaDetalle::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhereHas('contrato', function ($q) use ($userId) {
                    $q->where('idUser', $userId);
                });
        })
            ->distinct()
            ->orderBy('periodo', 'desc')
            ->pluck('periodo');

        // Si no hay ningún periodo, el usuario nunca tendrá planillas
        if ($periodos->isEmpty()) {
            $this->detalles = collect(); // vacío
            return view('livewire.planilla-archivos-empleados', [
                'periodos' => $periodos,
                'sinPlanilla' => true
            ]);
        }

        // Si hay periodos pero aún no se ha seleccionado, ponemos el más reciente
        if (!$this->periodoSeleccionado) {
            $this->periodoSeleccionado = $periodos->first();
        }

        // Cargar detalles del periodo actual
        $this->detalles = PlanillaDetalle::with('archivos')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('contrato', function ($q) use ($userId) {
                        $q->where('idUser', $userId);
                    });
            })
            ->where('periodo', $this->periodoSeleccionado)
            ->get();

        return view('livewire.planilla-archivos-empleados', [
            'periodos' => $periodos,
            'sinPlanilla' => false
        ]);
    }
}
