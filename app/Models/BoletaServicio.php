<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoletaServicio extends Model
{
    use HasFactory;
    protected $table = 'boleta_servicio';
    protected $fillable = ['boleta_id', 'servicio_ids'];
    protected $casts = [
        'servicio_ids' => 'array', // Convierte el campo servicio_ids a un array cuando sea necesario
    ];

    public function boleta()
    {
        return $this->belongsTo(Boleta::class, 'boleta_id');
    }
}
