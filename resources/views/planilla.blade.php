<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Planilla {{ $periodo }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            /* más compacto */
            margin: 15px;
            color: #333;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 8px;
        }

        .header img {
            height: 50px;
        }

        .header h2 {
            font-size: 14px;
            color: #1f2937;
            margin: 0;
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 4px;
            /* más compacto */
            text-align: center;
            white-space: nowrap;
            /* evita salto de línea */
            overflow: hidden;
            text-overflow: ellipsis;
        }

        th {
            background: #db9841;
            color: #fff;
            font-size: 11px;
        }

        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        tbody tr:hover {
            background: #eef2ff;
        }

        .total-row {
            background: #f3f4f6;
            font-weight: bold;
        }

        .total-label {
            text-align: right;
            padding-right: 8px;
        }

        .footer {
            margin-top: 15px;
            font-size: 9px;
            text-align: center;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="header">
        <div>
            <img src="{{ public_path('images/logo.png') }}" alt="Logo">
        </div>
        <div>
            <h2>Planilla - Nómina de Trabajadores<br>
                {{ \Carbon\Carbon::parse($periodo)->format('d/m/Y') }}</h2>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 2%">#</th>
                <th style="width: 24%">Inspector</th>
                <th style="width: 12%">Cargo</th>
                <th style="width: 9%">Fecha Ingreso</th>
                <th style="width: 9%">Sueldo Neto</th>
                <th style="width: 9%">Sueldo Base</th>
                <th style="width: 10%">N° Tarjeta</th>
                <th style="width: 15%">Observación</th>
                <th style="width: 10%">Total Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detalles as $i => $d)
                @php
                    $nombre = optional(optional($d->contrato)->empleado)->name
                            ?? optional($d->usuario)->name
                            ?? 'N/A';

                    $cargo = optional($d->contrato)->cargo ?? 'Apoyo Eventual';
                    $fechaIngreso = optional($d->contrato)->fechaInicio ?? '-';
                    $sueldoNeto = optional($d->contrato)->sueldo_neto;
                    $numeroCuenta = optional(optional($d->contrato)->empleado)->numero_cuenta
                        ?? optional($d->usuario)->numero_cuenta
                        ?? '-';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    {{-- Nombre inspector --}}
                    <td style="text-align: left;">{{ $nombre }}</td>
                    {{-- Cargo --}}
                    <td>{{ $cargo }}</td>
                    {{-- Fecha ingreso --}}
                    <td>{{ $fechaIngreso }}</td>
                    {{-- Sueldo neto --}}
                    <td>{{ $sueldoNeto ? 'S/. ' . number_format($sueldoNeto, 2) : '-' }}</td>
                    {{-- Sueldo base (siempre viene del detalle) --}}
                    <td>S/. {{ number_format($d->sueldo_base, 2) }}</td>
                    {{-- N° Tarjeta --}}
                    <td>{{ $numeroCuenta }}</td>
                    {{-- Observación --}}
                    <td>{{ $d->observacion }}</td>
                    {{-- Total pago --}}
                    <td><strong>S/. {{ number_format($d->total_pago, 2) }}</strong></td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="8" class="total-label">Total Planilla:</td>
                <td><strong>S/. {{ number_format($total, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Documento generado automáticamente - {{ config('app.name') }}
    </div>
</body>

</html>
