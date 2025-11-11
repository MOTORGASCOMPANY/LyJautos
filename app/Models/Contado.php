<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contado extends Model
{
    use HasFactory;
    protected $table = 'contado';
    protected $fillable = ['idSalida', 'estado', 'precio', 'pagado', 'observacion', 'created_at', 'updated_at'];

    public function salida()
    {
        return $this->belongsTo(Salida::class, 'idSalida');
    }
    public function contadoarchivo()
    {
        return $this->hasMany(ContadoArchivo::class, 'idContado', 'id');
    }
    public function scopeRangoFecha(Builder $query, string $desde, string $hasta): void
    {
        if ($desde && $hasta) {
            $query->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59']);
        }
    }
}
