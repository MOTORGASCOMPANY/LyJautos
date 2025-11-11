<?php

namespace App\Services;

use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\CertificacionTemporal;
use App\Models\Desmontes;
use App\Models\ServiciosImportados;
use Illuminate\Support\Collection;

class DataService
{
    public function generaData($ins, $taller, $servicio, $fechaInicio, $fechaFin)
    {
        $tabla = new Collection();

        // Certificaciones
        $certificaciones = Certificacion::IdTalleres($taller)
            ->IdInspectores($ins)
            ->IdTipoServicio($servicio)
            ->RangoFecha($fechaInicio, $fechaFin)
            ->whereIn('pagado', [0, 2])
            ->whereNotIn('estado', [2])
            ->where(function ($query) {
                $query->whereNull('placaantigua')
                      ->orWhere('placaantigua', 0);
            })
            ->get();

        foreach ($certificaciones as $certi) {
            $tabla->push([
                "id" => $certi->id,
                "placa" => $certi->Vehiculo->placa,
                "taller" => $certi->Taller->nombre,
                "representante" => $certi->Taller->representante,
                "inspector" => $certi->Inspector->name,
                "servicio" => $certi->Servicio->tipoServicio->descripcion,
                "num_hoja" => $certi->NumHoja,
                "ubi_hoja" => $certi->UbicacionHoja,
                "precio" => $certi->precio,
                "pagado" => $certi->pagado,
                "estado" => $certi->estado,
                "externo" => $certi->externo,
                "tipo_modelo" => $certi::class,
                "fecha" => $certi->created_at,
                "updated_at" => $certi->updated_at,
            ]);
        }

        // Certificaciones Pendientes
        $cerPendiente = CertificacionPendiente::IdTalleres($taller)
            ->IdInspectores($ins)
            ->IdTipoServicios($servicio)
            ->RangoFecha($fechaInicio, $fechaFin)
            ->get();

        foreach ($cerPendiente as $cert_pend) {
            $tabla->push([
                "id" => $cert_pend->id,
                "placa" => $cert_pend->Vehiculo->placa,
                "taller" => $cert_pend->Taller->nombre,
                "representante" => $cert_pend->Taller->representante,
                "inspector" => $cert_pend->Inspector->name,
                "servicio" => 'Activación de chip (Anual)',
                "num_hoja" => null,
                "ubi_hoja" => null,
                "precio" => $cert_pend->precio,
                "pagado" => $cert_pend->pagado,
                "estado" => $cert_pend->estado,
                "externo" => $cert_pend->externo,
                "tipo_modelo" => $cert_pend::class,
                "fecha" => $cert_pend->created_at,
                "updated_at" => $cert_pend->updated_at,
            ]);
        }

        // Desmontes
        $desmontes = Desmontes::IdTalleres($taller)
            ->IdInspectores($ins)
            ->IdTipoServicios($servicio)
            ->RangoFecha($fechaInicio, $fechaFin)
            ->get();

        foreach ($desmontes as $des) {
            $tabla->push([
                "id" => $des->id,
                "placa" => $des->placa,
                "taller" => $des->Taller->nombre,
                "representante" => $des->Taller->representante,
                "inspector" => $des->Inspector->name,
                "servicio" => $des->Servicio->tipoServicio->descripcion,
                "num_hoja" => null,
                "ubi_hoja" => null,
                "precio" => $des->precio,
                "pagado" => $des->pagado,
                "estado" => $des->estado,
                "externo" => $des->externo,
                "tipo_modelo" => $des::class,
                "fecha" => $des->created_at,
                "updated_at" => $des->updated_at,
            ]);
        }

        //TODO CERT TEMPORALES:
        $cerTemp = CertificacionTemporal::idTalleres($taller)
            ->IdInspectores($ins)
            ->IdTipoServicios($servicio)
            ->rangoFecha($fechaInicio, $fechaFin)
            ->get();

        foreach ($cerTemp as $ctemp) {
            $data = [
                "id" => $ctemp->id,
                "placa" => $ctemp->placa,
                "taller" => $ctemp->Taller->nombre,
                "representante" => $ctemp->Taller->representante,
                "inspector" => $ctemp->Inspector->name,
                "servicio" => $ctemp->Servicio->tipoServicio->descripcion,
                "num_hoja" => $ctemp->numSerie,
                "ubi_hoja" => Null,
                "precio" => $ctemp->precio,
                "pagado" => $ctemp->pagado,
                "estado" => $ctemp->estado,
                "externo" => $ctemp->externo,
                "tipo_modelo" => $ctemp::class,
                "fecha" => $ctemp->created_at,
                "updated_at" => $ctemp->updated_at,
            ];
            $tabla->push($data);
        }

        return $tabla;
    }

    public function cargaServiciosGasolution($ins, $taller, $servicio, $fechaInicio, $fechaFin)
    {
        $disc = new Collection();

        $dis = ServiciosImportados::Talleres($taller)
            ->Inspectores($ins)
            ->TipoServicio($servicio)
            ->RangoFecha($fechaInicio, $fechaFin)
            ->get();

        foreach ($dis as $registro) {
            $disc->push([
                "id" => $registro->id,
                "placa" => $registro->placa,
                "taller" => $registro->taller,
                "representante" => $registro->representante ?? null,
                "inspector" => $registro->certificador,
                "servicio" => $registro->TipoServicio->descripcion,
                "num_hoja" => null,
                "ubi_hoja" => null,
                "precio" => $registro->precio,
                "pagado" => $registro->pagado,
                "estado" => $registro->estado,
                "externo" => null,
                "tipo_modelo" => $registro::class,
                "fecha" => $registro->fecha,
                "updated_at" => $registro->fecha,
            ]);
        }

        return $disc;
    }

    public function encontrarDiferenciaPorPlaca($lista1, $lista2)
    {
        $diferencias = [];

        foreach ($lista1 as $elemento1) {
            $placa1 = $elemento1['placa'];
            $inspector1 = $elemento1['inspector'];
            $servicio1 = $elemento1['servicio'];
            $taller1 = $elemento1['taller'];
            $encontrado = false;

            foreach ($lista2 as $elemento2) {
                $placa2 = $elemento2['placa'];
                $inspector2 = $elemento2['inspector'];
                $servicio2 = $elemento2['servicio'];
                $taller2 = $elemento2['taller'];

                if ($placa1 === $placa2 && $inspector1 === $inspector2 && $taller1 === $taller2) {
                    if (
                        ($elemento2['tipo_modelo'] == 'App\Models\CertificacionPendiente' && $servicio1 == 'Revisión anual GNV') ||
                        ($servicio2 == 'Conversión a GNV + Chip' && $servicio1 == 'Conversión a GNV') ||
                        ($servicio2 == 'Pre-inicial GNV' && $servicio1 == 'Conversión a GNV') ||
                        ($servicio2 == 'Conversión a GNV OVERHUL' && $servicio1 == 'Conversión a GNV')
                    ) {
                        $encontrado = true;
                        break;
                    } else if ($servicio1 === $servicio2) {
                        $encontrado = true;
                        break;
                    }
                }
            }

            if (!$encontrado) {
                $diferencias[] = $elemento1;
            }
        }

        return $diferencias;
    }

    public function limpiarDatos($coleccion)
    {
        return $coleccion->map(function ($item) {
            $item['placa'] = trim($item['placa'] ?? '');
            $item['inspector'] = trim($item['inspector'] ?? '');
            $item['taller'] = trim($item['taller'] ?? '');
            return $item;
        });
    }


    public function procesar($ins, $taller, $servicio, $fechaInicio, $fechaFin)
    {
        /*$tabla = $this->generaData($ins, $taller, $fechaInicio, $fechaFin);
        $importados = $this->cargaServiciosGasolution($ins, $taller, $fechaInicio, $fechaFin);
        $tabla = $tabla->map(function ($item) {
            $item['placa'] = trim($item['placa']);
            $item['inspector'] = trim($item['inspector']);
            $item['taller'] = trim($item['taller']);
            return $item;
        });
        $importados = $importados->map(function ($item) {
            $item['placa'] = trim($item['placa']);
            $item['inspector'] = trim($item['inspector']);
            $item['taller'] = trim($item['taller']);
            return $item;
        });*/
        // Generar y limpiar datos
        $tabla = $this->limpiarDatos($this->generaData($ins, $taller, $servicio, $fechaInicio, $fechaFin));
        $importados = $this->limpiarDatos($this->cargaServiciosGasolution($ins, $taller, $servicio, $fechaInicio, $fechaFin));
        $diferencias = $this->encontrarDiferenciaPorPlaca($importados, $tabla);

        return $tabla->merge($diferencias, function ($item1, $item2) {
            $inspector1 = strtolower($item1['inspector']);
            $inspector2 = strtolower($item2['inspector']);
            $taller1 = strtolower($item1['taller']);
            $taller2 = strtolower($item2['taller']);
            $comparison = strcasecmp($inspector1 . $taller1, $inspector2 . $taller2);
            return $comparison;
        });
    }
}
