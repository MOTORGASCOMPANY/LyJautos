<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ReporteDocumentosEmpleados extends Component
{
    public $documentos = [];
    public $tipoDocumento = '';
    public $empleado = '';

    public function mount()
    {
        $this->documentos = [];
    }

    public function documentos()
    {
        $query = "
            SELECT 
                ct.idUser AS id_empleado, 
                u.name AS nombre_empleado, 
                tde.id AS id_tipo_documento, 
                tde.nombreTipo AS documento_faltante
            FROM contrato_trabajo ct
            CROSS JOIN tipodocumentoempleado tde
            LEFT JOIN (
                SELECT DISTINCT de.tipoDocumento, deu.idUser
                FROM documentoempleado de
                INNER JOIN documentoempleado_user deu ON deu.idDocumentoEmpleado = de.id
            ) AS documentos_existentes
            ON documentos_existentes.idUser = ct.id AND documentos_existentes.tipoDocumento = tde.id
            LEFT JOIN users u ON u.id = ct.idUser
            WHERE documentos_existentes.idUser IS NULL
        ";

        // Aplicar filtros dinámicamente
        if (!empty($this->empleado)) {
            $query .= " AND ct.idUser = ?";
        }
        if (!empty($this->tipoDocumento)) {
            $query .= " AND tde.id = ?";
        }

        $query .= " ORDER BY ct.idUser, tde.id";

        // Ejecutamos la consulta con los parámetros según los filtros seleccionados
        $bindings = [];
        if (!empty($this->empleado)) {
            $bindings[] = $this->empleado;
        }
        if (!empty($this->tipoDocumento)) {
            $bindings[] = $this->tipoDocumento;
        }

        $this->documentos = DB::select($query, $bindings);
    }

    public function getEmpleadosProperty()
    {
        return DB::table('users')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    public function getTiposDocumentosProperty()
    {
        return DB::table('tipodocumentoempleado')
            ->select('id', 'nombreTipo')
            ->orderBy('nombreTipo')
            ->get();
    }

    public function render()
    {
        return view('livewire.reporte-documentos-empleados', [
            'empleados' => $this->empleados,
            'tiposDocumentos' => $this->tiposDocumentos,
        ]);
    }
}
