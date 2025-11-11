<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoletaArchivo extends Model
{
    use HasFactory;
    protected $table = 'boleta_archivos';

    protected $fillable =
    [
        'boleta_id',
        'nombre',
        'ruta',
        'extension',
        'migrado',
    ];

    public function boleta(): BelongsTo
    {
        return $this->belongsTo(Boleta::class, 'boleta_id');
    }
}
