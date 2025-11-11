<?php

namespace App\Traits;

use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\CertificacionTemporal;
use App\Models\Desmontes;
use App\Models\ServiciosImportados;
use App\Models\Taller;
use App\Models\TallerInspector;
use App\Models\User;
use Illuminate\Support\Collection;

trait reporteTallerDiarioTrait
{
    public function procesar()
    {
        $tabla = $this->generaData();
        $importados = $this->cargaServiciosGasolution();
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
        });
        $diferencias = $this->encontrarDiferenciaPorPlaca($importados, $tabla);
        $tabla2 = $tabla->merge($diferencias);
        $aux = $this->agruparTalleresConsolidar($tabla2);
        $diarios = $this->filtrarPorFrecuencia($aux, 'es_diario');
        return [
            //'aux' => $aux,
            'diarios' => $diarios,
        ];

    }

    /*private function agruparTalleresConsolidar($tabla)
    {
        return $tabla->groupBy('taller')->map(function ($items) {
            $taller = $items->first()['taller'];

            $inspectoresDesignados = $this->obtenerInspectoresDesignados($taller);

            $itemsFiltrados = $inspectoresDesignados
                ? $items->filter(fn($item) => in_array($item['inspector'], $inspectoresDesignados))
                : $items;
            // Sumar precios filtrados
            $total = $itemsFiltrados->sum(fn($item) => (float) $item['precio']);
            // Agrupar IDs por modelo en un formato más estructurado
            $idsPorModelo = $itemsFiltrados->groupBy('tipo_modelo')->map(
                fn($items, $modelo) => "{$modelo} => " . $items->pluck('id')->implode(', ')
            )->values()->all(); 

            return [
                'taller' => $taller,
                'total' => $total,
                'ids' => $idsPorModelo,
            ];
        })
        ->filter(fn($data) => $data['total'] > 0)
        ->sortBy('taller');
    }*/

    private function agruparTalleresConsolidar($tabla)
    {
        // Definir mapa de consolidaciones
        $consolidaciones = [
            'AUTOGAS GREEN CAR E.I.R.L. - II' => 'WILTON MOTORS E.I.R.L -II'
        ];
        return $tabla->groupBy('taller')->map(function ($items) {
            $taller = $items->first()['taller'];

            $inspectoresDesignados = $this->obtenerInspectoresDesignados($taller);

            $itemsFiltrados = $inspectoresDesignados
                ? $items->filter(fn($item) => in_array($item['inspector'], $inspectoresDesignados))
                : $items;
            // Sumar precios filtrados
            $total = $itemsFiltrados->sum(fn($item) => (float) $item['precio']);
            // Agrupar IDs por modelo en un formato más estructurado
            $idsPorModelo = $itemsFiltrados->groupBy('tipo_modelo')->map(
                fn($items, $modelo) => "{$modelo} => " . $items->pluck('id')->implode(', ')
            )->values()->all();            

            return [
                'taller' => $taller,
                'total' => $total,
                'ids' => $idsPorModelo,
            ];
        })->filter(fn($data) => $data['total'] > 0)
            ->groupBy(function ($item) use ($consolidaciones) {
                // Verificar si el taller debe consolidarse en otro nombre
                return $consolidaciones[$item['taller']] ?? $item['taller'];
            })
            ->map(fn($groupedItems, $taller) => [
                'taller' => $taller,
                'total' => $groupedItems->sum('total'),
                'ids' => $groupedItems->pluck('ids')->flatten(1)->all(),
            ])->filter(fn($data) => $data['total'] > 0)
            ->sortBy('taller');
    }

    private function obtenerInspectoresDesignados($taller)
    {
        $inspectoresIds = TallerInspector::where('taller_id', Taller::where('nombre', $taller)->value('id'))
            ->pluck('inspector_id')
            ->toArray();

        return User::whereIn('id', $inspectoresIds)->pluck('name')->toArray();
    }

    private function filtrarPorFrecuencia($tabla, $frecuencia)
    {
        return $tabla->filter(function ($item) use ($frecuencia) {
            return Taller::where('nombre', $item['taller'])->value($frecuencia) == 1;
        });
    }

    public function generaData()
    {
        // Calcular el rango del dia anterior
        $fechaInicio = now()->subDay()->startOfDay()->format('Y-m-d H:i:s');
        $fechaFin = now()->subDay()->endOfDay()->format('Y-m-d H:i:s');
        $tabla = new Collection();
        // TODO CERTIFICACIONES
        $certificaciones = Certificacion::rangoFecha($fechaInicio, $fechaFin)
            ->where('pagado', 0)
            ->whereNotIn('estado', [2])
            ->where(function ($query) {
                $query->whereNull('placaantigua')
                      ->orWhere('placaantigua', 0);
            })
            ->get();
        // TODO CERTIFICACIONES PENDIENTES
        $cerPendiente = CertificacionPendiente::rangoFecha($fechaInicio, $fechaFin)
            ->get();
        //TODO DESMONTES PARA OFICINA:
        $desmontes = Desmontes::rangoFecha($fechaInicio, $fechaFin)
            ->get();
        //TODO CERT TEMPORALES:
        $cerTemp = CertificacionTemporal::rangoFecha($fechaInicio, $fechaFin)
            ->get();

        // UNIFICANDO CERTIFICACIONES
        foreach ($certificaciones as $certi) {
            $data = [
                "id" => $certi->id,
                "placa" => $certi->Vehiculo->placa,
                "taller" => $certi->Taller->nombre,
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
            ];
            $tabla->push($data);
        }
        foreach ($cerPendiente as $cert_pend) {
            $data = [
                "id" => $cert_pend->id,
                "placa" => $cert_pend->Vehiculo->placa,
                "taller" => $cert_pend->Taller->nombre,
                "inspector" => $cert_pend->Inspector->name,
                "servicio" => 'Activación de chip (Anual)', // es ese tipo de servicio por defecto
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $cert_pend->precio,
                "pagado" => $cert_pend->pagado,
                "estado" => $cert_pend->estado,
                "externo" => $cert_pend->externo,
                "tipo_modelo" => $cert_pend::class,
                "fecha" => $cert_pend->created_at,
            ];
            $tabla->push($data);   
        }
        foreach ($desmontes as $des) {
            $data = [
                "id" => $des->id,
                "placa" => $des->placa,
                "taller" => $des->Taller->nombre,
                "inspector" => $des->Inspector->name,
                "servicio" => $des->Servicio->tipoServicio->descripcion,
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $des->precio,
                "pagado" => $des->pagado,
                "estado" => $des->estado,
                "externo" => $des->externo,
                "tipo_modelo" => $des::class,
                "fecha" => $des->created_at,
            ];
            $tabla->push($data);
        }
        foreach ($cerTemp as $ctemp) {
            $data = [
                "id" => $ctemp->id,
                "placa" => $ctemp->placa,
                "taller" => $ctemp->Taller->nombre,
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
            ];
            $tabla->push($data);
        }
        return $tabla;
    }

    public function cargaServiciosGasolution()
    {
        $fechaInicio = now()->subDay()->startOfDay()->format('Y-m-d H:i:s');
        $fechaFin = now()->subDay()->endOfDay()->format('Y-m-d H:i:s');
        $disc = new Collection();
        $dis = ServiciosImportados::RangoFecha($fechaInicio, $fechaFin)
            ->get();
        foreach ($dis as $registro) {
            $data = [
                "id" => $registro->id,
                "placa" => $registro->placa,
                "taller" => $registro->taller,
                "inspector" => $registro->certificador,
                "servicio" => $registro->TipoServicio->descripcion,
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $registro->precio,
                "pagado" => $registro->pagado,
                "estado" => $registro->estado,
                "externo" => Null, //aun no se identifican los externos
                "tipo_modelo" => $registro::class,
                "fecha" => $registro->fecha,
            ];
            $disc->push($data);
        }
        return $disc;
    }

    public function encontrarDiferenciaPorPlaca($lista1, $lista2)
    {
        $diferencias = collect();

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

                // Verificar si las placas, inspectores y talleres son iguales
                if ($placa1 === $placa2 && $inspector1 === $inspector2 && $taller1 === $taller2) {
                    // Aplicar reglas específicas de comparación de servicios
                    if (
                        ($elemento2['tipo_modelo'] == 'App\Models\CertificacionPendiente' && $servicio1 == 'Revisión anual GNV') ||
                        ($servicio2 == 'Conversión a GNV + Chip' && $servicio1 == 'Conversión a GNV') ||
                        ($servicio2 == 'Pre-inicial GNV' && $servicio1 == 'Conversión a GNV') ||
                        ($servicio2 == 'Conversión a GNV OVERHUL' && $servicio1 == 'Conversión a GNV')
                    ) {
                        $encontrado = true;
                        break;
                    }

                    // Comparación estándar para servicios iguales
                    if ($servicio1 === $servicio2) {
                        $encontrado = true;
                        break;
                    }
                }
            }

            // Si no fue encontrado en lista2, agregarlo a las diferencias
            if (!$encontrado) {
                $diferencias->push($elemento1);
            }
        }

        return $diferencias;
    }
}
