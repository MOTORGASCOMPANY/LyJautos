<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Imagen extends Model
{
    use HasFactory;

    protected $table = 'imagenes';

    protected $fillable =
    [
        'nombre',
        'ruta',
        'extension',
        'estado',
        'Expediente_idExpediente',
        'migrado',
        'tipo_imagen_id',
        'confidence', // porcentaje de IA
        'clasificado_por_ia', // 0 = manual , 1 = clasificado IA
    ];

    protected $appends = ['url'];

    public function Expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'Expediente_idExpediente');
    }

    public function TipoImagen(): BelongsTo
    {
        return $this->belongsTo(TipoImagen::class, 'tipo_imagen_id');
    }    

    public function getUrlAttribute()
    {
        if ($this->migrado == 1) {
            return Storage::disk('do')->temporaryUrl($this->ruta, now()->addMinutes(5));
        }
        return Storage::url($this->ruta);
    }


    public function anulacion(): BelongsTo
    {
        return $this->belongsTo(Anulacion::class, 'Expediente_idExpediente');
    }
}
