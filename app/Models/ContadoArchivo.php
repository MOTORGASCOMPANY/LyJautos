<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContadoArchivo extends Model
{
    use HasFactory;
    protected $table = 'contado_archivo';

    protected $fillable =
    [
        'idContado',
        'nombre',
        'ruta',
        'extension',
        'migrado',
    ];

    public function contado(): BelongsTo
    {
        return $this->belongsTo(Contado::class, 'idContado');
    }
}
