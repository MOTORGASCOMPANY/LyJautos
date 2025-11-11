<?php

namespace App\Http\Livewire;

use App\Models\ContratoTrabajo;
use Livewire\Component;
use Livewire\WithPagination;

class DetalleVacaciones extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 20;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $contratos = ContratoTrabajo::with(['empleado', 'vacaciones'])
            ->where(function ($q) {
                $q->whereNull('cont_externo')
                  ->orWhere('cont_externo', 0);
            })
            
            ->whereHas('empleado', function ($q) {
                $q->whereDoesntHave('roles', fn($role) => 
                    $role->where('name', 'Inhabilitar')
                );
            })

            ->when($this->search, function ($q) {
                $q->whereHas('empleado', function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%");
                });
            })
            ->paginate($this->perPage);

        return view('livewire.detalle-vacaciones', compact('contratos'));
    }
}
