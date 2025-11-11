<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanillaArchivo extends Model
{
    use HasFactory;
    protected $table = 'planilla_archivos';

    protected $fillable = [
        'planilla_detalle_id',
        'tipo', // ENUM('boleta', 'comprobante') DEFAULT 'boleta'
        'nombre',
        'ruta',
        'extension',
    ];

    public function detalle()
    {
        return $this->belongsTo(PlanillaDetalle::class, 'planilla_detalle_id');
    }
}
