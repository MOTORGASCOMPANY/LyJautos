<?php

namespace App\Services;

use App\Models\Candado;
use Carbon\Carbon;

class CandadoService
{
    /*public function validarRangoDias($fechaCreacion)
    {
        $fechaCreacion = Carbon::parse($fechaCreacion);
        $fechaActual = now();

        return $fechaCreacion->diffInDays($fechaActual) <= 7;
    }*/

    public function validarRangoDias($fechaCreacion)
    {
        // Verificar si el candado está activado
        $candado = Candado::where('nombre', 'candado_7_dias')->first();

        if (!$candado || $candado->valor == 0) {
            // Si el candado no existe o está desactivado, no validar nada
            return true;
        }

        // Validar si está dentro del rango de 7 días
        $fechaCreacion = Carbon::parse($fechaCreacion);
        $fechaActual = now();

        return $fechaCreacion->diffInDays($fechaActual) <= 10;
    }
}
