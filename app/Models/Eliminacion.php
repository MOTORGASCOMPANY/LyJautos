<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eliminacion extends Model
{
    use HasFactory;
    
    protected $table = 'eliminacion';

    public $fillable=[
        "id",
        "placa",
        "numSerie",
        "tipoServicio",
    ];
}
