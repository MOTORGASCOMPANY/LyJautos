<!DOCTYPE html>
<html>

<head>
    <title>CARTA ACLARATORIA</title>
    <style>
        @page {
            margin: 0cm 0cm;
            font-family: sans-serif;
        }

        body {
            margin: 1cm 2cm 2cm;
            display: block;
        }

        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 3.5cm;
            color: black;
            font-weight: bold;
            text-align: center;
        }



        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            color: black;
            text-align: center;
            line-height: 35px;

        }

        p {
            font-size: 12px;
        }

        image {
            margin-left: 2cm;
        }

        h3 {
            margin-top: 3cm;
            color: black;
            text-align: center;
        }

        h4 {
            font-size: 14px;
            text-align: center;
        }

        h5 {
            text-align: right;
        }

        h6 {
            margin-bottom: -10px;
        }

        table,
        th,
        td {
            font-size: 10px;
            border: 1px solid;
            border-collapse: collapse;
        }

        table {
            width: 100%;
        }

        ol {
            list-style-type: lower-latin;
            font-size: 10px;
        }

        ul {
            font-size: 10px;
        }
    </style>
</head>

<body>
    <header>

    </header>
    <main>
        <br>
        <br>
        <br>
        <br>
        <br>
        <p>Lima, {{ $fecha }}</p>
        <p>Señores:</p>
        <p style="font-weight: bold; text-decoration: underline;">SUNARP</p>
        <p>
            Referencia: <span style="margin-left: 30px;">CARTA ACLARATORIA</span> <br>
            Título N°: <span style="margin-left: 42px;">{{ $certi->titulo }}</span> <br>
            Partida N°: <span style="margin-left: 34px;">{{ $certi->partida }}</span> <br>
            Placa: <span style="margin-left: 60px;">{{ $certi->placa }}</span>
        </p>
        <p>Presente.-</p>
        <p style="text-align: justify;">
            De mi especial consideración; <br>
            <strong>Yo LESLY PAMELA EGOAVIL LOMOTE,</strong> con <strong>DNI N°</strong> 74760461 en calidad de Gerente
            General de la
            empresa <strong>MOTOR GAS COMPANY</strong> con <strong>RUC N°</strong> 20472634501 con dirección en Jr. San
            Pedro de Carabayllo
            N° 180 Urb. Santa Isabel Distrito de Carabayllo, Provincia y Departamento de Lima, tengo el gusto de
            dirigirme a su distinguida
            persona para manifestarle lo siguiente:
        </p>
        <p style="text-align: justify;">
            Que mi representada es una Entidad Certificadora de
            @if ($certi->tipo == 'FORMATO GNV')
                Conversiones de Gas Natural Vehicular, autorizada Resolución Directoral <strong>N°
                    0321-2023-MTC/17.03</strong>, en este sentido les hacemos llegar la <strong>CARTA
                    ACLARATORIA</strong>, del Título N°
                {{ $certi->titulo . ', Partida N° ' . $certi->partida . ' y PLACA: ' . $certi->placa }}.
            @elseif ($certi->tipo == 'FORMATO GLP')
                Conversiones de Gas Licuado de Petróleo, autorizada Resolución Directoral <strong>N°
                    0464-2023-MTC/17.03</strong>, en este sentido les hacemos llegar la <strong>CARTA
                    ACLARATORIA</strong>, del Título N°
                {{ $certi->titulo . ', Partida N° ' . $certi->partida . ' y PLACA: ' . $certi->placa }}.
            @elseif ($certi->tipo == 'MODIFICACION')
                conformidad de modificación, montaje y fabricación autorizada con Resolución Directoral N°
                014-2022.MTC/17.03, en este sentido les hacemos llegar la <strong>CARTA ACLARATORIA</strong>,
                del Título N° {{ $certi->titulo . ', Partida N° ' . $certi->partida . ' - ' . $certi->placa }}.
            @else
            @endif
        </p>

        @if ($certi->parrafo != 1)
            <p>
                @if ($certi->tipo == 'FORMATO GNV')
                    Es cierto que, en el certificado de Conformidad de conversión a GNV, hubo un error de digitación.
                @elseif ($certi->tipo == 'FORMATO GLP')
                    Es cierto que, en el certificado de Conformidad de conversión a GLP, hubo un error de digitación.
                @elseif ($certi->tipo == 'MODIFICACION')
                    En el certificado de Conformidad de Modificación, hubo un error tipográfico.
                @else
                @endif
            </p>
        @endif

        @if ($certi->parrafo != 1)
            <p>DICE:</p>
        @endif

        @if (is_array($diceData) && count($diceData) > 0)
            <table style="width: 70%;">
                @foreach ($diceData as $data)
                    <tr>
                        <td style="padding: 0 5px 0 5px; text-align:center; width: 5%;">{{ $data['numero'] }}</td>
                        <td style="padding: 0 5px 0 5px; width: 20%;">{{ $data['titulo'] }}</td>
                        <td style="padding: 0 5px 0 5px; width: 75%;">{{ $data['descripcion'] }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if (is_array($dicemod) && count($dicemod) > 0)
            @foreach ($dicemod as $data)
                <p>{{ $data }}</p>
            @endforeach
        @endif

        @if ($certi->parrafo != 1)
            <p>DEBE DECIR:</p>
        @endif

        @if (is_array($debeDecirData) && count($debeDecirData) > 0)
            <table style="width: 70%;">
                @foreach ($debeDecirData as $data)
                    <tr>
                        <td style="padding: 0 5px 0 5px; text-align:center; width: 5%;">{{ $data['numero'] }}</td>
                        <td style="padding: 0 5px 0 5px; width: 20%;">{{ $data['titulo'] }}</td>
                        <td style="padding: 0 5px 0 5px; width: 75%;">{{ $data['descripcion'] }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
        @if (is_array($debeDecirmod) && count($debeDecirmod) > 0)
            @foreach ($debeDecirmod as $data)
                <p>{{ $data }}</p>
            @endforeach
        @endif

        <p>Sin otro particular, me despido.</p>
        <p>Atentamente;</p>


    </main>

    <footer>

    </footer>
</body>

</html>
