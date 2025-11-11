<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante Contado</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .details {
            /*margin-bottom: 5px;*/
        }

        .details p {
            margin: 0;
        }

        .materials {
            clear: both;
            margin-bottom: 20px;
        }

        .materials p {
            margin: 0;
        }

        .materials ol {
            padding-left: 20px;
            margin-top: 10px;
        }

        .materials ol li {
            margin-bottom: 5px;
        }

        .images-container {
            display: flex;
            text-align: center;
            justify-content: center; /* Centrar imágenes */
            align-items: center; /* Alinear verticalmente */
            gap: 20px; /* Espaciado entre imágenes */
            flex-wrap: nowrap; /* Evita que las imágenes se apilen si caben en la pantalla */
            margin-top: 130px;
        }

        .images-container img {
            max-width: 45%; /* Controla el tamaño para que no sobrepasen el contenedor */
            height: auto; /* Mantiene la proporción */
            object-fit: contain; /* Ajusta el contenido sin distorsionarlo */
        }

        .observations {
            margin-top: 20px;
            font-style: italic;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Comprobante de Formatos al Contado</h1>

        <div class="details">
            <p><strong>Inspector:</strong> {{ $contado->salida->usuarioAsignado->name }}</p>
            <p><strong>Fecha de entrega:</strong>
                {{ $contado->created_at ? $contado->created_at->format('d/m/Y') : 'Fecha no disponible' }}</p>
        </div>
        <div class="materials">
            <p><strong>Materiales enviados:</strong></p>
            <ol>
                {{-- 
                @foreach ($materialesAgrupados as $index => $grupo)
                    <li>{{ $grupo['tipo'] }} - ({{ $grupo['menor'] }} - {{ $grupo['mayor'] }})</li>
                @endforeach
                --}}
                @foreach ($materialesAgrupados as $grupo)
                    @if ($grupo['menor'] === 'Sin Serie' && $grupo['mayor'] === 'Sin Serie')
                        <li> {{ $grupo['tipo'] }} - ({{ $grupo['cantidad'] }} {{ $grupo['cantidad'] === 1 ? 'unidad' : 'unidades' }}) </li>                    
                    @else
                        <li>{{ $grupo['tipo'] }} - ({{ $grupo['menor'] }} - {{ $grupo['mayor'] }})</li>
                    @endif
                @endforeach
            </ol>
        </div>

        @if ($documentos->isNotEmpty())
            <div class="images-container">
                @foreach ($documentos as $index => $doc)
                    <img {{--src="{{ public_path('storage/docsContados/' . basename($doc->ruta)) }}"--}} src="{{ $doc->url }}" alt="{{ $doc->nombre }}"                        
                        style="width: 260px; height: {{ $doc->nombre === 'estado de cuenta' ? '100px' : '400px' }};">
                @endforeach
            </div>
        @else
            <p>No hay comprobantes disponibles.</p>
        @endif

        @if (!empty($contado->observacion))
            <div class="observations">
                {!! nl2br(e($contado->observacion)) !!}
            </div>
        @endif
    </div>
</body>

</html>
