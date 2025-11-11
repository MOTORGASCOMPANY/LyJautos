<?php

namespace App\Http\Livewire;

use App\Exports\FormatosContadoExport;
use App\Models\Contado;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReporteContados extends Component
{
    public $data; // Variable para almacenar los materiales
    public $tipos; // Lista de tipos de material
    public $tipoSeleccionado = null; // Tipo seleccionado por el usuario
    public $fechaInicio, $fechaFin; // Agregar propiedades de fecha

    protected $listeners = ['exportarExcelContado'];

    public function mount()
    {
        // Obtén la lista de tipos únicos desde la base de datos
        $this->tipos = Contado::with('salida.materiales.tipo')
            ->get()
            ->flatMap(fn($contado) => $contado->salida->materiales->pluck('tipo.descripcion'))
            ->unique()
            ->values()
            ->toArray();
    }

    /*public function actualizarDatos()
    {
        if ($this->tipoSeleccionado) {
            // Obtener y procesar datos
            $this->data = Contado::with('salida.materiales.tipo', 'salida.usuarioAsignado')
                ->get()
                ->groupBy(fn($contado) => $contado->salida->usuarioAsignado->name)
                ->map(function ($group, $inspector) {
                    // Filtrar materiales por tipo
                    $materiales = $group->flatMap(fn($contado) => $contado->salida->materiales)
                        ->where('tipo.descripcion', $this->tipoSeleccionado);

                    $numSeries = $materiales->pluck('numSerie')->sort()->values();
                    $cantidad = $materiales->count(); // Contar los materiales

                    if ($numSeries->isEmpty()) {
                        return null;
                    }

                    return [
                        'inspector' => $inspector,
                        'numSerie' => $numSeries->first() . ' - ' . $numSeries->last(),
                        'cantidad' => $cantidad,
                        'fecha' => $group->first()->created_at, // Puedes ajustar esto si necesitas otra lógica para la fecha
                        'monto' => $group->first()->precio,
                    ];
                })
                ->filter() // Filtrar null
                ->values()
                ->toArray();
        } else {
            $this->data = []; // Vacía los datos si no hay selección
        }
    }*/

    public function actualizarDatos()
    {
        $query = Contado::with('salida.materiales.tipo', 'salida.usuarioAsignado');

        // Filtrar por tipo seleccionado
        if ($this->tipoSeleccionado) {
            $query->whereHas('salida.materiales.tipo', function ($q) {
                $q->where('descripcion', $this->tipoSeleccionado);
            });
        }

        // Filtrar por rango de fechas si ambas están seleccionadas
        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->fechaInicio)->startOfDay(),
                Carbon::parse($this->fechaFin)->endOfDay()
            ]);
        }

        $contados = $query->get();

        // Agrupar y transformar datos
        /*$this->data = $contados->groupBy(fn($contado) => $contado->salida->usuarioAsignado->name)
            ->map(function ($group, $inspector) {
                $materiales = $group->flatMap(fn($contado) => $contado->salida->materiales)
                    ->where('tipo.descripcion', $this->tipoSeleccionado);

                $numSeries = $materiales->pluck('numSerie')->sort()->values();
                $cantidad = $materiales->count();

                if ($numSeries->isEmpty()) {
                    return null;
                }

                return [
                    'inspector' => $inspector,
                    'numSerie' => $numSeries->first() . ' - ' . $numSeries->last(),
                    'cantidad' => $cantidad,
                    'fecha' => $group->first()->created_at,
                    'monto' => $group->first()->precio,
                ];
            })
        */
        $this->data = $contados->map(function ($contado) {
            if (!$contado->salida) {
                return null;
            }

            $materiales = $contado->salida->materiales->where('tipo.descripcion', $this->tipoSeleccionado);
            //dd($materiales);
            
            if ($materiales->isEmpty()) {
                return null;
            }

            $stockCount = $materiales->where('estado', 3)->count();
            //$numSeries = $materiales->pluck('numSerie')->sort()->values();
            // Filtrar solo los numSerie que no sean null ni vacíos
            $numSeries = $materiales->pluck('numSerie')->filter(fn($serie) => trim($serie) !== '')->sort()->values();

            return [
                'inspector' => $contado->salida->usuarioAsignado->name,
                //'numSerie' => $materiales->pluck('numSerie')->implode(', '),
                //'numSerie' => $numSeries->first() . ' - ' . $numSeries->last(),
                'numSerie' => $numSeries->isNotEmpty() ? $numSeries->first() . ' - ' . $numSeries->last() : 'Sin Serie',
                'cantidad' => $materiales->count(),
                'fecha' => $contado->created_at,
                'monto' => $contado->precio,
                'pagado' => $contado->pagado,
                'stock' => $stockCount
            ];
        })
            ->filter()
            ->values()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.reporte-contados', [
            'materiales' => $this->data,
        ]);
    }

    public function exportarExcelContado($datae)
    {
        return Excel::download(new FormatosContadoExport($datae), 'formatos_Contado.xlsx');
    }
}


/*
public function mount()
    {
        // Obtenemos los números de serie de los materiales asociados
        $this->data = Contado::with('salida.materiales')
            ->get()
            ->flatMap(function ($contado) {
                return $contado->salida->materiales->map(function ($material) use ($contado) {
                    return [
                        'numSerie' => $material->numSerie,
                        'tipomaterial' => $material->tipo->descripcion,
                        'fecha' => $contado->created_at,
                        'inspector' => $contado->salida->usuarioAsignado->name
                    ];
                });
            });
    }

    public function actualizarDatos()
    {
        // Actualiza los datos basados en el tipo seleccionado
        if ($this->tipoSeleccionado) {
            $this->data = Contado::with('salida.materiales.tipo')
                ->get()
                ->flatMap(function ($contado) {
                    return $contado->salida->materiales->map(function ($material) use ($contado) {
                        return [
                            'numSerie' => $material->numSerie,
                            'tipomaterial' => $material->tipo->descripcion,
                            'fecha' => $contado->created_at,
                            'inspector' => $contado->salida->usuarioAsignado->name,
                        ];
                    });
                })
                ->filter(fn($item) => $item['tipomaterial'] === $this->tipoSeleccionado)
                ->values()
                ->toArray();
        } else {
            $this->data = []; // Vacía los datos si no hay selección
        }
    }
*/