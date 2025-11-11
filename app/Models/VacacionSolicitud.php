<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacacionSolicitud extends Model
{
    use HasFactory;

    protected $table = 'vacacion_solicitud';

    protected $fillable = [
        'idVacacion',
        'f_inicio_deseado',
        'f_termino_deseado',
        'comentario',
    ];

    public function vacacion()
    {
        return $this->belongsTo(Vacacion::class, 'idVacacion');
    }
}
