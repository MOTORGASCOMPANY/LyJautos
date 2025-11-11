<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoImagen extends Model
{
    use HasFactory;

    protected $table = 'tipos_imagen';

    protected $fillable = [
        'codigo',
        'descripcion',
    ];

    public function imagenes()
    {
        return $this->hasMany(Imagen::class, 'tipo_imagen_id');
    }
}
