<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CertificacionTemporal extends Model
{
    use HasFactory;

    protected $table = "certificados_temporales";

    protected $fillable =
    [
        'idTaller',
        'idServicio',
        'idInspector',
        'placa',
        'numSerie',
        'precio',
        'externo', // 0 = cert de taller ; 1 = cert de inspector
        'estado',        
        'pagado',
    ];

    // Relaciones
    public function taller()
    {
        return $this->belongsTo(Taller::class, 'idTaller');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'idServicio');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'idInspector');
    }

    // Accesores
    public function getPrecioFormateadoAttribute()
    {
        return number_format($this->precio, 2);
    }


    //Scope para reporte 
    public function scopeIdTalleres($query, $search): void
    {
        if ($search) {
            $query->whereHas('Taller', function (Builder $query) use ($search) {
                $query->whereIn('id', $search);
            });
        }
    }

    public function scopeIdInspectores(Builder $query, $search): void
    {
        if ($search) {
            $query->whereIn('idInspector', $search);
        }
    }

    public function scopeRangoFecha(Builder $query, string $desde, string $hasta): void
    {
        if ($desde && $hasta) {
            $query->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59']);
        }
    }

    public function scopeIdTipoServicios($query, $search): void
    {
        if ($search) {
            $query->whereHas('Servicio', function (Builder $query) use ($search) {
                $query->where('tipoServicio_idtipoServicio', $search);
            });
        }
    }

    // Método para certificar GLP
    public static function certificarGlp(Taller $taller, Servicio $servicio, User $inspector, $placa, $numSerie)
    {
        // Determinar si el certificado es externo o no
        $externo = ($inspector->externo == 1) ? 1 : 0;

        // Condición para jalar el precio de la tabla precios_inspector si el inspector es externo
        if ($inspector->externo === 1) {
            $precio = PrecioInspector::where([
                ['idServicio', $servicio->TipoServicio->id],
                ['idUsers', $inspector->id]
            ])->first()->precio ?? 0;
        } else {
            // Si el inspector no es externo, se usa el precio del servicio.
            $precio = $servicio->precio;
        }

        $cert = self::create([
            "idTaller"    => $taller->id,
            "idInspector" => $inspector->id,
            "idServicio"  => $servicio->id,
            "placa"       => $placa,
            "numSerie"    => $numSerie,
            "precio"      => $precio,
            "externo"     => $externo,
            "estado"      => 1,
            "pagado"      => 0,
        ]);

        return $cert ?: null;
    }
}
