<?php

namespace App\Console\Commands;

use App\Models\Boleta;
use App\Models\BoletaServicio;
use App\Traits\reporteExternosTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GuardarReporteExternos extends Command
{
    use reporteExternosTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guardar:reporteExt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generar el reporte de servicios externos en segundo plano y el resultado lo ingresa en la tabla boleta';

    /**
     * Execute the console command.
     *
     * @return int
     */

    /*public function handle()
    {
        $fechaFin = now()->subWeek()->endOfWeek();
        $fechaInicio = $fechaFin->copy()->startOfWeek();
        $this->info("Generando reporte del {$fechaInicio->toDateString()} al {$fechaFin->toDateString()}...");

        try {
            $resultados = $this->procesar();

            foreach ($resultados['aux'] as $inspector => $items) {
                // Normalizamos el nombre del inspector para la búsqueda
                $inspectorNormalizado = preg_replace('/\s+/', '', strtolower($inspector));
                // Buscamos el ID del inspector en la tabla users
                $inspectorId = DB::table('users')
                    ->whereRaw("REPLACE(LOWER(name), ' ', '') = ?", [$inspectorNormalizado])
                    ->value('id');
                if (!$inspectorId) {
                    $this->warn("No se encontró el ID para el inspector: {$inspector}");
                    $inspectorId = null; // Opcional: define un valor por defecto
                }

                $cantidadServicios = $resultados['cantidades'][$inspector] ?? [];
                $precioTotal = $resultados['precios'][$inspector] ?? 0;

                Boleta::create([
                    'taller' => null,
                    'fechaInicio' => $fechaInicio,
                    'fechaFin' => $fechaFin,
                    'monto' => $precioTotal,
                    'observacion' => null,
                    //'certificador' => $inspector,
                    'certificador' => $inspectorId,
                    'anual' => $cantidadServicios['Revisión anual GNV'] ?? 0,
                    'duplicado' => $cantidadServicios['Duplicado GNV'] ?? 0,
                    'inicial' => $cantidadServicios['Conversión a GNV'] ?? 0,
                    'desmonte' => $cantidadServicios['Desmonte de Cilindro'] ?? 0,
                    'identificador' => null,
                    'auditoria' => 0,
                ]);

                //$this->info('Se guardo en bd: '.$resultado);
                $this->info("Se guardó en BD para el inspector {$inspector}: " . json_encode($cantidadServicios));
            }

            $this->info('Reporte generado e insertado en la tabla Boleta con éxito.');
        } catch (\Exception $e) {
            $this->error('Error al generar el reporte: ' . $e->getMessage());
            Log::error('Error al ejecutar guardar:reporteExt', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
        }
    }*/

    /*public function handle()
    {
        // Asegurarte de que las fechas estén formateadas correctamente
        $fechaFin = now()->subWeek()->endOfWeek()->format('Y-m-d');
        $fechaInicio = now()->subWeek()->startOfWeek()->format('Y-m-d');
        $this->info("Iniciando generación del reporte: {$fechaInicio} al {$fechaFin}");
        Log::channel('reporte_Externos')->info('Iniciando ejecución de handle()');
        Log::channel('reporte_Externos')->info("Iniciando generación del reporte: {$fechaInicio} al {$fechaFin}");

        try {
            $resultados = $this->procesar();
            //dd($resultados);
            $contador = 1; // Inicializamos el contador

            foreach ($resultados['aux'] as $inspector => $items) {
                Log::channel('reporte_Externos')->info("Procesando inspector: {$inspector}");

                // Normalizamos el nombre del inspector para la búsqueda
                $inspectorNormalizado = preg_replace('/\s+/', '', strtolower($inspector));
                Log::channel('reporte_Externos')->debug("Inspector normalizado: {$inspectorNormalizado}");

                // Buscar ID del inspector en la tabla users
                $inspectorId = DB::table('users')
                    ->whereRaw("REPLACE(LOWER(name), ' ', '') = ?", [$inspectorNormalizado])
                    ->value('id');

                if (!$inspectorId) {
                    Log::channel('reporte_Externos')->warning("No se encontró el ID para el inspector: {$inspector}");
                    continue;
                }

                // Verificar si ya existe un registro con estas fechas y este inspector
                $registroExistente = Boleta::whereDate('fechaInicio', $fechaInicio)
                    ->where('fechaFin', $fechaFin)
                    ->where('certificador', $inspectorId)
                    ->first();

                if ($registroExistente) {
                    Log::channel('reporte_Externos')->info("Ya existe un registro para el inspector {$inspector} en la semana del {$fechaInicio} al {$fechaFin}");

                    // Actualizar el registro existente
                    $registroExistente->update([
                        'monto' => $resultados['precios'][$inspector] ?? 0,
                        'anual' => $resultados['cantidades'][$inspector]['Revisión anual GNV'] ?? 0,
                        'duplicado' => $resultados['cantidades'][$inspector]['Duplicado GNV'] ?? 0,
                        'inicial' => $resultados['cantidades'][$inspector]['Conversión a GNV'] ?? 0,
                        'desmonte' => $resultados['cantidades'][$inspector]['Desmonte de Cilindro'] ?? 0,
                    ]);
                    Log::channel('reporte_Externos')->info("Registro actualizado para el inspector {$inspector}.");
                    continue;
                }

                // Crear un nuevo registro
                $cantidadServicios = $resultados['cantidades'][$inspector] ?? [];
                $precioTotal = $resultados['precios'][$inspector] ?? 0;

                $boleta = Boleta::create([
                    'taller' => null,
                    'fechaInicio' => $fechaInicio,
                    'fechaFin' => $fechaFin,
                    'monto' => $precioTotal,
                    'observacion' => null,
                    'certificador' => $inspectorId ?? null,
                    'anual' => $cantidadServicios['Revisión anual GNV'] ?? 0,
                    'duplicado' => $cantidadServicios['Duplicado GNV'] ?? 0,
                    'inicial' => $cantidadServicios['Conversión a GNV'] ?? 0,
                    'desmonte' => $cantidadServicios['Desmonte de Cilindro'] ?? 0,
                    'identificador' => $contador,
                    'auditoria' => 0,
                ]);

                $servicioIdsInspector = $resultados['ids'][$inspector] ?? [];
                // Crear la entrada en BoletaServicio
                if (!empty($servicioIdsInspector)) {
                    BoletaServicio::create([
                        'boleta_id' => $boleta->id,
                        'servicio_ids' => $servicioIdsInspector, // Asegurarse de que sea un array
                    ]);
                } else {
                    Log::channel('reporte_Externos')->warning("No se encontraron servicios asociados para el inspector {$inspector['inspector']}.");
                }

                Log::channel('reporte_Externos')->info("Se creó un registro para el inspector {$inspector} con datos: " . json_encode($cantidadServicios));
                $contador++;
            }

            $this->info('Reporte generado e insertado en la tabla Boleta con éxito.');
            Log::channel('reporte_Externos')->info('Reporte generado con éxito e insertado en la tabla Boleta.');
        } catch (\Exception $e) {
            Log::channel('reporte_Externos')->error('Error al generar el reporte', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'línea' => $e->getLine(),
                'pila' => $e->getTraceAsString(),
            ]);

            $this->error('Error al generar el reporte: ' . $e->getMessage());
        }
    }*/

    // Para que tambien se pueda actualizar el modelo BoletaServicio y agregar la relacion de los nuevo ids
    public function handle()
    {
        $fechaFin = now()->subWeek()->endOfWeek()->format('Y-m-d');
        $fechaInicio = now()->subWeek()->startOfWeek()->format('Y-m-d');
        $this->info("Iniciando generación del reporte: {$fechaInicio} al {$fechaFin}");
        Log::channel('reporte_Externos')->info("Iniciando ejecución de handle() y generación del reporte: {$fechaInicio} al {$fechaFin}");

        // Función para convertir ["Modelo => id1, id2"] en ['Modelo' => [id1, id2]]
        $parseServicioIds = function ($servicioIds) {
            $result = [];
            foreach ($servicioIds as $entrada) {
                [$modelo, $ids] = explode(' => ', $entrada);
                $idsArray = array_map('trim', explode(',', $ids));
                $modelo = trim($modelo);
                if (!isset($result[$modelo])) {
                    $result[$modelo] = [];
                }
                $result[$modelo] = array_merge($result[$modelo], $idsArray);
            }
            return $result;
        };

        // Función para convertir ['Modelo' => [id1, id2]] en ["Modelo => id1, id2"]
        $serializeServicioIds = function ($servicioArray) {
            $resultado = [];
            foreach ($servicioArray as $modelo => $ids) {
                $unicos = array_unique($ids);
                sort($unicos);
                $resultado[] = "{$modelo} => " . implode(', ', $unicos);
            }
            return $resultado;
        };

        try {
            $resultados = $this->procesar();
            $contador = 1;

            foreach ($resultados['aux'] as $inspector => $items) {
                Log::channel('reporte_Externos')->info("Procesando inspector: {$inspector}");

                $inspectorNormalizado = preg_replace('/\s+/', '', strtolower($inspector));
                $inspectorId = DB::table('users')
                    ->whereRaw("REPLACE(LOWER(name), ' ', '') = ?", [$inspectorNormalizado])
                    ->value('id');

                if (!$inspectorId) {
                    Log::channel('reporte_Externos')->warning("No se encontró el ID para el inspector: {$inspector}");
                    continue;
                }

                $registroExistente = Boleta::whereDate('fechaInicio', $fechaInicio)
                    ->where('fechaFin', $fechaFin)
                    ->where('certificador', $inspectorId)
                    ->first();

                $servicioIdsInspector = $resultados['ids'][$inspector] ?? [];

                if ($registroExistente) {
                    Log::channel('reporte_Externos')->info("Ya existe un registro para el inspector {$inspector}");

                    $registroExistente->update([
                        'monto' => $resultados['precios'][$inspector] ?? 0,
                        'anual' => $resultados['cantidades'][$inspector]['Revisión anual GNV'] ?? 0,
                        'duplicado' => $resultados['cantidades'][$inspector]['Duplicado GNV'] ?? 0,
                        'inicial' => $resultados['cantidades'][$inspector]['Conversión a GNV'] ?? 0,
                        'desmonte' => $resultados['cantidades'][$inspector]['Desmonte de Cilindro'] ?? 0,
                    ]);

                    if (!empty($servicioIdsInspector)) {
                        $boletaServicio = BoletaServicio::where('boleta_id', $registroExistente->id)->first();

                        if ($boletaServicio) {
                            $serviciosActuales = is_array($boletaServicio->servicio_ids) ? $boletaServicio->servicio_ids : [];

                            $serviciosActualesParsed = $parseServicioIds($serviciosActuales);
                            $serviciosNuevosParsed = $parseServicioIds($servicioIdsInspector->toArray());

                            foreach ($serviciosNuevosParsed as $modelo => $ids) {
                                if (!isset($serviciosActualesParsed[$modelo])) {
                                    $serviciosActualesParsed[$modelo] = [];
                                }
                                $serviciosActualesParsed[$modelo] = array_merge($serviciosActualesParsed[$modelo], $ids);
                            }

                            $serviciosFinal = $serializeServicioIds($serviciosActualesParsed);

                            $boletaServicio->update([
                                'servicio_ids' => $serviciosFinal
                            ]);

                            Log::channel('reporte_Externos')->info("Servicios actualizados para la boleta ID {$registroExistente->id}");
                        } else {
                            BoletaServicio::create([
                                'boleta_id' => $registroExistente->id,
                                'servicio_ids' => $servicioIdsInspector,
                            ]);
                            Log::channel('reporte_Externos')->info("Se creó nuevo BoletaServicio para boleta ID {$registroExistente->id}");
                        }
                    }

                    continue;
                }

                // Crear nueva boleta
                $cantidadServicios = $resultados['cantidades'][$inspector] ?? [];
                $precioTotal = $resultados['precios'][$inspector] ?? 0;

                $boleta = Boleta::create([
                    'taller' => null,
                    'fechaInicio' => $fechaInicio,
                    'fechaFin' => $fechaFin,
                    'monto' => $precioTotal,
                    'observacion' => null,
                    'certificador' => $inspectorId ?? null,
                    'anual' => $cantidadServicios['Revisión anual GNV'] ?? 0,
                    'duplicado' => $cantidadServicios['Duplicado GNV'] ?? 0,
                    'inicial' => $cantidadServicios['Conversión a GNV'] ?? 0,
                    'desmonte' => $cantidadServicios['Desmonte de Cilindro'] ?? 0,
                    'identificador' => $contador,
                    'auditoria' => 0,
                ]);

                if (!empty($servicioIdsInspector)) {
                    BoletaServicio::create([
                        'boleta_id' => $boleta->id,
                        'servicio_ids' => $servicioIdsInspector,
                    ]);
                } else {
                    Log::channel('reporte_Externos')->warning("No se encontraron servicios para el inspector {$inspector}");
                }

                Log::channel('reporte_Externos')->info("Boleta creada para inspector {$inspector} con ID {$boleta->id}");
                $contador++;
            }

            $this->info('Reporte generado con éxito.');
            Log::channel('reporte_Externos')->info('Reporte finalizado con éxito.');
        } catch (\Exception $e) {
            Log::channel('reporte_Externos')->error('Error al generar el reporte', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'línea' => $e->getLine(),
                'pila' => $e->getTraceAsString(),
            ]);

            $this->error('Error: ' . $e->getMessage());
        }
    }
}
