<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Boleta extends Model
{
    use HasFactory;
    protected $table = 'boleta';

    protected $fillable =
    [
        'taller',
        'certificador',
        'fechaInicio',
        'fechaFin',
        'monto',
        'monto_pagado',
        'observacion',
        'anual',
        'duplicado',
        'inicial',
        'desmonte',
        'identificador',
        'auditoria', // 0 = Por revisar, 1 = Aprovado (Auditado) , nuevo lodigca 2 = Rechazado
        'estado' // 0 = Pendiente, 1 = Completo con vaucher
    ];

    
    /*public function taller(): BelongsTo   
    {
        return $this->belongsTo(Taller::class, 'idTaller');
    }*/

    public function Taller()
    {
        return $this->belongsTo(Taller::class, 'taller');
    }

    public function Certificador()
    {
        return $this->belongsTo(User::class, 'certificador');
    }


    public function boletaarchivo()
    {
        return $this->hasMany(BoletaArchivo::class, 'boleta_id', 'id');
    }

    public function boletaServicios()
    {
        return $this->hasMany(BoletaServicio::class, 'boleta_id');
    }

    public function scopeRangoFecha(Builder $query, string $desde, string $hasta): void
    {   
        if ($desde && $hasta) {            
            $query->where('fechaInicio', '>=', $desde.' 00:00:00')
                  ->where('fechaFin', '<=', $hasta.' 23:59:59');
        }       
    }
    public function scopeRangoFecha2(Builder $query, string $desde, string $hasta): void
    {   
        if ($desde && $hasta) {            
            $query->where('fechaInicio', '=', $desde)
                  ->where('fechaFin', '=', $hasta);
        }          
    }

    public function scopeTalleres(Builder $query, $search): void
    {   
        $nombres = [];

        if (is_string($search)) {
            $search = explode(',', $search); // Convertir la cadena en un array
        }

        if (is_array($search)) {
            foreach ($search as $id) {
                $taller = Taller::find($id);
                if ($taller) {
                    $nombres[] = $taller->nombre;
                }
            }

            if (!empty($nombres)) {
                $query->whereIn('taller', $nombres);
            }
        }
    }

    public function scopeInspectores(Builder $query, $search): void
    {   
        $nombres = [];

        if (is_string($search)) {
            $search = explode(',', $search); // Convertir la cadena en un array
        }

        if (is_array($search)) {
            foreach ($search as $id) {
                $inspector = User::find($id);
                if ($inspector) {
                    $nombres[] = $inspector->name;
                }
            }

            if (!empty($nombres)) {
                $query->whereIn('certificador', $nombres);
            }
        }
    }

     // Aquí agregamos el método para establecer valores por defecto si son null
     public function setDefaultsIfNeeded()
     {
         $this->desmonte = $this->desmonte ?? 0;
         $this->anual = $this->anual ?? 0;
         $this->duplicado = $this->duplicado ?? 0;
         $this->inicial = $this->inicial ?? 0;
     }
 
     // Método de búsqueda optimizado
     public function scopeSearchByTallerOrCertificador($query, $search)
     {
         $query->where(function ($q) use ($search) {
             $q->whereHas('taller', function ($q2) use ($search) {
                 $q2->where('nombre', 'like', '%' . $search . '%');
             })->orWhereHas('certificador', function ($q3) use ($search) {
                 $q3->where('name', 'like', '%' . $search . '%');
             });
         });
     }
    
}
