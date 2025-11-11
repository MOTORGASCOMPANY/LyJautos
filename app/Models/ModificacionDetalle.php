<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModificacionDetalle extends Model
{
    use HasFactory;
    protected $table = 'modificacion_detalle';

    protected $fillable = [
        'certificacion_id',
        'tipo_modificacion',
    ];

    public function certificacion()
    {
        return $this->belongsTo(Certificacion::class);
    }
}
