<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoEmpleadoUser extends Model
{
    use HasFactory;

    protected $table="documentoempleado_user"; // tabla intermedia entre contrato_trabajo y documentoempleado

    public $fillable=[
        "idDocumentoEmpleado", // relacion con documentoempleado
        "idUser", // relacion con contrato_trabajo
        "estado",
    ];
}
