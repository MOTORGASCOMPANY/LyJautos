<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanillaDetalle extends Model
{
    use HasFactory;
    protected $table = 'planilla_detalles';

    protected $fillable = [
        'contrato_id',
        'user_id',
        'periodo',
        'sueldo_base',
        'horas_extras',
        'pasajes',
        'otros',
        'descuentos',
        'observacion',
        'total_pago',
        'taller',
        'planilla',
        'pagado', // 1 = pago realizado, 0 = pendiente
        'fecha_pago', // fecha en que se realizo el pago
    ];

    protected $casts = [
        'pagado' => 'boolean',
        'fecha_pago' => 'datetime',
    ];

    public function contrato()
    {
        return $this->belongsTo(ContratoTrabajo::class, 'contrato_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function archivos()
    {
        return $this->hasMany(PlanillaArchivo::class, 'planilla_detalle_id');
    }

}
