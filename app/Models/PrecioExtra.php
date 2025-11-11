<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecioExtra extends Model
{
    use HasFactory;

    protected $table = 'precios_extra';

    protected $fillable = [
        'idUsers',
        'idTipoServicio',
        'precio',
        'estado',
    ];


    public function inspector(){
        return $this->belongsTo(User::class,'idUsers');
    }

    
    public function tipoServicio(){
        return $this->belongsTo(TipoServicio::class,'idTipoServicio');
    }

}
