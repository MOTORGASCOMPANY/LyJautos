<?php

namespace App\Http\Livewire;

use App\Models\Candado;
use Livewire\Component;

class ConfiguracionCandado extends Component
{
    public $candadoActivo;

    public function mount()
    {
        // Cargar el estado inicial del candado
        $configuracion = Candado::where('nombre', 'candado_7_dias')->first();
        $this->candadoActivo = $configuracion ? $configuracion->valor : 1;
    }

    public function actualizarEstado()
    {
        // Actualizar el estado del candado en la base de datos
        Candado::updateOrCreate(
            ['nombre' => 'candado_7_dias'],
            ['valor' => $this->candadoActivo]
        );

        // Emitir una notificación de éxito
        $this->emit('CustomAlert', [
            'titulo' => 'Configuración actualizada',
            'mensaje' => 'El estado del candado se ha actualizado correctamente.',
            'icono' => 'success',
        ]);
    }


    public function render()
    {
        return view('livewire.configuracion-candado');
    }
}
