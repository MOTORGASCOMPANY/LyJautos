<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $table="equipo";

    public $fillable=[
        "id",
        "idTipoEquipo",
        "numSerie",
        "marca",
        "modelo",
        "capacidad",
        "fechaFab",
        "peso",
        "claseGnv",
        "fechaCaducidad",
        "fechaCaducidad_aplica", // 1 = Aplica fechaCaducidad , 0 = El equipo no tiene fechaCaducidad "NE"
        "combustible",
        "created_at",
        "updated_at"
    ];

    public function tipo(){
        return $this->belongsTo(TipoEquipo::class,'idTipoEquipo');
    }

}
