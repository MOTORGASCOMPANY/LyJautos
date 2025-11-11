<?php

namespace App\Traits;

use App\Models\Certificacion;
use App\Models\CertificacionPendiente;
use App\Models\CertificacionTemporal;
use App\Models\Desmontes;
use App\Models\ServiciosImportados;
use App\Models\User;
use Illuminate\Support\Collection;

trait reporteExternosTrait
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
        $tabla3 = $this->aplicarFiltros($tabla2);
        // Agrupación principal por inspector
        $aux = $tabla3->groupBy('inspector')->sortBy(fn($item, $key) => $key);
        // Contar servicios por inspector
        $cantidades = $this->cuentaServicios($aux);
        // Sumar precios por inspector
        $precios = $this->sumaPrecios($aux);
        // Agrupar IDs por modelo dentro de cada inspector
        $idsPorModelo = $aux->map(function ($items) {
            return $items->groupBy('tipo_modelo')->map(function ($modeloItems, $modelo) {
                return "{$modelo} => " . $modeloItems->pluck('id')->implode(', ');
            })->values();
        });
        return [
            //'tabla' => $tabla2,
            'aux' => $aux,
            'cantidades' => $cantidades,
            'precios' => $precios,
            'ids' => $idsPorModelo,
        ];
    }

    /*public function cuentaServicios($data)
    {
        $cantidades = [];
        $mapeoServicios = [
            'Activación de chip (Anual)' => 'Revisión anual GNV',
            'Conversión a GNV + Chip' => 'Conversión a GNV',
            'Pre-inicial GNV' => 'Conversión a GNV'
        ];
        // Transformamos los nombres de los servicios en base al mapeo
        $todo = collect($data)->map(function ($item) use ($mapeoServicios) {
            // Si el servicio tiene un nombre que debe mapearse, lo reemplazamos
            $item['servicio'] = $mapeoServicios[$item['servicio']] ?? $item['servicio'];
            return $item;
        });
        // Contamos por inspector y servicio
        foreach ($data as $item) {
            $inspector = $item['inspector'];
            $servicio = $item['servicio'];

            if (!isset($cantidades[$inspector])) {
                $cantidades[$inspector] = [
                    'Revisión anual GNV' => 0,
                    'Conversión a GNV' => 0,
                    'Duplicado GNV' => 0,
                    'Desmonte de Cilindro' => 0,
                ];
            }

            if (isset($cantidades[$inspector][$servicio])) {
                $cantidades[$inspector][$servicio]++;
            }
        }
        return $cantidades;
    }*/

    public function cuentaServicios($data)
    {
        $cantidades = [];
        $mapeoServicios = [
            'Activación de chip (Anual)' => 'Revisión anual GNV',
            'Conversión a GNV + Chip' => 'Conversión a GNV',
            'Pre-inicial GNV' => 'Conversión a GNV',
            'Conversión a GNV OVERHUL' => 'Conversión a GNV'
        ];

        // Iteramos sobre cada inspector y sus servicios
        foreach ($data as $inspector => $items) {
            if (!isset($cantidades[$inspector])) {
                $cantidades[$inspector] = [
                    'Revisión anual GNV' => 0,
                    'Conversión a GNV' => 0,
                    'Duplicado GNV' => 0,
                    'Desmonte de Cilindro' => 0,
                ];
            }

            foreach ($items as $item) {
                $servicio = $mapeoServicios[$item['servicio']] ?? $item['servicio'];

                if (isset($cantidades[$inspector][$servicio])) {
                    $cantidades[$inspector][$servicio]++;
                }
            }
        }

        return $cantidades;
    }

    /*public function sumaPrecios($aux)
    {
        $precios = [];
        $servicios = ['Revisión anual GNV', 'Conversión a GNV', 'Desmonte de Cilindro', 'Duplicado GNV', 'Activación de chip (Anual)', 'Conversión a GNV + Chip'];
        //dd($todo);
        foreach ($aux as $servicio => $item) {
            $precio = 0;
            foreach ($item as $target) {
                if (in_array($target['servicio'], $servicios)) {
                    $precio += $target['precio'];
                }
            }
            $precios[$servicio] = $precio;
        }
        return $precios;
    }*/
    public function sumaPrecios($aux)
    {
        $precios = [];

        foreach ($aux as $inspector => $items) {
            $precios[$inspector] = $items->sum('precio'); // Sumar directamente el campo 'precio'
        }

        return $precios;
    }

    public function aplicarFiltros($tabla2)
    {
        $inspectoresExternos = User::role(['inspector'])->where('externo', 1)->pluck('name')->toArray();
        $inspectoresAdicionales = [
            'Cristhian David Saenz Nuñez',
            'Luis Alberto Esteban Torres',
            'Elvis Alexander Matto Perez',
            'Jhonatan Michael Basilio Soncco',
            'Cristhian Smith Huanay Condor',
            'Javier Alfredo Chevez Parcano',
            'Raul Llata Pacheco',
        ];
        $serviciosFiltrados = ['Duplicado GNV', 'Activación de chip (Anual)', 'Conversión a GNV + Chip', 'Conversión a GNV', 'Revisión anual GNV', 'Desmonte de Cilindro', 'Chip por deterioro', 'Pre-inicial GNV', 'Conversión a GNV OVERHUL'];
        // Modelos permitidos que requieren `externo == 1`
        $modelosExternoRequerido  = ['App\Models\Certificacion', 'App\Models\CertificacionPendiente', 'App\Models\Desmontes', 'App\Models\CertificacionTemporal'];
        // Modelo que no requiere `externo == 1`
        $modeloSinFiltroExterno = 'App\Models\ServiciosImportados';

        $registrosExternos = $tabla2->filter(
            fn($item) =>
                /*in_array($item['inspector'], $inspectoresExternos) &&
                in_array($item['servicio'], $serviciosFiltrados)*/
                in_array($item['inspector'], $inspectoresExternos) &&
                in_array($item['servicio'], $serviciosFiltrados) &&
                (
                    in_array($item['tipo_modelo'], $modelosExternoRequerido) && $item['externo'] == 1 ||
                    $item['tipo_modelo'] === $modeloSinFiltroExterno
                )
        );

        $registrosAdicionalesFiltrados = $tabla2->filter(
            fn($item) =>
                /*in_array($item['inspector'], $inspectoresAdicionales) &&
                ($item['tipo_modelo'] == 'App\Models\Certificacion' || $item['tipo_modelo'] == 'App\Models\CertificacionPendiente') &&
                $item['externo'] == 1*/
                in_array($item['inspector'], $inspectoresAdicionales) &&
                in_array($item['tipo_modelo'], $modelosExternoRequerido) &&
                $item['externo'] == 1
        );

        // Eliminar registros de `registrosAdicionalesFiltrados` que ya están en `registrosExternos`
        $registrosAdicionalesFiltrados = $registrosAdicionalesFiltrados->reject(
            fn($item) => $registrosExternos->contains('id', $item['id'])
        );

        //return $registrosExternos->merge($registrosAdicionalesFiltrados);
        return $registrosExternos->merge($registrosAdicionalesFiltrados)->unique('id');
    }

    public function generaData()
    {
        // Calcular el rango de la semana anterior
        $fechaFin = now()->subWeek()->endOfWeek();
        $fechaInicio = $fechaFin->copy()->startOfWeek();
        $tabla = new Collection();
        // TODO CERTIFICACIONES
        $certificaciones = Certificacion::rangoFecha($fechaInicio, $fechaFin)
            ->where('pagado', 0)
            ->whereIn('estado', [3, 1])
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
                "servicio" => $cert_pend->Servicio->tipoServicio->descripcion, //'Activación de chip (Anual)' es ese tipo de servicio por defecto
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
        $fechaFin = now()->subWeek()->endOfWeek();
        $fechaInicio = $fechaFin->copy()->startOfWeek();
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

    /*public function encontrarDiferenciaPorPlaca($lista1, $lista2)
    {
        //$diferencias = [];
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

                // Verificar si las placas e inspectores son iguales
                if ($placa1 === $placa2 && $inspector1 === $inspector2 && $taller1 === $taller2) {
                    // Si estamos en el caso de procesar2, aplicar la lógica de exclusión especial
                    if (
                        ($elemento2['tipo_modelo'] == 'App\Models\CertificacionPendiente' && $servicio1 == 'Revisión anual GNV') ||
                        ($servicio2 == 'Conversión a GNV + Chip' && $servicio1 == 'Conversión a GNV') ||
                        ($servicio2 == 'Pre-inicial GNV' && $servicio1 == 'Conversión a GNV')
                    ) {
                        $encontrado = true;
                        break;
                    } else if ($servicio1 === $servicio2) {
                        $encontrado = true;
                        break;
                    }
                }
            }

            // Si no fue encontrado, agregar a la lista de diferencias
            if (!$encontrado) {
                $diferencias[] = $elemento1;
            }
        }

        return $diferencias;
    }*/

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
