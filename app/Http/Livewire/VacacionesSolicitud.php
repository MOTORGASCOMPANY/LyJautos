<?php

namespace App\Http\Livewire;

use App\Models\Vacacion;
use App\Models\VacacionSolicitud;
use Livewire\Component;
use Carbon\Carbon;

class VacacionesSolicitud extends Component
{
    public $contratoId, $vacaciones;
    public $idVacacion, $f_inicio_deseado, $f_termino_deseado, $comentario;
    public $addSolicitud = false;

    public function mount($contratoId)
    {
        $this->contratoId = $contratoId;
        $this->vacaciones = Vacacion::where('idContrato', $this->contratoId)->first();
    }

    public function render()
    {
        return view('livewire.vacaciones-solicitud');
    }

    public function solicitarVacacion()
    {
        $this->validate([
            'f_inicio_deseado' => 'required|date',
            'f_termino_deseado' => 'required|date|after_or_equal:f_inicio_deseado',
            'comentario' => 'nullable|string|max:500',
        ], [
            'f_inicio_deseado.required' => 'Debe ingresar la fecha de inicio deseada.',
            'f_termino_deseado.required' => 'Debe ingresar la fecha de término deseada.',
            'f_termino_deseado.after_or_equal' => 'La fecha de término no puede ser antes del inicio.',
        ]);

        if (!$this->vacaciones) {
            $this->emit("minAlert", [
                "titulo" => "Error",
                "mensaje" => "No se encontró el registro de vacaciones del empleado.",
                "icono" => "error"
            ]);
            return;
        }

        VacacionSolicitud::create([
            'idVacacion' => $this->vacaciones->id,
            'f_inicio_deseado' => Carbon::parse($this->f_inicio_deseado),
            'f_termino_deseado' => Carbon::parse($this->f_termino_deseado),
            'comentario' => $this->comentario,
        ]);

        $this->reset(['f_inicio_deseado', 'f_termino_deseado', 'comentario', 'addSolicitud']);

        $this->emit("minAlert", [
            "titulo" => "¡EXCELENTE TRABAJO!",
            "mensaje" => "La solicitud se registró correctamente.",
            "icono" => "success"
        ]);

        $this->emitUp("refrescaEmpleado");
    }
}
