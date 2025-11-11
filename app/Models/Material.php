<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Material extends Model
{
    use HasFactory;

    protected $table = "material";

    protected $fillable =
    [
        'id',
        'descripcion',
        'numSerie',
        'idUsuario',
        'stock',
        'estado',
        'ubicacion',
        'grupo',
        'idTipoMaterial',
        'añoActivo',
        'anio',
        'devuelto',
        'created_at',
        'updated_at',
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoMaterial::class, 'idTipoMaterial');
    }
    public function Inspector()
    {
        return $this->belongsTo(User::class, 'idUsuario');
    }

    //agregue esto para filtro ingreso material
    public function detalleIngresos()
    {
        return $this->hasMany(IngresoDetalle::class, 'idMaterial');
    }
    public function detalleSalidas()
    {
        return $this->hasMany(SalidaDetalle::class, 'idMaterial');
    }

    // relacion certificaciones
    public function certificaciones()
    {
        return $this->belongsToMany(Certificacion::class, 'serviciomaterial', 'idMaterial', 'idCertificacion');
    }

    public function servicioMaterial()
    {
        return $this->hasOne(ServicioMaterial::class, 'idMaterial');
    }


    // --- RELACIONES OPTIMIZADAS PARA CONTROL FORMATOS---
    public function latestSalida(): HasOne
    {
        // Usamos hasOne para simular que solo hay una salida, la más reciente
        // Esto es mucho más eficiente que cargar todos los detalleSalidas y tomar el primero.
        return $this->hasOne(SalidaDetalle::class, 'idMaterial')
                    ->with('salida') // Cargamos la relación 'salida' de forma anidada
                    ->latest('created_at');
    }

    // --- ACCESORES OPTIMIZADOS ---
    public function getPlacaAttribute()
    {
        /*if (in_array($this->estado, [4, 5]) && $this->certificaciones->isNotEmpty()) {
            return optional($this->certificaciones->first()->Vehiculo)->placa;
        }
        return null;*/

        if (!in_array($this->estado, [4, 5])) {
            return null;
        }
    
        // Verificamos si hay certificación
        $certificacion = $this->certificaciones->first();
        if (!$certificacion) {
            return null;
        }
    
        // CASO CHIP (idTipoMaterial == 2)
        if ($this->idTipoMaterial == 2) {
            $vehiculo = optional($certificacion->Vehiculo);

            // Si el vehículo asociado es el de uso general (id = 1), usamos `ubicacion` (último fragmento después de "/")
            if ($vehiculo->id == 1) {
                return Str::afterLast($this->ubicacion, '/');
            }

            // Si es otro vehículo, usamos su placa
            return $vehiculo->placa;
        }
    
        // Otros tipos de materiales
        return optional($certificacion->Vehiculo)->placa;
    }
    public function getTallerAttribute()
    {
        if (in_array($this->estado, [4, 5]) && $this->certificaciones->isNotEmpty()) {
            return optional($this->certificaciones->first()->Taller)->nombre;
        }
        return null;
    }
    public function getFechaEntregaAttribute()
    {
        // Accede a la relación ya cargada (latestSalida)
        $latestSalida = $this->latestSalida;

        // Ya no necesitas '->first()' porque latestSalida es un hasOne.
        return $latestSalida && $latestSalida->salida
            ? $latestSalida->salida->created_at
            : null;
    }
    public function getTallerOExternoAttribute()
    {
        if (in_array($this->estado, [4, 5]) && $this->certificaciones->isNotEmpty()) {
            $externo = $this->certificaciones->first()->externo;
            return ($externo === 1) ? 'EXTERNO' : 'TALLER';
        }
        return null;
    }
    public function getFechaUsoAttribute()
    {
        $cert = $this->certificaciones->first();
        return $cert?->created_at;
    }
    public function getTipoServicioAttribute()
    {
        $cert = $this->certificaciones->first();
        return $cert?->Servicio->tipoServicio->descripcion;
    }

    // --- LÓGICA DE PAGO REFACTORIZADA Y OPTIMIZADA ---
    /* Esta relación es para obtener el id de la certificación vinculada al material
    public function latestCertificacion()
    {
        return $this->hasOne(ServicioMaterial::class, 'idMaterial')->latest('created_at');
    }
    public function getFechaPagoAttribute()
    {
        // Si el material no tiene una certificación, no hay fecha de pago
        if (!$this->latestCertificacion) {
            return null;
        }
        $idCertificacion = $this->latestCertificacion->idCertificacion;

        // --- Lógica de Búsqueda Mejorada para Boleta ---
        // En lugar de usar whereHas, buscamos directamente en la tabla boleta_servicio
        // Usaremos un where raw para una búsqueda más flexible
        $boletaServicio = DB::table('boleta_servicio')
        ->where('servicio_ids', 'like', '%"App\\\Models\\\Certificacion => %' . $idCertificacion . '%"')
        ->first();

        if ($boletaServicio) {
            // Encontramos el boleta_id, ahora buscamos la boleta real
            $boleta = Boleta::where('id', $boletaServicio->boleta_id)
                ->where('estado', 1)
                ->with('boletaarchivo')
                ->first();
                
            if ($boleta && $boleta->boletaarchivo->isNotEmpty()) {
                return $boleta->boletaarchivo->last()->created_at;
            }
        }

        // Opción 2: Buscar en pagos al contado
        $idSalida = optional($this->latestSalida)->idSalida;
        if ($idSalida) {
            $contado = Contado::where('idSalida', $idSalida)
                ->where('pagado', 2)
                ->with('contadoarchivo')
                ->first();

            if ($contado && $contado->contadoarchivo->isNotEmpty()) {
                return $contado->contadoarchivo->last()->created_at;
            }
        }
        return null;
    }*/

    public function getBoletaIdAttribute()
    {
        $idCertificacion = $this->servicioMaterial->idCertificacion ?? null;

        if (!$idCertificacion) {
            return null;
        }

        $registros = DB::table('boleta_servicio')
        ->select('boleta_id', 'servicio_ids')
        ->get();

        foreach ($registros as $registro) {
            // servicio_ids es un array codificado como JSON (array de strings)
            $servicios = json_decode($registro->servicio_ids, true);

            if (is_array($servicios)) {
                foreach ($servicios as $entrada) {
                    // Solo procesar los que son del modelo Certificacion
                    if (str_starts_with($entrada, 'App\\Models\\Certificacion =>')) {
                        // Extraer los IDs
                        [$modelo, $idsTexto] = explode('=>', $entrada);
                        $ids = array_map('trim', explode(',', $idsTexto));

                        if (in_array((string)$idCertificacion, $ids)) {
                            return $registro->boleta_id;
                        }
                    }
                }
            }
        }

        return null;
    }
    //Mejorar la forma de saber que materiales fueron pagados en certificacion y al contado
    public function getFechaPagoAttribute()
    {
        // --------------------------
        // Opción 1: Pago por Boleta
        // --------------------------
        $boletaId = $this->boleta_id;

        if ($boletaId) {
            $boleta = Boleta::where('id', $boletaId)
                ->where('estado', 1) // Pago realizado
                ->first();

            if ($boleta) {
                $fechaBoleta = BoletaArchivo::where('boleta_id', $boletaId)
                    ->orderByDesc('created_at')
                    ->value('created_at');

                if ($fechaBoleta) {
                    return $fechaBoleta;
                }
            }
        }

        // --------------------------
        // Opción 2: Pago por Contado
        // --------------------------
        $idSalida = $this->detalleSalidas()
            ->latest('created_at')
            ->value('idSalida');

        if ($idSalida) {
            $contado = Contado::where('idSalida', $idSalida)
                ->where('pagado', 2) // Pago realizado
                ->first();

            if ($contado) {
                return ContadoArchivo::where('idContado', $contado->id)
                    ->orderByDesc('created_at')
                    ->value('created_at');
            }
        }

        return null;
    }




    //relacion para acceder a la placa dado que está en la tabla certificacion, a la cual se accede a través de serviciomaterial
    public function certificacion()
    {
        return $this->hasOneThrough(Certificacion::class, ServicioMaterial::class,
            'idMaterial',       // Clave foránea en serviciomaterial
            'id',               // Clave primaria en certificacion
            'id',               // Clave primaria en material
            'idCertificacion'   // Clave foránea en serviciomaterial hacia certificacion
        );
    }

    public static function formatosGnvEnStock($tipo)
    {
        $res = new Collection();
        $aux = DB::table('material')
            ->select('material.idTipoMaterial', 'tipomaterial.descripcion')
            ->join('tipomaterial', 'material.idTipoMaterial', "=", 'tipomaterial.id')
            ->where([
                ['material.idTipoMaterial', $tipo],
                ['material.estado', 1],
            ])
            ->get();
        $res = $aux;
        return $res->count();
    }

    public static function stockPorGruposGnv()
    {
        $grupos = DB::table('material')
            ->select(DB::raw('grupo as guia,count(*) as stock,min(numSerie) as minimo,Max(numSerie) as maximo'))
            ->where([
                ['estado', 1],
                ['idTipoMaterial', 1] //TIPO DE MATERIAL: FORMATO GNV
            ])
            ->groupBy('grupo')
            ->get();
        return json_encode($grupos);
    }

    public static function stockPorGruposGlp()
    {
        $grupos = DB::table('material')
            ->select(DB::raw('grupo as guia,count(*) as stock,min(numSerie) as minimo,Max(numSerie) as maximo'))
            ->where([
                ['estado', 1],
                ['idTipoMaterial', 3] //TIPO DE MATERIAL: FORMATO GLP
            ])
            ->groupBy('grupo')
            ->get();
        return json_encode($grupos);
    }

    public static function stockPorGruposModi()
    {
        $grupos = DB::table('material')
            ->select(DB::raw('grupo as guia,count(*) as stock,min(numSerie) as minimo,Max(numSerie) as maximo'))
            ->where([
                ['estado', 1],
                ['idTipoMaterial', 4] //TIPO DE MATERIAL: FORMATO MODIFICACION
            ])
            ->groupBy('grupo')
            ->get();
        return json_encode($grupos);
    }

    public static function materialPorGrupo($grupo)
    {
        $grupo = DB::table('material')
            ->select("material.*")
            ->where([
                ['estado', 1],
                ['idTipoMaterial', 1],
                ['grupo', $grupo],
            ])
            ->get();

        return $grupo;
    }

    public function scopeSearchSerieFormmato($query, $search)
    {
        if ($search) {
            return $query->where([['idTipoMaterial', 1], ['numSerie', 'like', '%' . $search . '%']]);
        }
    }

    public function scopeInspector($query, $search)
    {
        if ($search) {
            return $query->where([['idUsuario', $search]]);
        }
    }

    public function scopeTipoMaterialInvetariado($query, $search)
    {
        if ($search) {
            return $query->where([['idTipoMaterial', $search]]);
        }
    }

    public function scopeEstadoInvetariado($query, $search)
    {
        if ($search) {
            return $query->where([['estado', $search]]);
        }
    }

    public function scopeRangoFecha(Builder $query, string $desde, string $hasta): void
    {
        if ($desde && $hasta) {
            $query->whereBetween('updated_at', [$desde . ' 00:00', $hasta . ' 23:59']);
        }
    }

    public function scopeResumenInventario($query)
    {
        return $query->where('estado', 3)
            ->whereIn('idTipoMaterial', [1, 2, 3, 4])
            ->select('idUsuario', 'idTipoMaterial', Material::raw('count(*) as total'))
            ->groupBy('idUsuario', 'idTipoMaterial');
    }
}
