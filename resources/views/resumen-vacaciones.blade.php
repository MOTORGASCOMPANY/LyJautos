<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Resumen de Vacaciones</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            margin: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 11px;
            border-radius: 2px;
            overflow: hidden;
        }

        thead {
            background: #f59e0b;
            /* mismo anaranjado */
            color: white;
        }

        th,
        td {
            padding: 8px 10px;
            font-size: 11px;
            text-align: left;
            white-space: nowrap; /* 👈 evita que se rompa en varias líneas */
        }

        /* Centrar solo columnas específicas */
        td:nth-child(3),
        td:nth-child(4),
        td:nth-child(5),
        td:nth-child(6),
        td:nth-child(7),
        th:nth-child(3),
        th:nth-child(4),
        th:nth-child(5),
        th:nth-child(6),
        th:nth-child(7) {
            text-align: center;
        }


        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        tbody tr:nth-child(odd) {
            background: #ffffff;
        }
    </style>
</head>

<body>
    <header>
        <table style="width:100%; border-collapse: collapse;">
            <tr style=" color: rgb(39, 38, 38);">
                <td style="padding: 10px; width: 20%; vertical-align: middle;">
                    <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="height:70px;">
                </td>
                <td style="padding: 10px; text-align: right; vertical-align: middle;">
                    <h1 style="margin:0; font-size:20px; letter-spacing:0.5px;">Resumen de Vacaciones de Empleados</h1>
                    <p style="margin:2px 0 0; font-size:12px;">Generado el {{ now()->format('d/m/Y') }}</p>
                </td>
            </tr>
        </table>
    </header>

    <table>
        <thead>
            <tr>
                {{--<th>#</th>--}}
                <th>Empleado</th>
                <th>FechaInicio</th>
                <th>D.Ganados</th>
                <th>D.Tomados</th>
                <th>D.Restantes</th>
                <th>Prox Vacac</th>
                <th>Solic Vacac</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($contratos as $i => $contrato)
                <tr>
                    {{--<td>{{ $i + 1 }}</td>--}}
                    <td>{{ $contrato->empleado->name ?? 'Sin nombre' }}</td>
                    <td>{{ $contrato->fechaInicio ? \Carbon\Carbon::parse($contrato->fechaInicio)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $contrato->vacaciones->dias_ganados ?? 0 }}</td>
                    <td>{{ $contrato->vacaciones->dias_tomados ?? 0 }}</td>
                    <td>{{ $contrato->vacaciones->dias_restantes ?? 0 }}</td>
                    <td>
                        {{ $contrato->fechaExpiracion ? \Carbon\Carbon::parse($contrato->fechaExpiracion)->addDay()->format('d/m/Y') : '-' }}
                    </td>
                    <td>
                        @php
                            $ultimaSolicitud = $contrato->vacaciones->solicitudes->sortByDesc('created_at')->first();
                        @endphp
                        {{ $ultimaSolicitud && $ultimaSolicitud->f_inicio_deseado 
                            ? \Carbon\Carbon::parse($ultimaSolicitud->f_inicio_deseado)->format('d/m/Y') 
                            : '-' }}
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
