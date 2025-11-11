@component('mail::message')
    # Hola {{ $inspector->name }},

    El sistema ha detectado que tienes **expedientes con fotos incompletas** correspondientes a la **semana pasada (de lunes
    a domingo)**.

    A continuación se detallan los expedientes pendientes:

    @foreach ($expedientes as $item)
        @php
            $exp = $item['expediente'];
            $faltantes = implode(', ', $item['faltantes_codigos']);
        @endphp

        ---

        **Placa:** {{ $exp->placa }}
        **Certificado:** {{ $exp->certificado ?? 'N/A' }}
        **Faltantes:** {{ $faltantes }}
    @endforeach

    ---

    @component('mail::button', ['url' => config('app.url')])
        Ir al Sistema
    @endcomponent

    Gracias por mantener la información completa.
    **{{ config('app.name') }}**
@endcomponent
