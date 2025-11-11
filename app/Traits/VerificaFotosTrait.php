<?php

namespace App\Traits;

use App\Models\Expediente;
use App\Models\TipoImagen;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait VerificaFotosTrait
{
    protected array $tiposGNV = [1, 2, 7, 10, 14];
    protected array $tiposGLP = [3, 4];
    protected array $imagenesGNV = [1,2,3,4,5,6,7,8,9];
    protected array $imagenesGLP = [1,2,3,4,5,6,8,9,10,11];

    /*public function getRequeridasPorServicio(?int $tipoServicioId): array
    {
        if ($tipoServicioId && in_array($tipoServicioId, $this->tiposGNV, true)) {
            return $this->imagenesGNV;
        }

        if ($tipoServicioId && in_array($tipoServicioId, $this->tiposGLP, true)) {
            return $this->imagenesGLP;
        }

        return [];
    }*/
    public function getRequeridasPorServicio(?int $tipoServicioId): array
    {
        if ($tipoServicioId && in_array($tipoServicioId, $this->tiposGNV, true)) {
            $requeridas = $this->imagenesGNV;

            // Excepción: tipo 7 Activación de chip (sin certificado ni ficha técnica)
            if ($tipoServicioId === 7) {
                $requeridas = array_diff($requeridas, [1, 2]);
            }

            return array_values($requeridas);
        }

        if ($tipoServicioId && in_array($tipoServicioId, $this->tiposGLP, true)) {
            return $this->imagenesGLP;
        }

        return [];
    }

    protected function getImagesCollection(Expediente $expediente): Collection
    {
        // intenta nombres de relación comunes
        if (method_exists($expediente, 'Archivos')) {
            return $expediente->Archivos ?? collect();
        }

        if (method_exists($expediente, 'imagenes')) {
            return $expediente->imagenes ?? collect();
        }

        // fallback: intenta obtener cualquier relación cargada que parezca contener tipo_imagen_id
        foreach ($expediente->getRelations() as $rel) {
            if ($rel instanceof Collection && $rel->first() && array_key_exists('tipo_imagen_id', $rel->first()->getAttributes())) {
                return $rel;
            }
        }

        return collect();
    }

    public function expedienteFaltantes(Expediente $expediente): array
    {
        // obtener tipo de servicio (si existe)
        $tipoServicioId = $expediente->Servicio->tipoServicio->id ?? null;

        // requeridas según tipo de servicio
        $requeridas = $this->getRequeridasPorServicio($tipoServicioId);

        // imágenes presentes (ids)
        $imagenes = $this->getImagesCollection($expediente);
        $presentes = $imagenes->pluck('tipo_imagen_id')->unique()->filter()->values()->toArray();

        // ids faltantes
        $faltantesIds = array_values(array_diff($requeridas, $presentes));

        // map id -> codigo (cargar una sola vez por cache para evitar queries repetidas)
        $mapCodigo = Cache::remember('tipo_imagen_codigo_map', 60*60, function () {
            return TipoImagen::pluck('codigo', 'id')->toArray(); // [id => codigo]
        });

        $faltantesCodigos = array_map(fn($id) => $mapCodigo[$id] ?? (string)$id, $faltantesIds);

        return [
            'presentes' => $presentes,
            'faltantes_ids' => $faltantesIds,
            'faltantes_codigos' => $faltantesCodigos,
            'requeridas' => $requeridas,
        ];
    }

    public function isExpedienteCompleto(Expediente $expediente): bool
    {
        $res = $this->expedienteFaltantes($expediente);
        return count($res['faltantes_ids']) === 0;
    }

    public function agruparExpedientesIncompletosPorInspector(array $options = []): array
    {
        $query = Expediente::with(['Inspector', 'Servicio.tipoServicio', 'Archivos.TipoImagen']);

        // filtros básicos
        if (!empty($options['ins'])) {
            $query->where('usuario_idusuario', $options['ins']);
        }

        if (!empty($options['fecIni']) && !empty($options['fecFin'])) {
            $query->whereBetween('created_at', [
                $options['fecIni'] . ' 00:00:00',
                $options['fecFin'] . ' 23:59:59',
            ]);
        } elseif (!empty($options['fecIni'])) {
            $query->whereDate('created_at', '>=', $options['fecIni']);
        } elseif (!empty($options['fecFin'])) {
            $query->whereDate('created_at', '<=', $options['fecFin']);
        }

        if (!empty($options['sinceDays']) && is_numeric($options['sinceDays'])) {
            $query->where('created_at', '>=', now()->subDays((int)$options['sinceDays']));
        }

        /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Expediente[] $expedientes */
        // Traer los expedientes que cumplan los filtros
        $expedientes = $query->get();

        $grouped = [];

        foreach ($expedientes as $exp) {
            $falt = $this->expedienteFaltantes($exp);

            if (count($falt['faltantes_ids']) === 0) {
                // completo => no incluir
                continue;
            }

            // obtener inspector (puede ser null)
            $inspector = $exp->Inspector ?? null;
            $inspectorId = $inspector->id ?? 'sin-inspector-' . ($exp->usuario_idusuario ?? 'null');

            $grouped[$inspectorId]['inspector'] = $inspector;
            $grouped[$inspectorId]['expedientes'][] = [
                'expediente' => $exp,
                'presentes' => $falt['presentes'],
                'faltantes_ids' => $falt['faltantes_ids'],
                'faltantes_codigos' => $falt['faltantes_codigos'],
                'requeridas' => $falt['requeridas'],
            ];
        }

        return $grouped;
    }
}
