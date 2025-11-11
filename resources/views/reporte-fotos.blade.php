<!-- resources/views/reporte-fotos.blade.php -->
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Inspector</th>
            <th>GNV Cant</th>
            <th>GNV Incompletos</th>
            <th>GNV %</th>
            <th>GLP Cant</th>
            <th>GLP Incompletos</th>
            <th>GLP %</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($resumen as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['inspector'] }}</td>
                <td>{{ $row['gnv_tot'] }}</td>
                <td>{{ $row['gnv_incomp'] }}</td>
                <td>{{ $row['gnv_pct'] }}%</td>
                <td>{{ $row['glp_tot'] }}</td>
                <td>{{ $row['glp_incomp'] }}</td>
                <td>{{ $row['glp_pct'] }}%</td>
            </tr>
            <tr>
                <td colspan="8" style="font-weight: bold; background: #f0f0f0;">
                    Detalles de {{ $row['inspector'] }}
                </td>
            </tr>
            @foreach($row['detalles'] as $d)
                <tr>
                    <td></td>
                    <td>{{ $d['placa'] }}</td>
                    <td>{{ $d['certificado'] }}</td>
                    <td>{{ $d['tipo'] }}</td>
                    <td colspan="4">
                        Faltantes: {{ empty($d['faltantes']) ? 'Ninguno' : implode(', ', $d['faltantes']) }}
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
